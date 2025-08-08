<?php
// Standalone script to be run by a cron job

// --- 1. Setup & Bootstrap ---
if (!defined('FCPATH')) {
    // This block only runs when executed from CLI
    $root_path = realpath(__DIR__ . '/..');
    define('FCPATH', $root_path . '/');
    define('BASEPATH', FCPATH . 'system/');
    define('APPPATH', FCPATH . 'application/');
    define('ENVIRONMENT', $_ENV['CI_ENV'] ?? 'production');
    chdir(FCPATH);
    require_once BASEPATH . 'core/CodeIgniter.php';
}

// --- 2. Lock Mechanism ---
$lock_file = APPPATH . 'logs/broadcast.lock';
if (file_exists($lock_file)) {
    if (time() - filemtime($lock_file) > 900) {
        unlink($lock_file);
        echo "Removed stale lock file.\n";
    } else {
        echo "Broadcast process is already running. Exiting.\n";
        exit;
    }
}
file_put_contents($lock_file, getmypid());
register_shutdown_function(function() use ($lock_file) {
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
});

// --- 3. Main Logic ---
try {
    $CI =& get_instance();
    $CI->load->model('BotModel');
    $CI->load->model('BroadcastModel');
    $CI->load->model('UserModel');
    $CI->load->model('Settings_model');
    $CI->load->model('Log_model');
    require_once FCPATH . 'bot/ApiClient.php';

    // This variable is set when the script is included by Cron.php
    $run_for_specific_bot = isset($target_bot_id) ? (int)$target_bot_id : 0;

    if ($run_for_specific_bot > 0) {
        // Run for a single bot
        echo "Running for specific bot ID: $run_for_specific_bot\n";
        $bot = $CI->BotModel->getBotById($run_for_specific_bot); // Assumes getBotById exists
        if ($bot) {
            process_broadcasts_for_bot($CI, $bot);
        } else {
            echo "Bot with ID $run_for_specific_bot not found.\n";
        }
    } else {
        // Run for all bots (CLI context)
        echo "Running for all bots...\n";
        $all_bots = $CI->BotModel->getAllBots();
        foreach ($all_bots as $bot) {
            echo "\n--- Checking Bot: {$bot['name']} (ID: {$bot['id']}) ---\n";
            process_broadcasts_for_bot($CI, $bot);
        }
        echo "\n--- All bots checked. ---\n";
    }

} catch (Throwable $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    log_message('error', 'Cron Job Failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
}

// The main processing logic for one bot
function process_broadcasts_for_bot($CI, $bot) {
    $bot_id = $bot['id'];
    $bot_api_token = $bot['token'];

    // Update timestamp for bot health check
    $CI->Settings_model->save_setting('last_cron_run', date('Y-m-d H:i:s'), $bot_id);

    $apiClient = new ApiClient($bot_api_token, $CI->Log_model, $bot_id);
    $batch_size = 25;

    $broadcast = $CI->BroadcastModel->get_job_to_process($bot_id); // Needs update

    if (!$broadcast) {
        echo "No pending broadcasts to process for this bot.\n";
        return;
    }

    echo "Found job #{$broadcast['id']}. Status: {$broadcast['status']}.\n";
    if ($broadcast['status'] === 'pending') {
        $CI->BroadcastModel->mark_as_processing($broadcast['id']);
        echo "Marked job #{$broadcast['id']} as processing.\n";
    }

    $offset = (int)$broadcast['sent_count'] + (int)$broadcast['failed_count'];
    $users = $CI->UserModel->getActiveUsersBatch($bot_id, $batch_size, $offset, $broadcast['target_tag'], $broadcast['is_test_broadcast']);

    if (empty($users)) {
        $CI->BroadcastModel->mark_as_completed($broadcast['id']);
        echo "Broadcast #{$broadcast['id']} completed. No more active users.\n";
        return;
    }

    echo "Processing broadcast #{$broadcast['id']}. Batch of " . count($users) . " users.\n";
    $current_sent = 0;
    $current_failed = 0;

    foreach ($users as $user) {
        try {
            $apiClient->sendMessage($user['chat_id'], $broadcast['message']);
            $current_sent++;
        } catch (Exception $e) {
            $current_failed++;
            $errorMessage = $e->getMessage();
            $log_message = "Broadcast #{$broadcast['id']} to user {$user['chat_id']} failed: " . $errorMessage;
            $CI->Log_model->add_log('error', $log_message, $user['chat_id'], $bot_id);
            $CI->BroadcastModel->update_error_message($broadcast['id'], $log_message);

            if (stripos($errorMessage, 'bot was blocked') !== false) {
                $CI->UserModel->markUserAsBanned($user['chat_id'], $bot_id);
            }
        }
        usleep(120000);
    }

    $CI->BroadcastModel->update_stats($broadcast['id'], $current_sent, $current_failed);
    echo "Batch complete. Sent: {$current_sent}, Failed: {$current_failed}.\n";

    $updated_broadcast = $CI->BroadcastModel->get_broadcast($broadcast['id'], $bot_id);
    if (($updated_broadcast['sent_count'] + $updated_broadcast['failed_count']) >= $updated_broadcast['total_recipients']) {
        $CI->BroadcastModel->mark_as_completed($broadcast['id']);
        echo "Broadcast #{$broadcast['id']} fully completed.\n";
    }
}

exit(0);

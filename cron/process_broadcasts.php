<?php
// Standalone script to be run by a cron job

// --- 1. Setup & Bootstrap ---
// Prevent direct web access.
if (php_sapi_name() !== 'cli') {
    exit('Access Denied: This script can only be run from the command line.');
}

// Set up paths
$root_path = realpath(__DIR__ . '/..');
define('FCPATH', $root_path . '/');
define('BASEPATH', FCPATH . 'system/');
define('APPPATH', FCPATH . 'application/');
define('ENVIRONMENT', $_ENV['CI_ENV'] ?? 'production');

// Bootstrap CodeIgniter
chdir(FCPATH);
require_once BASEPATH . 'core/CodeIgniter.php';

// --- 2. Lock Mechanism ---
$lock_file = APPPATH . 'logs/broadcast.lock';
if (file_exists($lock_file)) {
    // If lock file is older than 15 minutes, it might be stale from a crashed process.
    if (time() - filemtime($lock_file) > 900) {
        unlink($lock_file);
        echo "Removed stale lock file.\n";
    } else {
        echo "Broadcast process is already running. Exiting.\n";
        exit;
    }
}
file_put_contents($lock_file, getmypid());

// Ensure the lock file is removed on exit, success or failure
register_shutdown_function(function() use ($lock_file) {
    if (file_exists($lock_file)) {
        unlink($lock_file);
    }
});

// --- 3. Main Logic ---
try {
    $CI =& get_instance();
    $CI->load->model('BroadcastModel');
    $CI->load->model('UserModel');
    $CI->load->model('Settings_model');
    $CI->load->model('Log_model');

    require_once FCPATH . 'bot/ApiClient.php';

    $botToken = $CI->Settings_model->get_setting('bot_token');
    if (empty($botToken)) {
        throw new Exception('Bot token not found in settings.');
    }
    $apiClient = new ApiClient($botToken, $CI->Log_model);

    $batch_size = 25; // Number of users to process per run

    // Prioritize 'processing' jobs, then 'pending'
    $broadcast = $CI->BroadcastModel->get_job_to_process();

    if (!$broadcast) {
        echo "No pending broadcasts to process.\n";
        exit;
    }

    echo "Found job #{$broadcast['id']}. Status: {$broadcast['status']}.\n";

    // If it was pending, mark as processing
    if ($broadcast['status'] === 'pending') {
        $CI->BroadcastModel->mark_as_processing($broadcast['id']);
        echo "Marked job #{$broadcast['id']} as processing.\n";
    }

    // Get the next batch of users based on targeting
    $offset = (int)$broadcast['sent_count'] + (int)$broadcast['failed_count'];
    $users = $CI->UserModel->getActiveUsersBatch(
        $batch_size,
        $offset,
        $broadcast['target_tag'],
        $broadcast['is_test_broadcast']
    );

    if (empty($users)) {
        // No more users to process, mark as complete
        $CI->BroadcastModel->mark_as_completed($broadcast['id']);
        echo "Broadcast #{$broadcast['id']} completed. No more active users in the queue.\n";
        exit;
    }

    echo "Processing broadcast #{$broadcast['id']}. Batch of " . count($users) . " users, starting from offset {$offset}.\n";

    $current_sent = 0;
    $current_failed = 0;

    foreach ($users as $user) {
        try {
            $apiClient->sendMessage($user['chat_id'], $broadcast['message']);
            $current_sent++;
            echo " -> Sent to {$user['chat_id']}\n";
        } catch (Exception $e) {
            $current_failed++;
            $errorMessage = $e->getMessage();
            echo " -> Failed for {$user['chat_id']}: {$errorMessage}\n";

            // Log the specific error to the main log table and the broadcast job
            $log_message = "Broadcast #{$broadcast['id']} to user {$user['chat_id']} failed: " . $errorMessage;
            $CI->Log_model->add_log('error', $log_message, $user['chat_id']);
            $CI->BroadcastModel->update_error_message($broadcast['id'], $log_message);

            // Check if the user blocked the bot
            if (stripos($errorMessage, 'forbidden') !== false && stripos($errorMessage, 'bot was blocked') !== false) {
                $CI->UserModel->markUserAsBanned($user['chat_id']);
                echo " --> User {$user['chat_id']} marked as banned.\n";
            }
        }
        usleep(120000); // 120ms delay to stay well within rate limits
    }

    // Update stats for this run
    $CI->BroadcastModel->update_stats($broadcast['id'], $current_sent, $current_failed);
    echo "Batch complete. Sent: {$current_sent}, Failed: {$current_failed}.\n";

    // Final check if this batch completed the job
    $updated_broadcast = $CI->BroadcastModel->get_broadcast($broadcast['id']);
    if (($updated_broadcast['sent_count'] + $updated_broadcast['failed_count']) >= $updated_broadcast['total_recipients']) {
        $CI->BroadcastModel->mark_as_completed($broadcast['id']);
        echo "Broadcast #{$broadcast['id']} fully completed.\n";
    } else {
        echo "Broadcast #{$broadcast['id']} batch processed. More users remaining.\n";
    }

} catch (Throwable $e) {
    echo "An error occurred: " . $e->getMessage() . "\n";
    // Log any fatal errors
    if (isset($CI) && isset($CI->Log_model)) {
        $CI->Log_model->add_log('error', 'Cron Job Failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    } else {
        error_log('Cron Job Failed (CI might not be loaded): ' . $e->getMessage());
    }
}

// Lock is released by the shutdown function
exit(0);

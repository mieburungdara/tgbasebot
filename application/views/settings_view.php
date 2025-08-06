<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Pengaturan Bot</title>
	<style type="text/css">
	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }
	body { background-color: #fff; margin: 40px; font: 13px/20px normal Helvetica, Arial, sans-serif; color: #4F5155; }
	a { color: #003399; background-color: transparent; font-weight: normal; text-decoration: none; }
	a:hover { color: #97310e; }
	h1 { color: #444; background-color: transparent; border-bottom: 1px solid #D0D0D0; font-size: 19px; font-weight: normal; margin: 0 0 14px 0; padding: 14px 15px 10px 15px; }
	code { font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; background-color: #f9f9f9; border: 1px solid #D0D0D0; color: #002166; display: block; margin: 14px 0 14px 0; padding: 12px 10px 12px 10px; }
	#body { margin: 0 15px 0 15px; min-height: 96px; }
	p { margin: 0 0 10px; padding:0; }
	#container { margin: 10px; border: 1px solid #D0D0D0; box-shadow: 0 0 8px #D0D0D0; }
    .message { padding: 10px; margin-bottom: 15px; border-radius: 5px; }
    .success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    input[type="text"] { width: 95%; padding: 8px; margin-bottom: 10px; }
    .btn { padding: 10px 15px; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
    .btn-primary { background-color: #007bff; }
    .btn-secondary { background-color: #6c757d; }
    .btn-action { background-color: #E13300; }
	</style>
</head>
<body>

<div id="container">
	<div style="padding: 10px 15px; border-bottom: 1px solid #D0D0D0; background-color: #f2f2f2;">
		<a href="/index.php/welcome">Home</a> |
		<a href="/index.php/dashboard"><strong>Log Dashboard</strong></a> |
		<a href="/index.php/settings"><strong>Pengaturan Bot</strong></a>
	</div>
	<h1>Pengaturan Bot Telegram</h1>

	<div id="body">
        <?php if (!empty($success_message)): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <h2>Token Bot</h2>
        <p>Masukkan token bot Telegram Anda di bawah ini.</p>
        <?php echo form_open(site_url('settings')); ?>
            <input type="text" name="bot_token" placeholder="Masukkan token bot Anda" value="<?php echo htmlspecialchars($bot_token ?? ''); ?>">
            <button type="submit" name="save_token" value="save" class="btn btn-primary">Simpan Token</button>
        <?php echo form_close(); ?>

        <hr style="margin: 20px 0;">

        <h2>Webhook</h2>
        <p>Webhook adalah mekanisme di mana Telegram akan mengirim pembaruan (seperti pesan baru) ke server Anda.</p>
        <p>URL Webhook untuk bot Anda adalah: <code><?php echo site_url('bot/index.php', 'https'); ?></code></p>
        <p>Klik tombol di bawah ini untuk mengatur URL ini secara otomatis di server Telegram. Pastikan token bot sudah disimpan dengan benar.</p>
        <a href="<?php echo site_url('settings/set_webhook'); ?>" class="btn btn-primary">Set Webhook</a>

        <hr style="margin: 20px 0;">

        <h2>Manajemen Webhook</h2>
        <p>Gunakan tombol di bawah untuk memeriksa atau menghapus konfigurasi webhook Anda saat ini.</p>
        <a href="<?php echo site_url('settings/get_webhook_info'); ?>" class="btn btn-secondary">Cek Info Webhook</a>
        <a href="<?php echo site_url('settings/delete_webhook'); ?>" class="btn btn-action" onclick="return confirm('Anda yakin ingin menghapus webhook?');">Hapus Webhook</a>

        <?php if (!empty($bot_token)): ?>
            <p style="margin-top: 10px;">Atau, Anda dapat <a href="https://api.telegram.org/bot<?php echo htmlspecialchars($bot_token); ?>/getWebhookInfo" target="_blank" rel="noopener noreferrer">memeriksa langsung dari API Telegram</a> (membuka tab baru).</p>
        <?php endif; ?>

        <?php if (!empty($webhook_info)): ?>
        <div style="margin-top: 20px;">
            <h3>Informasi Webhook Saat Ini:</h3>
            <pre style="background-color: #f9f9f9; border: 1px solid #D0D0D0; padding: 10px; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word;"><code><?php echo htmlspecialchars(json_encode($webhook_info, JSON_PRETTY_PRINT)); ?></code></pre>
        </div>
        <?php endif; ?>
	</div>
</div>

</body>
</html>

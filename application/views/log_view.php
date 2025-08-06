<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf--8">
	<title>Dasbor Log Bot</title>
	<style type="text/css">
	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }
	body { background-color: #fff; margin: 40px; font: 13px/20px normal Helvetica, Arial, sans-serif; color: #4F5155; }
	h1 { color: #444; background-color: transparent; border-bottom: 1px solid #D0D0D0; font-size: 19px; font-weight: normal; margin: 0 0 14px 0; padding: 14px 15px 10px 15px; }
	#body { margin: 0 15px 0 15px; min-height: 96px; }
	#container { margin: 10px; border: 1px solid #D0D0D0; box-shadow: 0 0 8px #D0D0D0; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 8px 12px; border: 1px solid #D0D0D0; text-align: left; }
    th { background-color: #f2f2f2; }
    .log-type { padding: 3px 6px; border-radius: 4px; color: white; font-size: 11px; text-transform: uppercase; }
    .log-incoming { background-color: #28a745; }
    .log-outgoing { background-color: #007bff; }
    .log-error { background-color: #dc3545; }
    .log-message { font-family: Consolas, Monaco, Courier New, Courier, monospace; font-size: 12px; white-space: pre-wrap; word-wrap: break-word; }
	</style>
    <meta http-equiv="refresh" content="30"> <!-- Auto-refresh halaman setiap 30 detik -->
</head>
<body>

<div id="container">
	<div style="padding: 10px 15px; border-bottom: 1px solid #D0D0D0; background-color: #f2f2f2;">
		<a href="/index.php/welcome">Home</a> |
		<a href="/index.php/dashboard"><strong>Log Dashboard</strong></a> |
		<a href="/index.php/settings"><strong>Pengaturan Bot</strong></a>
	</div>
	<h1>Dasbor Log Bot</h1>

	<div id="body">
        <?php if (empty($logs)): ?>
            <p>Belum ada aktivitas log yang tercatat.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">Timestamp</th>
                        <th style="width: 10%;">Tipe</th>
                        <th>Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo $log['created_at']; ?></td>
                            <td>
                                <?php
                                    $type_class = 'log-' . strtolower($log['log_type']);
                                    echo '<span class="log-type ' . $type_class . '">' . htmlspecialchars($log['log_type']) . '</span>';
                                ?>
                            </td>
                            <td class="log-message"><?php echo htmlspecialchars($log['log_message']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
	</div>
</div>

</body>
</html>

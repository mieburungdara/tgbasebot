<?php $this->load->view('templates/header', ['title' => 'Pengaturan']); ?>

<h1 class="mb-4">Pengaturan</h1>

<?php if ($this->session->flashdata('success_message')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success_message'); ?></div>
<?php endif; ?>
<?php if ($this->session->flashdata('error_message')): ?>
    <div class="alert alert-danger"><?= $this->session->flashdata('error_message'); ?></div>
<?php endif; ?>

<?php if (!$selected_bot): ?>
    <div class="alert alert-warning">
        Silakan <a href="<?= site_url('bot_management') ?>">tambahkan bot</a> atau pilih bot dari menu di atas untuk mengelola pengaturan spesifik bot.
    </div>
<?php else: ?>
    <h2 class="h3 mb-3">Pengaturan untuk Bot: <?= html_escape($selected_bot['name']); ?></h2>
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Detail Bot</div>
                <div class="card-body">
                    <p><strong>Nama Bot:</strong> <?= html_escape($selected_bot['name']); ?></p>
                    <p><strong>Token API:</strong> <code id="bot-token" data-token="<?= html_escape($selected_bot['token']); ?>">************</code> <button id="toggle-token-btn" class="btn btn-sm btn-outline-secondary ms-2"><i class="bi bi-eye"></i></button></p>
                    <a href="<?= site_url('bot_management/edit/' . $selected_bot['id']) ?>" class="btn btn-primary">Edit Nama & Token</a>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">Manajemen Webhook</div>
                <div class="card-body">
                    <p>URL Webhook untuk bot ini adalah:</p>
                    <input type="text" class="form-control" value="<?= site_url('bot/webhook/' . $selected_bot['webhook_token']) ?>" readonly>
                    <hr>
                    <p>Gunakan tombol di bawah untuk mengelola webhook di server Telegram.</p>
                    <a href="<?= site_url('settings/set_webhook'); ?>" class="btn btn-success">Set Webhook</a>
                    <a href="<?= site_url('settings/delete_webhook'); ?>" class="btn btn-danger" onclick="return confirm('Anda yakin ingin menghapus webhook?');">Hapus Webhook</a>
                    <a href="<?= site_url('settings/get_webhook_info'); ?>" class="btn btn-info">Cek Info Webhook</a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('webhook_info')): ?>
    <div class="card">
        <div class="card-header">Informasi Webhook Saat Ini</div>
        <div class="card-body">
            <pre style="background-color: #e9ecef; border: 1px solid #D0D0D0; padding: 10px; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word;"><code><?= html_escape($this->session->flashdata('webhook_info')); ?></code></pre>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<hr class="my-4">

<h2 class="h3 mb-3">Pengaturan Database</h2>
<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="bi bi-exclamation-triangle-fill"></i> Zona Berbahaya
            </div>
            <div class="card-body">
                <p>Menekan tombol ini akan <strong>menghapus semua data</strong> dan mengatur ulang database ke status awal. Semua bot, pengguna, log, dan riwayat siaran akan hilang secara permanen.</p>
                <p class="text-danger">Tindakan ini tidak dapat diurungkan. Lanjutkan dengan hati-hati.</p>
                <a href="<?= site_url('settings/forge_database'); ?>" class="btn btn-danger" onclick="return confirm('PERINGATAN: Anda akan menghapus SEMUA data di database. Apakah Anda benar-benar yakin ingin melanjutkan?');">
                    <i class="bi bi-trash-fill"></i> Forge Database
                </a>
            </div>
        </div>
    </div>
</div>


<script>
    // Toggle Bot Token Visibility
    const toggleTokenBtn = document.getElementById('toggle-token-btn');
    const tokenSpan = document.getElementById('bot-token');
    if (toggleTokenBtn && tokenSpan) {
        let tokenVisible = false;
        toggleTokenBtn.addEventListener('click', () => {
            tokenVisible = !tokenVisible;
            if (tokenVisible) {
                tokenSpan.textContent = tokenSpan.dataset.token;
                toggleTokenBtn.innerHTML = '<i class="bi bi-eye-slash-fill"></i>';
            } else {
                tokenSpan.textContent = '************';
                toggleTokenBtn.innerHTML = '<i class="bi bi-eye-fill"></i>';
            }
        });
    }
</script>

<?php $this->load->view('templates/footer'); ?>

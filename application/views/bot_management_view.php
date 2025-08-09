<?php $this->load->view('templates/header'); ?>

        <div class="row">
            <div class="col-lg-4">
                <h1 class="h3 mb-4">Tambah Bot Baru</h1>
                <div class="card">
                    <div class="card-body">
                        <?= form_open('bot_management/add') ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Bot</label>
                                <input type="text" name="name" class="form-control" value="<?= set_value('name') ?>" required>
                                <?= form_error('name', '<div class="text-danger small">', '</div>') ?>
                            </div>
                            <div class="mb-3">
                                <label for="token" class="form-label">Token API Telegram</label>
                                <input type="text" name="token" class="form-control" value="<?= set_value('token') ?>" required>
                                <?= form_error('token', '<div class="text-danger small">', '</div>') ?>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Bot</button>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <h1 class="h3 mb-4">Daftar Bot</h1>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama</th>
                                        <th>Webhook URL</th>
                                        <th>Cron Key</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($bots)): foreach ($bots as $bot): ?>
                                    <tr>
                                        <td><?= $bot['id'] ?></td>
                                        <td><?= html_escape($bot['name']) ?></td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control" value="<?= site_url('bot/webhook/' . $bot['webhook_token']) ?>" readonly>
                                                <button class="btn btn-outline-secondary btn-copy" type="button"><i class="bi bi-clipboard"></i></button>
                                            </div>
                                        </td>
                                        <td>
                                            <?php $key = $bot['cron_secret_key'] ?? null; ?>
                                            <?php if ($key): ?>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control cron-key-input" value="************" data-key="<?= html_escape($key) ?>" readonly>
                                                    <button class="btn btn-outline-secondary btn-toggle-key" type="button"><i class="bi bi-eye"></i></button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted small">Not Set</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('bot_management/edit/' . $bot['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit Bot"><i class="bi bi-pencil"></i></a>
                                            <a href="<?= site_url('bot_management/reset_cron_key/' . $bot['id']) ?>" class="btn btn-sm btn-outline-warning" title="Reset Cron Key" onclick="return confirm('Yakin ingin mereset kunci untuk bot ini?')"><i class="bi bi-arrow-clockwise"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada bot yang ditambahkan.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Copy to clipboard for webhook URLs
    document.querySelectorAll('.btn-copy').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            input.select();
            document.execCommand('copy');
            // Optional: feedback to user
            const originalIcon = this.innerHTML;
            this.innerHTML = '<i class="bi bi-check-lg"></i>';
            setTimeout(() => { this.innerHTML = originalIcon; }, 1500);
        });
    });

    // Toggle visibility for cron keys
    document.querySelectorAll('.btn-toggle-key').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password' || input.value === '************') {
                input.type = 'text';
                input.value = input.dataset.key;
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'text'; // Keep it as text so '************' is visible
                input.value = '************';
                icon.className = 'bi bi-eye';
            }
        });
    });
});
</script>

<?php $this->load->view('templates/footer'); ?>

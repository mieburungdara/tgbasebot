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
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($bots)): foreach ($bots as $bot): ?>
                                    <tr>
                                        <td><?= $bot['id'] ?></td>
                                        <td><?= html_escape($bot['name']) ?></td>
                                        <td><code class="webhook-url"><?= site_url('bot/webhook/' . $bot['webhook_token']) ?></code></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="3" class="text-center">Belum ada bot yang ditambahkan.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<?php $this->load->view('templates/footer'); ?>

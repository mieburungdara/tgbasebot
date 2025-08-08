<?php $this->load->view('templates/header'); ?>

<div class="container" style="max-width: 720px;">
    <h1 class="mb-4">Edit Bot: <?= html_escape($bot['name']) ?></h1>
    <div class="card">
        <div class="card-body">
            <?= form_open('bot_management/update/' . $bot['id']) ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Bot</label>
                    <input type="text" name="name" class="form-control" value="<?= set_value('name', $bot['name']) ?>" required>
                    <?= form_error('name', '<div class="text-danger small">', '</div>') ?>
                </div>
                <div class="mb-3">
                    <label for="token" class="form-label">Token API Telegram</label>
                    <input type="text" name="token" class="form-control" value="<?= set_value('token', $bot['token']) ?>" required>
                    <?= form_error('token', '<div class="text-danger small">', '</div>') ?>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= site_url('bot_management') ?>" class="btn btn-secondary">Batal</a>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?php $this->load->view('templates/footer'); ?>

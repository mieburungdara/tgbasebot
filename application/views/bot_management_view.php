<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1140px; }
        .card-header { font-weight: 500; }
        .webhook-url { font-size: 0.85rem; word-break: break-all; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="<?= site_url('dashboard') ?>"><i class="bi bi-robot"></i> Bot Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard') ?>">Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/keywords') ?>">Keywords</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/broadcast') ?>">Broadcast</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('user_management') ?>">Users</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= site_url('bot_management') ?>">Bots</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

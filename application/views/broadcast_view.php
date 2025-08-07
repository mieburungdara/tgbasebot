<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Pesan Siaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 960px; }
        .card-header { font-weight: 500; }
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
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('dashboard') ?>">Logs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('dashboard/keywords') ?>">Keywords</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="<?= site_url('dashboard/broadcast') ?>">Broadcast</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Kirim Pesan Siaran</h1>

        <div class="card">
            <div class="card-header">
                Tulis Pesan
            </div>
            <div class="card-body">
                <?= form_open('dashboard/send_broadcast') ?>
                    <div class="mb-3">
                        <label for="message" class="form-label">Pesan</label>
                        <textarea class="form-control" name="message" id="message" rows="8" required></textarea>
                        <div class="form-text">
                            Pesan ini akan dikirim ke <strong>semua pengguna</strong> yang pernah berinteraksi dengan bot.
                            Anda dapat menggunakan format Markdown dasar (misalnya, *tebal*, _miring_).
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Apakah Anda yakin ingin mengirim pesan ini ke semua pengguna?')">
                        <i class="bi bi-send"></i> Kirim Siaran
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

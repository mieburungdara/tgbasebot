<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 720px; }
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
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard') ?>">Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/keywords') ?>">Keywords</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/broadcast') ?>">Broadcast</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= site_url('user_management') ?>">Users</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="mb-4">Edit Pengguna</h1>
        <div class="card">
            <div class="card-header">
                Mengedit: <?= html_escape(trim($user['first_name'] . ' ' . $user['last_name'])) ?> (@<?= html_escape($user['username']) ?>)
            </div>
            <div class="card-body">
                <?= form_open('user_management/update/' . $user['id']) ?>
                    <div class="mb-3">
                        <label for="tags" class="form-label">Tags (dipisahkan koma)</label>
                        <input type="text" class="form-control" name="tags" id="tags" value="<?= html_escape($user['tags']) ?>">
                        <div class="form-text">Contoh: premium, anggota_baru, prioritas</div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="is_test_user" id="is_test_user" value="1" <?= $user['is_test_user'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_test_user">
                            Jadikan Pengguna Tes
                        </label>
                        <div class="form-text">Siaran tes hanya akan dikirim ke pengguna ini.</div>
                    </div>
                    <hr>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="<?= site_url('user_management') ?>" class="btn btn-secondary">Batal</a>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

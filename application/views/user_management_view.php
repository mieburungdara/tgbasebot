<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1140px; }
        .card-header { font-weight: 500; }
        .status-badge { font-size: 0.9em; text-transform: capitalize; }
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
        <h1 class="mb-4">Manajemen Pengguna</h1>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Chat ID</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Status</th>
                                <th>Tags</th>
                                <th>Test User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><small class="text-muted"><?= $user['chat_id'] ?></small></td>
                                <td><?= html_escape(trim($user['first_name'] . ' ' . $user['last_name'])) ?></td>
                                <td><?= $user['username'] ? '@' . html_escape($user['username']) : '' ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'danger' ?> status-badge"><?= $user['status'] ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($user['tags'])): ?>
                                        <?php foreach(explode(',', $user['tags']) as $tag): ?>
                                            <span class="badge bg-secondary"><?= html_escape(trim($tag)) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($user['is_test_user']): ?>
                                        <span class="badge bg-info">Ya</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Tidak</span>
                                    <?php endif; ?>
                                </td>
                                <td><a href="<?= site_url('user_management/edit/' . $user['id']) ?>" class="btn btn-sm btn-primary">Edit</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if(isset($pagination_links)) { echo $pagination_links; } ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

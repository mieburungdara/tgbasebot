<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Siaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 1140px; }
        .card-header { font-weight: 500; }
        .progress { height: 20px; font-size: 0.8rem; }
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= site_url('dashboard/broadcast') ?>">Broadcast</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('user_management') ?>">Users</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <h1 class="mb-4 h3">Buat Siaran Baru</h1>
                <div class="card mb-4">
                    <div class="card-header">Tulis Pesan</div>
                    <div class="card-body">
                        <?= form_open('dashboard/send_broadcast') ?>
                            <div class="mb-3">
                                <label for="message" class="form-label">Pesan Siaran</label>
                                <textarea class="form-control" name="message" id="message" rows="8" required><?= set_value('message') ?></textarea>
                                <?php if(form_error('message')): ?><div class="text-danger small mt-1"><?= form_error('message') ?></div><?php endif; ?>
                            </div>
                            <div class="mb-3">
                                <label for="target_tag" class="form-label">Target Segmen</label>
                                <select name="target_tag" id="target_tag" class="form-select">
                                    <option value="all" selected>Semua Pengguna Aktif</option>
                                    <?php if (!empty($tags)): ?>
                                        <?php foreach($tags as $tag): ?>
                                            <option value="<?= html_escape($tag) ?>"><?= html_escape(ucfirst($tag)) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                                <div class="form-text">Pilih segmen pengguna untuk ditargetkan. Biarkan "Semua" untuk mengirim ke semua pengguna aktif.</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="queue_send" value="1" class="btn btn-primary" <?= (isset($user_stats['active_users']) && $user_stats['active_users'] == 0) ? 'disabled' : '' ?>>
                                    <i class="bi bi-send"></i> Tambahkan ke Antrean
                                </button>
                                <button type="submit" name="test_send" value="1" class="btn btn-outline-secondary">
                                    <i class="bi bi-person-check"></i> Kirim Tes (hanya ke pengguna tes)
                                </button>
                            </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <h1 class="mb-4 h3">Riwayat Siaran</h1>

                <!-- Filter Form -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Filter Riwayat</h5>
                        <?= form_open('dashboard/broadcast', ['method' => 'get', 'class' => 'row g-3 align-items-center']) ?>
                            <div class="col-md-4">
                                <label for="status" class="visually-hidden">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="" <?= set_select('status', '') ?>>Semua Status</option>
                                    <option value="pending" <?= set_select('status', 'pending', !empty($filters['status']) && $filters['status'] == 'pending') ?>>Pending</option>
                                    <option value="processing" <?= set_select('status', 'processing', !empty($filters['status']) && $filters['status'] == 'processing') ?>>Processing</option>
                                    <option value="completed" <?= set_select('status', 'completed', !empty($filters['status']) && $filters['status'] == 'completed') ?>>Completed</option>
                                    <option value="failed" <?= set_select('status', 'failed', !empty($filters['status']) && $filters['status'] == 'failed') ?>>Failed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="<?= site_url('dashboard/broadcast') ?>" class="btn btn-secondary">Reset</a>
                            </div>
                        <?= form_close() ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">Daftar Pekerjaan Siaran</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">ID</th>
                                        <th style="width: 10%;">Status</th>
                                        <th style="width: 40%;">Progres</th>
                                        <th style="width: 25%;">Pesan</th>
                                        <th style="width: 20%;">Tanggal Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($broadcasts)): ?>
                                        <?php foreach ($broadcasts as $job):
                                            $sent = (int)$job['sent_count'];
                                            $failed = (int)$job['failed_count'];
                                            $total = (int)$job['total_recipients'];
                                            $processed = $sent + $failed;
                                            $progress = ($total > 0) ? ($processed / $total) * 100 : 0;

                                            $status_class = 'secondary';
                                            if ($job['status'] === 'processing') $status_class = 'primary';
                                            if ($job['status'] === 'completed') $status_class = 'success';
                                            if ($job['status'] === 'failed') $status_class = 'danger';
                                        ?>
                                            <tr>
                                                <td><small class="text-muted">#<?= $job['id'] ?></small></td>
                                                <td>
                                                    <span class="badge bg-<?= $status_class ?> status-badge"><?= $job['status'] ?></span>
                                                </td>
                                                <td>
                                                    <div class="progress" role="progressbar" aria-label="Broadcast progress" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <div class="progress-bar" role="progressbar" style="width: <?= ($total > 0 ? ($sent/$total)*100 : 0) ?>%" aria-valuenow="<?= $sent ?>" aria-valuemin="0" aria-valuemax="<?= $total ?>"></div>
                                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= ($total > 0 ? ($failed/$total)*100 : 0) ?>%" aria-valuenow="<?= $failed ?>" aria-valuemin="0" aria-valuemax="<?= $total ?>"></div>
                                                    </div>
                                                    <small class="text-muted d-block mt-1">
                                                        <?= $processed ?> dari <?= $total ?> (Terkirim: <?= $sent ?>, Gagal: <?= $failed ?>)
                                                    </small>
                                                </td>
                                                <td>
                                                    <small title="<?= html_escape($job['message']) ?>"><?= html_escape(substr($job['message'], 0, 45)) . (strlen($job['message']) > 45 ? '...' : '') ?></small>
                                                </td>
                                                <td><small><?= $job['created_at'] ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Belum ada siaran yang dibuat.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if(isset($pagination_links) && $pagination_links) { echo $pagination_links; } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

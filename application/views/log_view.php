<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor Log Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { margin-bottom: 1.5rem; }
        .log-message {
            font-family: Consolas, Monaco, 'Courier New', Courier, monospace;
            font-size: 0.875rem;
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #e9ecef;
            padding: 0.5rem;
            border-radius: 0.25rem;
        }
        .log-type-badge {
            font-size: 0.75em;
            padding: 0.35em 0.65em;
        }
        .filters-card {
            margin-bottom: 2rem;
        }
        .pagination .page-link {
            color: #007bff;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="<?= site_url('dashboard') ?>">Logs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/keywords') ?>">Keywords</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/broadcast') ?>">Broadcast</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= site_url('user_management') ?>">Users</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">Dasbor Log Bot</h1>

        <!-- Bagian Grafik Aktivitas -->
        <div class="card mb-4">
            <div class="card-header">
                Aktivitas Log (14 Hari Terakhir)
            </div>
            <div class="card-body" style="height: 250px;">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

        <!-- Bagian Statistik Pengguna -->
        <h2 class="h4 mb-3">Statistik Pengguna</h2>
        <div class="row">
            <div class="col-lg-3">
                <div class="card text-white bg-success mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-check-fill"></i> Aktif</h5>
                        <p class="card-text fs-4"><?= number_format($user_stats['active_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card text-white bg-warning mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-dash-fill"></i> Berhenti</h5>
                        <p class="card-text fs-4"><?= number_format($user_stats['unsubscribed_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card text-white bg-danger mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-person-x-fill"></i> Diblokir</h5>
                        <p class="card-text fs-4"><?= number_format($user_stats['banned_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card text-white bg-secondary mb-4">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-people-fill"></i> Total</h5>
                        <p class="card-text fs-4"><?= number_format($user_stats['total_users'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="h4 mb-3">Statistik Log</h2>
        <!-- Bagian Statistik Log -->
        <div class="row">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h5 class="card-title">Total Log</h5>
                        <p class="card-text fs-4"><?= number_format($stats['total_logs']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h5 class="card-title">Log (24 Jam)</h5>
                        <p class="card-text fs-4"><?= number_format($stats['logs_today']) ?></p>
                    </div>
                </div>
            </div>
            <?php
                $type_counts = [];
                foreach ($stats['logs_by_type'] as $type) {
                    $type_counts[$type['log_type']] = $type['count'];
                }
            ?>
            <div class="col-md-2">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Incoming</h5>
                        <p class="card-text fs-4"><?= number_format($type_counts['incoming'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Outgoing</h5>
                        <p class="card-text fs-4"><?= number_format($type_counts['outgoing'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Error</h5>
                        <p class="card-text fs-4"><?= number_format($type_counts['error'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter dan Aksi -->
        <div class="card filters-card">
            <div class="card-body">
                <h5 class="card-title">Filter Log</h5>
                <?= form_open('dashboard', ['method' => 'get', 'class' => 'row g-3 align-items-center']) ?>
                    <div class="col-md-2">
                        <?= form_dropdown('log_type', ['' => 'Semua Tipe'] + array_combine($log_types, $log_types), $filters['log_type'] ?? '', 'class="form-select"') ?>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="chat_id" class="form-control" placeholder="Chat ID" value="<?= html_escape($filters['chat_id'] ?? '') ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="chat_name" class="form-control" placeholder="Nama Chat" value="<?= html_escape($filters['chat_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="keyword" class="form-control" placeholder="Kata Kunci di Pesan" value="<?= html_escape($filters['keyword'] ?? '') ?>">
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary me-2">Reset</a>
                        <a href="<?= site_url('migrate') ?>" class="btn btn-warning me-2" target="_blank" onclick="return confirm('Ini akan menjalankan migrasi database. Lanjutkan?')">Jalankan Migrasi</a>
                        <a href="<?= site_url('dashboard/clear_logs') ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus semua log? Tindakan ini tidak dapat diurungkan.')">Hapus Semua</a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>

        <!-- Tabel Log -->
        <div class="card">
            <div class="card-body">
                <?php if (empty($logs)): ?>
                    <p class="text-center">Tidak ada log yang cocok dengan filter Anda.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Timestamp</th>
                                    <th>Tipe</th>
                                    <th>Chat ID</th>
                                    <th>Nama Chat</th>
                                    <th>Pesan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?= $log['id'] ?></td>
                                        <td><?= $log['created_at'] ?></td>
                                        <td>
                                            <?php
                                                $badge_class = 'bg-secondary';
                                                if ($log['log_type'] === 'incoming') $badge_class = 'bg-success';
                                                if ($log['log_type'] === 'outgoing') $badge_class = 'bg-info';
                                                if ($log['log_type'] === 'error') $badge_class = 'bg-danger';
                                            ?>
                                            <span class="badge log-type-badge <?= $badge_class ?>"><?= html_escape($log['log_type']) ?></span>
                                        </td>
                                        <td><?= html_escape($log['chat_id']) ?></td>
                                        <td><?= html_escape($log['chat_name']) ?></td>
                                        <td>
                                            <?php
                                                $message = html_escape($log['log_message']);
                                                $decoded_message = json_decode($log['log_message'], true);
                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                    echo '<pre class="log-message"><code>' . html_escape(json_encode($decoded_message, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) . '</code></pre>';
                                                } else {
                                                    echo '<div class="log-message">' . $message . '</div>';
                                                }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?= site_url('dashboard/delete/' . $log['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus log ini?')">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <nav class="mt-3">
                        <?= $pagination_links ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('activityChart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?= $chart_labels ?>,
                    datasets: [{
                        label: 'Total Logs',
                        data: <?= $chart_values ?>,
                        fill: true,
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderColor: 'rgba(0, 123, 255, 1)',
                        tension: 0.3,
                        pointBackgroundColor: 'rgba(0, 123, 255, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Pastikan hanya integer yang ditampilkan di sumbu Y
                                stepSize: 1,
                                callback: function(value) {if (Math.floor(value) === value) {return value;}}
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>

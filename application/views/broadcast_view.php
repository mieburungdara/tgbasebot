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
        .container-xl { max-width: 1440px; }
        .card-header { font-weight: 500; }
        .progress { height: 20px; font-size: 0.8rem; }
        .status-badge { font-size: 0.9em; text-transform: capitalize; }
        .message-snippet { cursor: pointer; }
        .cron-info code { background-color: #e9ecef; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-xl">
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

    <div class="container-xl">
        <div class="row">
            <!-- Kolom Kiri: Form dan Info Cron -->
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
                                    <?php if (!empty($tags)): foreach($tags as $tag): ?>
                                        <option value="<?= html_escape($tag) ?>"><?= html_escape(ucfirst($tag)) ?></option>
                                    <?php endforeach; endif; ?>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="queue_send" value="1" class="btn btn-primary"><i class="bi bi-send"></i> Tambahkan ke Antrean</button>
                                <button type="submit" name="test_send" value="1" class="btn btn-outline-secondary"><i class="bi bi-person-check"></i> Kirim Tes</button>
                            </div>
                        <?= form_close() ?>
                    </div>
                </div>
                <div class="card mb-4 cron-info">
                    <div class="card-header"><i class="bi bi-clock-history"></i> Informasi Cron Job</div>
                    <div class="card-body">
                        <p class="mb-2">Gunakan salah satu metode di bawah ini untuk menjalankan siaran di latar belakang (setiap menit disarankan).</p>

                        <label class="form-label small"><strong>Opsi 1: Perintah CLI (Disarankan)</strong></label>
                        <input type="text" class="form-control form-control-sm mb-3" value="* * * * * /usr/bin/php <?= FCPATH ?>cron/process_broadcasts.php" readonly>

                        <label class="form-label small"><strong>Opsi 2: URL Cron Job</strong></label>
                        <?php $cron_key = $cron_secret_key ?? 'NOT_SET'; ?>
                        <div class="input-group input-group-sm mb-3">
                            <input type="text" id="cron-url" class="form-control" value="<?= site_url('cron/run?token=') . $cron_key ?>" readonly>
                            <button id="toggle-key-btn" class="btn btn-outline-secondary" type="button"><i class="bi bi-eye"></i></button>
                        </div>

                        <label class="form-label small"><strong>Manajemen Kunci:</strong></label>
                        <div>
                        <?= form_open('dashboard/reset_cron_key', ['class' => 'd-inline']) ?>
                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Apakah Anda yakin ingin me-reset kunci rahasia? URL cron lama akan berhenti bekerja.')">
                                <i class="bi bi-arrow-clockwise"></i> Reset Kunci Rahasia
                            </button>
                        <?= form_close() ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Riwayat -->
            <div class="col-lg-8">
                <h1 class="mb-4 h3">Riwayat Siaran</h1>
                <div class="card">
                    <div class="card-header">Daftar Pekerjaan Siaran</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Status</th>
                                        <th>Target</th>
                                        <th>Progres</th>
                                        <th>Pesan</th>
                                        <th>Waktu</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($broadcasts)): foreach ($broadcasts as $job):
                                        $sent = (int)$job['sent_count']; $failed = (int)$job['failed_count']; $total = (int)$job['total_recipients'];
                                        $processed = $sent + $failed; $progress = ($total > 0) ? ($processed / $total) * 100 : 0;
                                        $status_class = 'secondary';
                                        if ($job['status'] === 'processing') $status_class = 'primary';
                                        if ($job['status'] === 'completed') $status_class = 'success';
                                        if ($job['status'] === 'failed') $status_class = 'danger';
                                    ?>
                                    <tr>
                                        <td><small class="text-muted">#<?= $job['id'] ?></small></td>
                                        <td>
                                            <span class="badge bg-<?= $status_class ?> status-badge"><?= $job['status'] ?></span>
                                            <?php if($job['status'] === 'failed' && !empty($job['last_error_message'])): ?>
                                                <i class="bi bi-exclamation-circle-fill text-danger" data-bs-toggle="tooltip" title="<?= html_escape($job['last_error_message']) ?>"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($job['is_test_broadcast']) { echo '<span class="badge bg-info">Tes</span>'; }
                                                  elseif(!empty($job['target_tag'])) { echo '<span class="badge bg-secondary">'.html_escape($job['target_tag']).'</span>'; }
                                                  else { echo '<span class="badge bg-dark">Semua</span>'; } ?>
                                        </td>
                                        <td>
                                            <div class="progress" role="progressbar"><div class="progress-bar" style="width:<?=($sent/$total)*100?>%"></div><div class="progress-bar bg-danger" style="width:<?=($failed/$total)*100?>%"></div></div>
                                            <small class="text-muted d-block mt-1"><?= $processed ?>/<?= $total ?> (Terkirim: <?= $sent ?>, Gagal: <?= $failed ?>)</small>
                                        </td>
                                        <td><a href="#" class="message-snippet" data-bs-toggle="modal" data-bs-target="#messageModal" data-message="<?= html_escape($job['message']) ?>"><small><?= html_escape(substr($job['message'], 0, 25)) . '...' ?></small></a></td>
                                        <td><small>Dibuat: <?= $job['created_at'] ?><br>Selesai: <?= $job['completed_at'] ?? 'N/A' ?></small></td>
                                        <td><a href="<?= site_url('dashboard/delete_broadcast/' . $job['id']) ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus riwayat siaran ini?')"><i class="bi bi-trash"></i></a></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="7" class="text-center">Belum ada siaran yang dibuat.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if(isset($pagination_links)) { echo $pagination_links; } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Pesan -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="messageModalLabel">Isi Pesan Siaran</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p style="white-space: pre-wrap;"></p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inisialisasi Tooltip
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

        // Inisialisasi Modal Pesan
        const messageModal = document.getElementById('messageModal');
        messageModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            const message = button.getAttribute('data-message');
            const modalBodyP = messageModal.querySelector('.modal-body p');
            modalBodyP.textContent = message;
        });

        // Toggle Cron Key Visibility
        const toggleBtn = document.getElementById('toggle-key-btn');
        const cronUrlInput = document.getElementById('cron-url');
        if (toggleBtn && cronUrlInput) {
            const originalUrl = cronUrlInput.value;
            const secretKey = "<?= $cron_secret_key ?? '' ?>";
            let keyVisible = false;

            // Initially hide the key and disable button if not set
            if (secretKey && secretKey !== 'NOT_SET') {
                cronUrlInput.value = originalUrl.replace(secretKey, '************');
            } else {
                toggleBtn.disabled = true;
            }

            toggleBtn.addEventListener('click', () => {
                keyVisible = !keyVisible;
                if (keyVisible) {
                    cronUrlInput.value = originalUrl;
                    toggleBtn.innerHTML = '<i class="bi bi-eye-slash-fill"></i>';
                } else {
                    cronUrlInput.value = originalUrl.replace(secretKey, '************');
                    toggleBtn.innerHTML = '<i class="bi bi-eye-fill"></i>';
                }
            });
        }
    </script>
</body>
</html>

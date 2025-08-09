<?php $this->load->view('templates/header'); ?>

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
                                <label for="sender_bot_id" class="form-label">Kirim Dari Bot</label>
                                <select name="sender_bot_id" id="sender_bot_id" class="form-select" required>
                                    <option value="">-- Pilih Bot Pengirim --</option>
                                    <?php foreach($all_bots as $bot): ?>
                                        <option value="<?= $bot['id'] ?>"><?= html_escape($bot['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
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
                    <div class="card-header"><i class="bi bi-clock-history"></i> Informasi Proses Latar Belakang</div>
                    <div class="card-body">
                        <p class="mb-2">Siaran dikirim oleh proses di latar belakang. Pastikan Anda telah mengatur cron job untuk memproses antrean.</p>

                        <label class="form-label small"><strong>Perintah Cron Job (Disarankan)</strong></label>
                        <div class="input-group input-group-sm">
                           <input type="text" class="form-control" value="* * * * * /usr/bin/php <?= FCPATH ?>index.php cron process_broadcasts" readonly>
                           <button class="btn btn-outline-secondary" onclick="navigator.clipboard.writeText(this.previousElementSibling.value)"><i class="bi bi-clipboard"></i></button>
                        </div>
                        <div class="form-text">
                            Jalankan perintah ini setiap menit di server Anda. Kunci rahasia untuk setiap bot sekarang dikelola di halaman <a href="<?= site_url('bot_management') ?>">Manajemen Bot</a>.
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

            const testBtn = document.getElementById('test-cron-btn');
            // Initially hide the key and disable buttons if not set
            if (secretKey && secretKey !== 'NOT_SET') {
                cronUrlInput.value = originalUrl.replace(secretKey, '************');
            } else {
                toggleBtn.disabled = true;
                if(testBtn) testBtn.classList.add('disabled');
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
<?php $this->load->view('templates/footer'); ?>

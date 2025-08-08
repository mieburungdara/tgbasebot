<?php $this->load->view('templates/header'); ?>

<h1 class="mb-4">Dasbor: <?= html_escape($selected_bot['name'] ?? 'Tidak ada bot'); ?></h1>

<!-- Panel Kesehatan Bot -->
<div class="card mb-4">
    <div class="card-header">
        <i class="bi bi-heart-pulse-fill"></i> Panel Kesehatan Bot
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-6">
                <h6 class="card-title">Pesan Terakhir Diterima</h6>
                <p class="card-text text-muted"><?= $health_stats['last_incoming_message'] ? $health_stats['last_incoming_message'] : 'Belum ada' ?></p>
            </div>
            <div class="col-md-6">
                <h6 class="card-title">Cron Job Siaran Terakhir</h6>
                <p class="card-text text-muted"><?= $health_stats['last_cron_run'] ? $health_stats['last_cron_run'] : 'Belum pernah' ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Bagian Grafik Aktivitas -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Aktivitas Log</span>
        <div class="btn-group btn-group-sm" role="group">
            <a href="<?= site_url('dashboard?days=7') ?>" class="btn btn-outline-secondary <?= ($chart_days == 7) ? 'active' : '' ?>">7 Hari</a>
            <a href="<?= site_url('dashboard?days=14') ?>" class="btn btn-outline-secondary <?= ($chart_days == 14) ? 'active' : '' ?>">14 Hari</a>
            <a href="<?= site_url('dashboard?days=30') ?>" class="btn btn-outline-secondary <?= ($chart_days == 30) ? 'active' : '' ?>">30 Hari</a>
        </div>
    </div>
    <div class="card-body" style="height: 250px;">
        <canvas id="activityChart"></canvas>
    </div>
</div>

<!-- Bagian Statistik Pengguna & Log -->
<div class="row">
    <div class="col-lg-8">
        <h2 class="h4 mb-3">Statistik Pengguna</h2>
        <div class="row">
            <div class="col-lg-3 col-6"><div class="card text-white bg-success mb-4"><div class="card-body"><h5 class="card-title"><i class="bi bi-person-check-fill"></i> Aktif</h5><p class="card-text fs-4"><?= number_format($user_stats['active_users'] ?? 0) ?></p></div></div></div>
            <div class="col-lg-3 col-6"><div class="card text-white bg-warning mb-4"><div class="card-body"><h5 class="card-title"><i class="bi bi-person-dash-fill"></i> Berhenti</h5><p class="card-text fs-4"><?= number_format($user_stats['unsubscribed_users'] ?? 0) ?></p></div></div></div>
            <div class="col-lg-3 col-6"><div class="card text-white bg-danger mb-4"><div class="card-body"><h5 class="card-title"><i class="bi bi-person-x-fill"></i> Diblokir</h5><p class="card-text fs-4"><?= number_format($user_stats['banned_users'] ?? 0) ?></p></div></div></div>
            <div class="col-lg-3 col-6"><div class="card text-white bg-secondary mb-4"><div class="card-body"><h5 class="card-title"><i class="bi bi-people-fill"></i> Total</h5><p class="card-text fs-4"><?= number_format($user_stats['total_users'] ?? 0) ?></p></div></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <h2 class="h4 mb-3">Distribusi Log</h2>
        <div class="card mb-4"><div class="card-body" style="height: 140px;"><canvas id="logTypeChart"></canvas></div></div>
    </div>
</div>


<!-- Filter dan Aksi -->
<div class="card filters-card">
    <div class="card-body">
        <h5 class="card-title">Filter & Aksi Log</h5>
        <?= form_open('dashboard', ['method' => 'get', 'class' => 'row g-3 align-items-center']) ?>
            <div class="col-md-2"><input type="text" name="chat_id" class="form-control" placeholder="Chat ID" value="<?= html_escape($filters['chat_id'] ?? '') ?>"></div>
            <div class="col-md-2"><input type="text" name="chat_name" class="form-control" placeholder="Nama Chat" value="<?= html_escape($filters['chat_name'] ?? '') ?>"></div>
            <div class="col-md-3"><input type="text" name="keyword" class="form-control" placeholder="Kata Kunci di Pesan" value="<?= html_escape($filters['keyword'] ?? '') ?>"></div>
            <div class="col-md-5 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="<?= site_url('dashboard') ?>" class="btn btn-secondary me-2">Reset</a>
                <a href="<?= site_url('dashboard/export_csv?' . http_build_query($filters)) ?>" class="btn btn-success me-2">Unduh CSV</a>
                <a href="<?= site_url('dashboard/clear_logs') ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus semua log untuk bot ini?')">Hapus Semua</a>
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
                    <thead><tr><th>ID</th><th>Timestamp</th><th>Tipe</th><th>Chat ID</th><th>Nama Chat</th><th>Pesan</th><th>Aksi</th></tr></thead>
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
                                <td><pre class="log-message"><code><?= html_escape($log['log_message']) ?></code></pre></td>
                                <td><a href="<?= site_url('dashboard/delete/' . $log['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus log ini?')"><i class="bi bi-trash"></i></a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <nav class="mt-3"><?= $pagination_links ?></nav>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('activityChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: { labels: <?= $chart_labels ?>, datasets: [{ label: 'Total Logs', data: <?= $chart_values ?>, fill: true, backgroundColor: 'rgba(0, 123, 255, 0.1)', borderColor: 'rgba(0, 123, 255, 1)', tension: 0.3 }] },
            options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { stepSize: 1, callback: function(value) {if (Math.floor(value) === value) {return value;}} } } }, plugins: { legend: { display: false } } }
        });
    }
    const donutCtx = document.getElementById('logTypeChart');
    if(donutCtx) {
        new Chart(donutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= $donut_labels ?>,
                datasets: [{ data: <?= $donut_values ?>, backgroundColor: ['rgba(40, 167, 69, 0.8)', 'rgba(23, 162, 184, 0.8)', 'rgba(220, 53, 69, 0.8)'], borderColor: '#f8f9fa' }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: true, position: 'left' } } }
        });
    }
</script>

<?php $this->load->view('templates/footer'); ?>

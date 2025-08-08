<?php $this->load->view('templates/header'); ?>

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
                                    <?php
                                        $status_class = 'secondary'; // Fallback
                                        if ($user['status'] === 'active') {
                                            $status_class = 'success';
                                        } elseif ($user['status'] === 'banned') {
                                            $status_class = 'danger';
                                        } elseif ($user['status'] === 'unsubscribed') {
                                            $status_class = 'warning';
                                        }
                                    ?>
                                    <span class="badge bg-<?= $status_class ?> status-badge"><?= $user['status'] ?></span>
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

<?php $this->load->view('templates/footer'); ?>

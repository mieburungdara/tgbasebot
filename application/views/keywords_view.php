<?php $this->load->view('templates/header'); ?>

<h1 class="mb-4">Manajemen Balasan Kata Kunci</h1>

        <!-- Form Tambah Kata Kunci -->
        <div class="card mb-4">
            <div class="card-header">
                Tambah Balasan Kata Kunci Baru
            </div>
            <div class="card-body">
                <?= form_open('dashboard/add_keyword', ['class' => 'row g-3']) ?>
                    <div class="col-md-4">
                        <label for="keyword" class="form-label">Kata Kunci</label>
                        <input type="text" class="form-control" name="keyword" id="keyword" value="<?= set_value('keyword') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="reply" class="form-label">Pesan Balasan</label>
                        <input type="text" class="form-control" name="reply" id="reply" value="<?= set_value('reply') ?>" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-plus-lg"></i> Tambah</button>
                    </div>
                <?= form_close() ?>
                <?php if(validation_errors()): ?>
                    <div class="alert alert-danger mt-3 mb-0">
                        <?= validation_errors() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabel Kata Kunci yang Ada -->
        <div class="card">
            <div class="card-header">
                Daftar Kata Kunci
            </div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 5%;">#</th>
                            <th scope="col" style="width: 25%;">Kata Kunci</th>
                            <th scope="col">Balasan</th>
                            <th scope="col" style="width: 10%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($keywords)): ?>
                            <?php foreach ($keywords as $index => $item): ?>
                                <tr>
                                    <th scope="row"><?= $index + 1 ?></th>
                                    <td><?= html_escape($item['keyword']) ?></td>
                                    <td><?= html_escape($item['reply']) ?></td>
                                    <td>
                                        <a href="<?= site_url('dashboard/delete_keyword/' . $item['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus kata kunci ini?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">Belum ada kata kunci yang ditambahkan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php $this->load->view('templates/footer'); ?>

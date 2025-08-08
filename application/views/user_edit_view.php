<?php $this->load->view('templates/header'); ?>

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

<?php $this->load->view('templates/footer'); ?>

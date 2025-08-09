<?php $this->load->view('templates/header'); ?>

<div class="container" style="max-width: 720px;">
    <h1 class="mb-4">Edit Bot: <?= html_escape($bot['name']) ?></h1>
    <div class="card">
        <div class="card-body">
            <?= form_open('bot_management/update/' . $bot['id']) ?>
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Bot</label>
                    <input type="text" name="name" class="form-control" value="<?= set_value('name', $bot['name']) ?>" required>
                    <?= form_error('name', '<div class="text-danger small">', '</div>') ?>
                </div>
                <div class="mb-3">
                    <label for="token" class="form-label">Token API Telegram</label>
                    <input type="text" name="token" class="form-control" value="<?= set_value('token', $bot['token']) ?>" required>
                    <?= form_error('token', '<div class="text-danger small">', '</div>') ?>
                </div>
                <hr>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="<?= site_url('bot_management') ?>" class="btn btn-secondary">Batal</a>
                <button type="button" id="check-info-btn" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#botInfoModal">Cek Info Bot</button>
            <?= form_close() ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="botInfoModal" tabindex="-1" aria-labelledby="botInfoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="botInfoModalLabel">Informasi Bot</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="info-spinner" class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div id="info-content" style="display: none;">
            <p><strong>Info Dasar (getMe):</strong></p>
            <pre id="getMeInfo"></pre>
            <p><strong>Info Webhook (getWebhookInfo):</strong></p>
            <pre id="getWebhookInfo"></pre>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" id="set-webhook-btn" class="btn btn-success">Set Webhook</button>
        <button type="button" id="delete-webhook-btn" class="btn btn-danger">Hapus Webhook</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const botId = <?= $bot['id'] ?>;
    const infoModalEl = document.getElementById('botInfoModal');

    const getMeInfoElem = document.getElementById('getMeInfo');
    const getWebhookInfoElem = document.getElementById('getWebhookInfo');
    const infoSpinner = document.getElementById('info-spinner');
    const infoContent = document.getElementById('info-content');

    const setWebhookBtn = document.getElementById('set-webhook-btn');
    const deleteWebhookBtn = document.getElementById('delete-webhook-btn');

    async function fetchBotInfo() {
        infoSpinner.style.display = 'block';
        infoContent.style.display = 'none';

        try {
            const response = await fetch(`<?= site_url('bot_management/info/') ?>${botId}`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const data = await response.json();

            getMeInfoElem.textContent = JSON.stringify(data.getMe, null, 2);
            getWebhookInfoElem.textContent = JSON.stringify(data.getWebhookInfo, null, 2);

        } catch (error) {
            getMeInfoElem.textContent = 'Gagal memuat data.';
            getWebhookInfoElem.textContent = 'Gagal memuat data. Error: ' + error.message;
        } finally {
            infoSpinner.style.display = 'none';
            infoContent.style.display = 'block';
        }
    }

    infoModalEl.addEventListener('show.bs.modal', function (event) {
        fetchBotInfo();
    });

    setWebhookBtn.addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        try {
            const response = await fetch(`<?= site_url('bot_management/set_webhook/') ?>${botId}`);
            const result = await response.json();
            alert(result.description || (result.ok ? 'Webhook berhasil di-set!' : 'Gagal melakukan operasi.'));
            await fetchBotInfo(); // Refresh info
        } catch (error) {
            alert('Terjadi kesalahan saat set webhook: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = 'Set Webhook';
        }
    });

    deleteWebhookBtn.addEventListener('click', async function() {
        if (!confirm('Apakah Anda yakin ingin menghapus webhook?')) {
            return;
        }
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
        try {
            const response = await fetch(`<?= site_url('bot_management/delete_webhook/') ?>${botId}`);
            const result = await response.json();
            alert(result.description || (result.ok ? 'Webhook berhasil dihapus!' : 'Gagal melakukan operasi.'));
            await fetchBotInfo(); // Refresh info
        } catch (error) {
            alert('Terjadi kesalahan saat menghapus webhook: ' + error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = 'Hapus Webhook';
        }
    });
});
</script>

<?php $this->load->view('templates/footer'); ?>

<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? html_escape($title) . ' - ' : '' ?>Bot Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .container-xl { max-width: 1440px; }
        .card-header { font-weight: 500; }
        .status-badge { font-size: 0.9em; text-transform: capitalize; }
        .nav-item .nav-link.active { font-weight: bold; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container-xl">
            <a class="navbar-brand" href="<?= site_url('dashboard') ?>"><i class="bi bi-robot"></i> Multi-Bot Dasbor</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link <?= ($this->uri->segment(1) == 'dashboard' || $this->uri->segment(1) == '') ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">Logs</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($this->uri->segment(1) == 'keywords') ? 'active' : '' ?>" href="<?= site_url('dashboard/keywords') ?>">Keywords</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($this->uri->segment(1) == 'broadcast') ? 'active' : '' ?>" href="<?= site_url('dashboard/broadcast') ?>">Broadcast</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($this->uri->segment(1) == 'user_management') ? 'active' : '' ?>" href="<?= site_url('user_management') ?>">Users</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($this->uri->segment(1) == 'bot_management') ? 'active' : '' ?>" href="<?= site_url('bot_management') ?>">Bots</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($this->uri->segment(1) == 'settings') ? 'active' : '' ?>" href="<?= site_url('settings') ?>">Pengaturan</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Start of main content container -->
    <div class="container-xl">

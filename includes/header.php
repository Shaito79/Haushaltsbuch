<?php
$pageTitle = $pageTitle ?? 'Haushaltsbuch';
$assetPrefix = $assetPrefix ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $assetPrefix ?>style.css">
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar sticky-top">
    <div class="container-fluid app-shell">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= $assetPrefix ?>index.php">
            <span class="brand-icon"><i class="bi bi-wallet2"></i></span>
            <span>Haushaltsbuch</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Navigation öffnen">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= $assetPrefix ?>index.php"><i class="bi bi-grid me-1"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $assetPrefix ?>index.php#neue-buchung"><i class="bi bi-plus-circle me-1"></i>Neue Buchung</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $assetPrefix ?>index.php#buchungen"><i class="bi bi-list-ul me-1"></i>Buchungen</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $assetPrefix ?>diagramme/index.php"><i class="bi bi-bar-chart me-1"></i>Diagramme</a></li>
            </ul>
        </div>
    </div>
</nav>
<main class="app-shell py-4 py-lg-5">

<?php require_once __DIR__ . "/../../core/Session.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <meta name="description" content="Sistema de gestión para procesos de almacén">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Sistema de Almacén">
    <title><?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Sistema de Almacén' ?></title>
    <link rel="manifest" href="<?= BASE_URL ?>/public/manifest.json">
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/public/assets/img/logo.svg">
    <link rel="apple-touch-icon" sizes="192x192" href="<?= BASE_URL ?>/public/assets/img/logo.svg">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css">
</head>
<body>
<div class="topbar">
    <div class="wrap">
        <div class="brand">Sistema de Almacén</div>
        <div class="nav">
            <a href="<?= BASE_URL ?>/">Inicio</a>
            <a href="<?= BASE_URL ?>/dashboard">Panel</a>
            <?php if (Session::user()) { ?>
            <a href="<?= BASE_URL ?>/logout">Salir (<?= htmlspecialchars(Session::user()['username'], ENT_QUOTES, 'UTF-8') ?>)</a>
            <?php } else { ?>
            <a href="<?= BASE_URL ?>/login">Entrar</a>
            <?php } ?>
        </div>
    </div>
</div>
<div class="container">

<?php require_once __DIR__ . "/../../core/Session.php"; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : 'Sistema de Almacén' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/app.css">
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

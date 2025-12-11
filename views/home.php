<?php $title = 'Inicio'; include __DIR__ . '/partials/header.php'; ?>
<div class="hero">
    <h1>Bienvenido</h1>
    <p class="muted">Servidor funcionando correctamente. Fecha desde la base de datos:</p>
    <p><b><?= htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') ?></b></p>
</div>
<div class="card-grid">
    <div class="card">
        <h3>Entradas</h3>
        <p class="muted">Registra nuevas entradas de mercancía.</p>
        <div class="actions"><a class="btn" href="#">Registrar entrada</a></div>
    </div>
    <div class="card">
        <h3>Salidas</h3>
        <p class="muted">Gestiona salidas y entregas.</p>
        <div class="actions"><a class="btn" href="#">Registrar salida</a></div>
    </div>
    <div class="card">
        <h3>Inventario</h3>
        <p class="muted">Consulta stock y reposiciones.</p>
        <div class="actions"><a class="btn" href="#">Ver inventario</a></div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>

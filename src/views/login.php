<?php $title = 'Iniciar sesión'; include __DIR__ . '/partials/header.php'; ?>
<div class="login-box">
    <h2>Iniciar sesión</h2>
    <?php if (!empty($error)) { echo '<div class="error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'; } ?>
    <form method="POST" action="<?= BASE_URL ?>/login">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <div class="field">
            <label>Usuario</label>
            <input type="text" name="username" autocomplete="username" required>
        </div>
        <div class="field">
            <label>Contraseña</label>
            <input type="password" name="password" autocomplete="current-password" required>
        </div>
        <button class="btn" type="submit">Entrar</button>
    </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>

<?php $title = 'Iniciar sesión'; include __DIR__ . '/partials/header.php'; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/assets/css/login.css">

<div id="version-link">
    <a href="<?= BASE_URL ?>/public/changelog.html">
        <i class="fas fa-code-branch"></i> Ver historial de versiones
    </a>
    </div>

<section class="credits" id="credits">
    <button class="close-credits" onclick="toggleCredits()">
        <i class="fas fa-times"></i>
    </button>
    <div class="credits-container">
        <h2 class="credits-title">Nuestro Equipo de Desarrollo</h2>
        <div class="credit-card">
            <div class="avatar">
                <i class="fas fa-code"></i>
            </div>
            <h3>Gabriel Morales</h3>
            <p class="role">Desarrollador Full-Stack – Encargado del desarrollo de la versión actual</p>
            <p>Ing. Desarrollo de Software – Especialista en Back-end y DevOps.</p>
        </div>
        <div class="credit-card">
            <div class="avatar">
                <i class="fas fa-code"></i>
            </div>
            <h3>Antonio Torres</h3>
            <p class="role">Desarrollador Full-Stack - Agradecimiento</p>
            <p>Especialista en UX/UI y Diseño Web.</p>
        </div>
    </div>
</section>

<button class="credits-button" onclick="toggleCredits()">
    <i class="fas fa-users"></i>
    <span class="tooltip">Créditos</span>
</button>

<div id="contenedor">
    <div id="imagen">
        <img id="logoImage" src="<?= BASE_URL ?>/public/assets/img/logo.svg" alt="Logo">
    </div>
    <div id="central">
        <div id="login">
            <div class="titulo" id="tituloAnimado">Bienvenido</div>
            <form id="loginForm" method="POST" action="<?= BASE_URL ?>/login" autocomplete="off">
                <div class="formUsuario">
                    <div class="input-container">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" id="usuario" placeholder="Usuario" required autocomplete="username">
                    </div>
                    <div class="input-container">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" id="contrasena" placeholder="Contraseña" required autocomplete="current-password">
                    </div>
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                    <input type="submit" id="btnIngresar" name="Ingresar" value="Ingresar">
                    <div class="forgot-password">
                        <a href="#"><i class="fas fa-key"></i> ¿Olvidaste tu contraseña?</a>
                    </div>
                </div>
            </form>
            <?php if (!empty($error)) { echo '<div class="error">' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</div>'; } ?>
        </div>
    </div>
</div>

<div id="loaderContainer" class="container">
    <div class="loader"></div>
    <div class="loader"></div>
    <div class="loader"></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
<script>
function toggleCredits(){
  const el = document.getElementById('credits');
  el.style.display = (el.style.display==='block') ? 'none' : 'block';
}

if ('serviceWorker' in navigator) {
  window.addEventListener('load', function() {
    const isDevelopment = (() => {
      const hostname = window.location.hostname;
      const protocol = window.location.protocol;
      return hostname === 'localhost' || hostname === '127.0.0.1' || protocol === 'http:';
    })();
    const registrationOptions = isDevelopment ? { scope: './', updateViaCache: 'none' } : { scope: './' };
    navigator.serviceWorker.register('<?= BASE_URL ?>/service-worker.js', registrationOptions)
      .then(function(registration) {
        // OK
      })
      .catch(function(err) {
        // Error
      });
  });
}

let deferredPrompt, installButton;
window.addEventListener('beforeinstallprompt', (e) => {
  e.preventDefault();
  deferredPrompt = e;
  showInstallButton();
});
function showInstallButton(){
  if (!document.getElementById('installBtn')){
    const btn = document.createElement('button');
    btn.id='installBtn';
    btn.textContent=' Instalar App';
    btn.style.cssText='position:fixed;top:10px;right:10px;z-index:1000;padding:10px 15px;background:#007bff;color:#fff;border:none;border-radius:5px;cursor:pointer;font-size:14px;box-shadow:0 2px 10px rgba(0,0,0,.2)';
    btn.addEventListener('click', installApp);
    document.body.appendChild(btn);
    installButton=btn;
  }
}
function installApp(){
  if (deferredPrompt){
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then((choice)=>{ if (choice.outcome==='accepted' && installButton){ installButton.style.display='none'; } deferredPrompt=null; });
  }
}
</script>

<?php include __DIR__ . '/partials/footer.php'; ?>

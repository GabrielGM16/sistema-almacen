<?php
include("config/conexion.php");
$con = conectar();
session_start();
session_unset();
session_destroy();
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Sistema de Almacén</title>
    
    <!-- PWA Meta Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#2c3e50">
    <meta name="description" content="Sistema de gestión de almacén e inventario">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Sistema Almacén">
    
    <!-- PWA Icons and Manifest -->
    <link rel="icon" type="image/png" href="assets/img/warehouse-icon.png">
    <link rel="manifest" href="manifest.json">
    <link rel="apple-touch-icon" sizes="192x192" href="assets/img/warehouse-icon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/img/warehouse-icon.png">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/vendor/sweetalert2/sweetalert2.min.css">
    
    <!-- Scripts -->
    <script src="assets/js/vendor/jquery/jquery-3.6.0.min.js"></script>
    <script src="assets/js/vendor/sweetalert2/sweetalert2.min.js"></script>
</head>

<body>
    <!-- Enlace de historial de versiones en esquina -->
    <div id="version-link">
        <a href="changelog.html">
            <i class="fas fa-code-branch"></i> Ver historial de versiones
        </a>
    </div>

    <!-- Sección de Créditos  -->
    <section class="credits">
        <button class="close-credits" onclick="toggleCredits()">
            <i class="fas fa-times"></i>
        </button>
        <div class="credits-container">
            <h2 class="credits-title">Nuestro Equipo de Desarrollo</h2>

            <div class="credit-card">
                <div class="avatar">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Tu Nombre</h3>
                <p class="role">Desarrollador Full-Stack Principal</p>
                <p>Ing. Desarrollo de Software – Especialista en sistemas de gestión empresarial y desarrollo web.</p>
            </div>

            <div class="credit-card">
                <div class="avatar">
                    <i class="fas fa-warehouse"></i>
                </div>
                <h3>Tu Empresa</h3>
                <p class="role">Sistema de Gestión de Almacén</p>
                <p>Solución integral para el control y administración de inventarios, productos y movimientos de almacén.</p>
            </div>
        </div>
    </section>
    
    <!-- Botón para mostrar/ocultar la sección de Créditos -->
    <button class="credits-button" onclick="toggleCredits()">
        <i class="fas fa-users"></i>
        <span class="tooltip">Créditos</span>
    </button>

    <div id="contenedor">
        <div id="imagen">
            <img id="logoImage" src="assets/img/warehouse-logo.png" alt="Logo Almacén">
            <div class="warehouse-animation">
                <i class="fas fa-boxes"></i>
            </div>
        </div>
        <div id="central">
            <div id="login">
                <div class="titulo" id="tituloAnimado">Bienvenido</div>
                <div class="subtitulo">Sistema de Almacén</div>
                <form id="loginForm" autocomplete="off" action="login/solUsuario.php" method="post">
                    <div class="formUsuario">
                        <div class="input-container">
                            <i class="fas fa-user"></i>
                            <input type="text" name="usuario" id="usuario" placeholder="Usuario" required autofocus
                                autocomplete="off">
                        </div>
                        <div class="input-container">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="contrasena" id="contrasena" placeholder="Contraseña" required>
                            <span class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                        <input type="submit" id="btnIngresar" name="Ingresar" value="Ingresar al Sistema">
                        <div class="forgot-password">
                            <a href="recursos/recuperar_contrasena.php">
                                <i class="fas fa-key"></i> ¿Olvidaste tu contraseña?
                            </a>
                        </div>
                        <div class="info-extras">
                            <p><i class="fas fa-shield-alt"></i> Acceso seguro y encriptado</p>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Loader animado -->
    <div id="loaderContainer" class="container">
        <div class="warehouse-loader">
            <div class="box box1"></div>
            <div class="box box2"></div>
            <div class="box box3"></div>
            <div class="forklift">
                <i class="fas fa-pallet"></i>
            </div>
        </div>
        <p class="loader-text">Cargando sistema...</p>
    </div>

    <script>
        // ==================== PWA VARIABLES GLOBALES ====================
        let deferredPrompt;
        let installButton;
        let updateIntervalId = null;
        let isUpdating = false;
        let registrationRef = null;

        // ==================== PWA SERVICE WORKER REGISTRATION ====================
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                const isDevelopment = (() => {
                    const hostname = window.location.hostname;
                    const protocol = window.location.protocol;
                    
                    const isLocalhost = hostname === 'localhost';
                    const is127001 = hostname === '127.0.0.1';
                    const isHttp = protocol === 'http:';
                    const is192168 = /^192\.168\.(\d{1,3})\.(\d{1,3})$/.test(hostname);
                    const is10x = /^10\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})$/.test(hostname);
                    const is172 = /^172\.(1[6-9]|2[0-9]|3[0-1])\.(\d{1,3})\.(\d{1,3})$/.test(hostname);
                    
                    return isLocalhost || is127001 || isHttp || is192168 || is10x || is172;
                })();
                
                const registrationOptions = isDevelopment ? {
                    scope: './',
                    updateViaCache: 'none'
                } : {
                    scope: './'
                };
                
                navigator.serviceWorker.register('service-worker.js', registrationOptions)
                    .then(function(registration) {
                        registrationRef = registration;
                        
                        fetch('./manifest.json')
                            .then(response => response.json())
                            .then(manifest => {
                                console.log('✓ Manifest cargado');
                            })
                            .catch(error => {
                                console.warn('⚠ Error al cargar manifest');
                            });
                        
                        updateIntervalId = setInterval(() => {
                            try {
                                if (registrationRef && 
                                    typeof registrationRef.update === 'function' &&
                                    registrationRef.scope &&
                                    registrationRef.active &&
                                    !registrationRef.installing && 
                                    !registrationRef.waiting &&
                                    !isUpdating &&
                                    document.visibilityState === 'visible') {
                                    
                                    isUpdating = true;
                                    registrationRef.update()
                                        .finally(() => {
                                            isUpdating = false;
                                        });
                                }
                            } catch (error) {
                                isUpdating = false;
                            }
                        }, 30000);
                        
                        navigator.serviceWorker.addEventListener('message', event => {
                            if (event.data && event.data.type === 'NEW_VERSION') {
                                showUpdateNotification(event.data);
                            }
                        });
                    })
                    .catch(function(err) {
                        console.error('Error al registrar Service Worker:', err);
                    });
            });
        }

        // ==================== CLEANUP ====================
        window.addEventListener('beforeunload', function() {
            if (updateIntervalId) {
                clearInterval(updateIntervalId);
                updateIntervalId = null;
            }
            registrationRef = null;
            isUpdating = false;
        });

        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'hidden') {
                isUpdating = false;
            }
        });

        // ==================== PWA INSTALL PROMPT ====================
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallButton();
        });

        function showInstallButton() {
            if (!document.getElementById('installBtn')) {
                const installBtn = document.createElement('button');
                installBtn.id = 'installBtn';
                installBtn.innerHTML = '<i class="fas fa-download"></i> Instalar App';
                installBtn.style.cssText = `
                    position: fixed;
                    top: 10px;
                    right: 10px;
                    z-index: 1000;
                    padding: 12px 20px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    border: none;
                    border-radius: 8px;
                    cursor: pointer;
                    font-size: 14px;
                    font-weight: 600;
                    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                    transition: all 0.3s ease;
                `;
                
                installBtn.addEventListener('click', installApp);
                installBtn.addEventListener('mouseenter', () => {
                    installBtn.style.transform = 'scale(1.05)';
                    installBtn.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.6)';
                });
                installBtn.addEventListener('mouseleave', () => {
                    installBtn.style.transform = 'scale(1)';
                    installBtn.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
                });
                
                document.body.appendChild(installBtn);
                installButton = installBtn;
                
                setTimeout(() => {
                    installBtn.style.animation = 'pulse 2s infinite';
                }, 1000);
            }
        }

        function installApp() {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                
                deferredPrompt.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        if (installButton) {
                            installButton.style.display = 'none';
                        }
                    }
                    deferredPrompt = null;
                });
            }
        }

        // ==================== PWA APP INSTALLED EVENT ====================
        window.addEventListener('appinstalled', (evt) => {
            if (installButton) {
                installButton.style.display = 'none';
            }
            
            Swal.fire({
                title: '<i class="fas fa-check-circle"></i> ¡Instalación Exitosa!',
                html: `
                    <div style="font-size: 1.1rem; margin-bottom: 15px;">
                        Sistema de Almacén instalado correctamente
                    </div>
                    <div style="font-size: 2.5rem; color: #28a745; margin: 15px 0;">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div style="font-size: 0.9rem; color: #666;">
                        Ahora puedes acceder desde tu pantalla de inicio
                    </div>
                `,
                icon: 'success',
                confirmButtonText: '¡Genial!',
                confirmButtonColor: '#28a745',
                timer: 5000,
                timerProgressBar: true
            });
        });

        // ==================== PWA UPDATE NOTIFICATION ====================
        function showUpdateNotification(data) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 70px;
                right: 10px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                border-radius: 10px;
                z-index: 1001;
                max-width: 350px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.3);
                animation: slideInRight 0.5s ease;
                font-family: Arial, sans-serif;
            `;
            
            notification.innerHTML = `
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: 24px; margin-right: 10px;"><i class="fas fa-sync-alt"></i></span>
                    <h4 style="margin: 0; font-size: 16px;">Nueva versión disponible</h4>
                </div>
                <p style="margin: 5px 0; font-size: 14px;"><strong>Versión:</strong> ${data.version}</p>
                <p style="margin: 5px 0; font-size: 14px;"><strong>Fecha:</strong> ${data.date}</p>
                <p style="margin: 10px 0; font-size: 13px;">${data.changes}</p>
                <div style="margin-top: 15px;">
                    <button onclick="location.reload()" style="background: white; color: #667eea; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer; margin-right: 10px; font-weight: bold;">
                        <i class="fas fa-redo"></i> Actualizar
                    </button>
                    <button onclick="this.closest('div').parentElement.remove()" style="background: transparent; color: white; border: 1px solid rgba(255,255,255,0.5); padding: 8px 16px; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-clock"></i> Más tarde
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.style.animation = 'slideOutRight 0.5s ease';
                    setTimeout(() => notification.remove(), 500);
                }
            }, 15000);
        }

        // ==================== PWA CONNECTION STATUS ====================
        window.addEventListener('online', () => {
            showConnectionNotification('online');
            if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                navigator.serviceWorker.controller.postMessage({type: 'CHECK_VERSION'});
            }
        });

        window.addEventListener('offline', () => {
            showConnectionNotification('offline');
        });

        function showConnectionNotification(status) {
            const notification = document.createElement('div');
            const isOnline = status === 'online';
            
            notification.style.cssText = `
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: ${isOnline ? '#28a745' : '#ffc107'};
                color: ${isOnline ? 'white' : '#212529'};
                padding: 15px 20px;
                border-radius: 8px;
                z-index: 1002;
                animation: slideInUp 0.5s ease;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                font-weight: 600;
            `;
            notification.innerHTML = `<i class="fas fa-${isOnline ? 'wifi' : 'exclamation-triangle'}"></i> ${isOnline ? 'Conexión restaurada' : 'Modo offline - Funcionalidad limitada'}`;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, isOnline ? 3000 : 5000);
        }

        // ==================== TOGGLE PASSWORD VISIBILITY ====================
        function togglePassword() {
            const passwordInput = document.getElementById('contrasena');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // ==================== EXISTING FUNCTIONALITY ====================
        $(document).ready(function () {
            $('#usuario').keypress(function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#contrasena').focus();
                }
            });

            $('#contrasena').keypress(function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#loginForm').submit();
                }
            });

            $('#loginForm').submit(function (event) {
                event.preventDefault();

                $('#contenedor').fadeOut();
                $('#loaderContainer').fadeIn();

                $.ajax({
                    url: 'login/solUsuario.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        let data = JSON.parse(response);
                        setTimeout(function () {
                            if (data.status === 'success') {
                                window.location.href = data.redirectUrl;
                            } else {
                                $('#loaderContainer').fadeOut();
                                $('#contenedor').fadeIn();

                                $('#usuario, #contrasena').addClass('input-error');

                                $('#usuario, #contrasena').on('input', function () {
                                    $(this).removeClass('input-error');
                                });

                                Swal.fire({
                                    title: '¡Acceso Denegado!',
                                    html: `
                                        <div style="font-size: 1.2rem; margin-bottom: 15px;">
                                            Usuario o contraseña incorrectos
                                        </div>
                                        <div style="font-size: 3rem; color: #e74c3c; margin: 15px 0;">
                                            <i class="fas fa-lock fa-shake"></i>
                                        </div>
                                        <div style="font-size: 0.9rem; color: #666;">
                                            Por favor, verifica tus credenciales
                                        </div>
                                    `,
                                    icon: 'error',
                                    confirmButtonText: 'Intentar de nuevo',
                                    confirmButtonColor: '#e74c3c',
                                    backdrop: `rgba(0,0,0,0.4)`
                                });
                            }
                        }, 2000);
                    },
                    error: function () {
                        setTimeout(function () {
                            $('#loaderContainer').fadeOut();
                            $('#contenedor').fadeIn();
                            
                            Swal.fire({
                                title: 'Error de Conexión',
                                text: 'No se pudo conectar con el servidor. Intenta de nuevo.',
                                icon: 'error',
                                confirmButtonText: 'Entendido'
                            });
                        }, 2000);
                    }
                });
            });

            // Cambiar texto "Bienvenido" <-> "Sistema de Almacén"
            let mostrandoBienvenido = true;
            setInterval(function () {
                const titulo = document.getElementById("tituloAnimado");
                titulo.classList.remove("fade-in");
                titulo.classList.add("fade-out");
                setTimeout(function () {
                    titulo.textContent = mostrandoBienvenido ? "Sistema de Almacén" : "Bienvenido";
                    mostrandoBienvenido = !mostrandoBienvenido;
                    titulo.classList.remove("fade-out");
                    titulo.classList.add("fade-in");
                }, 1000);
            }, 4000);
        });

        function toggleCredits() {
            const creditsSection = document.querySelector('.credits');
            creditsSection.classList.toggle('show-credits');
        }

        // ==================== CSS ANIMATIONS DINÁMICAS ====================
        const dynamicStyles = document.createElement('style');
        dynamicStyles.textContent = `
            @keyframes pulse {
                0% { box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
                50% { box-shadow: 0 6px 25px rgba(102, 126, 234, 0.7); }
                100% { box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4); }
            }
            
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            
            @keyframes slideInUp {
                from { transform: translateY(100%); opacity: 0; }
                to { transform: translateY(0); opacity: 1; }
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }
        `;
        document.head.appendChild(dynamicStyles);

        console.log('📦 Sistema de Almacén - PWA Cargado');
        console.log('✓ Service Worker');
        console.log('✓ Modo Offline');
        console.log('✓ Instalación PWA');
    </script>

</body>
</html>

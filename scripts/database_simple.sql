CREATE TABLE roles (
    idRol INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rol VARCHAR(30) NOT NULL UNIQUE,
    permisos VARCHAR(255) NOT NULL,
    fechaCreacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    codigo_empleado VARCHAR(20) NOT NULL UNIQUE,

    nombre VARCHAR(60) NOT NULL,
    apellido_paterno VARCHAR(60) NOT NULL,
    apellido_materno VARCHAR(60),

    email VARCHAR(120) UNIQUE,
    contrasena VARCHAR(255) NOT NULL,

    idRol INT UNSIGNED NOT NULL,
    imagen_perfil VARCHAR(255) DEFAULT 'default-avatar.png',

    activo TINYINT(1) NOT NULL DEFAULT 1,

    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_roles
        FOREIGN KEY (idRol) REFERENCES roles(idRol)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE sesiones (
    idSesion INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    idUsuario INT UNSIGNED NOT NULL,
    sessionId VARCHAR(255) NOT NULL,
    ipAddress VARCHAR(45),
    userAgent TEXT,

    fechaInicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fechaFin DATETIME,
    ultimaActividad DATETIME DEFAULT CURRENT_TIMESTAMP 
        ON UPDATE CURRENT_TIMESTAMP,

    estado ENUM('activa', 'cerrada', 'expirada') DEFAULT 'activa',

    modulo VARCHAR(50),
    dispositivo VARCHAR(100),

    INDEX idx_sessionId (sessionId),
    INDEX idx_fechaInicio (fechaInicio),
    INDEX idx_estado (estado),

    CONSTRAINT fk_sesiones_usuarios
        FOREIGN KEY (idUsuario) REFERENCES usuarios(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

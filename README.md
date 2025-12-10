# sistema-almacen
Sistema de gestión de almacén con control de inventarios y capturas operativas.

## Desarrollo rápido

- Inicia el servidor embebido de PHP con router:

```
php -S 127.0.0.1:8080 server.php
```

- Configura `.env` (ver `.env.example`). Para el servidor embebido puedes usar:

```
APP_URL=http://127.0.0.1:8080
```

- Accede a `http://127.0.0.1:8080/login` y usa el usuario `admin` / `admin123` (puedes crear la tabla con `php scripts/create_users_table.php`).

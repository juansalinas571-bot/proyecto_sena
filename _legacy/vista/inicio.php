<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Access | Inicio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vista/assets/css/app.css">
</head>
<body class="landing-body">
    <header class="topbar">
        <div class="brand">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Secure Access</span>
        </div>
        <a class="btn btn-primary" href="index.php?accion=panel">Administrar usuarios</a>
    </header>

    <main class="hero">
        <section class="hero-card">
            <p class="badge-soft">Soluciones para empresas de seguridad</p>
            <h1>Control de acceso moderno, seguro y centralizado</h1>
            <p>
                Gestiona usuarios, perfiles y estados desde un solo panel MVC. Integración con
                base de datos única y flujo administrativo más ordenado.
            </p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="index.php?accion=panel">Ir al panel</a>
                <a class="btn btn-secondary" href="#servicios">Ver servicios</a>
            </div>
        </section>
    </main>

    <section id="servicios" class="features">
        <article class="feature">
            <i class="bi bi-fingerprint"></i>
            <h3>Identidad confiable</h3>
            <p>Registro único de usuarios y perfiles para control operativo.</p>
        </article>
        <article class="feature">
            <i class="bi bi-people-fill"></i>
            <h3>Gestión central</h3>
            <p>CRUD completo con visualización clara y acciones rápidas.</p>
        </article>
        <article class="feature">
            <i class="bi bi-database-lock"></i>
            <h3>Base de datos unificada</h3>
            <p>Todo conectado a `proyecto_sena` con la tabla `usuario`.</p>
        </article>
    </section>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios | Secure-Access</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --azul-principal: #0f172a;
            --azul-secundario: #020617;
            --cian: #06b6d4;
            --cian-hover: #0891b2;
            --blanco: #ffffff;
            --gris: #94a3b8;
            --fondo: #020617;
            --borde: rgba(6, 182, 212, 0.25);
        }

        body {
            background: linear-gradient(145deg, #020617, #0b1120 50%, #020617);
            color: var(--gris);
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(15, 23, 42, 0.96);
            border-bottom: 1px solid var(--borde);
            padding: 16px 28px;
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(8px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--cian);
            font-weight: 700;
            font-size: 1.2rem;
            letter-spacing: 0.4px;
        }

        .logo i {
            font-size: 1.3rem;
        }

        .btn-top {
            text-decoration: none;
            background: var(--cian);
            color: #001f2c;
            padding: 9px 16px;
            border-radius: 24px;
            font-weight: 700;
            transition: 0.2s;
        }

        .btn-top:hover {
            background: var(--cian-hover);
            color: var(--blanco);
        }

        .panel {
            width: min(1200px, 94%);
            margin: 30px auto 40px;
        }

        .panel-header {
            margin-bottom: 20px;
        }

        .panel-header h1 {
            color: var(--blanco);
            font-size: clamp(1.6rem, 3vw, 2.3rem);
            margin-bottom: 6px;
        }

        .panel-header p {
            color: var(--gris);
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1.6fr;
            gap: 20px;
            align-items: start;
        }

        .card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.97), rgba(2, 6, 23, 0.97));
            border: 1px solid var(--borde);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
            padding: 24px;
        }

        .card h2 {
            color: var(--cian);
            font-size: 1.2rem;
            margin-bottom: 14px;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            color: #dbe9ff;
            font-size: 0.95rem;
        }

        .form-control,
        .form-select {
            width: 100%;
            border-radius: 10px;
            border: 1px solid #233555;
            background: #0a1326;
            color: #dce8ff;
            padding: 10px 12px;
            margin-bottom: 14px;
        }

        .form-control:focus,
        .form-select:focus {
            outline: none;
            border-color: var(--cian);
            box-shadow: 0 0 0 3px rgba(6, 182, 212, 0.2);
        }

        .btn-primary {
            border: 0;
            border-radius: 10px;
            padding: 11px 14px;
            background: var(--cian);
            color: #012433;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-primary:hover {
            background: var(--cian-hover);
            color: var(--blanco);
        }

        .tabla-wrap {
            overflow-x: auto;
        }

        .hint {
            color: #8fb2d8;
            font-size: 0.9rem;
            margin-top: -4px;
            margin-bottom: 14px;
        }

        @media (max-width: 980px) {
            .grid {
                grid-template-columns: 1fr;
            }

            .topbar {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Secure-Access</span>
        </div>
        <a class="btn-top" href="inicio.html">Volver al inicio</a>
    </header>

    <main class="panel">
        <div class="panel-header">
            <h1>Gestión de Usuarios</h1>
            <p>Administra registros, perfiles y estado de acceso desde un panel central.</p>
        </div>

        <div class="grid">
            <section class="card">
                <h2><i class="bi bi-person-plus-fill"></i> Registrar Usuario</h2>
                <form action="../controlador/usuariocontrolador.php" method="post">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input name="nombre" type="text" class="form-control" id="nombre" required>

                    <label for="doc" class="form-label">Número de Documento</label>
                    <input name="doc" type="number" min="10000000" max="999999999999" class="form-control" id="doc" required>

                    <label for="tel" class="form-label">Teléfono de Contacto</label>
                    <input name="tel" type="number" min="3000000000" max="3999999999" class="form-control" id="tel" required>

                    <label for="correo" class="form-label">Correo</label>
                    <input name="correo" type="email" class="form-control" id="correo" required>

                    <label for="perfil_reg" class="form-label">Perfil de Registro</label>
                    <select name="perfil" class="form-select" id="perfil_reg">
                        <option value="3">Supervisor de seguridad</option>
                        <option value="2">vigilante</option>
                        <option value="1">Administrador</option>
                    </select>

                    <label for="contra" class="form-label">Contraseña</label>
                    <input name="contra" type="password" class="form-control" id="contra" required>

                    <button type="submit" class="btn-primary">Registrar usuario</button>
                </form>
            </section>

            <section class="card">
                <h2><i class="bi bi-people-fill"></i> Usuarios Registrados</h2>
                <p class="hint">Consulta, edita o desactiva usuarios activos del sistema.</p>
                <div class="tabla-wrap">
                    <?php
                    include "../controlador/usuario_controlador2.php";
                    ?>
                </div>
            </section>
        </div>
    </main>
</body>
</html>

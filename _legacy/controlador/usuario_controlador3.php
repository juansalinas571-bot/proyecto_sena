<?php
include "../modelo/usuarios.php";

$datos = null;
try {
    $consultar = new usuario();
    if (isset($_GET["documento"])) {
        $datos = $consultar->consultaespecifica($_GET["documento"]);
    } else {
        echo "<script>alert('Documento no especificado para la consulta.');
        window.location.href='../vista/index.php';
        </script>";
        exit();
    }

    if ($datos instanceof PDOException) {
        echo "<script>alert('Error al consultar usuario para edición: " . $datos->getMessage() . "');
        window.location.href='../vista/index.php';
        </script>";
        exit();
    } elseif (!$datos) {
        echo "<script>alert('Usuario no encontrado.');
        window.location.href='../vista/index.php';
        </script>";
        exit();
    }
} catch (Exception $e) {
    echo "<script>alert('Error del sistema al cargar usuario para edición: " . $e->getMessage() . "');
    window.location.href='../vista/index.php';
    </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Secure-Access</title>
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
            padding: 16px 28px;
            background: rgba(15, 23, 42, 0.96);
            border-bottom: 1px solid var(--borde);
            position: sticky;
            top: 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--cian);
            font-size: 1.15rem;
            font-weight: 700;
        }

        .btn-back {
            text-decoration: none;
            background: var(--cian);
            color: #002130;
            font-weight: 700;
            padding: 9px 16px;
            border-radius: 24px;
            transition: 0.2s;
        }

        .btn-back:hover {
            background: var(--cian-hover);
            color: var(--blanco);
        }

        .wrap {
            width: min(760px, 94%);
            margin: 30px auto;
        }

        .card {
            background: linear-gradient(180deg, rgba(15, 23, 42, 0.97), rgba(2, 6, 23, 0.97));
            border: 1px solid var(--borde);
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
            padding: 24px;
        }

        h2 {
            color: var(--cian);
            margin-bottom: 14px;
        }

        .desc {
            color: #8fb2d8;
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            color: #dbe8ff;
            margin-bottom: 6px;
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

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .btn-primary,
        .btn-secondary {
            border: 0;
            text-decoration: none;
            border-radius: 10px;
            padding: 11px 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            display: inline-block;
        }

        .btn-primary {
            background: var(--cian);
            color: #012433;
        }

        .btn-primary:hover {
            background: var(--cian-hover);
            color: var(--blanco);
        }

        .btn-secondary {
            background: transparent;
            color: #d5e6ff;
            border: 1px solid rgba(148, 163, 184, 0.35);
        }

        .btn-secondary:hover {
            border-color: var(--cian);
            color: var(--cian);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <i class="bi bi-pencil-square"></i>
            <span>Editar Usuario</span>
        </div>
        <a class="btn-back" href="../vista/index.php">Volver</a>
    </header>

    <main class="wrap">
        <section class="card">
            <h2>Actualizar información</h2>
            <p class="desc">Modifica los datos del usuario y guarda los cambios.</p>

            <form action="../controlador/controlador4.php" method="post">
                <label for="nombre" class="form-label">Nombre</label>
                <input value="<?php echo htmlspecialchars($datos['nombre']); ?>" name="nombre" type="text" class="form-control" id="nombre" required>

                <label for="num_doc" class="form-label">Número de Documento</label>
                <input readonly value="<?php echo htmlspecialchars($datos['num_doc']); ?>" name="num_doc" type="number" min="10000000" max="999999999999" class="form-control" id="num_doc" required>

                <label for="telefono" class="form-label">Teléfono de Contacto</label>
                <input value="<?php echo htmlspecialchars($datos['telefono']); ?>" name="telefono" type="number" min="3000000000" max="3999999999" class="form-control" id="telefono" required>

                <label for="correo" class="form-label">Correo</label>
                <input value="<?php echo htmlspecialchars($datos['correo']); ?>" name="correo" type="email" class="form-control" id="correo" required>

                <label for="perfil" class="form-label">Perfil de Registro</label>
                <select name="perfil" class="form-select" id="perfil">
                    <option value="2" <?php echo ($datos['perfil'] == '2') ? 'selected' : ''; ?>>Vigilante</option>
                    <option value="1" <?php echo ($datos['perfil'] == '1') ? 'selected' : ''; ?>>Administrador</option>
                </select>

                <label for="contrasena" class="form-label">Contraseña (dejar en blanco para no cambiar)</label>
                <input name="contrasena" type="password" class="form-control" id="contrasena" placeholder="Ingrese nueva contraseña si desea cambiarla">

                <div class="actions">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="../vista/index.php" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>

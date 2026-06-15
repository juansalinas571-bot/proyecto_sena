<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Access | Editar usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="vista/assets/css/app.css">
</head>
<body class="panel-body">
    <header class="topbar">
        <div class="brand">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Secure Access</span>
        </div>
        <a class="btn btn-secondary" href="index.php?accion=panel">Volver al panel</a>
    </header>

    <main class="edit-container">
        <section class="card form-card">
            <h2>Editar usuario</h2>
            <p class="muted">Actualiza la información del usuario seleccionado.</p>

            <form action="index.php?accion=actualizar" method="post" class="form-grid">
                <label>
                    Nombre
                    <input name="nombre" type="text" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </label>
                <label>
                    Número de documento
                    <input name="num_doc" type="number" value="<?php echo htmlspecialchars((string)$usuario['num_doc']); ?>" readonly required>
                </label>
                <label>
                    Teléfono
                    <input name="telefono" type="number" value="<?php echo htmlspecialchars((string)$usuario['telefono']); ?>" min="1" required>
                </label>
                <label>
                    Correo
                    <input name="correo" type="email" value="<?php echo htmlspecialchars($usuario['correo']); ?>" required>
                </label>
                <label>
                    Perfil
                    <select name="perfil" required>
                        <option value="1" <?php echo $usuario['perfil'] === '1' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="2" <?php echo $usuario['perfil'] === '2' ? 'selected' : ''; ?>>Cajero</option>
                        <option value="3" <?php echo $usuario['perfil'] === '3' ? 'selected' : ''; ?>>Cliente</option>
                    </select>
                </label>
                <label>
                    Nueva contraseña (opcional)
                    <input name="contrasena" type="password" minlength="4" placeholder="Dejar en blanco para mantener actual">
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-floppy2-fill"></i> Guardar cambios
                </button>
            </form>
        </section>
    </main>
</body>
</html>

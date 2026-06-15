<?php
include "../modelo/usuarios.php";

try {
    $modificar = new usuario();
    $Respuesta = $modificar->actualizar(
        $_POST["nombre"] ?? '',
        $_POST["num_doc"] ?? '',
        $_POST["telefono"] ?? '',
        $_POST["correo"] ?? '',
        $_POST["perfil"] ?? '',
        $_POST["contrasena"] ?? ''
    );

    if ($Respuesta === 1) {
        echo "<script>alert('Usuario actualizado exitosamente');
        window.location.href='../vista/index.php';
        </script>";
    } elseif ($Respuesta instanceof PDOException) {
        echo "<script>alert('Error al actualizar usuario: " . $Respuesta->getMessage() . "');
        window.location.href='../vista/index.php';
        </script>";
    } else {
        echo "<script>alert('Error desconocido al actualizar usuario');
        window.location.href='../vista/index.php';
        </script>";
    }
} catch (Exception $e) {
    echo "<script>alert('Error del sistema: " . $e->getMessage() . "');
    window.location.href='../vista/index.php';
    </script>";
}
?>

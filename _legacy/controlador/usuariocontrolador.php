<?php
include "../modelo/usuarios.php";

try {
    $nuevo_usuario = new usuario();
    $Respuesta = $nuevo_usuario->registrar(
        $_POST["nombre"] ?? '',
        $_POST["doc"] ?? '',
        $_POST["tel"] ?? '',
        $_POST["correo"] ?? '',
        $_POST["perfil"] ?? '',
        $_POST["contra"] ?? ''
    );

    if ($Respuesta === 1) {
        echo "<script>alert('Registro exitoso');
        window.location.href='../vista/index.php';
        </script>";
    } elseif ($Respuesta instanceof PDOException) {
        if ($Respuesta->getCode() == "23000") {
            echo "<script>alert('El número de documento ya existe. Por favor, verifique.');
            window.location.href='../vista/index.php';
            </script>";
        } else {
            echo "<script>alert('Error de base de datos al registrar: " . $Respuesta->getMessage() . "');
            window.location.href='../vista/index.php';
            </script>";
        }
    } else {
        echo "<script>alert('Error desconocido al registrar usuario');
        window.location.href='../vista/index.php';
        </script>";
    }
} catch (Exception $e) {
    echo "<script>alert('Error del sistema: " . $e->getMessage() . "');
    window.location.href='../vista/index.php';
    </script>";
}
?>

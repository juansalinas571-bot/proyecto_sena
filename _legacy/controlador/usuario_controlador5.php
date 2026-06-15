<?php
include "../modelo/usuarios.php";

if (isset($_GET["documento"])) {
    try {
        $eliminar_usuario = new usuario();
        $Respuesta = $eliminar_usuario->eliminar($_GET["documento"]);

        if ($Respuesta === 1) {
            echo "<script>alert('Usuario eliminado exitosamente');
            window.location.href='../vista/index.php';
            </script>";
        } elseif ($Respuesta instanceof PDOException) {
            echo "<script>alert('Error de base de datos al eliminar: " . $Respuesta->getMessage() . "');
            window.location.href='../vista/index.php';
            </script>";
        } else {
            echo "<script>alert('Error desconocido al eliminar usuario');
            window.location.href='../vista/index.php';
            </script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Error del sistema al eliminar usuario: " . $e->getMessage() . "');
        window.location.href='../vista/index.php';
        </script>";
    }
} else {
    echo "<script>alert('Documento no especificado para eliminar.');
    window.location.href='../vista/index.php';
    </script>";
}
?>

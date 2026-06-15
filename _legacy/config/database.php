<?php
function obtenerConexion(): PDO
{
    static $conexion = null;

    if ($conexion === null) {
        $dsn = 'mysql:host=localhost;port=3307;dbname=proyecto_sena;charset=utf8mb4';
        $usuario = 'root';
        $contrasena = '';

        $conexion = new PDO($dsn, $usuario, $contrasena, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    return $conexion;
}
?>

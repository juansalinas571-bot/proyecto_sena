<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = obtenerConexion();
    }

    public function crear(string $nombre, int $numDoc, int $telefono, string $correo, string $perfil, string $contrasena): bool
    {
        $sql = 'INSERT INTO usuario (nombre, num_doc, telefono, correo, perfil, contrasena) VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $this->conexion->prepare($sql);
        $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

        return $stmt->execute([$nombre, $numDoc, $telefono, $correo, $perfil, $contrasenaHash]);
    }

    public function obtenerActivos(): array
    {
        $sql = "SELECT id, nombre, num_doc, telefono, correo, perfil FROM usuario WHERE estado = 'A' ORDER BY id DESC";
        $stmt = $this->conexion->query($sql);

        return $stmt->fetchAll();
    }

    public function obtenerPorDocumento(int $numDoc): ?array
    {
        $sql = "SELECT id, nombre, num_doc, telefono, correo, perfil FROM usuario WHERE num_doc = ? AND estado = 'A' LIMIT 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$numDoc]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public function actualizar(string $nombre, int $numDoc, int $telefono, string $correo, string $perfil, ?string $contrasena = null): bool
    {
        if ($contrasena !== null && $contrasena !== '') {
            $sql = 'UPDATE usuario SET nombre = ?, telefono = ?, correo = ?, perfil = ?, contrasena = ? WHERE num_doc = ?';
            $stmt = $this->conexion->prepare($sql);
            $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);

            return $stmt->execute([$nombre, $telefono, $correo, $perfil, $contrasenaHash, $numDoc]);
        }

        $sql = 'UPDATE usuario SET nombre = ?, telefono = ?, correo = ?, perfil = ? WHERE num_doc = ?';
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([$nombre, $telefono, $correo, $perfil, $numDoc]);
    }

    public function eliminar(int $numDoc): bool
    {
        $sql = "UPDATE usuario SET estado = 'I' WHERE num_doc = ?";
        $stmt = $this->conexion->prepare($sql);

        return $stmt->execute([$numDoc]);
    }
}
?>

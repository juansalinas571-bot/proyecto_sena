<?php

require_once "conexion.php";

class Usuario
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = Conexion::conectar();
    }

    // =========================
    // REGISTRAR USUARIO
    // =========================
    public function registrar($nombre, $documento, $telefono, $correo, $perfil, $password)
    {
        try {

            $clave = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO usuario
            (nombre, num_doc, telefono, correo, perfil, contrasena, estado)
            VALUES
            (:nombre, :documento, :telefono, :correo, :perfil, :contrasena, 'A')";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":documento", $documento);
            $stmt->bindParam(":telefono", $telefono);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":perfil", $perfil);
            $stmt->bindParam(":contrasena", $clave);

            return $stmt->execute();

        } catch (PDOException $e) {

            error_log("Error registrar usuario: " . $e->getMessage());

            return false;
        }
    }

    // =========================
    // CONSULTA GENERAL
    // =========================
    public function consultageneral()
    {
        try {

            $sql = "SELECT 
                        id,
                        nombre,
                        num_doc,
                        telefono,
                        correo,
                        perfil
                    FROM usuario
                    WHERE estado = 'A'
                    ORDER BY id DESC";

            $stmt = $this->conexion->prepare($sql);

            $stmt->execute();

            return $stmt->fetchAll();

        } catch (PDOException $e) {

            error_log("Error consulta general: " . $e->getMessage());

            return [];
        }
    }

    // =========================
    // CONSULTA ESPECÍFICA
    // =========================
    public function consultaespecifica($documento)
    {
        try {

            $sql = "SELECT * 
                    FROM usuario
                    WHERE num_doc = :documento
                    AND estado = 'A'";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":documento", $documento);

            $stmt->execute();

            return $stmt->fetch();

        } catch (PDOException $e) {

            error_log("Error consulta específica: " . $e->getMessage());

            return false;
        }
    }

    // =========================
    // ACTUALIZAR USUARIO
    // =========================
    public function actualizar($nombre, $documento, $telefono, $correo, $perfil, $password = null)
    {
        try {

            // SI ACTUALIZA CONTRASEÑA
            if (!empty($password)) {

                $clave = password_hash($password, PASSWORD_DEFAULT);

                $sql = "UPDATE usuario SET
                        nombre = :nombre,
                        telefono = :telefono,
                        correo = :correo,
                        perfil = :perfil,
                        contrasena = :contrasena
                        WHERE num_doc = :documento";

                $stmt = $this->conexion->prepare($sql);

                $stmt->bindParam(":contrasena", $clave);

            } else {

                $sql = "UPDATE usuario SET
                        nombre = :nombre,
                        telefono = :telefono,
                        correo = :correo,
                        perfil = :perfil
                        WHERE num_doc = :documento";

                $stmt = $this->conexion->prepare($sql);
            }

            $stmt->bindParam(":nombre", $nombre);
            $stmt->bindParam(":telefono", $telefono);
            $stmt->bindParam(":correo", $correo);
            $stmt->bindParam(":perfil", $perfil);
            $stmt->bindParam(":documento", $documento);

            return $stmt->execute();

        } catch (PDOException $e) {

            error_log("Error actualizar usuario: " . $e->getMessage());

            return false;
        }
    }

    // =========================
    // ELIMINAR (LÓGICO)
    // =========================
    public function eliminar($documento)
    {
        try {

            $sql = "UPDATE usuario
                    SET estado = 'I'
                    WHERE num_doc = :documento";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":documento", $documento);

            return $stmt->execute();

        } catch (PDOException $e) {

            error_log("Error eliminar usuario: " . $e->getMessage());

            return false;
        }
    }

    // =========================
    // LOGIN
    // =========================
    public function login($documento, $password)
    {
        try {

            $sql = "SELECT *
                    FROM usuario
                    WHERE num_doc = :documento
                    AND estado = 'A'
                    LIMIT 1";

            $stmt = $this->conexion->prepare($sql);

            $stmt->bindParam(":documento", $documento);

            $stmt->execute();

            $usuario = $stmt->fetch();

            if ($usuario && password_verify($password, $usuario["contrasena"])) {

                return $usuario;
            }

            return false;

        } catch (PDOException $e) {

            error_log("Error login: " . $e->getMessage());

            return false;
        }
    }

    // =========================
    // DESTRUCTOR
    // =========================
    public function __destruct()
    {
        $this->conexion = null;
    }
}

?>

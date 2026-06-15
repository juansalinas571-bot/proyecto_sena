<?php
require_once "conexion.php";

class Usuario{

    public function login($usuario,$password){

        $conexion = Conexion::conectar();

        $sql = "SELECT * FROM usuarios 
                WHERE usuario = :usuario 
                AND password = :password";

        $stmt = $conexion->prepare($sql);

        $stmt->bindParam(":usuario",$usuario);
        $stmt->bindParam(":password",$password);

        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

}
?>
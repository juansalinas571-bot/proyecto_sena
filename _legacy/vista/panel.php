<?php
session_start();

if(!isset($_SESSION['usuario'])){
header("Location: login.php");
}
?>

<h2>Bienvenido</h2>

Usuario: <?php echo $_SESSION['usuario']; ?>

<br><br>

Perfil: <?php echo $_SESSION['perfil']; ?>

<br><br>

<a href="../controlador/logout.php">Cerrar sesión</a>
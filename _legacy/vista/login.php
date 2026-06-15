<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Secure Access | Login</title>

<style>
*{
margin:0;
padding:0;
box-sizing:border-box;
font-family: Arial, Helvetica, sans-serif;
}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background: linear-gradient(135deg,#020617,#020f2a,#021c3a);
color:white;
}

.login-box{
width:350px;
background:#06152e;
padding:35px;
border-radius:12px;
box-shadow:0px 0px 25px rgba(0,255,255,0.15);
text-align:center;
border:1px solid rgba(6,182,212,0.2);
}

.login-box h2{
color:#00e5ff;
margin-bottom:5px;
letter-spacing:1px;
}

.login-box p{
color:#94a3b8;
margin-bottom:25px;
font-size:14px;
}

.login-box input{
width:100%;
padding:12px;
margin:10px 0;
background:#020617;
border:1px solid rgba(6,182,212,0.2);
border-radius:8px;
color:white;
outline:none;
transition:0.2s;
}

.login-box input:focus{
border-color:#00e5ff;
box-shadow:0 0 8px rgba(0,229,255,0.3);
}

.login-box button{
width:100%;
padding:12px;
margin-top:10px;
border:none;
border-radius:8px;
background:#00e5ff;
color:#021024;
font-weight:bold;
cursor:pointer;
transition:0.3s;
}

.login-box button:hover{
background:#00bcd4;
}

.error{
color:#ff6b6b;
font-size:13px;
margin-top:10px;
display:block;
}

.logo{
font-size:28px;
margin-bottom:10px;
}
</style>

</head>
<body>

<div class="login-box">

<div class="logo">🔐</div>
<h2>SECURE-ACCESS</h2>
<p>Seguridad Biométrica Inteligente</p>

<form action="../vista/index.php" method="POST">

<input type="text" name="usuario" placeholder="Usuario" required>

<input type="password" name="password" placeholder="Contraseña" required>

<button type="submit">Iniciar Sesión</button>

</form>

<?php
if(isset($_GET['error'])){
echo "<span class='error'>Usuario o contraseña incorrectos</span>";
}
?>

</div>

</body>
</html> 
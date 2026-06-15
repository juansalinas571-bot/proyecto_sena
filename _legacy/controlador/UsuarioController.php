<?php
require_once __DIR__ . '/../modelo/Usuario.php';

class UsuarioController
{
    private Usuario $modelo;

    public function __construct()
    {
        $this->modelo = new Usuario();
    }

    public function inicio(): void
    {
        require __DIR__ . '/../vista/inicio.php';
    }

    public function panel(): void
    {
        $usuarios = $this->modelo->obtenerActivos();
        $mensaje = $_GET['mensaje'] ?? '';
        $tipo = $_GET['tipo'] ?? '';

        require __DIR__ . '/../vista/index.php';
    }

    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirConMensaje('panel', 'Método no permitido.', 'error');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $numDoc = (int)($_POST['num_doc'] ?? 0);
        $telefono = (int)($_POST['telefono'] ?? 0);
        $correo = trim($_POST['correo'] ?? '');
        $perfil = trim($_POST['perfil'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        if ($nombre === '' || $numDoc === 0 || $telefono === 0 || $correo === '' || $perfil === '' || $contrasena === '') {
            $this->redirigirConMensaje('panel', 'Completa todos los campos obligatorios.', 'error');
        }

        try {
            $this->modelo->crear($nombre, $numDoc, $telefono, $correo, $perfil, $contrasena);
            $this->redirigirConMensaje('panel', 'Usuario registrado exitosamente.', 'ok');
        } catch (PDOException $e) {
            if ((string)$e->getCode() === '23000') {
                $this->redirigirConMensaje('panel', 'El número de documento ya existe.', 'error');
            }

            $this->redirigirConMensaje('panel', 'Error al registrar el usuario.', 'error');
        }
    }

    public function formEditar(): void
    {
        $numDoc = (int)($_GET['num_doc'] ?? 0);

        if ($numDoc === 0) {
            $this->redirigirConMensaje('panel', 'Documento inválido para editar.', 'error');
        }

        $usuario = $this->modelo->obtenerPorDocumento($numDoc);

        if (!$usuario) {
            $this->redirigirConMensaje('panel', 'No se encontró el usuario solicitado.', 'error');
        }

        require __DIR__ . '/../vista/editar.php';
    }

    public function actualizar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigirConMensaje('panel', 'Método no permitido.', 'error');
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $numDoc = (int)($_POST['num_doc'] ?? 0);
        $telefono = (int)($_POST['telefono'] ?? 0);
        $correo = trim($_POST['correo'] ?? '');
        $perfil = trim($_POST['perfil'] ?? '');
        $contrasena = $_POST['contrasena'] ?? null;

        if ($nombre === '' || $numDoc === 0 || $telefono === 0 || $correo === '' || $perfil === '') {
            $this->redirigirConMensaje('panel', 'Faltan campos obligatorios para actualizar.', 'error');
        }

        try {
            $this->modelo->actualizar($nombre, $numDoc, $telefono, $correo, $perfil, $contrasena);
            $this->redirigirConMensaje('panel', 'Usuario actualizado correctamente.', 'ok');
        } catch (PDOException $e) {
            $this->redirigirConMensaje('panel', 'No se pudo actualizar el usuario.', 'error');
        }
    }

    public function eliminar(): void
    {
        $numDoc = (int)($_GET['num_doc'] ?? 0);

        if ($numDoc === 0) {
            $this->redirigirConMensaje('panel', 'Documento inválido para eliminar.', 'error');
        }

        try {
            $this->modelo->eliminar($numDoc);
            $this->redirigirConMensaje('panel', 'Usuario desactivado correctamente.', 'ok');
        } catch (PDOException $e) {
            $this->redirigirConMensaje('panel', 'No se pudo eliminar el usuario.', 'error');
        }
    }

    private function redirigirConMensaje(string $accion, string $mensaje, string $tipo): void
    {
        header('Location: index.php?accion=' . urlencode($accion) . '&mensaje=' . urlencode($mensaje) . '&tipo=' . urlencode($tipo));
        exit();
    }
}
?>

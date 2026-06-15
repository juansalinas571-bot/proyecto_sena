<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

class Auth extends BaseController // Controlador para autenticación de usuarios (web)
{
    // Muestra el formulario de login y procesa el inicio de sesión
    public function login()
    {
        $session = service('session'); // Obtiene el servicio de sesión de CodeIgniter

        // Si el usuario ya está logueado, redirige al dashboard
        if ($session->get('user_id')) {
            return redirect()->to('/home'); // Redirección a la página principal
        }

        $data = ['title' => 'Iniciar Sesión', 'error' => '']; // Datos iniciales para la vista

        // Si la petición es POST, procesa el formulario de login
        if ($this->request->getMethod() === 'POST') {
            $username = trim($this->request->getPost('username')); // Obtiene el usuario del formulario
            $password = $this->request->getPost('password'); // Obtiene la contraseña

            // Valida que ambos campos tengan contenido
            if ($username === '' || $password === '') {
                $data['error'] = 'Todos los campos son obligatorios.'; // Mensaje de error
            } else {
                // Busca al usuario en la base de datos por nombre de usuario y activo
                $userModel = model('App\Models\UserModel');
                $user = $userModel->where('username', $username)->where('is_active', 1)->first();

                // Si el usuario existe y la contraseña es correcta
                if ($user && password_verify($password, $user['password'])) {
                    $session->set('user_id', $user['id']); // Guarda el ID en sesión
                    $session->set('user_data', $user); // Guarda todos los datos del usuario
                    return redirect()->to('/home'); // Redirige al dashboard
                } else {
                    $data['error'] = 'Usuario o contraseña incorrectos.'; // Credenciales inválidas
                }
            }
        }

        return view('auth/login', $data); // Renderiza la vista de login con los datos
    }

    // Cierra la sesión del usuario
    public function logout()
    {
        service('session')->destroy(); // Destruye todos los datos de sesión
        return redirect()->to('/login'); // Redirige al formulario de login
    }
}

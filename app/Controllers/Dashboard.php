<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

class Dashboard extends BaseController // Controlador para el panel principal del sistema
{
    // Muestra la página principal del dashboard con estadísticas
    public function index()
    {
        $session = service('session'); // Obtiene el servicio de sesión
        $user = $session->get('user_data'); // Obtiene los datos del usuario logueado

        $faceModel = model('App\Models\FaceModel'); // Carga el modelo de rostros
        $userModel = model('App\Models\UserModel'); // Carga el modelo de usuarios

        $data = [
            'title'  => 'Panel Principal', // Título de la página
            'user'   => $user, // Datos del usuario para la vista
            'faceCount' => $faceModel->countAll(), // Cuenta total de rostros registrados
            'userCount' => $userModel->countAll(), // Cuenta total de usuarios del sistema
        ];

        return view('dashboard/index', $data); // Renderiza la vista del dashboard con los datos
    }
}

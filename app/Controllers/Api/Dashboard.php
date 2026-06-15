<?php // Inicia el bloque de código PHP

namespace App\Controllers\Api; // Espacio de nombres para controladores API

use App\Controllers\ApiController; // Clase base para la API

class Dashboard extends ApiController // Controlador API para estadísticas del dashboard
{
    // Devuelve estadísticas generales del sistema
    public function stats()
    {
        $faceModel = model('App\Models\FaceModel'); // Modelo de rostros
        $userModel = model('App\Models\UserModel'); // Modelo de usuarios

        $totalFaces = $faceModel->countAllResults(); // Total de rostros registrados
        $totalUsers = $userModel->countAllResults(); // Total de usuarios del sistema
        $activeUsers = $userModel->where('is_active', 1)->countAllResults(); // Usuarios activos

        $recentFaces = $faceModel // Últimos 5 rostros registrados
            ->orderBy('created_at', 'DESC')
            ->findAll(5);

        return $this->respondSuccess([ // Respuesta con todas las estadísticas
            'total_faces'  => $totalFaces,   // Cuenta de rostros
            'total_users'  => $totalUsers,   // Cuenta de usuarios
            'active_users' => $activeUsers,  // Usuarios activos
            'recent_faces' => $recentFaces,  // Últimos rostros
        ]);
    }
}

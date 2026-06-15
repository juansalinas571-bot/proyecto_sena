<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Espacio de nombres raíz para controladores

use CodeIgniter\HTTP\ResponseInterface; // Interfaz para respuestas HTTP
use Config\JWT; // Configuración del JWT

abstract class ApiController extends BaseController // Clase base abstracta para controladores de la API
{
    // Inicialización: establece el tipo de contenido JSON para todas las respuestas
    public function initController($request, $response, $logger)
    {
        parent::initController($request, $response, $logger); // Llama al init del padre
        $this->response->setContentType('application/json'); // Todas las respuestas serán JSON
    }

    // Obtiene el usuario autenticado desde la petición (inyectado por JWTAuthFilter)
    protected function getAuthUser(): ?array
    {
        return $this->request->authUser ?? null; // Retorna los datos del usuario o null si no autenticado
    }

    // Envía una respuesta JSON genérica con código HTTP
    protected function respond($data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)->setJSON($data); // Asigna código y cuerpo JSON
    }

    // Respuesta exitosa estándar (código 200)
    protected function respondSuccess($data = null, string $message = 'OK'): ResponseInterface
    {
        return $this->respond([
            'status'  => 'success', // Indica que la operación fue exitosa
            'message' => $message,  // Mensaje descriptivo
            'data'    => $data,     // Datos de respuesta (puede ser null)
        ]);
    }

    // Respuesta de error estándar con código personalizable
    protected function respondError(string $message, int $status = 400, $errors = null): ResponseInterface
    {
        $body = [
            'status'  => 'error',   // Indica que ocurrió un error
            'message' => $message,  // Descripción del error
        ];

        if ($errors !== null) { // Si hay errores de validación detallados
            $body['errors'] = $errors; // Los agrega al cuerpo de la respuesta
        }

        return $this->respond($body, $status); // Envía la respuesta con el código HTTP indicado
    }

    // Respuesta para recursos creados exitosamente (código 201)
    protected function respondCreated($data = null, string $message = 'Creado exitosamente'): ResponseInterface
    {
        return $this->respond([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], 201); // Código 201 = Created
    }

    // Respuesta para eliminación exitosa (código 200)
    protected function respondDeleted(string $message = 'Eliminado exitosamente'): ResponseInterface
    {
        return $this->respond([
            'status'  => 'success',
            'message' => $message,
        ]);
    }

    // Respuesta para no autorizado (código 401)
    protected function respondUnauthorized(string $message = 'No autorizado'): ResponseInterface
    {
        return $this->respondError($message, 401); // 401 = Unauthorized
    }

    // Respuesta para acceso denegado por permisos (código 403)
    protected function respondForbidden(string $message = 'Acceso denegado'): ResponseInterface
    {
        return $this->respondError($message, 403); // 403 = Forbidden
    }

    // Respuesta para recurso no encontrado (código 404)
    protected function respondNotFound(string $message = 'Recurso no encontrado'): ResponseInterface
    {
        return $this->respondError($message, 404); // 404 = Not Found
    }

    // Respuesta para errores de validación (código 422)
    protected function respondValidationError($errors, string $message = 'Error de validación'): ResponseInterface
    {
        return $this->respondError($message, 422, $errors); // 422 = Unprocessable Entity
    }

    // Obtiene los datos JSON del cuerpo de la petición
    protected function getJsonInput(): ?array
    {
        return $this->request->getJSON(true); // true = devuelve como array asociativo
    }

    // Verifica si el usuario autenticado tiene un rol específico
    protected function requireRole(string $role): bool
    {
        $user = $this->getAuthUser(); // Obtiene usuario autenticado
        if (!$user) {
            return false; // No hay usuario autenticado
        }
        return $user['role'] === $role; // Verifica si el rol coincide
    }

    // Verifica si es administrador
    protected function requireAdmin(): bool
    {
        return $this->requireRole('admin'); // Solo rol 'admin'
    }

    // Verifica si es admin o supervisor
    protected function requireAdminOrSupervisor(): bool
    {
        $user = $this->getAuthUser(); // Obtiene usuario autenticado
        if (!$user) {
            return false; // No autenticado
        }
        return in_array($user['role'], ['admin', 'supervisor']); // Roles permitidos
    }

    // Obtiene parámetros de paginación desde la URL (query string)
    protected function getPaginationParams(): array
    {
        $page    = max(1, (int) $this->request->getGet('page'));  // Página actual (mínimo 1)
        $limit   = max(1, min(100, (int) $this->request->getGet('limit') ?: 10)); // Límite por página (1-100, default 10)
        $offset  = ($page - 1) * $limit; // Desplazamiento para la consulta SQL

        return compact('page', 'limit', 'offset'); // Devuelve los tres valores como array
    }
}

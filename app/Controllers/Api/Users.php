<?php // Inicia el bloque de código PHP

namespace App\Controllers\Api; // Espacio de nombres para controladores API

use App\Controllers\ApiController; // Clase base para la API

class Users extends ApiController // Controlador API para la gestión de usuarios (solo admin)
{
    // Lista todos los usuarios con paginación
    public function index()
    {
        if (!$this->requireAdmin()) { // Solo administradores
            return $this->respondForbidden('Solo administradores pueden ver usuarios');
        }

        $userModel = model('App\Models\UserModel');
        $params = $this->getPaginationParams(); // page, limit, offset

        $total = $userModel->countAllResults(); // Total de usuarios
        $users = $userModel
            ->orderBy('id', 'DESC')
            ->findAll($params['limit'], $params['offset']); // Paginado

        $sanitized = array_map(fn($u) => $this->sanitizeUser($u), $users); // Elimina contraseña de cada usuario

        return $this->respondSuccess([ // Respuesta paginada
            'users'      => $sanitized,
            'total'      => $total,
            'page'       => $params['page'],
            'limit'      => $params['limit'],
            'total_pages' => ceil($total / $params['limit']),
        ]);
    }

    // Muestra un usuario específico
    public function show($id)
    {
        $userModel = model('App\Models\UserModel');
        $user = $userModel->find($id);

        if (!$user) {
            return $this->respondNotFound('Usuario no encontrado');
        }

        $user = $this->getAuthUser(); // Usuario autenticado
        if ($user['role'] !== 'admin' && $user['id'] !== (int)$id) { // Solo admin o el propio usuario
            return $this->respondForbidden();
        }

        return $this->respondSuccess($this->sanitizeUser($user)); // Datos sin contraseña
    }

    // Crea un nuevo usuario
    public function create()
    {
        if (!$this->requireAdmin()) { // Solo admin
            return $this->respondForbidden();
        }

        $input = $this->getJsonInput(); // Datos JSON
        $errors = $this->validateUserInput($input); // Valida campos

        if (!empty($errors)) {
            return $this->respondValidationError($errors);
        }

        $userModel = model('App\Models\UserModel');
        $existing = $userModel->where('username', trim($input['username']))->first(); // Verifica duplicado
        if ($existing) {
            return $this->respondValidationError([
                'username' => 'El nombre de usuario ya existe',
            ]);
        }

        $userModel->insert([ // Crea el usuario
            'username'  => trim($input['username']),
            'password'  => password_hash($input['password'], PASSWORD_DEFAULT),
            'role'      => $input['role'] ?? 'vigilante',
            'full_name' => trim($input['full_name']),
            'is_active' => 1,
        ]);

        $user = $userModel->find($userModel->getInsertID()); // Recupera el nuevo usuario

        return $this->respondCreated(
            $this->sanitizeUser($user), // Sin contraseña
            'Usuario creado exitosamente'
        );
    }

    // Actualiza un usuario existente
    public function update($id)
    {
        if (!$this->requireAdmin()) { // Solo admin
            return $this->respondForbidden();
        }

        $userModel = model('App\Models\UserModel');
        $user = $userModel->find($id);

        if (!$user) {
            return $this->respondNotFound('Usuario no encontrado');
        }

        $input = $this->getJsonInput();
        $data = []; // Solo campos enviados

        if (!empty($input['username'])) { // Verifica que no esté duplicado
            $existing = $userModel->where('username', trim($input['username']))
                ->where('id !=', $id)->first();
            if ($existing) {
                return $this->respondValidationError([
                    'username' => 'El nombre de usuario ya existe',
                ]);
            }
            $data['username'] = trim($input['username']);
        }
        if (!empty($input['full_name'])) {
            $data['full_name'] = trim($input['full_name']);
        }
        if (!empty($input['role'])) { // Validar rol
            if (!in_array($input['role'], ['admin', 'supervisor', 'vigilante'])) {
                return $this->respondValidationError([
                    'role' => 'Rol inválido',
                ]);
            }
            $data['role'] = $input['role'];
        }
        if (!empty($input['password'])) { // Nueva contraseña
            $data['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (empty($data)) { // Sin datos para actualizar
            return $this->respondValidationError(['data' => 'No hay datos para actualizar']);
        }

        $userModel->update($id, $data); // Actualiza en BD
        $user = $userModel->find($id); // Recupera datos actualizados

        return $this->respondSuccess(
            $this->sanitizeUser($user),
            'Usuario actualizado exitosamente'
        );
    }

    // Elimina un usuario
    public function delete($id)
    {
        if (!$this->requireAdmin()) { // Solo admin
            return $this->respondForbidden();
        }

        $authUser = $this->getAuthUser();
        if ((int)$id === (int)$authUser['id']) { // Evita auto-eliminación
            return $this->respondError('No puedes eliminar tu propio usuario', 400);
        }

        $userModel = model('App\Models\UserModel');
        $user = $userModel->find($id);

        if (!$user) {
            return $this->respondNotFound('Usuario no encontrado');
        }

        $userModel->delete($id); // Elimina

        return $this->respondDeleted('Usuario eliminado exitosamente');
    }

    // Valida los campos para crear un usuario
    private function validateUserInput(?array $input): array
    {
        $errors = [];

        if (empty($input['username'])) { // Usuario requerido
            $errors['username'] = 'El usuario es requerido';
        } elseif (strlen($input['username']) < 3) { // Mínimo 3 caracteres
            $errors['username'] = 'El usuario debe tener al menos 3 caracteres';
        }

        if (empty($input['full_name'])) { // Nombre requerido
            $errors['full_name'] = 'El nombre completo es requerido';
        }

        if (empty($input['password'])) { // Contraseña requerida
            $errors['password'] = 'La contraseña es requerida';
        } elseif (strlen($input['password']) < 6) { // Mínimo 6 caracteres
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($input['role']) && !in_array($input['role'], ['admin', 'supervisor', 'vigilante'])) {
            $errors['role'] = 'Rol inválido. Use: admin, supervisor o vigilante';
        }

        return $errors; // Array vacío si todo está bien
    }

    // Elimina la contraseña del array por seguridad
    private function sanitizeUser(array $user): array
    {
        unset($user['password']); // Remueve campo sensible
        return $user;
    }
}

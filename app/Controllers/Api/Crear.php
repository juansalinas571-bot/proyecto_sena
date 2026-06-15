<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

class Crear extends ApiController
{
    public function index()
    {
        if (!$this->requireAdmin()) {
            return $this->respondForbidden('Solo administradores pueden crear usuarios');
        }

        $input = $this->getJsonInput();
        $errors = [];

        if (empty($input['username']) || strlen($input['username']) < 3) {
            $errors['username'] = 'El usuario es requerido (mín. 3 caracteres)';
        }
        if (empty($input['full_name'])) {
            $errors['full_name'] = 'El nombre completo es requerido';
        }
        if (empty($input['password']) || strlen($input['password']) < 6) {
            $errors['password'] = 'La contraseña es requerida (mín. 6 caracteres)';
        }
        if (!empty($input['role']) && !in_array($input['role'], ['admin', 'supervisor', 'vigilante'])) {
            $errors['role'] = 'Rol inválido. Use: admin, supervisor o vigilante';
        }

        if (!empty($errors)) {
            return $this->respondValidationError($errors);
        }

        $userModel = model('App\Models\UserModel');
        $existing = $userModel->where('username', trim($input['username']))->first();
        if ($existing) {
            return $this->respondValidationError([
                'username' => 'El nombre de usuario ya existe',
            ]);
        }

        $userModel->insert([
            'username'  => trim($input['username']),
            'password'  => password_hash($input['password'], PASSWORD_DEFAULT),
            'role'      => $input['role'] ?? 'vigilante',
            'full_name' => trim($input['full_name']),
            'is_active' => 1,
        ]);

        $user = $userModel->find($userModel->getInsertID());
        unset($user['password']);

        return $this->respondCreated($user, 'Usuario creado exitosamente');
    }
}

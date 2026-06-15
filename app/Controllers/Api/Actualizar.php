<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

class Actualizar extends ApiController
{
    public function index($id)
    {
        if (!$this->requireAdmin()) {
            return $this->respondForbidden('Solo administradores pueden actualizar usuarios');
        }

        $userModel = model('App\Models\UserModel');
        $user = $userModel->find($id);

        if (!$user) {
            return $this->respondNotFound('Usuario no encontrado');
        }

        $input = $this->getJsonInput();
        $data = [];

        if (!empty($input['username'])) {
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

        if (!empty($input['role'])) {
            if (!in_array($input['role'], ['admin', 'supervisor', 'vigilante'])) {
                return $this->respondValidationError([
                    'role' => 'Rol inválido. Use: admin, supervisor o vigilante',
                ]);
            }
            $data['role'] = $input['role'];
        }

        if (!empty($input['password'])) {
            $data['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        if (empty($data)) {
            return $this->respondValidationError(['data' => 'No hay datos para actualizar']);
        }

        $userModel->update($id, $data);
        $user = $userModel->find($id);
        unset($user['password']);

        return $this->respondSuccess($user, 'Usuario actualizado exitosamente');
    }
}

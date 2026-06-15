<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

class Eliminar extends ApiController
{
    public function index($id)
    {
        if (!$this->requireAdmin()) {
            return $this->respondForbidden('Solo administradores pueden eliminar usuarios');
        }

        $authUser = $this->getAuthUser();
        if ((int)$id === (int)$authUser['id']) {
            return $this->respondError('No puedes eliminar tu propio usuario', 400);
        }

        $userModel = model('App\Models\UserModel');
        $user = $userModel->find($id);

        if (!$user) {
            return $this->respondNotFound('Usuario no encontrado');
        }

        $userModel->delete($id);

        return $this->respondDeleted('Usuario eliminado exitosamente');
    }
}

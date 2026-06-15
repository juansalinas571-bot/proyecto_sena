<?php

namespace App\Controllers\Api;

use App\Controllers\ApiController;

class Consultar extends ApiController
{
    public function index()
    {
        if (!$this->requireAdmin()) {
            return $this->respondForbidden('Solo administradores pueden ver usuarios');
        }

        $userModel = model('App\Models\UserModel');
        $params = $this->getPaginationParams();

        $total = $userModel->countAllResults();
        $users = $userModel
            ->orderBy('id', 'DESC')
            ->findAll($params['limit'], $params['offset']);

        $sanitized = array_map(fn($u) => $this->sanitize($u), $users);

        return $this->respondSuccess([
            'users'       => $sanitized,
            'total'       => $total,
            'page'        => $params['page'],
            'limit'       => $params['limit'],
            'total_pages' => ceil($total / $params['limit']),
        ]);
    }

    public function show($id)
    {
        $userModel = model('App\Models\UserModel');
        $user = $userModel->find($id);

        if (!$user) {
            return $this->respondNotFound('Usuario no encontrado');
        }

        $authUser = $this->getAuthUser();
        if ($authUser['role'] !== 'admin' && $authUser['id'] !== (int)$id) {
            return $this->respondForbidden();
        }

        return $this->respondSuccess($this->sanitize($user));
    }

    private function sanitize(array $user): array
    {
        unset($user['password']);
        return $user;
    }
}

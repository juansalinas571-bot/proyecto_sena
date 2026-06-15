<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

class Users extends BaseController // Controlador para la gestión de usuarios del sistema
{
    // Muestra la lista de todos los usuarios registrados
    public function index()
    {
        $user = service('session')->get('user_data'); // Obtiene usuario logueado
        $this->_requireAdmin($user); // Solo admin puede ver usuarios

        $userModel = model('App\Models\UserModel');
        $users = $userModel->findAll(); // Obtiene todos los usuarios
        $session = service('session');

        return view('users/index', [
            'title' => 'Usuarios',
            'user'  => $user,
            'users' => $users, // Lista completa de usuarios
            'flash' => $session->getFlashdata('flash'), // Mensaje de error temporal
            'flashSuccess' => $session->getFlashdata('flash_success'), // Mensaje de éxito temporal
        ]);
    }

    // Muestra formulario y procesa la creación de un nuevo usuario
    public function create()
    {
        $user = service('session')->get('user_data');
        $this->_requireAdmin($user); // Solo admin

        if ($this->request->getMethod() === 'POST') { // Si envió el formulario
            $username  = trim($this->request->getPost('username') ?? ''); // Nombre de usuario
            $password  = $this->request->getPost('password') ?? ''; // Contraseña
            $fullName  = trim($this->request->getPost('full_name') ?? ''); // Nombre completo
            $role      = $this->request->getPost('role') ?? 'vigilante'; // Rol (default: vigilante)

            if ($username === '' || $password === '' || $fullName === '') { // Validación campos obligatorios
                $error = 'Todos los campos son obligatorios.';
            } else {
                $userModel = model('App\Models\UserModel');
                $existing = $userModel->where('username', $username)->first(); // Verifica si el usuario ya existe
                if ($existing) {
                    $error = 'El nombre de usuario ya existe.'; // Username duplicado
                } else {
                    $userModel->insert([ // Crea el nuevo usuario
                        'username'  => $username,
                        'password'  => password_hash($password, PASSWORD_DEFAULT), // Contraseña hasheada
                        'full_name' => $fullName,
                        'role'      => $role,
                        'is_active' => 1, // Activo por defecto
                    ]);
                    service('session')->setFlashdata('flash_success', 'Usuario creado correctamente.');
                    return redirect()->to('/users');
                }
            }
        }

        return view('users/edit', [
            'title' => 'Crear Usuario',
            'user'  => $user,
            'error' => $error ?? null, // Error si hubo
            'editUser' => null, // null indica que es creación, no edición
        ]);
    }

    // Muestra formulario y procesa la edición de un usuario existente
    public function edit($id)
    {
        $user = service('session')->get('user_data');
        $this->_requireAdmin($user); // Solo admin

        $userModel = model('App\Models\UserModel');
        $editUser = $userModel->find($id); // Busca el usuario por ID

        if (!$editUser) { // Si no existe
            service('session')->setFlashdata('flash', 'Usuario no encontrado.');
            return redirect()->to('/users');
        }

        if ($this->request->getMethod() === 'POST') { // Si envió formulario
            $fullName = trim($this->request->getPost('full_name') ?? ''); // Nuevo nombre
            $role     = $this->request->getPost('role') ?? 'vigilante'; // Nuevo rol
            $password = $this->request->getPost('password') ?? ''; // Nueva contraseña

            if ($fullName === '') { // Validación
                $error = 'El nombre completo es obligatorio.';
            } else {
                $updateData = ['full_name' => $fullName, 'role' => $role]; // Datos a actualizar
                if ($password !== '') { // Si quiere cambiar contraseña
                    $updateData['password'] = password_hash($password, PASSWORD_DEFAULT); // Hashea nueva
                }
                $userModel->update($id, $updateData); // Actualiza en BD
                service('session')->setFlashdata('flash_success', 'Usuario actualizado correctamente.');
                return redirect()->to('/users');
            }
        }

        return view('users/edit', [
            'title' => 'Editar Usuario',
            'user'  => $user,
            'editUser' => $editUser, // Datos del usuario a editar
            'error' => $error ?? null,
        ]);
    }

    // Elimina un usuario del sistema
    public function delete($id)
    {
        $user = service('session')->get('user_data');
        $this->_requireAdmin($user); // Solo admin

        // Evita que el admin se elimine a sí mismo
        if ((int)$id === (int)$user['id']) {
            service('session')->setFlashdata('flash', 'No puedes eliminarte a ti mismo.');
            return redirect()->to('/users');
        }

        model('App\Models\UserModel')->delete($id); // Elimina el usuario
        service('session')->setFlashdata('flash_success', 'Usuario eliminado correctamente.');
        return redirect()->to('/users');
    }

    // Verifica que el usuario logueado sea administrador
    private function _requireAdmin($user): void
    {
        if (!$user || $user['role'] !== 'admin') { // Si no es admin
            service('session')->setFlashdata('flash', 'No tienes permisos para esta sección.');
            redirect()->to('/home')->send(); // Redirige al dashboard
            exit;
        }
    }
}

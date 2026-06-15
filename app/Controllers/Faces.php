<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

class Faces extends BaseController // Controlador para la gestión de rostros
{
    // Muestra la lista de todos los rostros registrados
    public function index()
    {
        $session = service('session'); // Obtiene el servicio de sesión
        $user = $session->get('user_data'); // Datos del usuario logueado
        $faceModel = model('App\Models\FaceModel'); // Carga el modelo de rostros
        $faces = $faceModel->orderBy('created_at', 'DESC')->findAll(); // Obtiene todos los rostros ordenados por fecha

        $data = [
            'title' => 'Registro de Rostros', // Título de la página
            'user'  => $user,
            'faces' => $faces, // Lista completa de rostros
            'flash' => $session->getFlashdata('flash'), // Mensaje de error temporal
            'flashSuccess' => $session->getFlashdata('flash_success'), // Mensaje de éxito temporal
        ];

        return view('faces/index', $data); // Renderiza la vista con los datos
    }

    // Procesa la subida y registro de un nuevo rostro
    public function upload()
    {
        $this->_requireRole('admin', 'supervisor'); // Solo admin y supervisor pueden registrar
        $session = service('session');

        // Solo acepta POST
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/faces');
        }

        $name = trim($this->request->getPost('name') ?? ''); // Nombre de la persona
        if ($name === '') { // Validación: nombre obligatorio
            $session->setFlashdata('flash', 'El nombre es obligatorio.');
            return redirect()->to('/faces');
        }

        $numDoc = trim($this->request->getPost('num_doc') ?? ''); // Número de documento
        if ($numDoc === '') { // Validación: documento obligatorio
            $session->setFlashdata('flash', 'El número de identificación es obligatorio.');
            return redirect()->to('/faces');
        }

        $accessPassword = $this->request->getPost('access_password') ?? ''; // Contraseña de acceso
        if ($accessPassword === '' || strlen($accessPassword) < 4) { // Validación: mínimo 4 caracteres
            $session->setFlashdata('flash', 'La contraseña de acceso debe tener al menos 4 caracteres.');
            return redirect()->to('/faces');
        }

        $filename = uniqid('face_') . '.jpg'; // Genera nombre único para la imagen
        $dest = FCPATH . 'uploads/' . $filename; // Ruta completa de destino

        // ====== PROCESAR IMAGEN ======
        $captured = $this->request->getPost('captured_base64'); // Imagen capturada por cámara (base64)
        if ($captured && $captured !== '') { // Si viene como base64
            if (strpos($captured, 'data:image/') === 0) { // Si tiene prefijo data URL
                $captured = substr($captured, strpos($captured, ',') + 1); // Extrae solo el base64
            }
            $decoded = base64_decode($captured); // Decodifica base64 a binario
            if ($decoded === false || file_put_contents($dest, $decoded) === false) {
                $session->setFlashdata('flash', 'Error al procesar la imagen.');
                return redirect()->to('/faces');
            }
        } else { // Si viene como archivo subido
            $file = $this->request->getFile('image'); // Obtiene el archivo
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $allowed = ['image/jpeg', 'image/png', 'image/webp']; // Formatos permitidos
                if (!in_array($file->getMimeType(), $allowed)) { // Validar tipo MIME
                    $session->setFlashdata('flash', 'Formato no válido.');
                    return redirect()->to('/faces');
                }
                $file->move(FCPATH . 'uploads', $filename); // Mueve el archivo a uploads/
            } else {
                $session->setFlashdata('flash', 'Debes seleccionar una imagen o capturar una foto.');
                return redirect()->to('/faces');
            }
        }

        $faceHash = trim($this->request->getPost('face_hash') ?? ''); // Hash facial (opcional)
        if ($faceHash === '') $faceHash = null; // Si viene vacío, lo pone como null

        $faceModel = model('App\Models\FaceModel');
        $faceModel->insert([ // Inserta el nuevo rostro en la base de datos
            'name'             => $name, // Nombre de la persona
            'num_doc'          => $numDoc, // Número de documento
            'image_path'       => 'uploads/' . $filename, // Ruta relativa de la imagen
            'face_hash'        => $faceHash, // Hash facial para reconocimiento
            'access_password'  => password_hash($accessPassword, PASSWORD_DEFAULT), // Contraseña hasheada
            'faces_detected'   => 1, // Cantidad de rostros detectados
        ]);

        $session->setFlashdata('flash_success', 'Rostro registrado correctamente.'); // Mensaje de éxito
        return redirect()->to('/faces');
    }

    // Muestra el formulario de edición y procesa la actualización de un rostro
    public function edit($id)
    {
        $this->_requireRole('admin', 'supervisor'); // Solo admin y supervisor
        $session = service('session');

        $faceModel = model('App\Models\FaceModel');
        $face = $faceModel->find($id); // Busca el rostro por ID

        if (!$face) { // Si no existe
            $session->setFlashdata('flash', 'Rostro no encontrado.');
            return redirect()->to('/faces');
        }

        $error = null;
        if ($this->request->getMethod() === 'POST') { // Si envió el formulario
            $name = trim($this->request->getPost('name') ?? ''); // Nuevo nombre
            $numDoc = trim($this->request->getPost('num_doc') ?? ''); // Nuevo documento
            $password = $this->request->getPost('access_password') ?? ''; // Nueva contraseña

            if ($name === '') { // Validaciones
                $error = 'El nombre es obligatorio.';
            } elseif ($numDoc === '') {
                $error = 'El número de identificación es obligatorio.';
            } else {
                $updateData = ['name' => $name, 'num_doc' => $numDoc]; // Datos a actualizar
                if ($password !== '' && strlen($password) >= 4) { // Si quiere cambiar contraseña
                    $updateData['access_password'] = password_hash($password, PASSWORD_DEFAULT); // Hashea la nueva
                }
                $faceModel->update($id, $updateData); // Actualiza en BD
                $session->setFlashdata('flash_success', 'Rostro actualizado correctamente.');
                return redirect()->to('/faces');
            }
        }

        // Muestra el formulario de edición con los datos actuales
        return view('faces/edit', [
            'title' => 'Editar Rostro',
            'user'  => $session->get('user_data'),
            'face'  => $face, // Datos del rostro a editar
            'error' => $error, // Mensaje de error si hubo
        ]);
    }

    // Elimina un rostro y su imagen del servidor
    public function delete($id)
    {
        $this->_requireRole('admin', 'supervisor'); // Solo admin y supervisor
        $session = service('session');

        $faceModel = model('App\Models\FaceModel');
        $face = $faceModel->find($id); // Busca el rostro

        if ($face) { // Si existe
            $filePath = FCPATH . $face['image_path']; // Ruta completa de la imagen
            if (file_exists($filePath)) {
                unlink($filePath); // Elimina el archivo de imagen del servidor
            }
            $faceModel->delete($id); // Elimina el registro de la base de datos
        }

        $session->setFlashdata('flash_success', 'Rostro eliminado correctamente.');
        return redirect()->to('/faces');
    }

    // Verifica que el usuario tenga uno de los roles requeridos
    private function _requireRole(...$roles): void
    {
        $user = service('session')->get('user_data'); // Obtiene usuario logueado
        if (!$user || !in_array($user['role'], $roles)) { // Si no tiene el rol
            service('session')->setFlashdata('flash', 'No tienes permisos para esta acción.');
            redirect()->to('/faces')->send(); // Redirige y finaliza
            exit;
        }
    }
}

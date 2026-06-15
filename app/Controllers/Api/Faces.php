<?php // Inicia el bloque de código PHP

namespace App\Controllers\Api; // Espacio de nombres para controladores API

use App\Controllers\ApiController; // Clase base para la API

class Faces extends ApiController // Controlador API para la gestión de rostros
{
    // Lista todos los rostros con paginación
    public function index()
    {
        $faceModel = model('App\Models\FaceModel');
        $params = $this->getPaginationParams(); // Obtiene page, limit, offset de la URL

        $total = $faceModel->countAllResults(); // Total de rostros
        $faces = $faceModel
            ->orderBy('id', 'DESC') // Ordena del más reciente al más antiguo
            ->findAll($params['limit'], $params['offset']); // Obtiene solo los de la página

        return $this->respondSuccess([ // Respuesta con datos paginados
            'faces'      => $faces,
            'total'      => $total,
            'page'       => $params['page'],
            'limit'      => $params['limit'],
            'total_pages' => ceil($total / $params['limit']), // Cálculo de páginas totales
        ]);
    }

    // Muestra un rostro específico por ID
    public function show($id)
    {
        $faceModel = model('App\Models\FaceModel');
        $face = $faceModel->find($id); // Busca por ID

        if (!$face) { // Si no existe
            return $this->respondNotFound('Rostro no encontrado');
        }

        return $this->respondSuccess($face); // Devuelve los datos del rostro
    }

    // Crea un nuevo rostro
    public function create()
    {
        if (!$this->requireAdminOrSupervisor()) { // Solo admin/supervisor
            return $this->respondForbidden();
        }

        $input = $this->getJsonInput(); // Datos JSON de la petición

        if (empty($input['name'])) { // Validación: nombre requerido
            return $this->respondValidationError([
                'name' => 'El nombre es requerido',
            ]);
        }

        $faceModel = model('App\Models\FaceModel');
        $data = [ // Datos del nuevo rostro
            'name'           => trim($input['name']),
            'num_doc'        => trim($input['num_doc'] ?? ''),
            'face_hash'      => $input['face_hash'] ?? null,
            'faces_detected' => $input['faces_detected'] ?? 0,
        ];

        if (empty($data['num_doc'])) { // Validación: documento requerido
            return $this->respondValidationError([
                'num_doc' => 'El número de identificación es requerido',
            ]);
        }

        if (!empty($input['access_password'])) { // Si incluye contraseña de acceso
            if (strlen($input['access_password']) < 4) { // Validación mínimo 4 caracteres
                return $this->respondValidationError([
                    'access_password' => 'La contraseña debe tener al menos 4 caracteres',
                ]);
            }
            $data['access_password'] = password_hash($input['access_password'], PASSWORD_DEFAULT); // Hashea la contraseña
        }

        $faceModel->insert($data); // Inserta en BD
        $face = $faceModel->find($faceModel->getInsertID()); // Recupera el registro recién creado

        return $this->respondCreated($face, 'Rostro registrado exitosamente');
    }

    // Actualiza un rostro existente
    public function update($id)
    {
        if (!$this->requireAdminOrSupervisor()) {
            return $this->respondForbidden();
        }

        $faceModel = model('App\Models\FaceModel');
        $face = $faceModel->find($id);

        if (!$face) { // Si no existe
            return $this->respondNotFound('Rostro no encontrado');
        }

        $input = $this->getJsonInput();
        $data = []; // Solo los campos que vienen en la petición

        if (!empty($input['name'])) {
            $data['name'] = trim($input['name']);
        }
        if (!empty($input['num_doc'])) {
            $data['num_doc'] = trim($input['num_doc']);
        }
        if (isset($input['face_hash'])) { // Puede ser null para limpiar el hash
            $data['face_hash'] = $input['face_hash'] ?: null;
        }
        if (isset($input['faces_detected'])) {
            $data['faces_detected'] = (int)$input['faces_detected'];
        }
        if (!empty($input['access_password'])) {
            if (strlen($input['access_password']) < 4) {
                return $this->respondValidationError([
                    'access_password' => 'La contraseña debe tener al menos 4 caracteres',
                ]);
            }
            $data['access_password'] = password_hash($input['access_password'], PASSWORD_DEFAULT);
        }

        if (empty($data)) { // Si no hay datos para actualizar
            return $this->respondValidationError(['data' => 'No hay datos para actualizar']);
        }

        $faceModel->update($id, $data); // Actualiza en BD
        $face = $faceModel->find($id); // Recupera datos actualizados

        return $this->respondSuccess($face, 'Rostro actualizado exitosamente');
    }

    // Elimina un rostro y su imagen del servidor
    public function delete($id)
    {
        if (!$this->requireAdminOrSupervisor()) {
            return $this->respondForbidden();
        }

        $faceModel = model('App\Models\FaceModel');
        $face = $faceModel->find($id);

        if (!$face) {
            return $this->respondNotFound('Rostro no encontrado');
        }

        // Elimina el archivo de imagen si existe
        if (!empty($face['image_path'])) {
            $filePath = FCPATH . str_replace(base_url(), '', $face['image_path']); // Ruta absoluta
            if (file_exists($filePath)) {
                unlink($filePath); // Borra el archivo físico
            }
        }

        $faceModel->delete($id); // Elimina el registro de BD

        return $this->respondDeleted('Rostro eliminado exitosamente');
    }

    // Sube una imagen de rostro (multipart)
    public function upload()
    {
        if (!$this->requireAdminOrSupervisor()) {
            return $this->respondForbidden();
        }

        $file = $this->request->getFile('image'); // Archivo de imagen
        $name = $this->request->getPost('name'); // Nombre
        $faceHash = $this->request->getPost('face_hash'); // Hash facial

        if (!$file || !$file->isValid()) { // Validación archivo
            return $this->respondValidationError([
                'image' => 'La imagen es requerida',
            ]);
        }
        if ($file->hasMoved()) { // Si ya fue movido
            return $this->respondError('El archivo ya fue movido', 400);
        }
        if (empty($name)) { // Nombre requerido
            return $this->respondValidationError([
                'name' => 'El nombre es requerido',
            ]);
        }

        $uploadPath = FCPATH . 'uploads'; // Directorio de uploads
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true); // Crea el directorio si no existe
        }

        $newName = $file->getRandomName(); // Genera nombre aleatorio seguro
        $file->move($uploadPath, $newName); // Mueve el archivo

        $accessPassword = $this->request->getPost('access_password') ?? ''; // Contraseña
        $numDoc = trim($this->request->getPost('num_doc') ?? ''); // Documento

        $data = [ // Datos para insertar
            'name'           => trim($name),
            'num_doc'        => $numDoc,
            'image_path'     => base_url('uploads/' . $newName), // Ruta completa
            'face_hash'      => $faceHash ?: null,
            'faces_detected' => 1,
        ];

        if ($accessPassword !== '' && strlen($accessPassword) >= 4) {
            $data['access_password'] = password_hash($accessPassword, PASSWORD_DEFAULT);
        }

        $faceModel = model('App\Models\FaceModel');
        $faceModel->insert($data);
        $face = $faceModel->find($faceModel->getInsertID());

        return $this->respondCreated($face, 'Rostro registrado exitosamente');
    }

    // Busca un rostro por documento y contraseña (verificación de acceso)
    public function search()
    {
        $input = $this->getJsonInput(); // Datos JSON

        if (empty($input['num_doc']) || empty($input['password'])) { // Validación campos requeridos
            return $this->respondValidationError([
                'num_doc'  => 'El número de identificación es requerido',
                'password' => 'La contraseña de acceso es requerida',
            ]);
        }

        $faceModel = model('App\Models\FaceModel');
        $match = $faceModel->findByDocAndPassword($input['num_doc'], $input['password']); // Busca coincidencia

        if ($match) { // Si encontró
            return $this->respondSuccess([ // Acceso permitido
                'found' => true,
                'name'  => $match['name'],
                'face'  => $match,
            ], 'ACCESO SATISFACTORIO');
        }

        return $this->respondSuccess([ // Acceso denegado
            'found' => false,
            'name'  => null,
        ], 'ACCESO DENEGADO');
    }
}

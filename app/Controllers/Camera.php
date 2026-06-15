<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

class Camera extends BaseController // Controlador para el módulo de cámara y control de acceso
{
    // Muestra la interfaz de la cámara con la lista de rostros registrados
    public function index()
    {
        $faceModel = model('App\Models\FaceModel'); // Carga el modelo de rostros
        $faces = $faceModel->select('id, name, image_path')->findAll(); // Obtiene solo los datos necesarios para la vista

        $data = [
            'title' => 'Control de Acceso', // Título de la página
            'user'  => service('session')->get('user_data'), // Datos del usuario logueado
            'faces' => $faces, // Lista de rostros para mostrar en la interfaz
        ];
        return view('camera/index', $data); // Renderiza la vista de la cámara
    }

    // Registra un intento de acceso en la base de datos (access_logs)
    private function _logAccess($faceId, $name, $numDoc, $status, $method)
    {
        $logModel = model('App\Models\AccessLogModel'); // Carga el modelo de logs
        $request = service('request'); // Obtiene la petición actual

        $logModel->insert([ // Inserta un nuevo registro en access_logs
            'face_id'    => $faceId,   // ID del rostro (null si no identificado)
            'name'       => $name,     // Nombre de la persona
            'num_doc'    => $numDoc,   // Número de documento
            'status'     => $status,   // 'success' o 'denied'
            'method'     => $method,   // 'camera' o 'password'
            'ip_address' => $request->getIPAddress(), // Dirección IP del solicitante
            'user_agent' => $request->getUserAgent()->getAgentString(), // Navegador/dispositivo
        ]);
    }

    // Procesa la búsqueda y verificación de acceso (endpoint AJAX)
    public function search()
    {
        // Solo acepta peticiones POST
        if ($this->request->getMethod() !== 'POST') {
            return $this->response->setJSON(['found' => false, 'error' => 'Método no permitido']);
        }

        $session = service('session'); // Obtiene la sesión
        $input = $this->request->getJSON(true); // Obtiene datos JSON del cuerpo de la petición
        $password = $input['password'] ?? ''; // Contraseña ingresada
        $numDoc = $input['num_doc'] ?? ''; // Documento ingresado

        // ====== MODO VERIFICACIÓN POR CONTRASEÑA ======
        // Si vienen con contraseña y documento, verifica contra la base de datos
        if ($password !== '' && $numDoc !== '') {
            $faceModel = model('App\Models\FaceModel'); // Carga el modelo
            $match = $faceModel->findByDocAndPassword($numDoc, $password); // Busca coincidencia

            if ($match) { // Si encontró coincidencia (documento + contraseña correctos)
                $session->remove('face_search_attempts'); // Reinicia contador de intentos
                $this->_logAccess($match['id'], $match['name'], $match['num_doc'], 'success', 'password'); // Log de éxito
                return $this->response->setJSON([ // Respuesta: acceso permitido
                    'found'   => true,
                    'name'    => $match['name'],
                    'num_doc' => $match['num_doc'],
                    'message' => 'ACCESO SATISFACTORIO',
                    'status'  => 'success',
                ]);
            }

            // Si no coincidió, registra acceso denegado
            $this->_logAccess(null, null, $numDoc, 'denied', 'password');
            return $this->response->setJSON([
                'found'  => false,
                'name'   => null,
                'message' => 'ACCESO DENEGADO',
                'status'  => 'denied',
            ]);
        }

        // ====== MODO CÁMARA (SIN CONTRASEÑA) ======
        // Cuenta los intentos fallidos en esta sesión
        $attempts = (int) $session->get('face_search_attempts', 0); // Obtiene intentos actuales
        $attempts++; // Incrementa el contador
        $session->set('face_search_attempts', $attempts); // Guarda el nuevo contador

        $remaining = max(0, 3 - $attempts); // Intentos restantes (máximo 3)
        $showPassword = $attempts >= 3; // Si llegó a 3 intentos, muestra formulario de contraseña

        $this->_logAccess(null, null, null, 'denied', 'camera'); // Registra intento por cámara fallido

        if ($showPassword) { // Si ya agotó los 3 intentos
            $session->remove('face_search_attempts'); // Reinicia el contador para el próximo ciclo
        }

        // Respuesta con información de intentos
        return $this->response->setJSON([
            'found'         => false,       // No se encontró rostro
            'name'          => null,
            'message'       => 'ACCESO DENEGADO',
            'status'        => 'denied',
            'attempts'      => $attempts,     // Intentos realizados
            'max_attempts'  => 3,             // Máximo de intentos permitidos
            'remaining'     => $remaining,    // Intentos restantes
            'show_password' => $showPassword, // Si debe mostrar el panel de contraseña
        ]);
    }
}

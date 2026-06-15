<?php // Inicia el bloque de código PHP

namespace App\Controllers; // Define el espacio de nombres para controladores

use CodeIgniter\Controller; // Clase base de CodeIgniter para controladores
use CodeIgniter\HTTP\RequestInterface; // Interfaz para manejar peticiones HTTP
use CodeIgniter\HTTP\ResponseInterface; // Interfaz para manejar respuestas HTTP
use Psr\Log\LoggerInterface; // Interfaz para logging

abstract class BaseController extends Controller // Clase base abstracta para todos los controladores web
{
    // Método que se ejecuta al inicializar el controlador
    // Recibe la petición, respuesta y logger automáticamente
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Llama al initController del padre para mantener la funcionalidad base
        parent::initController($request, $response, $logger);
    }
}

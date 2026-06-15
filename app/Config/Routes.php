<?php // Inicia el bloque de código PHP

use CodeIgniter\Router\RouteCollection; // Importa la clase para definir rutas

/** @var RouteCollection $routes */
$routes->setAutoRoute(false); // Desactiva el enrutamiento automático (obliga a definir rutas manualmente)

// ===================== RUTAS WEB (AUTENTICACIÓN) =====================

$routes->get('/', 'Auth::login'); // Ruta raíz: redirige al login
$routes->match(['get', 'post'], '/login', 'Auth::login'); // GET muestra formulario, POST procesa login
$routes->get('/logout', 'Auth::logout'); // Cierra sesión y redirige al login

// Dashboard (protegido con filtro 'auth' = requiere sesión activa)
$routes->get('/home', 'Dashboard::index', ['filter' => 'auth']);

// Faces (CRUD de rostros, protegido con filtro 'auth')
$routes->group('faces', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/',                 'Faces::index');      // Lista todos los rostros
    $routes->match(['get','post'],    'edit/(:num)', 'Faces::edit/$1'); // Editar rostro por ID
    $routes->get('delete/(:num)',     'Faces::delete/$1');  // Eliminar rostro por ID
    $routes->post('upload',           'Faces::upload');     // Subir nueva foto de rostro
});

// Camera (control de acceso por cámara, protegido)
$routes->group('camera', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/',                 'Camera::index');    // Muestra la interfaz de cámara
    $routes->post('search',           'Camera::search');   // Procesa búsqueda facial y verificación
});

// Reports (reportes de acceso, protegido)
$routes->get('/reports', 'Reports::index', ['filter' => 'auth']);

// Users (gestión de usuarios del sistema, solo admin)
$routes->group('users', ['filter' => 'auth'], static function ($routes) {
    $routes->get('/',                 'Users::index');     // Lista todos los usuarios
    $routes->match(['get','post'],    'create',    'Users::create'); // Crear nuevo usuario
    $routes->match(['get','post'],    'edit/(:num)','Users::edit/$1'); // Editar usuario por ID
    $routes->get('delete/(:num)',     'Users::delete/$1'); // Eliminar usuario por ID
});

// ===================== API REST (JWT) =====================
// Todas las rutas API están bajo el prefijo /api

$routes->group('api', static function ($routes) {
    // Auth (público - no requiere token)
    $routes->post('auth/login',   'Api\Auth::login');   // Iniciar sesión, devuelve JWT
    $routes->post('auth/refresh', 'Api\Auth::refresh'); // Renovar token con refresh_token

    // Auth (protegido - requiere JWT)
    $routes->group('auth', ['filter' => 'jwtauth'], static function ($routes) {
        $routes->get('me',      'Api\Auth::me');     // Obtener datos del usuario autenticado
        $routes->post('logout', 'Api\Auth::logout'); // Cerrar sesión (solo confirma, es stateless)
    });

    // Faces API (protegido - CRUD completo de rostros)
    $routes->group('faces', ['filter' => 'jwtauth'], static function ($routes) {
        $routes->get('/',              'Api\Faces::index');   // Listar rostros (paginado)
        $routes->get('(:num)',         'Api\Faces::show/$1'); // Ver un rostro por ID
        $routes->post('/',             'Api\Faces::create');  // Crear nuevo rostro
        $routes->put('(:num)',         'Api\Faces::update/$1'); // Actualizar rostro
        $routes->delete('(:num)',      'Api\Faces::delete/$1'); // Eliminar rostro
        $routes->post('upload',        'Api\Faces::upload'); // Subir imagen de rostro
        $routes->post('search',        'Api\Faces::search'); // Buscar rostro por documento+contraseña
    });

    // Users API (protegido - solo admin) — versión en español
    $routes->group('usuarios', ['filter' => 'jwtauth'], static function ($routes) {
        $routes->get('/',                  'Api\Consultar::index');      // Listar usuarios
        $routes->get('(:num)',             'Api\Consultar::show/$1');    // Ver usuario por ID
        $routes->post('crear',             'Api\Crear::index');          // Crear usuario
        $routes->put('actualizar/(:num)',  'Api\Actualizar::index/$1'); // Actualizar usuario
        $routes->delete('eliminar/(:num)', 'Api\Eliminar::index/$1');   // Eliminar usuario
    });

    // Users API (alias en inglés para compatibilidad)
    $routes->group('users', ['filter' => 'jwtauth'], static function ($routes) {
        $routes->get('/',                  'Api\Consultar::index');
        $routes->get('(:num)',             'Api\Consultar::show/$1');
        $routes->post('crear',             'Api\Crear::index');
        $routes->put('actualizar/(:num)',  'Api\Actualizar::index/$1');
        $routes->delete('eliminar/(:num)', 'Api\Eliminar::index/$1');
    });

    // Dashboard API (protegido - estadísticas)
    $routes->group('dashboard', ['filter' => 'jwtauth'], static function ($routes) {
        $routes->get('stats', 'Api\Dashboard::stats'); // Obtener estadísticas del sistema
    });
});

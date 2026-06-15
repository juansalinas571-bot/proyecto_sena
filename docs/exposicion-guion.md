# Guión de Exposición - Secure Access

**Duración:** 15 minutos  
**Expositores:** Juan, Ehidan, Krispi  
**Proyecto:** Sistema de Control de Acceso con Reconocimiento Facial

---

## Juan (5 min) — Introducción, Stack y Arquitectura

### 0:00 - 0:30 | Saludo y presentación
"Hola, somos Juan, Ehidan y Krispi. Hoy les presentamos **Secure Access**, un sistema de control de acceso que combina reconocimiento facial y verificación por contraseña para gestionar la entrada de personas a un lugar seguro."

### 0:30 - 1:30 | ¿Qué problema resuelve?
"El proyecto nace de la necesidad de controlar quién ingresa a un establecimiento: en lugar de depender de un vigilante que revisa documentos manualmente, Secure Access permite:
- Registrar rostros con nombre, documento y contraseña
- Capturar una foto desde la cámara web y verificar la identidad
- Usar el documento + contraseña como respaldo si la cámara falla
- Generar reportes detallados de todos los accesos (exitosos y denegados)
- Exponer una API REST para integraciones externas"

### 1:30 - 2:30 | Stack tecnológico
"El stack que usamos es:

| Tecnología | Función |
|------------|---------|
| **CodeIgniter 4** | Framework PHP (MVC) |
| **PHP 8.2.12** | Lenguaje backend |
| **MySQL / MariaDB** | Base de datos relacional |
| **JavaScript (Vanilla)** | Captura de cámara web y hash facial dHash |
| **JWT (firebase/php-jwt)** | Autenticación de la API REST |
| **CSS personalizado** | Diseño oscuro con colores neón |
| **XAMPP** | Entorno de desarrollo local |"

### 2:30 - 4:00 | Arquitectura MVC
"La arquitectura sigue el patrón **Modelo-Vista-Controlador** de CodeIgniter 4:

```
proyecto_sena/
├── app/
│   ├── Controllers/     ← Controladores (lógica de negocio)
│   │   ├── Auth.php     → Login/logout con sesión
│   │   ├── Dashboard.php → Página principal
│   │   ├── Faces.php    → CRUD de rostros (web)
│   │   ├── Camera.php   → Captura y verificación
│   │   ├── Reports.php  → Reportes con filtros
│   │   ├── Users.php    → CRUD de usuarios (web)
│   │   └── Api/         → Controladores REST (JWT)
│   ├── Models/          → Interacción con BD
│   ├── Views/           → HTML + CSS + JS
│   ├── Filters/          → AuthFilter, JWTAuthFilter, RoleFilter
│   ├── Config/Routes.php → Definición de rutas
│   └── Database/Migrations/ → Estructura de BD
├── public/
│   ├── assets/css/      → Estilos oscuros neón
│   └── uploads/         → Fotos de rostros
└── _legacy/              → Código anterior (archivado)
```

Los **Modelos** consultan la base de datos, los **Controladores** procesan la lógica y las **Vistas** renderizan el HTML. Los **Filters** interceptan las peticiones para verificar autenticación."

### 4:00 - 4:30 | Base de datos
"La base de datos tiene 4 tablas principales:
- **users** → Usuarios del sistema (admin, supervisor, vigilante)
- **faces** → Rostros registrados con nombre, documento, hash facial y contraseña
- **access_logs** → Historial de intentos de acceso con IP, fecha, estado
- **migrations** → Control de versiones de la estructura"

### 4:30 - 5:00 | Cierre y paso a Ehidan
"Esa es la vista general del proyecto. Ahora Ehidan les va a mostrar cómo funciona el código por dentro: las rutas, los controladores y la API."

---

## Ehidan (5 min) — Rutas, Controladores, Filtros y API REST

### 5:00 - 5:45 | Sistema de rutas (Routes.php)
"Las rutas se definen en `app/Config/Routes.php` y se dividen en dos grupos principales:

**Rutas web** (protegidas con filtro `auth` = sesión PHP):
```php
$routes->get('/', 'Auth::login');                    // Login público
$routes->get('/home', 'Dashboard::index', ['filter' => 'auth']);  // Dashboard
$routes->group('faces', ['filter' => 'auth'], ...);  // CRUD rostros
$routes->group('camera', ['filter' => 'auth'], ...); // Cámara
$routes->group('users', ['filter' => 'auth'], ...);  // Usuarios
$routes->get('/reports', 'Reports::index', ['filter' => 'auth']); // Reportes
```

**Rutas API** (protegidas con filtro `jwtauth` = token JWT):
```php
$routes->group('api', ...);
  // Públicas:
  POST /api/auth/login
  POST /api/auth/refresh
  // Protegidas (JWT):
  GET  /api/auth/me
  GET  /api/faces, POST /api/faces, PUT /api/faces/{id}, DELETE /api/faces/{id}
  POST /api/faces/search, POST /api/faces/upload
  GET  /api/users, POST /api/users, PUT /api/users/{id}, DELETE /api/users/{id}
  GET  /api/dashboard/stats
```

El enrutamiento automático está desactivado; cada ruta se declara explícitamente."

### 5:45 - 6:30 | Filtros de autenticación
"Tenemos 3 filtros que protegen las rutas:

1. **AuthFilter** (`app/Filters/AuthFilter.php`): Verifica que exista una sesión PHP activa. Si no hay sesión, redirige al login.
2. **JWTAuthFilter** (`app/Filters/JWTAuthFilter.php`): Para la API. Extrae el token del header `Authorization: Bearer ...`, lo decodifica con la librería `firebase/php-jwt`, verifica su validez y expiración, e inyecta los datos del usuario en `$request->authUser`.
3. **RoleFilter** (`app/Filters/RoleFilter.php`): Verifica que el usuario tenga un rol específico. Se usa en validaciones dentro de los controladores."

### 6:30 - 7:15 | Controladores web (sesión)
"Los controladores web usan **sesiones PHP** para mantener la autenticación:

- **Auth.php**: `login()` valida contra la tabla `users` con `password_verify()`, guarda `user_data` en sesión. `logout()` destruye la sesión.
- **Faces.php**: CRUD completo con subida de fotos (base64 desde cámara o archivo), verificación de roles con `_requireRole()`.
- **Camera.php**: El corazón del sistema. Recibe una petición AJAX desde la vista, busca por documento + contraseña con `FaceModel::findByDocAndPassword()`, registra cada intento en `access_logs` y controla un límite de 3 intentos por cámara.
- **Reports.php**: Obtiene estadísticas del día (success/denied) y lista paginada con filtros por fecha, estado y búsqueda."

### 7:15 - 7:55 | API REST (JWT)
"La API usa **JWT** para autenticación stateless. Cuando el usuario hace login en `/api/auth/login`, recibe un token de acceso (24h) y un refresh token (7d).

El controlador **ApiController** es la clase base abstracta que provee:
- `respondSuccess()`, `respondCreated()`, `respondError()` → respuestas JSON estandarizadas
- `getJsonInput()` → obtiene el body JSON
- `requireAdmin()`, `requireAdminOrSupervisor()` → control de permisos
- `getPaginationParams()` → paginación desde query string

Cada endpoint devuelve una estructura uniforme:
```json
{
  "status": "success|error",
  "message": "Mensaje descriptivo",
  "data": { ... }
}
```

El flujo de la API es: Login → obtener token → enviar token en header → acceder a recursos."

### 7:55 - 8:15 | Hash facial (dHash)
"El sistema implementa **dHash (Difference Hash)** para reconocimiento facial. Se genera desde JavaScript en el navegador:

1. Se captura un frame de la cámara web
2. Se redimensiona a 9x8 píxeles en escala de grises
3. Se compara cada píxel con su vecino derecho → 64 bits de hash
4. Se envía el hash al servidor junto con la petición

Esto permite comparar rostros sin necesidad de servicios externos de IA. La comparación usa la **distancia de Hamming**: 2 hashes similares tienen pocos bits diferentes."

### 8:15 - 8:30 | Vista de cámara
La vista `camera/index.php` contiene JavaScript que:
- Accede a la cámara con `navigator.mediaDevices.getUserMedia()`
- Captura frames en un canvas `<canvas>`
- Genera el dHash con JavaScript puro
- Envía el hash al servidor vía AJAX (fetch POST)
- Muestra modales animados con feedback visual (verde = acceso, rojo = denegado, naranja = bloqueado)

### 8:30 - 9:00 | Reportes
"La vista `reports/index.php` muestra:
- 4 tarjetas de estadísticas en tiempo real (accesos hoy, exitosos, denegados, total acumulado)
- Tabla paginada (25 por página) con filtros por fecha, estado y búsqueda por texto
- Los datos vienen del controlador `Reports.php` que consulta `access_logs`"

### 9:00 - 9:30 | Códigos de respuesta y manejo de errores
"Todas las validaciones retornan mensajes claros en español. El sistema maneja:
- 401: Token inválido o expirado
- 403: Rol insuficiente
- 404: Recurso no encontrado
- 422: Error de validación en campos
- 500: Error interno del servidor"

### 9:30 - 10:00 | Cierre y paso a Krispi
"Hemos visto la arquitectura, las rutas y los controladores. Ahora Krispi va a hacer la demostración en vivo para que vean el sistema funcionando."

---

## Krispi (5 min) — Demostración en Vivo + Conclusión

### 10:00 - 10:30 | Login y Dashboard
"Vamos a la URL del proyecto: `http://localhost/proyecto_sena/`

1. Ingresamos con **admin / admin123**
2. Vemos el **Dashboard** con las estadísticas generales: total de rostros, usuarios activos, últimos registros
3. La interfaz usa diseño oscuro con colores neón (cyan, magenta, verde) para una experiencia moderna"

### 10:30 - 11:15 | Registrar un rostro
"Navegamos a **Facial > Registro de Rostros**.

1. Vemos la lista de rostros ya registrados
2. Hacemos clic en **Registrar Nuevo Rostro**
3. Llenamos: nombre, número de documento, contraseña de acceso
4. **Dos opciones para la foto:**
   - **Subir archivo**: seleccionamos una imagen desde el disco
   - **Capturar con cámara**: se abre la cámara web y tomamos una foto
5. Al guardar, la imagen se almacena en `public/uploads/` y los datos en la tabla `faces`
6. La contraseña se guarda hasheada con `password_hash()`"

### 11:15 - 11:50 | Control de acceso con cámara
"Navegamos a **Control de Acceso**.

1. Vemos la interfaz con la cámara web activada
2. Se muestran los rostros registrados en tarjetas
3. **Opción 1 - Búsqueda facial:** seleccionamos un rostro, la cámara captura, genera el hash y envía al servidor
4. **Opción 2 - Documento + Contraseña:** si la cámara falla 3 veces, aparece un formulario para ingresar documento y contraseña
5. Cada intento queda registrado en `access_logs` con IP, fecha, estado (success/denied) y método (camera/password)
6. La pantalla muestra un modal animado: **verde** para acceso exitoso, **rojo** para denegado, **naranja** cuando se bloquea por intentos"

### 11:50 - 12:15 | Reportes de acceso
"Navegamos a **Reportes**.

1. Vemos las 4 tarjetas de estadísticas: total hoy, exitosos, denegados, acumulados
2. Filtramos por rango de fechas con los campos **Desde / Hasta**
3. Filtramos por estado: Todos / Exitoso / Denegado
4. Usamos el buscador para filtrar por nombre o documento
5. La tabla muestra: fecha, nombre, documento, IP, estado, método
6. La paginación navega entre páginas de 25 registros"

### 12:15 - 12:35 | Probar la API con Postman
"Probamos la API REST con Postman:

1. **POST /api/auth/login** con `{"username":"admin","password":"admin123"}` → obtenemos JWT
2. **GET /api/dashboard/stats** con Authorization: Bearer token → vemos estadísticas
3. **GET /api/faces?page=1&limit=5** → lista paginada de rostros
4. **POST /api/faces/search** con `{"num_doc":"12345678","password":"1234"}` → verificar acceso

La documentación completa de los endpoints está en `docs/api-documentation.md`."

### 12:35 - 13:30 | Prueba de seguridad
"Probamos los controles de seguridad:

1. Intentar acceder a `/home` sin sesión → redirige al login
2. Llamar a `/api/faces` sin token → error 401
3. Usuario vigilante intenta crear un rostro → error 403
4. Token expirado → error 401"
5. Refresh token → se renueva el token automáticamente

### 13:30 - 14:30 | Código destacado - lo más interesante
"Tres partes del código que vale la pena destacar:

1. **dHash en JavaScript** — Reconocimiento facial sin librerías externas, solo con canvas y álgebra básica. El hash se genera en el navegador y se compara en el servidor con los hashes almacenados.

2. **JWTAuthFilter** — Intercepta cada petición API, extrae el token, lo decodifica y lo valida en una sola pasada, inyectando el usuario autenticado en el request para que los controladores lo usen sin repetir código.

3. **Límite de intentos en cámara** — Usa la sesión para contar intentos fallidos (máximo 3). Al alcanzar el límite, fuerza al usuario a usar el método de documento+contraseña como respaldo, y ambos métodos quedan registrados en el log."

### 14:30 - 14:45 | Limitaciones y trabajo futuro
"Lo que no hace el sistema actualmente y se podría mejorar:

- El hash facial dHash es sensible a la iluminación y ángulo; una red neuronal como FaceNet sería más precisa
- No hay notificaciones en tiempo real (WebSockets) cuando alguien intenta acceder
- La gestión de múltiples cámaras no está implementada
- No hay exportación de reportes a PDF o Excel
- El registro de acceso no tiene foto asociada al evento (solo nombre y documento)"

### 14:45 - 15:00 | Conclusión
"**Secure Access** demuestra que es posible construir un sistema de control de acceso funcional con tecnologías web estándar (PHP, JavaScript, MySQL) sin necesidad de hardware especializado ni servicios de IA costosos.

El proyecto aplica:
- **Patrón MVC** para separación de responsabilidades
- **JWT** para autenticación stateless en APIs
- **dHash** para reconocimiento facial básico en el navegador
- **Diseño responsive** con interfaz oscura moderna
- **Control de acceso por roles** (admin, supervisor, vigilante)

El código completo tiene comentarios línea por línea explicando cada función, y la API está completamente documentada para integración con aplicaciones externas.

Gracias por su atención. ¿Preguntas?"

---

## Resumen de Tiempos

| Minuto | Expositor | Tema |
|--------|-----------|------|
| 0:00-5:00 | **Juan** | Introducción, problema, stack tecnológico, arquitectura MVC, BD |
| 5:00-10:00 | **Ehidan** | Rutas, filtros JWT, controladores web y API, hash facial, reportes |
| 10:00-15:00 | **Krispi** | Demo en vivo (login, registro, cámara, reportes, Postman, seguridad, conclusión) |

## Recomendaciones para la Exposición

1. **Preparar el entorno**: tener XAMPP corriendo, base de datos con datos de prueba, y Postman con la colección importada
2. **Probar la cámara**: asegurarse de que el navegador tenga permiso para la cámara web
3. **Tener datos de muestra**: al menos 5 rostros registrados y algunos logs de acceso para mostrar reportes
4. **Postman**: tener la colección lista con variables de entorno configuradas
5. **Repartir turnos**: cada expositor debe hablar claramente y pasar el turno al siguiente sin silencios incómodos
6. **Tener un plan B**: si la cámara falla, usar el método de documento + contraseña para la demo

# Documentación de la API REST - Secure Access

## Configuración General

| Propiedad | Valor |
|-----------|-------|
| **Base URL** | `http://localhost/proyecto_sena/api` |
| **Content-Type** | `application/json` |
| **Auth** | JWT (Bearer token) |
| **Secret JWT** | `secure-access-jwt-secret-key-2026` |
| **Expiración token** | 24 horas (86400 segundos) |
| **Expiración refresh** | 7 días |
| **Algoritmo** | HS256 |

---

## Autenticación

### POST `/api/auth/login`

Inicia sesión y devuelve un token JWT + refresh token.

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Inicio de sesión exitoso",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "user": {
      "id": 1,
      "username": "admin",
      "role": "admin",
      "full_name": "Administrador",
      "is_active": 1
    }
  }
}
```

**Respuesta error (401):**
```json
{
  "status": "error",
  "message": "Usuario o contraseña incorrectos"
}
```

**Postman:**
- Método: `POST`
- URL: `{{base_url}}/auth/login`
- Body: raw > JSON
- Test: Asignar `token` y `refresh_token` a variables de colección

```javascript
// Postman Tests - asignar tokens globales
const jsonData = pm.response.json();
if (jsonData.status === "success") {
  pm.collectionVariables.set("token", jsonData.data.token);
  pm.collectionVariables.set("refresh_token", jsonData.data.refresh_token);
}
```

---

### POST `/api/auth/refresh`

Renueva el token JWT usando un refresh token válido.

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Token renovado exitosamente",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
    "refresh_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
  }
}
```

**Respuesta error (401):**
```json
{
  "status": "error",
  "message": "Refresh token inválido o expirado"
}
```

---

### GET `/api/auth/me`

Obtiene los datos del usuario autenticado (requiere token).

**Headers:**
```
Authorization: Bearer {{token}}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "OK",
  "data": {
    "id": 1,
    "username": "admin",
    "role": "admin",
    "full_name": "Administrador",
    "is_active": 1
  }
}
```

**Respuesta error (401):**
```json
{
  "status": "error",
  "message": "No autorizado"
}
```

---

### POST `/api/auth/logout`

Cierra la sesión (respuesta confirmatoria, JWT es stateless).

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body:** (vacío)

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Sesión cerrada exitosamente",
  "data": null
}
```

---

## Rostros (Faces)

Todas las rutas requieren **Authorization: Bearer {{token}}**.

### GET `/api/faces`

Lista todos los rostros registrados con paginación.

**Headers:**
```
Authorization: Bearer {{token}}
```

**Query params:**
| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `page` | int | 1 | Número de página |
| `limit` | int | 10 | Resultados por página (max 100) |

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "OK",
  "data": {
    "faces": [
      {
        "id": 1,
        "name": "Juan Pérez",
        "num_doc": "12345678",
        "image_path": "uploads/face_abc123.jpg",
        "face_hash": null,
        "faces_detected": 1,
        "created_at": "2026-06-10 10:00:00"
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "total_pages": 1
  }
}
```

---

### GET `/api/faces/{id}`

Obtiene un rostro específico por su ID.

**Headers:**
```
Authorization: Bearer {{token}}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "OK",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    "num_doc": "12345678",
    "image_path": "uploads/face_abc123.jpg",
    "face_hash": null,
    "access_password": "$2y$10$...",
    "faces_detected": 1,
    "created_at": "2026-06-10 10:00:00"
  }
}
```

**Respuesta error (404):**
```json
{
  "status": "error",
  "message": "Rostro no encontrado"
}
```

---

### POST `/api/faces`

Crea un nuevo rostro (solo admin/supervisor).

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "name": "Juan Pérez",
  "num_doc": "12345678",
  "face_hash": "abc123def456...",
  "access_password": "1234",
  "faces_detected": 1
}
```

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Rostro registrado exitosamente",
  "data": {
    "id": 2,
    "name": "Juan Pérez",
    "num_doc": "12345678",
    "image_path": null,
    "face_hash": "abc123def456...",
    "faces_detected": 1,
    "created_at": "2026-06-10 10:30:00"
  }
}
```

**Respuesta error (403):**
```json
{
  "status": "error",
  "message": "Acceso denegado"
}
```

**Respuesta error (422):**
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "name": "El nombre es requerido"
  }
}
```

---

### PUT `/api/faces/{id}`

Actualiza un rostro existente (solo admin/supervisor). Todos los campos son opcionales.

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "name": "Juan Pérez Actualizado",
  "num_doc": "87654321",
  "face_hash": "nuevo_hash",
  "access_password": "5678",
  "faces_detected": 2
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Rostro actualizado exitosamente",
  "data": {
    "id": 2,
    "name": "Juan Pérez Actualizado",
    "num_doc": "87654321",
    "face_hash": "nuevo_hash",
    "faces_detected": 2,
    "created_at": "2026-06-10 10:30:00"
  }
}
```

---

### DELETE `/api/faces/{id}`

Elimina un rostro y su imagen del servidor (solo admin/supervisor).

**Headers:**
```
Authorization: Bearer {{token}}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Rostro eliminado exitosamente"
}
```

---

### POST `/api/faces/upload`

Sube una imagen de rostro y lo registra (multipart, solo admin/supervisor).

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: multipart/form-data
```

**Form Data:**
| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `image` | file | Sí | Archivo de imagen (jpeg, png, webp) |
| `name` | string | Sí | Nombre de la persona |
| `num_doc` | string | No | Número de identificación |
| `access_password` | string | No | Contraseña de acceso (mín. 4 caracteres) |
| `face_hash` | string | No | Hash facial (dHash) |

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Rostro registrado exitosamente",
  "data": {
    "id": 3,
    "name": "María López",
    "num_doc": "98765432",
    "image_path": "http://localhost/proyecto_sena/uploads/abc123.jpg",
    "face_hash": "hash_value",
    "faces_detected": 1,
    "created_at": "2026-06-10 11:00:00"
  }
}
```

---

### POST `/api/faces/search`

Busca un rostro por documento y contraseña (verificación de acceso).

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "num_doc": "12345678",
  "password": "1234"
}
```

**Respuesta acceso permitido (200):**
```json
{
  "status": "success",
  "message": "ACCESO SATISFACTORIO",
  "data": {
    "found": true,
    "name": "Juan Pérez",
    "face": {
      "id": 1,
      "name": "Juan Pérez",
      "num_doc": "12345678",
      "image_path": "uploads/face_abc123.jpg",
      "face_hash": null,
      "faces_detected": 1,
      "created_at": "2026-06-10 10:00:00"
    }
  }
}
```

**Respuesta acceso denegado (200):**
```json
{
  "status": "success",
  "message": "ACCESO DENEGADO",
  "data": {
    "found": false,
    "name": null
  }
}
```

---

## Usuarios (Users)

Todas las rutas requieren **Authorization: Bearer {{token}}** y solo **admin**.

### GET `/api/users`

Lista todos los usuarios con paginación (solo admin).

**Headers:**
```
Authorization: Bearer {{token}}
```

**Query params:**
| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `page` | int | 1 | Número de página |
| `limit` | int | 10 | Resultados por página (max 100) |

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "OK",
  "data": {
    "users": [
      {
        "id": 1,
        "username": "admin",
        "role": "admin",
        "full_name": "Administrador",
        "is_active": 1
      }
    ],
    "total": 1,
    "page": 1,
    "limit": 10,
    "total_pages": 1
  }
}
```

---

### GET `/api/users/{id}`

Obtiene un usuario específico (solo admin o el propio usuario).

**Headers:**
```
Authorization: Bearer {{token}}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "OK",
  "data": {
    "id": 1,
    "username": "admin",
    "role": "admin",
    "full_name": "Administrador",
    "is_active": 1
  }
}
```

---

### POST `/api/users`

Crea un nuevo usuario (solo admin).

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "username": "operador1",
  "password": "secreto123",
  "full_name": "Operador Uno",
  "role": "vigilante"
}
```

**Roles válidos:** `admin`, `supervisor`, `vigilante`

**Respuesta exitosa (201):**
```json
{
  "status": "success",
  "message": "Usuario creado exitosamente",
  "data": {
    "id": 3,
    "username": "operador1",
    "role": "vigilante",
    "full_name": "Operador Uno",
    "is_active": 1
  }
}
```

**Respuesta error (422):**
```json
{
  "status": "error",
  "message": "Error de validación",
  "errors": {
    "username": "El nombre de usuario ya existe"
  }
}
```

---

### PUT `/api/users/{id}`

Actualiza un usuario existente (solo admin). Todos los campos son opcionales.

**Headers:**
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "username": "operador_nuevo",
  "full_name": "Operador Modificado",
  "role": "supervisor",
  "password": "nueva_pass123"
}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Usuario actualizado exitosamente",
  "data": {
    "id": 3,
    "username": "operador_nuevo",
    "role": "supervisor",
    "full_name": "Operador Modificado",
    "is_active": 1
  }
}
```

---

### DELETE `/api/users/{id}`

Elimina un usuario (solo admin, no permite auto-eliminación).

**Headers:**
```
Authorization: Bearer {{token}}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "Usuario eliminado exitosamente"
}
```

**Respuesta error (400):**
```json
{
  "status": "error",
  "message": "No puedes eliminar tu propio usuario"
}
```

---

## Dashboard

Requiere **Authorization: Bearer {{token}}**.

### GET `/api/dashboard/stats`

Obtiene estadísticas generales del sistema.

**Headers:**
```
Authorization: Bearer {{token}}
```

**Respuesta exitosa (200):**
```json
{
  "status": "success",
  "message": "OK",
  "data": {
    "total_faces": 25,
    "total_users": 5,
    "active_users": 4,
    "recent_faces": [
      {
        "id": 25,
        "name": "Carlos Ruiz",
        "num_doc": "11223344",
        "image_path": "uploads/face_xyz.jpg",
        "face_hash": null,
        "faces_detected": 1,
        "created_at": "2026-06-10 12:00:00"
      }
    ]
  }
}
```

---

## Códigos de Estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | Éxito |
| 201 | Creado exitosamente |
| 400 | Error del cliente |
| 401 | No autorizado (sin token o token inválido) |
| 403 | Prohibido (sin permisos suficientes) |
| 404 | Recurso no encontrado |
| 422 | Error de validación |

---

## Flujo de Autenticación en Postman

1. **Configurar variables de colección:**
   - `base_url`: `http://localhost/proyecto_sena/api`
   - `token`: (vacío)
   - `refresh_token`: (vacío)

2. **Login:** POST `/api/auth/login` → Script de Tests asigna `token` y `refresh_token`

3. **Usar token:** En las demás rutas, configurar Header:
   ```
   Authorization: Bearer {{token}}
   ```

4. **Refresh:** Cuando el token expire, llamar POST `/api/auth/refresh` con body `{"refresh_token": "{{refresh_token}}"}`

5. **Authorization automática en Postman:** Se puede configurar en la pestaña "Authorization" de la colección:
   - Type: `Bearer Token`
   - Token: `{{token}}`

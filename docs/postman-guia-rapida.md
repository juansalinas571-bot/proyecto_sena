# Guía Rápida: Probar la API con Postman

## Paso 1: Importar la colección

1. Abre Postman
2. Botón **Import** → elegir archivo `docs/Secure-Access-Postman.json`
3. Aparecerá la colección **Secure Access API** con todas las carpetas

Las variables `base_url`, `token` y `refresh_token` ya vienen configuradas.

---

## Paso 2: Login (obtener token)

1. En la colección, abre **Auth > Login**
2. Haz clic en **Send**
3. Si todo funciona, en la pestaña "Tests" se asignó automáticamente el token a la variable `{{token}}`
4. Abajo en la respuesta deberías ver:

```json
{
  "status": "success",
  "data": {
    "token": "eyJ0eXAiOiJKV1Qi...",
    "user": { "username": "admin", "role": "admin" }
  }
}
```

---

## Paso 3: Probar los demás endpoints

Ya con el token asignado, puedes probar cualquier endpoint:

| Qué probar | Dónde está | Qué esperar |
|-----------|-----------|-------------|
| Estadísticas | **Dashboard > Estadísticas** | total_faces, total_users |
| Lista de rostros | **Faces > Listar rostros** | Array de rostros paginado |
| Buscar acceso | **Faces > Buscar rostro** | found: true/false |
| Crear rostro | **Faces > Crear rostro** | status: success, código 201 |
| Usuarios | **Users > Listar usuarios** | Solo si eres admin |

---

## Paso 4: Probar la seguridad

1. **Sin token** → "Pruebas de Seguridad > Sin token" → debe responder **401**
2. **Token falso** → "Pruebas de Seguridad > Token inválido" → debe responder **401**

---

## Tips para la exposición

- Si el token expiró (24h), vuelve a ejecutar **Login** para renovarlo
- Para la demo de "Buscar rostro", cambia el `num_doc` y `password` por uno que exista en tu BD
- Para la demo de "Crear rostro", después ve a "Listar rostros" para ver que apareció el nuevo
- Si quieres probar la subida de imágenes, en "Subir imagen" haz clic en "Select Files" y elige un archivo JPG
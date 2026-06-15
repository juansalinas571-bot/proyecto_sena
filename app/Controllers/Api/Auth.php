<?php // Inicia el bloque de código PHP

namespace App\Controllers\Api; // Espacio de nombres para controladores de la API

use App\Controllers\ApiController; // Clase base para controladores API
use Config\JWT; // Configuración del JWT (secreto, expiración, algoritmo)
use Firebase\JWT\JWT as FirebaseJWT; // Librería para generar y validar tokens JWT

class Auth extends ApiController // Controlador de autenticación para la API
{
    // Inicia sesión y devuelve un token JWT
    public function login()
    {
        $input = $this->getJsonInput(); // Obtiene los datos JSON del cuerpo de la petición

        // Valida que haya usuario y contraseña
        if (empty($input['username']) || empty($input['password'])) {
            return $this->respondValidationError([ // Error de validación
                'username' => 'El usuario es requerido',
                'password' => 'La contraseña es requerida',
            ], 'Credenciales incompletas');
        }

        $userModel = model('App\Models\UserModel');
        $user = $userModel // Busca usuario por username que esté activo
            ->where('username', trim($input['username']))
            ->where('is_active', 1)
            ->first();

        if (!$user || !password_verify($input['password'], $user['password'])) { // Credenciales inválidas
            return $this->respondUnauthorized('Usuario o contraseña incorrectos');
        }

        // Genera tokens de acceso y refresco
        $token = $this->generateToken($user); // Token principal (corta duración)
        $refreshToken = $this->generateRefreshToken($user); // Token de refresco (larga duración)

        return $this->respondSuccess([ // Respuesta exitosa con tokens
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'user'          => $this->sanitizeUser($user), // Datos del usuario (sin contraseña)
        ], 'Inicio de sesión exitoso');
    }

    // Obtiene los datos del usuario autenticado
    public function me()
    {
        if (!$this->request->authUser) { // Si no hay usuario autenticado
            return $this->respondUnauthorized();
        }

        return $this->respondSuccess(
            $this->sanitizeUser($this->request->authUser) // Devuelve datos sin contraseña
        );
    }

    // Renueva el token usando el refresh token
    public function refresh()
    {
        $input = $this->getJsonInput(); // Obtiene JSON de la petición

        if (empty($input['refresh_token'])) { // Valida que tenga refresh_token
            return $this->respondValidationError([
                'refresh_token' => 'El refresh token es requerido',
            ]);
        }

        try { // Intenta decodificar el refresh token
            $config = config('JWT');
            $decoded = FirebaseJWT::decode( // Decodifica el refresh token
                $input['refresh_token'],
                new \Firebase\JWT\Key($config->secret, $config->algorithm)
            );

            $userModel = model('App\Models\UserModel');
            $user = $userModel->find($decoded->sub); // Busca usuario por ID del token

            if (!$user || !$user['is_active']) { // Usuario no válido
                return $this->respondUnauthorized('Usuario no válido');
            }

            // Genera nuevos tokens
            $newToken = $this->generateToken($user);
            $newRefresh = $this->generateRefreshToken($user);

            return $this->respondSuccess([ // Devuelve los nuevos tokens
                'token'         => $newToken,
                'refresh_token' => $newRefresh,
            ], 'Token renovado exitosamente');
        } catch (\Exception $e) { // Si el refresh token es inválido o expiró
            return $this->respondUnauthorized('Refresh token inválido o expirado');
        }
    }

    // Cierra la sesión (solo responde éxito, ya que JWT es stateless)
    public function logout()
    {
        return $this->respondSuccess(null, 'Sesión cerrada exitosamente');
    }

    // Genera un token JWT de acceso con los datos del usuario
    private function generateToken(array $user): string
    {
        $config = config('JWT'); // Obtiene configuración
        $time = time(); // Tiempo actual Unix

        $payload = [ // Datos del token
            'iss' => 'secure-access', // Emisor del token
            'iat' => $time, // Emitido en
            'exp' => $time + $config->expiration, // Expira (24 horas por defecto)
            'sub' => $user['id'], // Subject: ID del usuario
            'role' => $user['role'], // Rol del usuario (para autorización)
        ];

        return FirebaseJWT::encode($payload, $config->secret, $config->algorithm); // Codifica y devuelve el token
    }

    // Genera un token de refresco con mayor duración (7 días)
    private function generateRefreshToken(array $user): string
    {
        $config = config('JWT');
        $time = time();

        $payload = [
            'iss' => 'secure-access',
            'iat' => $time,
            'exp' => $time + ($config->expiration * 7), // Expira en 7 días
            'sub' => $user['id'],
            'type' => 'refresh', // Tipo: refresh para distinguirlo del token de acceso
        ];

        return FirebaseJWT::encode($payload, $config->secret, $config->algorithm);
    }

    // Elimina la contraseña del array del usuario por seguridad
    private function sanitizeUser(array $user): array
    {
        unset($user['password']); // Remueve el campo password
        return $user; // Devuelve el usuario sin datos sensibles
    }
}

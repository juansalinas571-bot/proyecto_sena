<?php // Inicia el bloque de código PHP

namespace App\Database\Migrations; // Espacio de nombres para migraciones

use CodeIgniter\Database\Migration; // Clase base para migraciones de CI4
use CodeIgniter\Database\RawSql; // Permite usar SQL raw (ej: CURRENT_TIMESTAMP)

class CreateUsersAndFaces extends Migration // Primera migración: crea tablas users y faces
{
    // Método que se ejecuta al aplicar la migración (crea las tablas)
    public function up()
    {
        // ========== TABLA: users ==========
        $this->forge->addField([ // Define los campos de la tabla
            'id'         => ['type' => 'INT', 'auto_increment' => true], // ID autoincremental
            'username'   => ['type' => 'VARCHAR', 'constraint' => 50, 'unique' => true], // Nombre de usuario (único)
            'password'   => ['type' => 'VARCHAR', 'constraint' => 255], // Contraseña hasheada (bcrypt)
            'role'       => ['type' => 'ENUM', 'constraint' => ['admin', 'supervisor', 'vigilante'], 'default' => 'vigilante'], // Rol del usuario
            'full_name'  => ['type' => 'VARCHAR', 'constraint' => 100], // Nombre completo del usuario
            'is_active'  => ['type' => 'TINYINT', 'default' => 1], // Estado activo/inactivo (1=activo)
            'created_at' => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')], // Fecha de creación automática
        ]);
        $this->forge->addKey('id', true); // Define 'id' como clave primaria
        $this->forge->createTable('users', true); // Crea la tabla 'users' (if not exists)

        // ========== TABLA: faces ==========
        $this->forge->addField([ // Define los campos de la tabla de rostros
            'id'            => ['type' => 'INT', 'auto_increment' => true], // ID autoincremental
            'name'          => ['type' => 'VARCHAR', 'constraint' => 100], // Nombre de la persona
            'image_path'    => ['type' => 'VARCHAR', 'constraint' => 255], // Ruta de la foto en el servidor
            'face_hash'     => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true], // Hash facial para reconocimiento (puede ser nulo)
            'faces_detected' => ['type' => 'INT', 'default' => 0], // Número de rostros detectados en la foto
            'created_at'    => ['type' => 'TIMESTAMP', 'default' => new RawSql('CURRENT_TIMESTAMP')], // Fecha de registro
        ]);
        $this->forge->addKey('id', true); // Define 'id' como clave primaria
        $this->forge->createTable('faces', true); // Crea la tabla 'faces'

        // ========== USUARIO POR DEFECTO ==========
        $userModel = model('App\Models\UserModel'); // Carga el modelo de usuarios
        $existing = $userModel->where('username', 'admin')->first(); // Verifica si ya existe el admin
        if (!$existing) { // Si no existe, lo crea
            $userModel->insert([
                'username'  => 'admin', // Usuario: admin
                'password'  => password_hash('admin123', PASSWORD_DEFAULT), // Contraseña hasheada: admin123
                'role'      => 'admin', // Rol: administrador
                'full_name' => 'Administrador', // Nombre mostrado
                'is_active' => 1, // Activo
            ]);
        }
    }

    // Método que se ejecuta al revertir la migración (elimina las tablas)
    public function down()
    {
        $this->forge->dropTable('faces', true); // Elimina la tabla faces si existe
        $this->forge->dropTable('users', true); // Elimina la tabla users si existe
    }
}

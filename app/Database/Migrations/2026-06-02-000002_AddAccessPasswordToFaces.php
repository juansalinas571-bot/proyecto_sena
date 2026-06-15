<?php // Inicia el bloque de código PHP

namespace App\Database\Migrations; // Espacio de nombres para migraciones

use CodeIgniter\Database\Migration; // Clase base para migraciones

class AddAccessPasswordToFaces extends Migration // Migración para agregar contraseña de acceso a la tabla faces
{
    // Método que se ejecuta al aplicar la migración
    public function up()
    {
        // Agrega la columna 'access_password' a la tabla 'faces'
        $this->forge->addColumn('faces', [
            'access_password' => [ // Campo para almacenar la contraseña de acceso hasheada
                'type'       => 'VARCHAR',
                'constraint' => 255, // Suficiente para hash bcrypt
                'null'       => true, // Puede ser nulo (rostros sin contraseña)
                'after'      => 'face_hash', // La columna se coloca después de 'face_hash'
            ],
        ]);
    }

    // Método que se ejecuta al revertir la migración
    public function down()
    {
        // Elimina la columna 'access_password' de la tabla 'faces'
        $this->forge->dropColumn('faces', 'access_password');
    }
}

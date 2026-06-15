<?php
include "../modelo/usuarios.php";

try {
    $consulta_usuario = new usuario();
    $datos = $consulta_usuario->consultageneral();

    if ($datos instanceof PDOException) {
        echo "<script>alert('Error de conexión o consulta: " . $datos->getMessage() . "');</script>";
    } elseif (is_array($datos)) {
        echo "<style>
                .tabla-usuarios {
                    width: 100%;
                    border-collapse: collapse;
                    border: 1px solid rgba(6, 182, 212, 0.2);
                    border-radius: 10px;
                    overflow: hidden;
                }
                .tabla-usuarios th {
                    background: #0b1a33;
                    color: #86e8ff;
                    font-weight: 600;
                    padding: 12px 10px;
                    border-bottom: 1px solid rgba(6, 182, 212, 0.2);
                    text-align: left;
                    white-space: nowrap;
                }
                .tabla-usuarios td {
                    padding: 12px 10px;
                    border-bottom: 1px solid rgba(148, 163, 184, 0.15);
                    color: #dbe8fd;
                }
                .tabla-usuarios tr:hover {
                    background: rgba(6, 182, 212, 0.07);
                }
                .perfil-badge {
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 999px;
                    font-size: 0.82rem;
                    font-weight: 600;
                    border: 1px solid transparent;
                }
                .perfil-1 {
                    color: #ffd18f;
                    background: rgba(250, 163, 7, 0.13);
                    border-color: rgba(250, 163, 7, 0.4);
                }
                .perfil-2 {
                    color: #9de6b8;
                    background: rgba(34, 197, 94, 0.13);
                    border-color: rgba(34, 197, 94, 0.4);
                }
                .perfil-3 {
                    color: #9fddff;
                    background: rgba(6, 182, 212, 0.15);
                    border-color: rgba(6, 182, 212, 0.35);
                }
                .acciones {
                    white-space: nowrap;
                }
                .btn-accion {
                    display: inline-block;
                    text-decoration: none;
                    font-size: 0.82rem;
                    border-radius: 8px;
                    padding: 6px 10px;
                    margin-right: 6px;
                    border: 1px solid transparent;
                    transition: 0.2s;
                }
                .btn-editar {
                    color: #8ce6ff;
                    border-color: rgba(6, 182, 212, 0.45);
                    background: rgba(6, 182, 212, 0.1);
                }
                .btn-editar:hover {
                    background: rgba(6, 182, 212, 0.24);
                }
                .btn-eliminar {
                    color: #ffb6c4;
                    border-color: rgba(244, 63, 94, 0.45);
                    background: rgba(244, 63, 94, 0.12);
                }
                .btn-eliminar:hover {
                    background: rgba(244, 63, 94, 0.25);
                }
                .tabla-vacia {
                    text-align: center;
                    padding: 16px;
                    border: 1px solid rgba(148, 163, 184, 0.2);
                    border-radius: 10px;
                    color: #94a3b8;
                    background: rgba(2, 6, 23, 0.6);
                }
              </style>";

        if (count($datos) === 0) {
            echo "<div class='tabla-vacia'>No hay usuarios activos registrados.</div>";
        } else {
            echo "<table class='tabla-usuarios'>";
            echo "<tr>
                    <th>Nombre</th>
                    <th>Número documento</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Perfil</th>
                    <th>Acciones</th>
                  </tr>";

            foreach ($datos as $valor) {
                $nombre = htmlspecialchars((string)$valor['nombre']);
                $numDoc = htmlspecialchars((string)$valor['num_doc']);
                $telefono = htmlspecialchars((string)$valor['telefono']);
                $correo = htmlspecialchars((string)$valor['correo']);
                $perfilRaw = (string)$valor['perfil'];
                $perfil = htmlspecialchars($perfilRaw);

                $perfilTexto = $perfil;
                if ($perfilRaw === '1') {
                    $perfilTexto = 'Administrador';
                } elseif ($perfilRaw === '2') {
                    $perfilTexto = 'Vigilante';
                }
                elseif ($perfilRaw === '3') {
                    $perfilTexto = 'Supervisor de seguridad';
                }

                echo "<tr>
                        <td>{$nombre}</td>
                        <td>{$numDoc}</td>
                        <td>{$telefono}</td>
                        <td>{$correo}</td>
                        <td><span class='perfil-badge perfil-{$perfil}'>{$perfilTexto}</span></td>
                        <td class='acciones'>
                            <a class='btn-accion btn-editar' href='../controlador/usuario_controlador3.php?documento={$numDoc}'>Editar</a>
                            <a class='btn-accion btn-eliminar' href='../controlador/usuario_controlador5.php?documento={$numDoc}' onclick='return confirm(\"¿Está seguro de eliminar este usuario?\")'>Eliminar</a>
                        </td>
                      </tr>";
            }
            echo "</table>";
        }
    } else {
        echo "<script>alert('No se pudieron obtener los datos de usuarios.');</script>";
    }
} catch (Exception $e) {
    echo "<script>alert('Error del sistema al cargar usuarios: " . $e->getMessage() . "');</script>";
}
?>

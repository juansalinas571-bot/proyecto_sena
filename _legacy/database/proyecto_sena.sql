CREATE DATABASE IF NOT EXISTS proyecto_sena;
USE proyecto_sena;

CREATE TABLE IF NOT EXISTS usuario (
    id SMALLINT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255) NOT NULL,
    num_doc BIGINT UNIQUE NOT NULL,
    telefono BIGINT NOT NULL,
    correo VARCHAR(255) NOT NULL,
    perfil CHAR(1) NOT NULL,
    contrasena VARCHAR(255),
    estado CHAR(1) DEFAULT 'A'
);

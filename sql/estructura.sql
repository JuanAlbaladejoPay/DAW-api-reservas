CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) UNIQUE,
    roles JSON,
    password VARCHAR(255),
    nombre VARCHAR(255),
    apellidos VARCHAR(255),
    telefono INT
  );

/* Aquí podríamos poner la duración max, o controlarlo directamente desde el cliente. Como queramos */
CREATE TABLE tipo_instalacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) UNIQUE,
    duracion INT NOT NULL 
);

CREATE TABLE instalacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    tipo_id INT,
    precioHora FLOAT,
    FOREIGN KEY (tipo_id) REFERENCES tipo_instalacion(id)
);

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE,
    hora TIME,
    duracion INT,
    importe FLOAT,
    idUsuario INT NOT NULL,
    idInstalacion INT NOT NULL,
    FOREIGN KEY (idUsuario) REFERENCES usuarios(id),
    FOREIGN KEY (idInstalacion) REFERENCES instalaciones(id),
    UNIQUE (idInstalacion, fecha, hora)
);

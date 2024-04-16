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


/*TODO: Comprobar que no superponemos reservas para la misma instalación en la misma fecha y hora (o duración).
- Antes de insertar una nueva reserva en la base de datos, consulta todas las reservas existentes para la misma instalación en la misma fecha.

- Para cada reserva existente, verifica si hay superposición de horarios con la nueva reserva que se está intentando insertar. Esto se puede hacer comparando la hora de inicio y la duración de la reserva existente con la hora de inicio y duración de la nueva reserva.

- Si encuentras alguna superposición, lanza un error indicando que la nueva reserva no se puede realizar debido a la superposición de horarios.

- Si no hay superposiciones con ninguna reserva existente, procede a insertar la nueva reserva en la base de datos. 
 */
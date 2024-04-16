INSERT INTO Instalaciones (nombre, precioHora) VALUES
('Tenis - 1', 6),
('Tenis - 2', 6),
('Fútbol 7 - 1', 21),
('Fútbol 7 - 2', 21),
('Padel - 1', 16),
('Padel - 2', 16),
('Padel - 3', 16),
('Padel - 4', 16),
('Fútbol sala - Outdoor', 10),
('Fútbol sala - Pabellon', 15),
('Baloncesto - Outdoor', 10),
('Baloncesto - Pabellon', 15),
('Piscina cubierta', 3),
('Gimnasio', 3),
('Volleyball', 15);

/* TODO
- Sería mejor añadir un campo de tipo de instalación para poder hacer consultas más eficientes? Respuesta de Copilot:
Sí, eso podría ser una buena idea si quieres normalizar tu base de datos. Podrías tener una tabla separada para los tipos de instalaciones y luego referenciar esa tabla en tu tabla de instalaciones. Aquí te dejo un ejemplo de cómo podrías hacerlo:
CREATE TABLE TipoInstalacion (
    id INT PRIMARY KEY,
    nombre VARCHAR(255)
);

INSERT INTO TipoInstalacion (id, nombre) VALUES
(1, 'Tenis'),
(2, 'Fútbol 7'),
(3, 'Padel'),
(4, 'Fútbol sala'),
(5, 'Baloncesto'),
(6, 'Piscina'),
(7, 'Gimnasio'),
(8, 'Volleyball');

CREATE TABLE Instalaciones (
    id INT PRIMARY KEY,
    nombre VARCHAR(255),
    precioHora DECIMAL(10, 2),
    tipoInstalacionId INT,
    FOREIGN KEY (tipoInstalacionId) REFERENCES TipoInstalacion(id)
);

INSERT INTO Instalaciones (nombre, precioHora, tipoInstalacionId) VALUES
('1', 6, 1),
('2', 6, 1),
('1', 21, 2),
('2', 21, 2),
('1', 16, 3),
('2', 16, 3),
('3', 16, 3),
('4', 16, 3),
('Outdoor', 10, 4),
('Pabellon', 15, 4),
('Outdoor', 10, 5),
('Pabellon', 15, 5),
('cubierta', 3, 6),
('', 3, 7),
('', 15, 8);
 */
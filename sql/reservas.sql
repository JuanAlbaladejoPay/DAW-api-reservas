INSERT INTO Reservas (fecha, hora, duracion, importe, idUsuario, idInstalacion) VALUES
('2024-04-24', '09:00', '60', '6', '1', '1'),
('2024-04-24', '14:30', '90', '9', '1', '1'),
('2024-04-24', '10:00', '60', '21', '1', '2'),
('2024-04-24', '15:30', '90', '21', '1', '2'),
('2024-04-24', '11:00', '60', '16', '1', '3'),
('2024-04-24', '16:30', '90', '16', '1', '4'),
('2024-04-24', '12:30', '60', '15', '1', '5'),
('2024-04-24', '17:00', '60', '15', '1', '6'),
('2024-04-24', '13:00', '60', '3', '1', '7'),
('2024-04-24', '18:30', '60', '15', '1', '8'),
('2024-04-25', '14:00', '60', '15', '1', '1'),
('2024-04-25', '19:00', '60', '15', '1', '2'),
('2024-04-25', '15:00', '60', '15', '1', '3'),
('2024-04-25', '20:00', '60', '15', '1', '4'),
('2024-04-25', '16:00', '60', '15', '1', '5'),
('2024-04-25', '21:00', '60', '15', '1', '6'),
('2024-04-25', '17:00', '60', '15', '1', '7'),
('2024-04-25', '22:00', '60', '15', '1', '8'),
('2024-04-25', '18:00', '60', '15', '1', '1'),
('2024-04-25', '23:00', '60', '15', '1', '2');

/* 

// SI NO TE FUNCIONA EL INSERT HAZ ESTO

// SELECT TABLE_NAME, CONSTRAINT_NAME
// FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
// WHERE COLUMN_NAME = 'idInstalacion';

// -- Eliminar la restricción de clave externa
// ALTER TABLE reservas DROP FOREIGN KEY FK_536BC957FE06F768; <- EL CODIGO QUE TE SALGA A TI

// -- Deshabilitar temporalmente las restricciones de clave externa
// SET FOREIGN_KEY_CHECKS = 0;

// -- Eliminar el índice
// ALTER TABLE reservas DROP INDEX UNIQ_536BC957FE06F768; <- EL CODIGO QUE TE SALGA A TI

// -- Volver a habilitar las restricciones de clave externa
// SET FOREIGN_KEY_CHECKS = 1;
 */
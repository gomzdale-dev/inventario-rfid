USE activos_fijos;
select * from usuarios;
-- ========================================================
-- LIMPIEZA DE TABLAS (Evita conflictos al reiniciar pruebas)
-- ========================================================
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE detalle_inventarios;
TRUNCATE TABLE inventarios;
TRUNCATE TABLE movimientos;
TRUNCATE TABLE activos;
TRUNCATE TABLE usuarios;
TRUNCATE TABLE tipo_usuarios;
TRUNCATE TABLE responsables;
TRUNCATE TABLE ubicaciones;
TRUNCATE TABLE laboratorios;
TRUNCATE TABLE edificios;
TRUNCATE TABLE modelos;
TRUNCATE TABLE marcas;
TRUNCATE TABLE etiquetas_rfid;
TRUNCATE TABLE tipo_movimientos;
TRUNCATE TABLE estado_activos;
SET FOREIGN_KEY_CHECKS = 1;

-- Edificios
INSERT INTO edificios (id_edificio, nombre_edificio, estado) VALUES
(1, 'Edificio A', 'A'),
(2, 'Edificio B', 'A'),
(3, 'Edificio C', 'A');

-- Categorías 
INSERT INTO categorias (id_categoria, nombre_categoria, estado) VALUES
(1, 'Equipo de Cómputo', 'A'),       -- Servidores, PCs, Laptops, Monitores
(2, 'Audiovisuales', 'A'),          -- Proyectores, Parlantes, Pantallas Smart
(3, 'Redes y Conectividad', 'A'),   -- Switches, Routers, Racks, Access Points
(4, 'Equipo de Laboratorio', 'A'),  -- Osciloscopios, Fuentes de Poder, Multímetros
(5, 'Mobiliario y Equipo', 'A'),    -- Escritorios, Sillas Ergonómicas, Estantes
(6, 'Línea Blanca y Clima', 'A'),   -- Aires Acondicionados, Dispensadores, Microondas
(7, 'Herramientas Eléctricas', 'A'),-- Taladros, Estaciones de Soldar, Esmeriles
(8, 'Equipo de Seguridad', 'A');    -- Grabadores DVR, Cámaras CCTV, Lectores Biométricos

-- Marcas
INSERT INTO marcas (id_marca, nombre_marca, estado) VALUES
(1, 'Lenovo', 'A'),
(2, 'Dell', 'A'),
(3, 'Epson', 'A');

-- Estado Activos 
INSERT INTO estado_activos (id_estado, nombre_estado, descripcion, estado) VALUES
(1, 'Alta', 'Registrado y operativo', 'A'),
(2, 'Mantenimiento', 'En revisión técnica', 'A'),
(3, 'Baja', 'Dado de baja definitivo', 'A');

-- Etiquetas RFID 
INSERT INTO etiquetas_rfid (id_etiqueta, codigo, estado) VALUES
(1, 'ITCA20260001', 'A'), (2, 'ITCA20260002', 'A'), (3, 'ITCA20260003', 'A'),
(4, 'ITCA20260004', 'A'), (5, 'ITCA20260005', 'A'), (6, 'ITCA20260006', 'A'),
(7, 'ITCA20260007', 'A'), (8, 'ITCA20260008', 'A'), (9, 'ITCA20260009', 'A'),
(10, 'ITCA20260010', 'A'), (11, 'ITCA20260011', 'A'), (12, 'ITCA20260012', 'A'),
(13, 'ITCA20260013', 'A'), (14, 'ITCA20260014', 'A'), (15, 'ITCA20260015', 'A'),
(16, 'ITCA20260016', 'A'), (17, 'ITCA20260017', 'A'), (18, 'ITCA20260018', 'A'),
(19, 'ITCA20260019', 'A'), (20, 'ITCA20260020', 'A'), (21, 'ITCA20260021', 'A'),
(22, 'ITCA20260022', 'A'), (23, 'ITCA20260023', 'A'), (24, 'ITCA20260024', 'A'),
(25, 'ITCA20260025', 'A'), (26, 'ITCA20260026', 'A'), (27, 'ITCA20260027', 'A'),
(28, 'ITCA20260028', 'A'), (29, 'ITCA20260029', 'A'), (30, 'ITCA20260030', 'A');

-- Responsables
INSERT INTO responsables (id, nombre, apellido, codigo_empleado, estado) VALUES
(1, 'Carlos', 'Mendoza', 'EMP-2026-001', 'A'),
(2, 'Ana Beatriz', 'Gómez', 'EMP-2026-002', 'A'),
(3, 'Roberto', 'Vásquez', 'EMP-2026-003', 'A');

-- Tipo Movimientos 
INSERT INTO tipo_movimientos (id, nombre_movimiento, estado) VALUES
(1, 'Alta de Activo', 'A'),
(2, 'Traslado', 'A'),
(3, 'En Mantenimiento', 'A'),
(4, 'Baja Administrativa', 'A');

-- Tipo Usuarios 
INSERT INTO tipo_usuarios 
(nombre_tipo, descripcion, estado, fecha_ingreso, usuario_ingreso, fecha_modifica, usuario_modifica )
VALUES
('Administrador' ,'Administrador del sistema', 'A', NOW(), 'SISTEMA', NOW() ,'SISTEMA'),
('Auditor' ,'Auditoria interna', 'A', NOW(), 'SISTEMA', NOW() ,'SISTEMA'),
('Contabilidad' ,'Contabilidad', 'A', NOW(), 'SISTEMA', NOW() ,'SISTEMA'),
('Bodeguero' ,'Encargado de bodega', 'A', NOW(), 'SISTEMA', NOW() ,'SISTEMA');

-- Laboratorios
INSERT INTO laboratorios (id_laboratorio, nombre_laboratorio, id_edificio, estado) VALUES
(1, 'Salón C-201', 1, 'A'),
(2, 'Salón C-202', 1, 'A'),
(3, 'Salón C-203', 2, 'A');

-- Modelos
INSERT INTO modelos (id_modelo, nombre_modelo, estado, id_marca) VALUES
(1, 'ThinkVision T24i', 'A', 1), -- Lenovo
(2, 'OptiPlex 7090', 'A', 2),    -- Dell
(3, 'PowerLite E20', 'A', 3);    -- Epson

-- Usuarios
INSERT INTO usuarios (id_usuario, nombre_usuario, correo, password, estado, fecha_ingreso, usuario_ingreso, fecha_modifica, usuario_modifica, id_tipo) VALUES
(1,'Nataly Cruz','nataly.cruz16@itca.edu.sv','$2y$12$KXC5jjk7TZtNTtSfGfLp9exKhmaLNdIMGWljSHtpTZfKuOfTi/l1e','A',NOW(), 'SISTEMA', NOW() ,'SISTEMA',2),
(2,'Jonatan Granados','jonatan.granados19@itca.edu.sv','$2y$12$6ffDRuMYUGTfh/6dpU9p..AQUkT/RdlZ5AluLYn5bdnHVGwMtCcjy','A',NOW(), 'SISTEMA', NOW() ,'SISTEMA',1),
(3,'Eduardo Gomez','alexander.gomez19@itca.edu.sv', '$2y$12$Boqva3pSIPcFeeZBCd0bh.NdRpcdKeiDtqtD0gyp2xszUBSEouOKG','A',NOW(), 'SISTEMA', NOW() ,'SISTEMA',3),
(4,'Julissa Martinez','julissa.martinez19@itca.edu.sv','$2y$12$lalmnWjTd/UhZhPM0W9i0eP2j7tI3.zKannaLHBQ7GQceocPhjPve','A',NOW(), 'SISTEMA', NOW() ,'SISTEMA',4);

-- Ubicaciones 
INSERT INTO ubicaciones (id_ubicacion, id_laboratorio, estado) VALUES
(1, 1, 'A'), (2, 1, 'A'), (3, 1, 'A'), (4, 1, 'A'), (5, 1, 'A'), (6, 1, 'A'), (7, 1, 'A'), (8, 1, 'A'), (9, 1, 'A'), (10, 1, 'A'),
(11, 2, 'A'), (12, 2, 'A'), (13, 2, 'A'), (14, 2, 'A'), (15, 2, 'A'), (16, 2, 'A'), (17, 2, 'A'), (18, 2, 'A'), (19, 2, 'A'), (20, 2, 'A'),
(21, 3, 'A'), (22, 3, 'A'), (23, 3, 'A'), (24, 3, 'A'), (25, 3, 'A'), (26, 3, 'A'), (27, 3, 'A'), (28, 3, 'A'), (29, 3, 'A'), (30, 3, 'A');

-- Inventarios 
INSERT INTO inventarios (id_inventario, fecha_inventario, id_usuario) VALUES
(1, '2026-06-15 09:00:00', 1),
(2, '2026-12-10 14:30:00', 1);

--  ACTIVOS
INSERT INTO activos (id_activo, nombre_activo, serie, valor_compra, fecha_compra, valor_actual, vida_util, depreciacion_anual, id_etiqueta, id_categoria, id_modelo, id_ubicacion, id_estado, id_responsable) VALUES
-- Cómputo 1: Activos del 1 al 10 -> Todos en id_estado = 1 (Alta)
(1, 'Computadora Dell OptiPlex', 'SER-001', 850.00, '2025-01-10', 700.00, 5, 170.00, 1, 1, 2, 1, 1, 1),
(2, 'Computadora Dell OptiPlex', 'SER-002', 850.00, '2025-01-10', 700.00, 5, 170.00, 2, 1, 2, 2, 1, 1),
(3, 'Computadora Dell OptiPlex', 'SER-003', 850.00, '2025-01-10', 700.00, 5, 170.00, 3, 1, 2, 3, 1, 1),
(4, 'Computadora Dell OptiPlex', 'SER-004', 850.00, '2025-01-10', 700.00, 5, 170.00, 4, 1, 2, 4, 1, 1),
(5, 'Computadora Dell OptiPlex', 'SER-005', 850.00, '2025-01-10', 700.00, 5, 170.00, 5, 1, 2, 5, 1, 1),
(6, 'Computadora Dell OptiPlex', 'SER-006', 850.00, '2025-01-10', 700.00, 5, 170.00, 6, 1, 2, 6, 1, 1),
(7, 'Computadora Dell OptiPlex', 'SER-007', 850.00, '2025-01-10', 700.00, 5, 170.00, 7, 1, 2, 7, 1, 1),
(8, 'Computadora Dell OptiPlex', 'SER-008', 850.00, '2025-01-10', 700.00, 5, 170.00, 8, 1, 2, 8, 1, 1),
(9, 'Computadora Dell OptiPlex', 'SER-009', 850.00, '2025-01-10', 700.00, 5, 170.00, 9, 1, 2, 9, 1, 1),
(10, 'Computadora Dell OptiPlex', 'SER-010', 850.00, '2025-01-10', 700.00, 5, 170.00, 10, 1, 2, 10, 1, 1),
(11, 'Monitor Lenovo ThinkVision', 'SER-011', 180.00, '2025-02-15', 150.00, 5, 36.00, 11, 1, 1, 11, 1, 2),
(12, 'Monitor Lenovo ThinkVision', 'SER-012', 180.00, '2025-02-15', 150.00, 5, 36.00, 12, 1, 1, 12, 1, 2),
(13, 'Monitor Lenovo ThinkVision', 'SER-013', 180.00, '2025-02-15', 150.00, 5, 36.00, 13, 1, 1, 13, 1, 2),
(14, 'Monitor Lenovo ThinkVision', 'SER-014', 180.00, '2025-02-15', 150.00, 5, 36.00, 14, 1, 1, 14, 1, 2),
(15, 'Monitor Lenovo ThinkVision', 'SER-015', 180.00, '2025-02-15', 140.00, 5, 36.00, 15, 1, 1, 15, 2, 2), 
(16, 'Monitor Lenovo ThinkVision', 'SER-016', 180.00, '2025-02-15', 150.00, 5, 36.00, 16, 1, 1, 16, 1, 2),
(17, 'Monitor Lenovo ThinkVision', 'SER-017', 180.00, '2025-02-15', 150.00, 5, 36.00, 17, 1, 1, 17, 1, 2),
(18, 'Monitor Lenovo ThinkVision', 'SER-018', 180.00, '2025-02-15', 150.00, 5, 36.00, 18, 1, 1, 18, 1, 2),
(19, 'Monitor Lenovo ThinkVision', 'SER-019', 180.00, '2025-02-15', 150.00, 5, 36.00, 19, 1, 1, 19, 1, 2),
(20, 'Monitor Lenovo ThinkVision', 'SER-020', 180.00, '2025-02-15', 150.00, 5, 36.00, 20, 1, 1, 20, 1, 2),
(21, 'Proyector Epson PowerLite', 'SER-021', 600.00, '2025-05-20', 500.00, 5, 120.00, 21, 2, 3, 21, 1, 3),
(22, 'Proyector Epson PowerLite', 'SER-022', 600.00, '2025-05-20', 500.00, 5, 120.00, 22, 2, 3, 22, 1, 3),
(23, 'Proyector Epson PowerLite', 'SER-023', 600.00, '2025-05-20', 500.00, 5, 120.00, 23, 2, 3, 23, 1, 3),
(24, 'Proyector Epson PowerLite', 'SER-024', 600.00, '2025-05-20', 500.00, 5, 120.00, 24, 2, 3, 24, 1, 3),
(25, 'Proyector Epson PowerLite', 'SER-025', 600.00, '2025-05-20', 450.00, 5, 120.00, 25, 2, 3, 25, 2, 3), 
(26, 'Proyector Epson PowerLite', 'SER-026', 600.00, '2025-05-20', 500.00, 5, 120.00, 26, 2, 3, 26, 1, 3),
(27, 'Proyector Epson PowerLite', 'SER-027', 600.00, '2025-05-20', 500.00, 5, 120.00, 27, 2, 3, 27, 1, 3),
(28, 'Proyector Epson PowerLite', 'SER-028', 600.00, '2025-05-20', 500.00, 5, 120.00, 28, 2, 3, 28, 1, 3),
(29, 'Proyector Epson PowerLite', 'SER-029', 600.00, '2025-05-20', 200.00, 5, 120.00, 29, 2, 3, 29, 3, 3), 
(30, 'Proyector Epson PowerLite', 'SER-030', 600.00, '2025-05-20', 500.00, 5, 120.00, 30, 2, 3, 30, 1, 3);

--  Movimientos
INSERT INTO movimientos (comentarios, tipo_movimiento, fecha_movimiento, id_usuario, id_activo, id_ubicacion) VALUES
-- Altas de 2025 (tipo_movimiento = 1)
('Ingreso por compra', 1, '2025-01-15 08:30:00', 1, 1, 1), ('Ingreso por compra', 1, '2025-01-15 08:32:00', 1, 2, 2),
('Ingreso por compra', 1, '2025-01-15 08:35:00', 1, 3, 3), ('Ingreso por compra', 1, '2025-01-15 08:37:00', 1, 4, 4),
('Ingreso por compra', 1, '2025-01-15 08:40:00', 1, 5, 5), ('Ingreso por compra', 1, '2025-01-15 08:42:00', 1, 6, 6),
('Ingreso por compra', 1, '2025-01-15 08:45:00', 1, 7, 7), ('Ingreso por compra', 1, '2025-01-15 08:47:00', 1, 8, 8),
('Ingreso por compra', 1, '2025-01-15 08:50:00', 1, 9, 9), ('Ingreso por compra', 1, '2025-01-15 08:52:00', 1, 10, 10),
('Ingreso por compra', 1, '2025-02-20 10:00:00', 1, 11, 11), ('Ingreso por compra', 1, '2025-02-20 10:02:00', 1, 12, 12),
('Ingreso por compra', 1, '2025-02-20 10:05:00', 1, 13, 13), ('Ingreso por compra', 1, '2025-02-20 10:07:00', 1, 14, 14),
('Ingreso por compra', 1, '2025-02-20 10:10:00', 1, 15, 15), ('Ingreso por compra', 1, '2025-02-20 10:12:00', 1, 16, 16),
('Ingreso por compra', 1, '2025-02-20 10:15:00', 1, 17, 17), ('Ingreso por compra', 1, '2025-02-20 10:17:00', 1, 18, 18),
('Ingreso por compra', 1, '2025-02-20 10:20:00', 1, 19, 19), ('Ingreso por compra', 1, '2025-02-20 10:22:00', 1, 20, 20),
('Compra laboratorios', 1, '2025-05-22 14:00:00', 1, 21, 21), ('Compra laboratorios', 1, '2025-05-22 14:02:00', 1, 22, 22),
('Compra laboratorios', 1, '2025-05-22 14:05:00', 1, 23, 23), ('Compra laboratorios', 1, '2025-05-22 14:07:00', 1, 24, 24),
('Compra laboratorios', 1, '2025-05-22 14:10:00', 1, 25, 25), ('Compra laboratorios', 1, '2025-05-22 14:12:00', 1, 26, 26),
('Compra laboratorios', 1, '2025-05-22 14:15:00', 1, 27, 27), ('Compra laboratorios', 1, '2025-05-22 14:17:00', 1, 28, 28),
('Compra laboratorios', 1, '2025-05-22 14:20:00', 1, 29, 29), ('Compra laboratorios', 1, '2025-05-22 14:22:00', 1, 30, 30),
('Traslado por préstamo de aula', 2, '2026-03-05 09:00:00', 1, 4, 14), 
('Retorno a ubicación original', 2, '2026-03-10 16:00:00', 1, 4, 4),
('Traslado por readecuación', 2, '2026-04-12 11:30:00', 1, 5, 15),
('Reporte de parpadeo continuo', 3, '2026-05-02 08:00:00', 1, 15, 15),
('Mantenimiento preventivo de óptica', 3, '2026-05-15 13:00:00', 1, 25, 25),
('Fallo crítico de tarjeta madre. Desecho.', 4, '2026-06-01 10:20:00', 1, 29, 29);

-- Detalle Inventarios 
INSERT INTO detalle_inventarios (observaciones, cantidad, id_inventario, id_activo) VALUES
('Verificado', 1, 1, 1), ('Verificado', 1, 1, 2), ('Verificado', 1, 1, 3), ('Verificado', 1, 1, 4),
('Verificado', 1, 1, 5), ('Verificado', 1, 1, 6), ('Verificado', 1, 1, 7), ('Verificado', 1, 1, 8),
('Verificado', 1, 1, 11), ('Verificado', 1, 1, 12), ('Verificado', 1, 1, 13), ('Verificado', 1, 1, 14),
('Filtros sucios', 1, 1, 15), ('Verificado', 1, 1, 16), ('Verificado', 1, 1, 17), ('Verificado', 1, 1, 18),
('Operativo', 1, 1, 21), ('Operativo', 1, 1, 22), ('Operativo', 1, 1, 23), ('Operativo', 1, 1, 24),
('Lente rayado', 1, 1, 25), ('Operativo', 1, 1, 26), ('Operativo', 1, 1, 27), ('Operativo', 1, 1, 28),
('Inoperativo', 1, 1, 29), ('Operativo', 1, 1, 30);
INSERT INTO detalle_inventarios (observaciones, cantidad, id_inventario, id_activo) VALUES
('OK', 1, 2, 1), ('OK', 1, 2, 2), ('OK', 1, 2, 3), ('OK', 1, 2, 4), ('OK', 1, 2, 5),
('OK', 1, 2, 6), ('OK', 1, 2, 7), ('OK', 1, 2, 8), ('Reaparece', 1, 2, 9), ('Reaparece', 1, 2, 10),
('OK', 1, 2, 11), ('OK', 1, 2, 12), ('OK', 1, 2, 13), ('OK', 1, 2, 14), ('Limpio', 1, 2, 15),
('OK', 1, 2, 16), ('OK', 1, 2, 17), ('OK', 1, 2, 18), ('Reaparece', 1, 2, 19), ('Reaparece', 1, 2, 20),
('Operativo', 1, 2, 21), ('Operativo', 1, 2, 22), ('Operativo', 1, 2, 23), ('Operativo', 1, 2, 24),
('Operativo', 1, 2, 26), ('Operativo', 1, 2, 27), ('Operativo', 1, 2, 28), ('Para desecho', 1, 2, 29),
('Operativo', 1, 2, 30);
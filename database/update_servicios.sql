-- Script de actualización de servicios
-- Ejecutar este script para actualizar los nombres de servicios existentes

UPDATE `servicios` SET `nombre` = 'Corte Clásico + Barba' WHERE `nombre` = 'Corte y Barba';
UPDATE `servicios` SET `nombre` = 'Perfilado de Barba' WHERE `nombre` = 'Asesoría' OR `nombre` = 'Asesoría de Imagen';

-- Verificar que los servicios estén en el orden correcto
-- Si es necesario, ajustar los IDs para que queden: 1=Corte Clásico, 2=Corte Clásico + Barba, 3=Perfilado de Barba





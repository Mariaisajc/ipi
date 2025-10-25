-- Eliminar FK incorrecta
ALTER TABLE business_areas
DROP FOREIGN KEY business_areas_ibfk_2;

-- Crear FK correcta
ALTER TABLE business_areas
ADD CONSTRAINT business_areas_created_by_fk
FOREIGN KEY (created_by) 
REFERENCES users(id)
ON DELETE SET NULL
ON UPDATE CASCADE;
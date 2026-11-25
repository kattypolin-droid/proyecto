-- Migración puntual: agrega la tabla histórica de compras a una instalación existente de db_bata.
-- Ejecutar solo después de crear la base y la tabla de calzados.
--
-- Motivo de esta migración:
-- Permite instalar compras sin reemplazar db_bata ni ejecutar toda la carga inicial.
-- CREATE TABLE IF NOT EXISTS evita romper una instalación donde la tabla ya existe.
-- ON DELETE SET NULL conserva el comprobante aunque el producto original se borre.
USE db_bata;

CREATE TABLE IF NOT EXISTS compras (
    id INT PRIMARY KEY AUTO_INCREMENT,
    calzado_id INT NULL,
    modelo VARCHAR(100) NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    precio_unitario DECIMAL(10,2) NOT NULL,
    fecha_compra DATETIME NOT NULL,
    CONSTRAINT fk_compra_calzado
        FOREIGN KEY (calzado_id) REFERENCES calzados(id)
        ON DELETE SET NULL
) ENGINE=InnoDB;

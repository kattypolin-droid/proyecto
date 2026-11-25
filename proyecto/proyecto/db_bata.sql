-- Crea el contenedor solo si no existe y selecciona el conjunto utf8mb4 completo.
CREATE DATABASE IF NOT EXISTS db_bata CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_bata;

-- Usuarios autorizados para iniciar sesión; la contraseña se almacena cifrada.
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

-- Marcas disponibles para clasificar cada calzado.
CREATE TABLE marcas (
    id_marca INT PRIMARY KEY AUTO_INCREMENT,
    nombre_marca VARCHAR(80) NOT NULL
) ENGINE=InnoDB;

-- Productos del catálogo y relación obligatoria con su marca.
CREATE TABLE calzados (
    id INT PRIMARY KEY AUTO_INCREMENT,
    modelo VARCHAR(100) NOT NULL,
    talla INT NOT NULL,
    color VARCHAR(40) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    id_marca INT NOT NULL,
    foto VARCHAR(100) NULL,
    -- Relación muchos-a-uno: id_marca identifica la marca propietaria del calzado.
    -- ON DELETE CASCADE elimina también sus calzados si se elimina la marca.
    CONSTRAINT fk_calzado_marca
        FOREIGN KEY (id_marca) REFERENCES marcas(id_marca)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Fotos adicionales por color, con soporte opcional para imágenes externas y atribución.
CREATE TABLE calzado_imagenes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    calzado_id INT NOT NULL,
    foto VARCHAR(255) NULL,
    url_imagen VARCHAR(1000) NULL,
    color VARCHAR(60) NOT NULL,
    fuente_url VARCHAR(1000) NULL,
    licencia VARCHAR(120) NULL,
    atribucion VARCHAR(500) NULL,
    orden INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_calzado_imagen_calzado
        FOREIGN KEY (calzado_id) REFERENCES calzados(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- Registro histórico de compras; conserva modelo y precio aunque el producto se elimine.
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

-- Datos iniciales de acceso, marcas y productos demostrativos.
INSERT INTO usuarios (usuario, password) VALUES
    ('admin@bata.pe', '$2y$10$qLmxTK4QKSrwKC9dC6bIgOib7dc2wMGwirsj6O53X29a6jk3bzkK2');

INSERT INTO marcas (nombre_marca) VALUES
    ('Bata Comfit'),
    ('Power'),
    ('Weinbrenner'),
    ('Adidas'),
    ('Genérica'),
    ('Escolar'),
    ('Moda'),
    ('Nike'),
    ('New Balance');

INSERT INTO calzados (modelo, talla, color, precio, id_marca, foto) VALUES
    ('Zapatilla Urbana Run', 41, 'gris y blanco', 189.00, 2, 'uploads/03-deportivo-blanco-azul.webp'),
    ('Zapatilla urbana bicolor', 39, 'negro y blanco', 129.90, 5, 'uploads/01-urbana-negra-blanca.webp'),
    ('Zapato escolar Kaida de cuero', 39, 'blanco', 159.00, 6, 'uploads/02-kaida-escolar-blanca.webp'),
    ('Adidas Duramo negro', 40, 'negro y blanco', 279.00, 4, 'uploads/04-adidas-racer-negro.webp'),
    ('Zapatilla Fashion rosa', 37, 'rosa', 119.90, 7, 'uploads/05-fashion-rosa.webp'),
    ('Adidas Campus 00s negro - lateral', 41, 'negro y blanco', 329.00, 4, 'uploads/06-campus-negro.webp'),
    ('Adidas Campus 00s negro - par', 40, 'negro y blanco', 329.00, 4, 'uploads/07-campus-par-negro.webp'),
    ('Nike Air Force 1 beige', 38, 'beige y blanco', 449.00, 8, 'uploads/08-nike-air-marron.webp'),
    ('New Balance 530 blanco', 40, 'blanco y gris', 399.00, 9, 'uploads/09-new-balance-blanco.webp');

INSERT INTO calzado_imagenes
    (calzado_id, url_imagen, color, fuente_url, licencia, atribucion, orden)
SELECT id,
    'https://thumb.wikimedia.org/wikipedia/commons/thumb/7/7e/Nike_air_Force_1_white_on_white.jpg/960px-Nike_air_Force_1_white_on_white.jpg?utm_source=commons.wikimedia.org&utm_campaign=imageinfo&utm_content=thumbnail',
    'Blanco',
    'https://commons.wikimedia.org/w/index.php?curid=81479805',
    'CC BY-SA 4.0',
    'Nike air Force 1 white on white, HI 622, CC BY-SA 4.0',
    1
FROM calzados
WHERE modelo = 'Nike Air Force 1 beige'
  AND NOT EXISTS (
      SELECT 1 FROM calzado_imagenes
      WHERE calzado_id = calzados.id
        AND color = 'Blanco'
  );

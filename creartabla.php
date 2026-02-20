<?php
require "config/connexio.php"; // usamos tu conexión existente

try {

$sql = <<<SQL

-- Script extenso para la base `e-commerce` (temática videojuegos) - CORREGIDO
CREATE DATABASE IF NOT EXISTS `e-commerce` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
USE `e-commerce`;

-- Géneros
CREATE TABLE IF NOT EXISTS `genres` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Plataformas
CREATE TABLE IF NOT EXISTS `platforms` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Desarrolladores
CREATE TABLE IF NOT EXISTS `developers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `country` VARCHAR(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Publicadoras
CREATE TABLE IF NOT EXISTS `publishers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `country` VARCHAR(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tags
CREATE TABLE IF NOT EXISTS `tags` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Juegos
CREATE TABLE IF NOT EXISTS `games` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL,
  `genre_id` INT UNSIGNED DEFAULT NULL,
  `platform_id` INT UNSIGNED DEFAULT NULL,
  `developer_id` INT UNSIGNED DEFAULT NULL,
  `publisher_id` INT UNSIGNED DEFAULT NULL,
  `price` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `release_date` DATE DEFAULT NULL,
  `description` TEXT,
  `stock` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_slug` (`slug`),
  KEY `idx_genre` (`genre_id`),
  KEY `idx_platform` (`platform_id`),
  CONSTRAINT `fk_games_genre` FOREIGN KEY (`genre_id`) REFERENCES `genres`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_games_platform` FOREIGN KEY (`platform_id`) REFERENCES `platforms`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_games_developer` FOREIGN KEY (`developer_id`) REFERENCES `developers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_games_publisher` FOREIGN KEY (`publisher_id`) REFERENCES `publishers`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Relación juegos <-> tags
CREATE TABLE IF NOT EXISTS `game_tags` (
  `game_id` INT UNSIGNED NOT NULL,
  `tag_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`game_id`,`tag_id`),
  CONSTRAINT `fk_gt_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_gt_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Imágenes / galería de juegos
CREATE TABLE IF NOT EXISTS `game_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_id` INT UNSIGNED NOT NULL,
  `url` VARCHAR(512) NOT NULL,
  `alt` VARCHAR(255) DEFAULT NULL,
  `is_main` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_gi_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews públicas (opcional, sin login)
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_id` INT UNSIGNED NOT NULL,
  `author_name` VARCHAR(150) DEFAULT 'Anónimo',
  `rating` TINYINT UNSIGNED NOT NULL DEFAULT 5,
  `comment` TEXT,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_reviews_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pedidos (sin necesidad de usuarios: guardamos nombre/email)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_name` VARCHAR(255) DEFAULT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
  `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Items de pedido
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT UNSIGNED NOT NULL,
  `game_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `unit_price` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_orderitems_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_orderitems_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Cupones / descuentos (opcional)
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL,
  `discount_percent` TINYINT UNSIGNED DEFAULT NULL,
  `discount_amount` DECIMAL(8,2) DEFAULT NULL,
  `valid_from` DATE DEFAULT NULL,
  `valid_to` DATE DEFAULT NULL,
  `max_uses` INT UNSIGNED DEFAULT NULL,
  `used` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uidx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Historial de inventario
CREATE TABLE IF NOT EXISTS `inventory_history` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `game_id` INT UNSIGNED NOT NULL,
  `change` INT NOT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_inv_game` FOREIGN KEY (`game_id`) REFERENCES `games`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Datos de ejemplo --------------------------------------------------------

INSERT INTO `genres` (`name`) VALUES
('Acción'), ('Aventura'), ('Deportes'), ('Rol'), ('Estrategia'), ('Simulación'), ('Carreras'), ('Indie');

INSERT INTO `platforms` (`name`) VALUES
('PC'), ('PlayStation 5'), ('PlayStation 4'), ('Xbox Series X'), ('Xbox One'), ('Nintendo Switch'), ('Mobile');

INSERT INTO `developers` (`name`, `country`) VALUES
('NovaWorks', 'EEUU'), ('Blue Orchard', 'Canadá'), ('Red Pixel Studio', 'España'), ('Aurora Games', 'Reino Unido'), ('GreenForge', 'Alemania');

INSERT INTO `publishers` (`name`, `country`) VALUES
('HyperPlay', 'EEUU'), ('SilverGate', 'Reino Unido'), ('CrystalSoft', 'Japón');

-- Tags (ahora con 8 entradas, CORRECCIÓN)
INSERT INTO `tags` (`name`) VALUES
('Multijugador'), ('Cooperativo'), ('Un jugador'), ('Open world'), ('Retro'), ('Competitivo'), ('Casual'), ('Exploración');

-- Juegos de ejemplo (asegúrate de que los IDs coinciden con FK)
INSERT INTO `games` (`title`,`slug`,`genre_id`,`platform_id`,`developer_id`,`publisher_id`,`price`,`release_date`,`description`,`stock`) VALUES
('BattleQuest VX','battlequest-vx',1,1,1,1,49.99,'2021-11-15','Shooter futurista con modo multijugador competitivo y ranking online.',25),
('Mystic Trails','mystic-trails',2,6,2,2,39.99,'2020-06-10','Aventura narrativa en mundo abierto con puzzles y decisiones morales.',15),
('Futbol Pro 2023','futbol-pro-2023',3,2,3,3,59.99,'2023-09-20','Simulador de fútbol con equipos licenciados y modo carrera profundo.',40),
('Elder Sagas','elder-sagas',4,1,4,1,69.99,'2019-03-05','RPG épico con sistema de progresión, crafting y mundos interconectados.',10),
('Empire Command','empire-command',5,4,5,2,29.99,'2018-08-30','Estrategia en tiempo real con campañas y multijugador competitivo.',30),
('Pixel Kart','pixel-kart',7,6,3,2,19.99,'2022-02-14','Carreras arcade para todos los públicos, con modo local y online.',50),
('Skyforge Simulator','skyforge-sim',6,1,5,3,24.99,'2021-05-11','Simulación relajada de construcción y gestión de islas flotantes.',20),
('Retro Dungeon','retro-dungeon',8,1,3,1,14.99,'2017-10-01','Roguelike retro con estética pixel art y partidas rápidas.',60),
('Arena Legends','arena-legends',1,1,1,2,39.99,'2022-07-18','MOBA/arena competitivo con personajes únicos y e-sports support.',35),
('Island Adventure','island-adventure',2,6,2,3,34.99,'2020-12-05','Aventura cooperativa en isla tropical con misiones y minijuegos.',12);

-- Asignar tags a juegos (corregido: tag_id 8 existe ahora)
INSERT INTO `game_tags` (`game_id`,`tag_id`) VALUES
(1,1),(1,6),
(2,3),(2,4),
(3,3),(3,6),
(4,3),(4,4),
(5,5),(5,6),
(6,7),(6,3),
(7,3),(7,5),
(8,8),(8,3),
(9,1),(9,6),
(10,2),(10,3);

-- Imágenes de ejemplo (URLs ilustrativas)
INSERT INTO `game_images` (`game_id`,`url`,`alt`,`is_main`) VALUES
(1,'/images/battlequest-1.jpg','BattleQuest portada',1),
(1,'/images/battlequest-2.jpg','BattleQuest screenshot',0),
(2,'/images/mystictrails-1.jpg','Mystic Trails portada',1),
(3,'/images/futbolpro-1.jpg','Futbol Pro portada',1),
(4,'/images/eldersagas-1.jpg','Elder Sagas portada',1),
(6,'/images/pixelkart-1.jpg','Pixel Kart portada',1);

-- Reviews ejemplo (sin login)
INSERT INTO `reviews` (`game_id`,`author_name`,`rating`,`comment`) VALUES
(1,'Carlos',9,'Muy competitivo y emocionante.'),(2,'Laura',8,'Historia preciosa y bien escrita.'),(8,'PlayerOne',7,'Divertido en pequeñas sesiones.');

-- Inventario inicial
INSERT INTO `inventory_history` (`game_id`,`change`,`note`) VALUES
(1,25,'Inicial'),(2,15,'Inicial'),(3,40,'Inicial'),(4,10,'Inicial'),(6,50,'Inicial'),(8,60,'Inicial');

-- Cupones de ejemplo
INSERT INTO `coupons` (`code`,`discount_percent`,`valid_from`,`valid_to`,`max_uses`) VALUES
('WELCOME10',10,'2024-01-01','2026-12-31',100),
('SPRING5',5,'2025-03-01','2025-05-31',50);

-- Pedidos de ejemplo (sin usuario)
INSERT INTO `orders` (`customer_name`,`customer_email`,`status`,`total`) VALUES
('Cliente Demo','demo@example.com','completed',69.98);

-- Insertar items para el pedido anterior (usa el order_id = 1)
INSERT INTO `order_items` (`order_id`,`game_id`,`quantity`,`unit_price`) VALUES
(1,1,1,49.99),(1,6,1,19.99);

-- FIN del script
SQL;

    $pdo->exec($sql);

    echo "✅ Base de datos creada correctamente bro 😎🔥";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>

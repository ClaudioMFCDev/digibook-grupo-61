-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2026 a las 03:08:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `digibook2`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `crearNuevoLibro` (IN `p_titulo` VARCHAR(255), IN `p_precio` DECIMAL(10,2), IN `p_idEditorial` INT, IN `p_sinopsis` TEXT, IN `p_paginas` INT, IN `p_idAutor` INT, IN `p_idGenero` INT, IN `p_img` VARCHAR(100), IN `p_fecha` DATE, OUT `p_resultado` INT, OUT `p_msj_error` VARCHAR(255))   BEGIN
    -- Manejador de errores
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        GET DIAGNOSTICS CONDITION 1 @text = MESSAGE_TEXT;
        ROLLBACK;
        SET p_resultado = 0;
        SET p_msj_error = CONCAT('Error SQL: ', @text);
    END;

    START TRANSACTION;

    -- 1. Insertar en ARTICULO (Sin fecha)
    INSERT INTO articulo (titulo, precio, idEditorial, sinopsis, paginas, idGenero, img)
    VALUES (p_titulo, p_precio, p_idEditorial, p_sinopsis, p_paginas, p_idGenero, p_img);

    -- Recuperar ID
    SET @idNuevoLibro = LAST_INSERT_ID();

    -- 2. Insertar en ARTICULOAUTOR (Con fecha)
    -- Aquí usamos 'fechaPublicacion' tal cual me confirmaste
    INSERT INTO articuloautor (idLibro, idAutor, fechaPublicacion)
    VALUES (@idNuevoLibro, p_idAutor, p_fecha);

    COMMIT;
    
    SET p_resultado = 1;
    SET p_msj_error = 'Libro creado exitosamente.';

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getAutores` ()   BEGIN
    SELECT idAutor, nombre, apellido FROM Autor;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getEditoriales` ()   BEGIN
    SELECT idEditorial, nombre, email FROM editorial;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `getGeneros` ()   BEGIN
    SELECT idGenero, nombre, descripcion FROM genero;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtenerLibroPorId` (IN `p_idLibro` INT)   BEGIN
    SELECT 
        l.idLibro as 'id',
        l.titulo AS 'Título',
        l.precio AS 'Precio',
        g.nombre AS 'Género',
        GROUP_CONCAT(CONCAT(a.nombre, ' ', a.apellido) SEPARATOR ', ') AS 'Autores',
        e.nombre AS 'Editorial'
    FROM 
        articulo l
    JOIN 
        editorial e ON l.idEditorial = e.idEditorial
    JOIN 
        genero g ON l.idGenero = g.idGenero
    JOIN 
        articuloautor la ON l.idLibro = la.idLibro
    JOIN 
        autor a ON la.idAutor = a.idAutor
    WHERE l.idLibro=p_idLibro
    GROUP BY 
        l.idLibro, l.titulo, l.precio, g.nombre, e.nombre;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `obtenerLibros` ()   BEGIN
    SELECT 
        l.idLibro as 'id',
        l.titulo AS 'Título',
        l.img,   -- <--- ¡AQUÍ ESTÁ LA CLAVE! Agregamos la columna de la imagen
        l.precio AS 'Precio',
        g.nombre AS 'Género',
        GROUP_CONCAT(CONCAT(a.nombre, ' ', a.apellido) SEPARATOR ', ') AS 'Autores',
        e.nombre AS 'Editorial'
    FROM 
        articulo l
    JOIN 
        editorial e ON l.idEditorial = e.idEditorial
    JOIN 
        genero g ON l.idGenero = g.idGenero
    JOIN 
        articuloautor la ON l.idLibro = la.idLibro
    JOIN 
        autor a ON la.idAutor = a.idAutor
    GROUP BY 
        l.idLibro; -- Importante: Agrupa por libro para que los autores se junten
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `registrar_compra_con_detalles` (IN `p_total` DECIMAL(10,2), IN `p_fecha` DATE, IN `p_dni` VARCHAR(15), IN `p_detalles` JSON, OUT `p_resultado` VARCHAR(255), OUT `p_idCompra` INT)   BEGIN
    DECLARE v_idCompra INT;
    DECLARE i INT;
    DECLARE n INT;

    DECLARE v_unidades INT;
    DECLARE v_idLibro INT;
    DECLARE v_precio INT;

    -- Manejador de error general
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_resultado = CONCAT('Error: fallo interno en la base de datos (verifica datos, claves foráneas o integridad referencial).');
        SET p_idCompra = -1;
    END;

    -- Bloque etiquetado
    compra_loop: BEGIN
        SET i = 0;

        -- Validación del parámetro JSON
        IF p_detalles IS NULL THEN
            SET p_resultado = 'Error: los detalles de la compra no pueden ser NULL.';
            SET p_idCompra = -1;
            LEAVE compra_loop;
        END IF;

        SET n = JSON_LENGTH(p_detalles);
        IF n = 0 OR n IS NULL THEN
            SET p_resultado = 'Error: los detalles de la compra están vacíos o mal formateados.';
            SET p_idCompra = -1;
            LEAVE compra_loop;
        END IF;

        -- Iniciar transacción
        START TRANSACTION;

        -- Insertar compra
        INSERT INTO compra (total, fecha, dni)
        VALUES (p_total, p_fecha, p_dni);

        SET v_idCompra = LAST_INSERT_ID();

        -- Procesar los detalles
        WHILE i < n DO
            SET v_unidades = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles, CONCAT('$[', i, '].qty'))) AS UNSIGNED);
            IF v_unidades IS NULL OR v_unidades <= 0 THEN
                ROLLBACK;
                SET p_resultado = CONCAT('Error: unidades inválidas en el detalle #', i + 1, '. Valor proporcionado: ', v_unidades);
                SET p_idCompra = -1;
                LEAVE compra_loop;
            END IF;

SET v_precio = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles, CONCAT('$[', i, '].precio'))) AS UNSIGNED);
            IF v_precio IS NULL OR v_precio <= 0 THEN
                ROLLBACK;
                SET p_resultado = CONCAT('Error: precio inválido en el detalle #', i + 1, '. Valor proporcionado: ', v_precio);
                SET p_idCompra = -1;
                LEAVE compra_loop;
            END IF;

            SET v_idLibro = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_detalles, CONCAT('$[', i, '].id'))) AS UNSIGNED);
            IF v_idLibro IS NULL OR v_idLibro <= 0 THEN
                ROLLBACK;
                SET p_resultado = CONCAT('Error: idLibro inválido en el detalle #', i + 1, '. Valor proporcionado: ', v_idLibro);
                SET p_idCompra = -1;
                LEAVE compra_loop;
            END IF;

            -- Validar existencia de articulo
            IF NOT EXISTS (SELECT 1 FROM articulo WHERE idLibro = v_idLibro) THEN
                ROLLBACK;
                SET p_resultado = CONCAT('Error: el articulo con id ', v_idLibro, ' no existe en la base de datos.');
                SET p_idCompra = -1;
                LEAVE compra_loop;
            END IF;

            -- Insertar detalle
            INSERT INTO detallescompra (unidades, precio, idLibro, idCompra)
            VALUES (v_unidades,v_precio, v_idLibro, v_idCompra);

            SET i = i + 1;
        END WHILE;

        COMMIT;

        SET p_resultado = 'La compra fue registrada correctamente.';
        SET p_idCompra = v_idCompra;

    END compra_loop;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_buscar_articulos_filtrados` (IN `p_titulo` VARCHAR(50), IN `p_idGenero` INT, IN `p_idAutor` INT, IN `p_precioMin` DOUBLE(10,2), IN `p_precioMax` DOUBLE(10,2))   BEGIN
    SELECT 
        a.idLibro, 
        a.titulo, 
        a.precio, 
        a.img, 
        g.nombre AS nombre_genero,
        GROUP_CONCAT(aut.nombre SEPARATOR ', ') AS nombres_autores
    FROM articulo a
    INNER JOIN genero g ON a.idGenero = g.idGenero
    -- Acá estaba el error. Cambiado a aa.idLibro para que coincida con tu tabla
    LEFT JOIN articuloautor aa ON a.idLibro = aa.idLibro 
    LEFT JOIN autor aut ON aa.idAutor = aut.idAutor
    WHERE a.activo = 1
      AND (p_titulo IS NULL OR a.titulo LIKE CONCAT('%', p_titulo, '%'))
      AND (p_idGenero IS NULL OR a.idGenero = p_idGenero)
      AND (p_idAutor IS NULL OR aa.idAutor = p_idAutor)
      AND (p_precioMin IS NULL OR a.precio >= p_precioMin)
      AND (p_precioMax IS NULL OR a.precio <= p_precioMax)
    GROUP BY a.idLibro;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_obtener_reporte_comercial` (IN `p_fecha_desde` DATE, IN `p_fecha_hasta` DATE)   BEGIN
    -- RESULT SET 1: Métricas Globales de Venta
    SELECT 
        COUNT(c.idCompra) AS cantidadVentas,
        COALESCE(SUM(c.total), 0.00) AS totalIngresos
    FROM compra c
    WHERE c.fecha BETWEEN p_fecha_desde AND p_fecha_hasta;

    -- RESULT SET 2: Demografía Dominante del USUARIO (Masculino/Femenino)
    SELECT 
        COALESCE(u.genero, 'Sin Datos') AS demografiaClientes
    FROM compra c
    JOIN usuario u ON c.dni = u.dni
    WHERE c.fecha BETWEEN p_fecha_desde AND p_fecha_hasta
    GROUP BY u.genero
    ORDER BY COUNT(c.idCompra) DESC
    LIMIT 1;

    -- RESULT SET 3: Top Géneros LITERARIOS de Libros más vendidos
    SELECT 
        g.nombre AS generoLibro
    FROM compra c
    JOIN detallescompra dc ON c.idCompra = dc.idCompra
    JOIN articulo a ON dc.idLibro = a.idLibro
    JOIN genero g ON a.idGenero = g.idGenero
    WHERE c.fecha BETWEEN p_fecha_desde AND p_fecha_hasta
    GROUP BY g.idGenero, g.nombre
    ORDER BY COUNT(dc.idDetalle) DESC
    LIMIT 3;

    -- RESULT SET 4: Top 3 de Libros más Vendidos individuales
    SELECT 
        a.titulo,
        COUNT(dc.idDetalle) AS unidadesVendidas
    FROM detallescompra dc
    JOIN compra c ON dc.idCompra = c.idCompra
    JOIN articulo a ON dc.idLibro = a.idLibro
    WHERE c.fecha BETWEEN p_fecha_desde AND p_fecha_hasta
    GROUP BY a.idLibro, a.titulo
    ORDER BY unidadesVendidas DESC
    LIMIT 3;

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulo`
--

CREATE TABLE `articulo` (
  `idLibro` int(10) NOT NULL,
  `titulo` varchar(50) NOT NULL,
  `precio` double(10,2) UNSIGNED NOT NULL,
  `paginas` int(10) NOT NULL,
  `sinopsis` varchar(250) NOT NULL,
  `img` varchar(100) DEFAULT NULL,
  `activo` int(1) NOT NULL DEFAULT 1,
  `idGenero` int(10) NOT NULL,
  `idEditorial` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `articulo`
--

INSERT INTO `articulo` (`idLibro`, `titulo`, `precio`, `paginas`, `sinopsis`, `img`, `activo`, `idGenero`, `idEditorial`) VALUES
(52, 'El programador pragmático', 4500.00, 350, 'Buenas prácticas de programación.', 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?w=400', 1, 1, 1),
(53, 'Clean Code', 5200.00, 464, 'Guía para escribir código limpio y mantenible.', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?w=400', 1, 1, 1),
(54, 'Patrones de Diseño MVC', 3800.00, 395, 'Soluciones en el diseño de software.', 'https://images.unsplash.com/photo-1629654297299-c8506221ca97?w=400', 1, 1, 1),
(55, 'Harry Potter y la Piedra Filosofal', 3200.50, 400, 'El inicio de la saga del joven mago.', 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?w=400', 1, 2, 2),
(56, 'Harry Potter y el prisionero de Azkaban', 3500.00, 450, 'Tercer libro de la saga.', 'https://images.unsplash.com/photo-1506880018603-83d5b814b5a6?w=400', 1, 2, 2),
(57, 'Cien años de soledad', 2900.00, 450, 'La historia de la familia Buendía en Macondo.', 'https://images.unsplash.com/photo-1532012197267-da84d127e765?w=400', 1, 2, 2),
(58, 'It (Eso)', 6500.00, 1100, 'Una entidad maligna aterroriza al pueblo de Derry.', 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400', 1, 3, 3),
(59, 'El Resplandor', 4100.00, 600, 'Un escritor enloquece en un hotel aislado.', 'https://images.unsplash.com/photo-1589829545856-d10d557cf95f?w=400', 1, 3, 3),
(60, '1984', 1500.00, 328, 'Una novela distópica sobre un estado totalitario.', 'https://images.unsplash.com/photo-1495640388908-05fa85288e61?w=400', 1, 4, 2),
(61, 'Fundación', 2100.00, 255, 'El inicio de la saga galáctica.', 'https://images.unsplash.com/photo-1543002588-bfa74002ed7e?w=400', 1, 4, 3),
(62, 'Introducing CodeIgniter 4', 4900.00, 280, 'Guía rápida para desarrollo ágil en PHP.', 'https://picsum.photos/id/1062/400/600', 1, 1, 1),
(63, 'Mastering TypeScript', 6100.00, 512, 'Conceptos avanzados y patrones con TS.', 'https://picsum.photos/id/1073/400/600', 1, 1, 1),
(64, 'Designing Data-Intensive Applications', 9500.00, 612, 'Principios de diseño para sistemas complejos y escalables.', 'https://picsum.photos/id/48/400/600', 1, 1, 1),
(65, 'Learning PHP and MySQL', 4200.00, 390, 'Fundamentos para la creación de sitios dinámicos.', 'https://picsum.photos/id/60/400/600', 1, 1, 1),
(66, 'Docker & Kubernetes en la Práctica', 8700.00, 440, 'Despliegue y optimización de contenedores.', 'https://picsum.photos/id/119/400/600', 1, 1, 1),
(67, 'Microservicios Desacoplados', 9200.00, 320, 'Arquitectura orientada a servicios eficientes.', 'https://picsum.photos/id/180/400/600', 1, 1, 1),
(68, 'El Alquimista', 3100.00, 192, 'Una novela sobre los sueños y el destino.', 'https://picsum.photos/id/24/400/600', 1, 2, 2),
(69, 'La Sombra del Viento', 4800.00, 565, 'Un misterio literario en la Barcelona gótica.', 'https://picsum.photos/id/24/400/600', 1, 2, 2),
(70, 'Ficciones', 2500.00, 220, 'Antología de cuentos de laberintos y espejos.', 'https://picsum.photos/id/24/400/600', 1, 2, 2),
(71, 'Crónica de una muerte anunciada', 2100.00, 120, 'La reconstrucción de un crimen inevitable.', 'https://picsum.photos/id/24/400/600', 1, 2, 2),
(72, 'El Aleph', 2700.00, 200, 'Exploraciones sobre el infinito y el espacio.', 'https://picsum.photos/id/24/400/600', 1, 2, 2),
(73, 'Misery', 4300.00, 400, 'Un escritor es secuestrado por su fanática número uno.', 'https://picsum.photos/id/24/400/600', 1, 3, 3),
(74, 'Drácula', 3600.00, 480, 'El clásico epistolar del vampirismo gótico.', 'https://picsum.photos/id/24/400/600', 1, 3, 3),
(75, 'El mito de Cthulhu', 3900.00, 350, 'Relatos de horror cósmico y deidades primigenias.', 'https://picsum.photos/id/24/400/600', 1, 3, 3),
(76, 'Seguridad Informática Ofensiva', 9400.00, 500, 'Análisis de vulnerabilidades y exploits de sistemas.', 'https://picsum.photos/id/24/400/600', 1, 3, 3),
(77, 'Dune', 5800.00, 700, 'La obra maestra de la ecología y política feudal galáctica.', 'https://picsum.photos/id/24/400/600', 1, 4, 3),
(78, 'Neuromante', 4600.00, 320, 'La novela fundacional del movimiento Cyberpunk.', 'https://picsum.photos/id/24/400/600', 1, 4, 2),
(79, 'Fahrenheit 451', 2900.00, 192, 'Una sociedad futura donde los libros están prohibidos.', 'https://picsum.photos/id/24/400/600', 1, 4, 2),
(80, 'Crónicas Marcianas', 3300.00, 250, 'La accidentada colonización humana de Marte.', 'https://picsum.photos/id/24/400/600', 1, 4, 2),
(81, 'Un mundo feliz', 3150.00, 288, 'Una utopía tecnológica basada en el control absoluto.', 'https://picsum.photos/id/24/400/600', 1, 4, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articuloautor`
--

CREATE TABLE `articuloautor` (
  `idLibro` int(10) NOT NULL,
  `idAutor` int(10) NOT NULL,
  `fechaPublicacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `articuloautor`
--

INSERT INTO `articuloautor` (`idLibro`, `idAutor`, `fechaPublicacion`) VALUES
(52, 3, '1994-10-21'),
(52, 4, '1994-10-21'),
(53, 5, '2008-08-01'),
(54, 3, '1995-01-01'),
(55, 6, '1997-06-26'),
(56, 6, '1998-07-02'),
(62, 5, '2020-05-15'),
(63, 5, '2019-11-20'),
(64, 3, '2015-03-12'),
(65, 4, '2018-01-10'),
(66, 5, '2021-08-14'),
(67, 3, '2022-02-28'),
(68, 6, '1988-04-15'),
(69, 6, '2001-05-22'),
(70, 6, '1944-01-01'),
(71, 6, '1981-04-01'),
(72, 6, '1949-09-03'),
(73, 5, '1987-06-08'),
(74, 5, '1897-05-26'),
(75, 3, '1928-02-01'),
(76, 5, '2023-09-11'),
(77, 6, '1965-08-01'),
(78, 4, '1984-07-01'),
(79, 3, '1953-10-19'),
(80, 3, '1950-05-01'),
(81, 4, '1932-02-01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `autor`
--

CREATE TABLE `autor` (
  `idAutor` int(10) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `autor`
--

INSERT INTO `autor` (`idAutor`, `nombre`, `apellido`) VALUES
(3, 'Erich Gamma', ''),
(4, 'Richard Helm', ''),
(5, 'Robert C. Martin', ''),
(6, 'J.K. Rowling', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra`
--

CREATE TABLE `compra` (
  `idCompra` int(10) NOT NULL,
  `total` double(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `dni` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `compra`
--

INSERT INTO `compra` (`idCompra`, `total`, `fecha`, `dni`) VALUES
(61, 28000.00, '2026-01-10', 33333333),
(62, 16500.00, '2026-01-22', 33333333),
(63, 42000.00, '2026-03-05', 32837262),
(64, 25000.00, '2026-03-18', 32837262),
(65, 18000.00, '2026-05-12', 33333333),
(66, 19500.00, '2026-05-15', 32837262),
(67, 17000.00, '2026-06-03', 32837262),
(68, 36500.00, '2026-01-15', 33333333),
(69, 57000.00, '2026-03-25', 32837262),
(70, 33000.00, '2026-05-18', 33333333),
(71, 16000.00, '2026-05-28', 32837262),
(72, 34500.00, '2026-06-04', 33333333),
(73, 4500.00, '2026-06-15', 32837262),
(74, 16700.50, '2026-06-15', 32837262),
(75, 2900.00, '2026-06-15', 32837262),
(76, 9600.50, '2026-06-16', 32837262);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallescompra`
--

CREATE TABLE `detallescompra` (
  `idDetalle` int(10) NOT NULL,
  `unidades` int(3) NOT NULL,
  `precio` float NOT NULL,
  `idLibro` int(10) NOT NULL,
  `idCompra` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `detallescompra`
--

INSERT INTO `detallescompra` (`idDetalle`, `unidades`, `precio`, `idLibro`, `idCompra`) VALUES
(56, 0, 15000, 55, 61),
(57, 0, 13000, 60, 61),
(58, 0, 16500, 61, 62),
(59, 0, 22000, 52, 63),
(60, 0, 20000, 53, 63),
(61, 0, 25000, 54, 64),
(62, 0, 18000, 57, 65),
(63, 0, 19500, 58, 66),
(64, 0, 17000, 59, 67),
(65, 0, 19500, 58, 68),
(66, 0, 17000, 59, 68),
(67, 0, 22000, 52, 69),
(68, 0, 20000, 53, 69),
(69, 0, 15000, 55, 69),
(70, 0, 20000, 53, 70),
(71, 0, 13000, 60, 70),
(72, 0, 16000, 56, 71),
(73, 0, 18000, 57, 72),
(74, 0, 16500, 61, 72),
(75, 1, 4500, 52, 73),
(76, 1, 4500, 52, 74),
(77, 1, 5200, 53, 74),
(78, 1, 3800, 54, 74),
(79, 1, 3200, 55, 74),
(80, 1, 2900, 57, 75),
(81, 1, 3200, 55, 76),
(82, 1, 3500, 56, 76),
(83, 1, 2900, 57, 76);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editorial`
--

CREATE TABLE `editorial` (
  `idEditorial` int(10) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `editorial`
--

INSERT INTO `editorial` (`idEditorial`, `nombre`, `email`) VALUES
(1, 'O Reilly', 'contacto@oreilly.com'),
(2, 'Planeta', 'ventas@planeta.com'),
(3, 'Minotauro', 'info@minotauro.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `idGenero` int(10) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(1000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`idGenero`, `nombre`, `descripcion`) VALUES
(1, 'Programación', 'Libros de informática y desarrollo de software.'),
(2, 'Ficción', 'Novelas y literatura fantástica.'),
(3, 'Terror', 'Novelas de suspenso y horror.'),
(4, 'Ciencia Ficción', 'Historias futuristas y del espacio.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipousuario`
--

CREATE TABLE `tipousuario` (
  `idTipoUsuario` int(10) NOT NULL,
  `tipo` varchar(30) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `tipousuario`
--

INSERT INTO `tipousuario` (`idTipoUsuario`, `tipo`, `descripcion`) VALUES
(1, 'Admin', 'Un administrador del sistema'),
(2, 'Cliente', 'El usuario que realizará las compras');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `dni` int(8) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `contrasenia` varchar(50) NOT NULL,
  `email` varchar(40) NOT NULL,
  `genero` varchar(20) DEFAULT NULL,
  `idTipoUsuario` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`dni`, `nombre`, `apellido`, `contrasenia`, `email`, `genero`, `idTipoUsuario`) VALUES
(11111111, 'Claudio Admin', 'Sistemas', 'admin123', 'admin@digibook.com', 'Masculino', 1),
(32837262, 'Kike', 'Espinoza', '12345', 'kike@gmail.com', 'Masculino', 2),
(33333333, 'Laura', 'Gomez', 'clienta123', 'laura@gmail.com', 'Femenino', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `articulo`
--
ALTER TABLE `articulo`
  ADD PRIMARY KEY (`idLibro`),
  ADD KEY `idGenero` (`idGenero`),
  ADD KEY `idEditorial` (`idEditorial`);

--
-- Indices de la tabla `articuloautor`
--
ALTER TABLE `articuloautor`
  ADD PRIMARY KEY (`idLibro`,`idAutor`),
  ADD KEY `idAutor` (`idAutor`);

--
-- Indices de la tabla `autor`
--
ALTER TABLE `autor`
  ADD PRIMARY KEY (`idAutor`);

--
-- Indices de la tabla `compra`
--
ALTER TABLE `compra`
  ADD PRIMARY KEY (`idCompra`),
  ADD KEY `dni` (`dni`);

--
-- Indices de la tabla `detallescompra`
--
ALTER TABLE `detallescompra`
  ADD PRIMARY KEY (`idDetalle`),
  ADD KEY `idCompra` (`idCompra`),
  ADD KEY `idLibro` (`idLibro`);

--
-- Indices de la tabla `editorial`
--
ALTER TABLE `editorial`
  ADD PRIMARY KEY (`idEditorial`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`idGenero`);

--
-- Indices de la tabla `tipousuario`
--
ALTER TABLE `tipousuario`
  ADD PRIMARY KEY (`idTipoUsuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`dni`),
  ADD KEY `idTipoUsuario` (`idTipoUsuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `articulo`
--
ALTER TABLE `articulo`
  MODIFY `idLibro` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT de la tabla `compra`
--
ALTER TABLE `compra`
  MODIFY `idCompra` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `detallescompra`
--
ALTER TABLE `detallescompra`
  MODIFY `idDetalle` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT de la tabla `editorial`
--
ALTER TABLE `editorial`
  MODIFY `idEditorial` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `idGenero` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipousuario`
--
ALTER TABLE `tipousuario`
  MODIFY `idTipoUsuario` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `dni` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33333334;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `articulo`
--
ALTER TABLE `articulo`
  ADD CONSTRAINT `libro_ibfk_1` FOREIGN KEY (`idGenero`) REFERENCES `genero` (`idGenero`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `libro_ibfk_2` FOREIGN KEY (`idEditorial`) REFERENCES `editorial` (`idEditorial`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `articuloautor`
--
ALTER TABLE `articuloautor`
  ADD CONSTRAINT `libroautor_ibfk_1` FOREIGN KEY (`idLibro`) REFERENCES `articulo` (`idLibro`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `libroautor_ibfk_2` FOREIGN KEY (`idAutor`) REFERENCES `autor` (`idAutor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`dni`) REFERENCES `usuario` (`dni`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detallescompra`
--
ALTER TABLE `detallescompra`
  ADD CONSTRAINT `detallescompra_ibfk_1` FOREIGN KEY (`idCompra`) REFERENCES `compra` (`idCompra`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallescompra_ibfk_2` FOREIGN KEY (`idLibro`) REFERENCES `articulo` (`idLibro`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`idTipoUsuario`) REFERENCES `tipousuario` (`idTipoUsuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

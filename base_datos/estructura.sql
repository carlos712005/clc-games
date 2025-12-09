-- ESTRUCTURA DE BASE DE DATOS - clcgames

-- Tabla principal de videojuegos
-- Almacena tanto juegos internos (jugables en la plataforma) como externos (solo venta)
CREATE TABLE juegos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,              -- Identificador único del juego
  nombre VARCHAR(160) NOT NULL,                               -- Nombre completo del videojuego
  slug VARCHAR(160) NOT NULL UNIQUE,                          -- URL amigable única (ej: zelda_breath_wild)
  desarrollador VARCHAR(120),                                 -- Estudio desarrollador del juego
  distribuidor VARCHAR(120),                                  -- Empresa distribuidora/editora
  fecha_lanzamiento DATE,                                     -- Fecha de lanzamiento original
  portada VARCHAR(255),                                       -- Ruta a la imagen de portada
  tipo ENUM('interno','externo') NOT NULL,                    -- interno: jugable aquí, externo: solo venta
  activo TINYINT(1) NOT NULL DEFAULT 1,                       -- 1 = visible en catálogo, 0 = retirado/inactivo
  precio DECIMAL(8,2) NOT NULL DEFAULT 0.00,                  -- Precio actual (hasta 999,999.99)
  resumen VARCHAR(255),                                       -- Descripción breve para listados
  descripcion TEXT,                                           -- Descripción completa del juego
  requisitos TEXT,                                            -- Requisitos del sistema
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,    -- Fecha de creación del registro
  actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha de última modificación
                   ON UPDATE CURRENT_TIMESTAMP               -- Se actualiza automáticamente
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserción de juegos de ejemplo con datos completos
-- Incluye variedad de géneros, precios y tipos (internos/externos)
INSERT INTO juegos
(nombre, slug, desarrollador, distribuidor, fecha_lanzamiento, portada, tipo, precio, resumen, descripcion, requisitos)
VALUES
-- 1) The Legend of Zelda: Breath of the Wild - Aventura mundo abierto
('The Legend of Zelda: Breath of the Wild','zelda','Nintendo EPD','Nintendo','2017-03-03',
 'recursos/imagenes/portadas/zelda.jpg','externo',59.99,
 'Aventura de mundo abierto en Hyrule.',
 'Explora libremente un Hyrule enorme, supera santuarios y físicas creativas, cocina, escala y gestiona resistencia mientras te enfrentas a Ganon. Destaca por su libertad, exploración orgánica y resolución de puzles con múltiples soluciones.',
 'Requisitos orientativos (PC/Emulación)*: CPU 4 núcleos, 8 GB RAM, GPU dedicada moderna, Windows 10/11 64-bit.'),

-- 2) Super Mario Maker 2 - Creación de niveles
('Super Mario Maker 2','super_mario','Nintendo EPD','Nintendo','2019-06-28',
 'recursos/imagenes/portadas/super_mario.jpg','externo',49.99,
 'Crea y comparte niveles de Mario.',
 'Editor potente con estilos clásicos y nuevas piezas, modo historia, desafíos y opciones cooperativas/competitivas locales y online. La comunidad comparte miles de niveles con retos diarios y eventos.',
 'Requisitos orientativos (PC/Emulación)*: CPU 4 núcleos, 8 GB RAM, GPU dedicada básica, Windows 10/11 64-bit.'),

-- 3) Just Dance 2019 - Juego musical/ritmo
('Just Dance 2019','just_dance','Ubisoft Paris','Ubisoft','2018-10-25',
 'recursos/imagenes/portadas/just_dance.jpg','externo',39.99,
 'Baila los éxitos del momento.',
 'Juego musical de ritmo con coreografías accesibles, listas de reproducción, modo fiesta y soporte de smartphone como mando. Ideal para sesiones en grupo y eventos familiares.',
 'Requisitos orientativos (PC): CPU dual/quad, 8 GB RAM, GPU con soporte DirectX 11, Windows 10 64-bit.'),

-- 4) Red Dead Redemption II - Western mundo abierto maduro
('Red Dead Redemption II','red_dead_redemption','Rockstar Studios','Rockstar Games','2018-10-26',
 'recursos/imagenes/portadas/red_dead_redemption.jpg','externo',59.99,
 'Western de mundo abierto.',
 'Sigue a Arthur Morgan y la banda de Van der Linde en una historia madura y cinematográfica. Mundo vivo con caza, campamento, personalización de armas y decisiones que impactan en la reputación. Incluye modo en línea.',
 'Requisitos orientativos (PC): CPU 6 núcleos, 12 GB RAM, GPU 6 GB VRAM (GTX 1060/RX 590), 150 GB de almacenamiento, Windows 10 64-bit.'),

-- 5) Minecraft - Sandbox construcción más vendido del mundo
('Minecraft','minecraft','Mojang Studios','Mojang Studios','2011-11-18',
 'recursos/imagenes/portadas/minecraft.jpg','externo',26.95,
 'Construcción y supervivencia por bloques.',
 'Mundos generados proceduralmente, artesanía (crafting), combate sencillo y gran libertad creativa. Modos supervivencia y creativo, servidores multijugador y marketplace de contenidos.',
 'Requisitos orientativos (PC Java): CPU dual, 8 GB RAM, GPU con OpenGL 4.4, Java 64-bit, Windows 10/11.'),

-- 6) Persona 5 - JRPG con elementos de vida social
('Persona 5','persona_5','P-Studio','Atlus','2016-09-15',
 'recursos/imagenes/portadas/persona_5.jpg','externo',39.99,
 'JRPG de vida estudiantil y sigilo social.',
 'Alterna vida escolar en Tokio con exploración de Palacios y combates por turnos estilizados. Sistema de confidants, calendario y fusión de Personas; gran énfasis en la narrativa y la estética.',
 'Requisitos orientativos (PC/versión moderna): CPU 4 núcleos, 8 GB RAM, GPU dedicada básica, Windows 10/11 64-bit.'),

-- 7) God of War (2018) - Reinvención de la saga con mitología nórdica
('God of War','god_of_war','Santa Monica Studio','Sony Interactive Entertainment','2018-04-20',
 'recursos/imagenes/portadas/god_of_war.jpg','externo',59.99,
 'Aventura de acción en mitología nórdica.',
 'Kratos y su hijo Atreus emprenden un viaje íntimo con combate cercano táctico, mejoras de equipo y una cámara sobre el hombro. Ritmo narrativo cuidado y diseño de niveles interconectado.',
 'Requisitos orientativos (PC): CPU 4-6 núcleos, 8–16 GB RAM, GPU 4–6 GB VRAM, Windows 10 64-bit, ±70–80 GB.'),

-- 8) The Witcher 3: Wild Hunt - RPG épico aclamado
('The Witcher 3: Wild Hunt','the_witcher_3','CD Projekt Red','CD Projekt','2015-05-19',
 'recursos/imagenes/portadas/the_witcher_3.jpg','externo',39.99,
 'RPG épico de mundo abierto.',
 'Como Geralt de Rivia, acepta contratos de monstruos, toma decisiones morales y recorre un mapa enorme con historias secundarias memorables y expansiones aclamadas.',
 'Requisitos orientativos (PC): CPU 4 núcleos, 8 GB RAM, GPU 2–4 GB VRAM, Windows 10 64-bit, ±50 GB.'),

-- 9) Dark Souls III - Acción desafiante característica de FromSoftware
('Dark Souls III','dark_souls_iii','FromSoftware','Bandai Namco Entertainment','2016-03-24',
 'recursos/imagenes/portadas/dark_souls_iii.jpg','externo',39.99,
 'Acción desafiante en un mundo oscuro.',
 'Explora Lothric, domina la gestión de estamina y el posicionamiento en combates exigentes contra jefes. Amplia personalización de builds y armas, además de componentes online.',
 'Requisitos orientativos (PC): CPU 4 núcleos, 8 GB RAM, GPU 2 GB VRAM, Windows 10 64-bit, ±25 GB.'),

-- 10) Ori and the Will of the Wisps - Metroidvania artístico indie
('Ori and the Will of the Wisps','ori','Moon Studios','Xbox Game Studios','2020-03-11',
 'recursos/imagenes/portadas/ori.jpg','externo',29.99,
 'Metroidvania artístico y preciso.',
 'Plataformas y exploración con control refinado, árbol de habilidades y jefes memorables. Destaca por su dirección artística y banda sonora emotiva.',
 'Requisitos orientativos (PC): CPU 4 núcleos, 8 GB RAM, GPU 2 GB VRAM, Windows 10 64-bit, ±20 GB.'),

-- 11) Cyberpunk 2077 - RPG futurista con lanzamiento polémico pero mejorado
('Cyberpunk 2077','cyberpunk_2077','CD Projekt Red','CD Projekt','2020-12-10',
 'recursos/imagenes/portadas/cyberpunk_2077.jpg','externo',59.99,
 'RPG futurista en Night City.',
 'Crea a V, elige trasfondo y toma decisiones en una ciudad densa con tiroteos, hackeo y cyberware. Misiones ramificadas y mejoras sustanciales tras parches y expansiones.',
 'Requisitos orientativos (PC): CPU 6 núcleos, 12–16 GB RAM, GPU 6–8 GB VRAM, SSD 70–100 GB, Windows 10/11 64-bit.'),

-- 12) Super Smash Bros. Ultimate - Juego de lucha crossover definitivo
('Super Smash Bros. Ultimate','super_smash_bros','Bandai Namco Studios; Sora Ltd.','Nintendo','2018-12-07',
 'recursos/imagenes/portadas/super_smash_bros.jpg','externo',59.99,
 'Lucha crossover party.',
 'Plantel masivo de personajes y escenarios, modos local/online y reglas personalizables. Apto para partidas casuales y también competitivo.',
 'Requisitos orientativos (Consola/Emulación)*: CPU 4 núcleos, 8 GB RAM, GPU dedicada moderna, Windows 10/11 64-bit.'),

-- 13) Fire Emblem: Three Houses - Estrategia táctica con elementos RPG
('Fire Emblem: Three Houses','fire_emblem','Intelligent Systems; Koei Tecmo','Nintendo','2019-07-26',
 'recursos/imagenes/portadas/fire_emblem.jpg','externo',59.99,
 'Estrategia táctica con gestión académica.',
 'Como profesor en la Academia de Oficiales, eliges una casa y gestionas clases, vínculos y batallas por turnos con énfasis narrativo y rejugabilidad.',
 'Requisitos orientativos (PC/Emulación)*: CPU 4 núcleos, 8 GB RAM, GPU dedicada básica, Windows 10/11 64-bit.'),

-- 14) Tetris - El puzzle clásico atemporal
('Tetris','tetris','Alexey Pajitnov','Varios','1984-06-06',
 'recursos/imagenes/portadas/tetris.jpg','externo',4.99,
 'El rompecabezas clásico.',
 'Encaja tetrominós para completar líneas con una curva de dificultad creciente. Ha dado lugar a numerosas versiones, efectos y modos multijugador.',
 'Requisitos orientativos (PC): Casi cualquiera; Windows 10/11.'),

-- 15) Need for Speed: Most Wanted (2005) - Carreras arcade clásicas
('Need for Speed: Most Wanted','need_for_speed','EA Black Box','Electronic Arts','2005-11-15',
 'recursos/imagenes/portadas/need_for_speed.jpg','externo',19.99,
 'Carreras urbanas y persecuciones.',
 'Escala la Blacklist en pruebas de velocidad, derrapes y sprints mientras esquivas a la policía. Mezcla de arcade con personalización de coches.',
 'Requisitos orientativos (PC): CPU dual, 4–8 GB RAM, GPU antigua dedicada aceptable, Windows 10 64-bit.'),

-- 16) FIFA 20 - Simulación deportiva de fútbol
('FIFA 20','fifa_20','EA Vancouver; EA Romania','Electronic Arts','2019-09-27',
 'recursos/imagenes/portadas/fifa_20.jpg','externo',39.99,
 'Fútbol con licencias y modos online.',
 'Incluye Ultimate Team, temporada online y VOLTA con fútbol urbano. Ajustes de física del balón y animaciones para un juego más fluido.',
 'Requisitos orientativos (PC): CPU 4 núcleos, 8 GB RAM, GPU 2–4 GB VRAM, Windows 10 64-bit.'),

-- 17) Gran Turismo 7 - Simulación de conducción premium
('Gran Turismo 7','gran_turismo','Polyphony Digital','Sony Interactive Entertainment','2022-03-04',
 'recursos/imagenes/portadas/gran_turismo.jpg','externo',69.99,
 'Simulación de conducción realista.',
 'Coches detallados, licencias, editor de librerías y competiciones online. Amplio modo campaña con climatología y físicas afinadas.',
 'Requisitos orientativos (Consola/Emulación)*: CPU 6 núcleos, 16 GB RAM, GPU moderna, SSD rápido, Windows 10/11 64-bit.'),

-- 18) League of Legends - MOBA gratuito más popular del mundo
('League of Legends','league_of_legends','Riot Games','Riot Games','2009-10-27',
 'recursos/imagenes/portadas/league_of_legends.jpg','externo',0.00,
 'MOBA 5v5 gratuito.',
 'Más de 140 campeones, roles diferenciados y partidas competitivas en la Grieta del Invocador. Constantes actualizaciones, eventos y escena esports.',
 'Requisitos orientativos (PC): CPU dual, 4–8 GB RAM, GPU integrada/entrada, Windows 10/11, conexión estable.'),

-- 19) Fortnite - Battle Royale cultural
('Fortnite','fortnite','Epic Games','Epic Games','2017-07-25',
 'recursos/imagenes/portadas/fortnite.jpg','externo',0.00,
 'Battle Royale con construcción.',
 'Partidas rápidas a 100 jugadores, modos por temporadas, pases de batalla y eventos en vivo. Opciones sin construcción y juego cruzado.',
 'Requisitos orientativos (PC): CPU 4 núcleos, 8 GB RAM, GPU 2–4 GB VRAM, Windows 10/11 64-bit, conexión estable.'),

-- 20) Assassin's Creed IV: Black Flag - Acción pirata exitosa
('Assassin''s Creed IV: Black Flag','assassins_creed_iv','Ubisoft Montreal','Ubisoft','2013-10-29',
 'recursos/imagenes/portadas/assassins_creed_iv.jpg','externo',19.99,
 'Acción y sigilo de piratas en el Caribe.',
 'Navega el Jackdaw, aborda navíos y explora islas con un sistema de combate naval ágil. Combina sigilo en tierra con misiones de exploración y coleccionables.',
 'Requisitos orientativos (PC): CPU 4 núcleos, 8 GB RAM, GPU 2 GB VRAM, Windows 10 64-bit, ±30–40 GB.'),

-- 21) Resident Evil Requiem - Survival horror cinematográfico
('Resident Evil Requiem','resident_evil_requiem','Capcom','Capcom','2026-02-27',
  'recursos/imagenes/portadas/resident_evil_requiem.jpg','externo',69.99,
  'Survival horror intenso.',
  'Resident Evil Requiem continúa la línea de terror atmosférico de la saga, combinando exploración claustrofóbica, criaturas mutadas y una narrativa madura. El jugador se mueve entre zonas oscuras y pasillos estrechos donde la gestión de recursos es crítica. La campaña incorpora decisiones que afectan a ciertos eventos y un sistema de combate renovado basado en esquivas, contraataques y vulnerabilidades enemigas. Incluye también modos adicionales como Desafío y Contrarreloj.',
  'Requisitos orientativos (PC): CPU 8 núcleos, 16 GB RAM, GPU equivalente a RTX 2070 o superior, DirectX 12, 50 GB de espacio libre, Windows 10/11 64-bit.'),

-- 22) Crimson Desert - Acción y aventura en mundo abierto
('Crimson Desert','crimson_desert','Pearl Abyss','Pearl Abyss','2026-03-19',
  'recursos/imagenes/portadas/crimson_desert.jpg','externo',69.99,
  'Acción y aventura en mundo abierto.',
  'Crimson Desert es un juego de acción y aventura en mundo abierto ambientado en el continente de Pywel, un mundo de fantasía medieval desgarrado por la guerra. Controlas a Kliff, líder de un grupo de mercenarios que lucha por sobrevivir mientras se ve atrapado entre facciones rivales, conspiraciones y criaturas peligrosas. Combina exploración libre, combates en tiempo real y misiones con narrativa cinematográfica, con gran énfasis en el mundo vivo y los eventos dinámicos.',
  'Requisitos orientativos (PC): CPU 8 núcleos, 16 GB RAM, GPU dedicada con 8 GB VRAM (equivalente a RTX 2070 o superior), 70 GB de espacio libre en SSD, Windows 10/11 64-bit.'),

-- 23) Warhammer 40,000: Boltgun 2 - Shooter retro frenético
('Warhammer 40,000: Boltgun 2','warhammer_40000_boltgun_2','Auroch Digital','Focus Entertainment','2026-11-15',
  'recursos/imagenes/portadas/boltgun2.jpg','externo',39.99,
  'Shooter retro de acción frenética.',
  'Warhammer 40,000: Boltgun 2 es la secuela del aclamado shooter retro inspirado en los FPS de los 90, combinando velocidad extrema, combate visceral y el estilo pixelado característico. Controla a un Marine Espacial en misiones sangrientas contra hordas del Caos, demonios y herejes, usando armas clásicas como el boltgun, la chainsword y artillería pesada. Incluye más niveles, enemigos mejorados y jefes masivos.',
  'Requisitos orientativos (PC): CPU 4–6 núcleos, 12 GB RAM, GPU dedicada 4–6 GB VRAM, 10–20 GB de espacio libre, Windows 10/11 64-bit.'),

-- 24) Pokémon Champions - Aventura Pokémon competitiva
('Pokémon Champions','pokemon_champions','Game Freak','The Pokémon Company','2026-10-15',
  'recursos/imagenes/portadas/pokemon_champions.jpg',
  'externo',59.99,
  'Aventura Pokémon competitiva.',
  'Pokémon Champions propone una aventura renovada centrada en combates estratégicos y progresión competitiva. Explora nuevas regiones, captura especies inéditas y participa en una Liga de Alto Nivel donde cada victoria otorga puntos de rango y mejoras de entrenador. Incluye modos clásicos, desafíos semanales y funciones online para combatir o intercambiar criaturas con jugadores de todo el mundo.',
  'Requisitos orientativos (PC/Emulación): CPU 4 núcleos, 8 GB RAM, GPU integrada moderna o 2 GB VRAM, Windows 10/11 64-bit.'),

-- 25) Pong – Clásico arcade CLC Games
('Pong','pong','CLC Games','CLC Games','2024-01-01',
 'recursos/imagenes/portadas/pong.jpg','interno',0.00,
 'Clásico arcade de palas.',
 'Versión moderna del mítico Pong, con controles fluidos y físicas revisadas. Ideal para partidas rápidas.',
 'Requisitos orientativos (PC): Navegador moderno compatible con HTML5, Windows 10/11.'), 

-- 26) Buscaminas – Edición CLC Games
('Buscaminas','buscaminas','CLC Games','CLC Games','2024-01-01',
 'recursos/imagenes/portadas/buscaminas.jpg','interno',0.00,
 'Busca minas sin explotar.',
 'Adaptación optimizada del clásico Buscaminas, con niveles de dificultad y diseño visual renovado.',
 'Requisitos orientativos (PC): Navegador moderno compatible con HTML5, Windows 10/11.'),

-- 27) Solitario – Versión clásica CLC Games
('Solitario','solitario','CLC Games','CLC Games','2024-01-01',
 'recursos/imagenes/portadas/solitario.jpg','interno',0.00,
 'Clásico juego de cartas.',
 'Versión digital del Solitario tradicional, con animaciones suaves y controles intuitivos.',
 'Requisitos orientativos (PC): Navegador moderno compatible con HTML5, Windows 10/11.'),

-- 28) Ahorcado – Edición temática CLC Games
('Ahorcado','ahorcado','CLC Games','CLC Games','2024-01-01',
 'recursos/imagenes/portadas/ahorcado.jpg','interno',0.00,
 'Adivina la palabra oculta.',
 'Juego del Ahorcado con categorías temáticas y diccionario propio. Perfecto para partidas rápidas.',
 'Requisitos orientativos (PC): Navegador moderno compatible con HTML5, Windows 10/11.'),

-- 29) Tres en Raya – Versión estratégica CLC Games
('Tres en Raya','tres_en_raya','CLC Games','CLC Games','2024-01-01',
 'recursos/imagenes/portadas/tres_en_raya.jpg','interno',0.00,
 'Clásico 3 en línea.',
 'Tres en Raya con modos jugador vs jugador y jugador vs IA sencilla. Interfaz minimalista.',
 'Requisitos orientativos (PC): Navegador moderno compatible con HTML5, Windows 10/11.');


-- Tabla de filtros para categorización de juegos
-- Sistema flexible para clasificar juegos por múltiples criterios
CREATE TABLE filtros (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,                 -- ID autoincremental interno
  id_fijo INT UNSIGNED NOT NULL UNIQUE,                      -- ID estable definido manualmente para referencias
  nombre VARCHAR(100) NOT NULL,                              -- Nombre descriptivo del filtro
  tipo_filtro ENUM('tipos','generos','categorias','modos','clasificacionPEGI') NOT NULL, -- Categoría del filtro
  clave VARCHAR(60) NOT NULL,                                -- Clave única para uso en código (slug)
  orden INT UNSIGNED NOT NULL DEFAULT 0,                     -- Orden para mostrar en la interfaz
  UNIQUE KEY u_tipo_clave (tipo_filtro, clave)              -- Evita duplicados de clave dentro del mismo tipo
);

-- FILTROS TIPO: Diferencia entre juegos internos (jugables) y externos (solo venta)
INSERT INTO filtros (id_fijo, nombre, tipo_filtro, clave, orden) VALUES
(1,'Juegos de la casa', 'tipos', 'interno', 1),        -- Juegos desarrollados CLC Games
(2,'Juegos de terceros', 'tipos', 'externo', 2);     -- Juegos de otros desarrolladores

-- FILTROS GÉNEROS: Clasificación por géneros de videojuegos
INSERT INTO filtros (id_fijo, nombre, tipo_filtro, clave, orden) VALUES
(3,'Acción','generos','accion',1),                          -- Juegos de acción y combate
(4,'Aventura','generos','aventura',2),                      -- Juegos de aventura y exploración
(5,'Rol (RPG)','generos','rpg',3),                          -- Juegos de rol tradicionales
(6,'Estrategia','generos','estrategia',4),                  -- Juegos de estrategia y táctica
(7,'Deportes','generos','deportes',5),                      -- Simulaciones deportivas
(8,'Carreras','generos','carreras',6),                      -- Juegos de coches y carreras
(9,'Puzzle','generos','puzzle',7),                          -- Rompecabezas y lógica
(10,'Lucha','generos','lucha',8),                           -- Juegos de lucha 1v1 o party
(11,'Plataformas','generos','plataformas',9),               -- Plataformas 2D/3D
(12,'Creación','generos','creacion',10),                    -- Juegos de construcción/creación
(13,'Música','generos','musica',11),                        -- Juegos musicales
(14,'Ritmo','generos','ritmo',12),                          -- Juegos de ritmo y baile
(15,'Sandbox','generos','sandbox',13),                      -- Mundos abiertos creativos
(16,'Supervivencia','generos','supervivencia',14),          -- Juegos de supervivencia
(17,'Mundo abierto','generos','mundo_abierto',15),          -- Juegos de exploración libre
(18,'Metroidvania','generos','metroidvania',16),            -- Exploración con progresión gradual
(19,'Party','generos','party',17),                          -- Juegos multijugador casual
(20,'Rol táctico','generos','rol_tactico',18),              -- RPG con combate táctico
(21,'Battle Royale','generos','battle_royale',19),          -- Supervivencia multijugador masiva
(22,'Disparos','generos','disparos',20),                    -- Juegos de disparos (FPS/TPS)
(23,'MOBA','generos','moba',21),                            -- Multiplayer Online Battle Arena
(24,'Simulación','generos','simulacion',22),                -- Simuladores realistas
(25,'JRPG','generos','jrpg',23);                            -- Japanese RPG con características específicas

-- FILTROS CATEGORÍAS: Tipo de contenido del producto
INSERT INTO filtros (id_fijo, nombre, tipo_filtro, clave, orden) VALUES
(26,'Juego base','categorias','juego_base',1),              -- Juego completo principal
(27,'DLC','categorias','dlc',2),                            -- Contenido descargable adicional
(28,'Edición especial','categorias','edicion_especial',3);  -- Versiones premium o coleccionista

-- FILTROS MODOS: Opciones de juego disponibles
INSERT INTO filtros (id_fijo, nombre, tipo_filtro, clave, orden) VALUES
(29,'Un jugador','modos','un_jugador',1),                   -- Modo individual/campaña
(30,'Multijugador','modos','multijugador',2),               -- Multijugador local/split-screen
(31,'Cooperativo','modos','cooperativo',3),                 -- Cooperación entre jugadores
(32,'Online','modos','online',4);                           -- Multijugador en línea

-- FILTROS CLASIFICACIÓN PEGI: Sistema europeo de clasificación por edades
INSERT INTO filtros (id_fijo, nombre, tipo_filtro, clave, orden) VALUES
(33,'PEGI 3','clasificacionPEGI','pegi3',1),                -- Apto para todas las edades
(34,'PEGI 7','clasificacionPEGI','pegi7',2),                -- Mayores de 7 años
(35,'PEGI 12','clasificacionPEGI','pegi12',3),              -- Mayores de 12 años
(36,'PEGI 16','clasificacionPEGI','pegi16',4),              -- Mayores de 16 años
(37,'PEGI 18','clasificacionPEGI','pegi18',5);              -- Solo adultos (18+)

-- Tabla de relación muchos-a-muchos entre juegos y filtros
-- Un juego puede tener múltiples filtros y un filtro puede aplicarse a múltiples juegos
CREATE TABLE juegos_filtros (
  id_juego BIGINT UNSIGNED NOT NULL,                         -- Referencia al juego
  id_filtro INT UNSIGNED NOT NULL,                           -- Referencia al filtro (usando id_fijo)
  PRIMARY KEY (id_juego, id_filtro),                         -- Clave primaria compuesta
  FOREIGN KEY (id_juego) REFERENCES juegos(id) ON DELETE CASCADE,     -- Eliminar relaciones si se borra el juego
  FOREIGN KEY (id_filtro) REFERENCES filtros(id_fijo)       -- Referencia al ID fijo estable
) ENGINE=InnoDB;

-- Asignación de filtros a cada juego
-- Se asignan múltiples categorías para permitir filtrado preciso

-- 1) The Legend of Zelda: Breath of the Wild - Aventura/Acción mundo abierto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(1, 4),(1, 3),(1, 26),(1, 29),(1, 35);                      -- aventura, acción, juego_base, un_jugador, pegi12

-- 2) Super Mario Maker 2 - Plataformas creativo con multijugador
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(2, 11),(2, 12),(2, 26),(2, 29),(2, 30),(2, 32),(2, 33);    -- plataformas, creacion, juego_base, un_jugador, multijugador, online, pegi3

-- 3) Just Dance 2019 - Música/Ritmo para todas las edades
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(3, 13),(3, 14),(3, 26),(3, 29),(3, 30),(3, 33);            -- musica, ritmo, juego_base, un_jugador, multijugador, pegi3

-- 4) Red Dead Redemption II - Acción/Aventura mundo abierto adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(4, 3),(4, 4),(4, 17),(4, 26),(4, 29),(4, 32),(4, 37);      -- accion, aventura, mundo_abierto, juego_base, un_jugador, online, pegi18

-- 5) Minecraft - Sandbox supervivencia familiar
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(5, 15),(5, 16),(5, 26),(5, 29),(5, 30),(5, 32),(5, 34);    -- sandbox, supervivencia, juego_base, un_jugador, multijugador, online, pegi7

-- 6) Persona 5 - JRPG solitario teen
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(6, 5),(6, 25),(6, 26),(6, 29),(6, 36);                     -- rpg, jrpg, juego_base, un_jugador, pegi16

-- 7) God of War (2018) - Acción/Aventura adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(7, 3),(7, 4),(7, 26),(7, 29),(7, 37);                      -- accion, aventura, juego_base, un_jugador, pegi18

-- 8) The Witcher 3: Wild Hunt - RPG mundo abierto adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(8, 5),(8, 17),(8, 26),(8, 29),(8, 37);                     -- rpg, mundo_abierto, juego_base, un_jugador, pegi18

-- 9) Dark Souls III - Acción/RPG con multijugador teen
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(9, 3),(9, 5),(9, 26),(9, 29),(9, 32),(9, 36);              -- accion, rpg, juego_base, un_jugador, online, pegi16

-- 10) Ori and the Will of the Wisps - Plataformas/Metroidvania familiar
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(10, 11),(10, 18),(10, 26),(10, 29),(10, 34);               -- plataformas, metroidvania, juego_base, un_jugador, pegi7

-- 11) Cyberpunk 2077 - Acción/RPG futurista adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(11, 3),(11, 5),(11, 26),(11, 29),(11, 37);                 -- accion, rpg, juego_base, un_jugador, pegi18

-- 12) Super Smash Bros. Ultimate - Lucha party multijugador
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(12, 10),(12, 19),(12, 26),(12, 29),(12, 30),(12, 32),(12, 35); -- lucha, party, juego_base, un_jugador, multijugador, online, pegi12

-- 13) Fire Emblem: Three Houses - Estrategia/Rol táctico
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(13, 6),(13, 20),(13, 26),(13, 29),(13, 35);                -- estrategia, rol_tactico, juego_base, un_jugador, pegi12

-- 14) Tetris - Puzzle clásico universal
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(14, 9),(14, 26),(14, 29),(14, 30),(14, 33);                -- puzzle, juego_base, un_jugador, multijugador, pegi3

-- 15) Need for Speed: Most Wanted (2005) - Carreras arcade
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(15, 8),(15, 26),(15, 29),(15, 32),(15, 35);                -- carreras, juego_base, un_jugador, online, pegi12

-- 16) FIFA 20 - Deportes con multijugador familiar
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(16, 7),(16, 26),(16, 29),(16, 30),(16, 32),(16, 33);       -- deportes, juego_base, un_jugador, multijugador, online, pegi3

-- 17) Gran Turismo 7 - Simulación carreras realista familiar
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(17, 8),(17, 24),(17, 26),(17, 29),(17, 32),(17, 33);       -- carreras, simulacion, juego_base, un_jugador, online, pegi3

-- 18) League of Legends - MOBA estratégico competitivo
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(18, 23),(18, 6),(18, 26),(18, 30),(18, 32),(18, 35);       -- moba, estrategia, juego_base, multijugador, online, pegi12

-- 19) Fortnite - Battle Royale con construcción
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(19, 21),(19, 22),(19, 26),(19, 30),(19, 32),(19, 35);      -- battle_royale, disparos, juego_base, multijugador, online, pegi12

-- 20) Assassin's Creed IV: Black Flag - Acción/Aventura pirata adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(20, 3),(20, 4),(20, 26),(20, 29),(20, 32),(20, 37);        -- accion, aventura, juego_base, un_jugador, online, pegi18

-- 21) Resident Evil Requiem - Acción/Aventura/Disparos horror cinematográfico adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(21, 2),(21, 3),(21, 4),(21, 22),(21, 26),(21, 29),(21, 32),(21, 37);      -- externo, accion, aventura, disparos, juego_base, un_jugador, online, pegi18

-- 22) Crimson Desert - Acción/Aventura mundo abierto adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(22, 2),(22, 3),(22, 4),(22, 17),(22, 26),(22, 29),(22, 37);    -- externo, accion, aventura, mundo_abierto, juego_base, un_jugador, pegi18

-- 23) Warhammer 40,000: Boltgun 2 - Acción/Disparos shooter retro frenético adulto
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(23, 2),(23, 3),(23, 22),(23, 26),(23, 29),(23, 32),(23, 37);   -- externo, accion, disparos, juego_base, un_jugador, online, pegi18

-- 24) Pokémon Champions - Aventura/Rol competitivo teen
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(24, 2),(24, 4),(24, 5),(24, 26),(24, 29),(24, 32),(24, 34);    -- externo, aventura, rpg, juego_base, un_jugador, online, pegi7

-- 25) Pong – Clásico arcade CLC Games
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(25, 1),(25, 7),(25, 19),(25, 26),(25, 29),(25, 30), (25, 33);  -- interno, deportes, party, juego_base, un_jugador, multijugador, pegi3

-- 26) Buscaminas – Edición CLC Games
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(26, 1),(26, 9),(26, 26),(26, 29),(26, 33);                     -- interno, puzzle, juego_base, un_jugador, pegi3

-- 27) Solitario – Versión clásica CLC Games
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(27, 1),(27, 9),(27, 26),(27, 29),(27, 33);                     -- interno, puzzle, juego_base, un_jugador, pegi3

-- 28) Ahorcado – Edición temática CLC Games
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(28, 1),(28, 9),(28, 26),(28, 29),(28, 33);                     -- interno, puzzle, juego_base, un_jugador, pegi3

-- 29) Tres en Raya – Versión estratégica CLC Games
INSERT INTO juegos_filtros (id_juego, id_filtro) VALUES
(29, 1),(29, 6),(29, 9),(29, 26),(29, 29),(29, 30),(29, 33);  -- interno, estrategia, puzzle, juego_base, un_jugador, multijugador, pegi3

-- Tabla de roles de usuario
-- Define los diferentes niveles de permisos en el sistema
CREATE TABLE roles (
  id TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,   -- ID autoincremental interno
  id_rol TINYINT UNSIGNED NOT NULL UNIQUE,          -- ID fijo para referencia en otras tablas
  nombre VARCHAR(50) NOT NULL UNIQUE                -- Nombre único del rol
);

-- Roles básicos del sistema de usuarios (con IDs fijos)
INSERT INTO roles (id_rol, nombre) VALUES
(1, 'administrador'),   -- Usuario con permisos de gestión completa
(2, 'registrado');      -- Usuario con cuenta activa
/*(3, 'noRegistrado');*/    -- Usuario visitante sin cuenta

-- Tabla principal de usuarios registrados
-- Almacena la información personal y de autenticación de cada usuario
CREATE TABLE usuarios (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,             -- Identificador único de usuario

  acronimo      VARCHAR(80)   NOT NULL UNIQUE,               -- Nombre de usuario (único para login y visualización)
  nombre        VARCHAR(80)   NOT NULL,                      -- Nombre personal del usuario
  apellidos     VARCHAR(120)  NOT NULL,                      -- Apellidos completos
  dni           VARCHAR(15)   NOT NULL UNIQUE,               -- Documento nacional de identidad (único)
  email         VARCHAR(120)  NOT NULL UNIQUE,               -- Correo electrónico (único para login)
  contrasena    VARCHAR(255)  NOT NULL,                      -- Hash seguro de la contraseña

  id_rol        TINYINT UNSIGNED NOT NULL,                   -- Referencia al rol del usuario

  ultimo_acceso DATETIME       NULL,                         -- Última vez que inició sesión
  creado_en     TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,    -- Fecha de registro
  actualizado_en TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP     -- Última modificación del perfil
                                 ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE RESTRICT       -- Impide borrar roles en uso
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Usuario administrador por defecto
-- Acronimo: admin
-- Contraseña: Admin2025@
INSERT INTO 
usuarios (acronimo, nombre, apellidos, dni, email, contrasena, id_rol)
VALUES (
    'admin',                                                          -- Acronimo único
    'Carlos',                                                         -- Nombre  
    'Lancho Cuadrado',                                                -- Apellidos
    '12345678Z',                                                      -- DNI
    'admin@clcgames.com',                                             -- Email
    '$2y$10$u1hENnNSiEEiAkNpse7GEObUK/3XfLNVmfH65YJycfKSlyAQ9USuK',   -- Contraseña (hashed)
    1                                                                 -- Rol (administrador)
);

-- Tabla de preferencias de filtros por usuario
-- Permite que cada usuario guarde sus géneros/categorías favoritas
CREATE TABLE preferencias_usuario (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,             -- ID único de la preferencia
  id_usuario BIGINT UNSIGNED NOT NULL,                       -- Usuario al que pertenece
  id_filtro INT UNSIGNED NOT NULL,                           -- Filtro marcado como preferencia
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,    -- Eliminar si se borra el usuario
  FOREIGN KEY (id_filtro) REFERENCES filtros(id_fijo) ON DELETE CASCADE, -- Eliminar si se borra el filtro
  UNIQUE KEY u_usuario_filtro (id_usuario, id_filtro)        -- Un usuario no puede tener el mismo filtro duplicado
);

-- Tabla del carrito de compras
-- Almacena los juegos que el usuario ha añadido pero aún no ha comprado
CREATE TABLE carrito (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,             -- ID único del elemento en carrito
  id_usuario BIGINT UNSIGNED NOT NULL,                       -- Usuario propietario del carrito
  id_juego   BIGINT UNSIGNED NOT NULL,                       -- Juego añadido al carrito
  creado_en  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  -- Cuándo se añadió al carrito

  UNIQUE KEY u_usuario_juego (id_usuario, id_juego),         -- Evita duplicados: un usuario no puede tener el mismo juego repetido en su carrito
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,    -- Eliminar carrito si se borra el usuario
  FOREIGN KEY (id_juego)   REFERENCES juegos(id)   ON DELETE RESTRICT    -- No permitir borrar juegos que están en carritos activos
) ENGINE=InnoDB;

-- Tabla de la biblioteca personal de cada usuario
-- Contiene los juegos que el usuario ha comprado y posee
CREATE TABLE biblioteca (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,             -- ID único de la adquisición
  id_usuario BIGINT UNSIGNED NOT NULL,                       -- Usuario propietario
  id_juego   BIGINT UNSIGNED NOT NULL,                       -- Juego adquirido
  precio_pagado DECIMAL(8,2) NOT NULL,                       -- Precio que pagó (puede diferir del precio actual)
  fecha_adquisicion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Cuándo se completó la compra

  UNIQUE KEY u_usuario_juego (id_usuario, id_juego),         -- Evita duplicados: un usuario no puede poseer el mismo juego múltiples veces

  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,    -- Eliminar biblioteca si se borra el usuario
  FOREIGN KEY (id_juego)   REFERENCES juegos(id)   ON DELETE RESTRICT    -- Mantener registro histórico aunque se retire el juego de la tienda
) ENGINE=InnoDB;

-- Tabla de historial de transacciones
-- Registra todos los eventos relacionados con compras, cancelaciones y devoluciones
CREATE TABLE historial (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,      -- ID de la operación global
  id_usuario BIGINT UNSIGNED NOT NULL,                -- Usuario que realizó la acción
  tipo ENUM('COMPRA','RESERVA','SOLICITUD_DEVOLUCION','DEVOLUCION') NOT NULL,          -- Tipo de operación general
  estado ENUM('PENDIENTE','PAGADA','CANCELADA','APROBADA','RECHAZADA','PARCIAL','PENDIENTE_REVISION','RESERVADA', 'COMPLETADA')
        NOT NULL DEFAULT 'PENDIENTE',                 -- Estado global de la operación
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00,          -- Total acumulado de la operación
  metodo_pago         VARCHAR(30)    NULL,            -- Método de pago utilizado
  paypal_order_id     VARCHAR(64)    NULL,            -- ID de orden de PayPal
  paypal_capture_id   VARCHAR(64)    NULL,            -- ID de captura de PayPal
  paypal_email        VARCHAR(160)   NULL,            -- Email asociado a la cuenta PayPal
  comentario VARCHAR(255) NULL,                       -- Motivo o comentario general
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Cuándo se creó el registro
  actualizado_en TIMESTAMP NOT NULL 
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,                  -- Última actualización del registro

  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE   -- Eliminar historial si se borra el usuario
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de detalles individuales por juego en cada transacción
-- Permite desglosar el estado y precio de cada juego dentro de una compra o devolución
CREATE TABLE historial_compras (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,      -- ID único del detalle
  id_historial BIGINT UNSIGNED NOT NULL,              -- FK al historial principal
  id_juego BIGINT UNSIGNED NOT NULL,                  -- Juego afectado
  precio DECIMAL(8,2) NOT NULL,                       -- Precio congelado en la transacción
  estado ENUM('PAGADO','RESERVADO','CANCELADO','PENDIENTE_REVISION','DEVUELTO','RECHAZADA', 'APROBADA')
        NOT NULL,                                     -- Estado individual del juego
  comentario VARCHAR(255) NULL,                       -- Motivo específico
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Cuándo se creó el registro

  FOREIGN KEY (id_historial) REFERENCES historial(id) ON DELETE CASCADE,  -- Eliminar detalles si se borra la operación principal
  FOREIGN KEY (id_juego) REFERENCES juegos(id) ON DELETE RESTRICT     -- Mantener registro aunque se retire el juego de la tienda
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de juegos marcados como favoritos
-- Permite a los usuarios crear una lista de deseos/favoritos
CREATE TABLE favoritos (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,             -- ID único del favorito
  id_usuario BIGINT UNSIGNED NOT NULL,                       -- Usuario que marcó el favorito
  id_juego   BIGINT UNSIGNED NOT NULL,                       -- Juego marcado como favorito
  creado_en  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  -- Cuándo se marcó como favorito

  UNIQUE KEY u_usuario_juego (id_usuario, id_juego),         -- Evita duplicados: un usuario no puede marcar el mismo juego como favorito múltiples veces

  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,    -- Eliminar favoritos si se borra el usuario
  FOREIGN KEY (id_juego)   REFERENCES juegos(id)   ON DELETE CASCADE     -- Eliminar de favoritos si se retira el juego de la tienda
) ENGINE=InnoDB;

-- Tabla de comentarios de usuarios sobre juegos
-- Permite a los usuarios dejar reseñas y opiniones sobre los juegos que poseen
CREATE TABLE comentarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,            -- ID único del comentario
    id_usuario BIGINT UNSIGNED NOT NULL,                      -- Usuario que hizo el comentario
    id_juego BIGINT UNSIGNED NOT NULL,                        -- Juego comentado
    comentario TEXT NOT NULL,                                 -- Texto del comentario
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- Cuándo se creó el comentario
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP, -- Última actualización
    UNIQUE(id_usuario, id_juego),                             -- Un usuario solo puede comentar una vez por juego

    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,     -- Eliminar comentarios si se borra el usuario
    FOREIGN KEY (id_juego) REFERENCES juegos(id) ON DELETE CASCADE          -- Eliminar comentarios si se retira el juego
);

-- Tabla de valoraciones de usuarios sobre juegos
-- Permite a los usuarios puntuar los juegos con una calificación numérica
CREATE TABLE valoraciones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,                           -- ID único de la valoración
    id_usuario BIGINT UNSIGNED NOT NULL,                                     -- Usuario que hizo la valoración
    id_juego BIGINT UNSIGNED NOT NULL,                                       -- Juego valorado
    valoracion TINYINT UNSIGNED NOT NULL CHECK (valoracion BETWEEN 1 AND 5), -- Puntuación del 1 al 5
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,                  -- Cuándo se creó la valoración
    actualizado_en TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  -- Última actualización

    UNIQUE(id_usuario, id_juego),                                            -- Un usuario solo puede valorar una vez por juego

    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,      -- Eliminar valoraciones si se borra el usuario
    FOREIGN KEY (id_juego) REFERENCES juegos(id) ON DELETE CASCADE           -- Eliminar valoraciones si se retira el juego
);

-- Tabla de notificaciones para usuarios
-- Almacena mensajes del sistema dirigidos a usuarios específicos
CREATE TABLE notificaciones (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,                        -- ID único
  id_usuario BIGINT UNSIGNED NOT NULL,                                  -- Usuario destinatario
  id_juego BIGINT UNSIGNED NULL,                                        -- Juego relacionado
  mensaje VARCHAR(255) NOT NULL,                                        -- Texto visible para el usuario
  tipo ENUM('INFO','ALERTA','SISTEMA') NOT NULL 
      DEFAULT 'INFO',                                                   -- Tipo de notificación
  leido TINYINT(1) NOT NULL DEFAULT 0,                                  -- 0 = no leída, 1 = leída
  creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,               -- Fecha de creación

  FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,   -- Eliminar notificaciones si se borra el usuario
  FOREIGN KEY (id_juego) REFERENCES juegos(id) ON DELETE SET NULL       -- Mantener notificación aunque se retire el juego
);

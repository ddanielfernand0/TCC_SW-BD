-- Remove o banco se existir (comando seguro)
DROP DATABASE IF EXISTS bd;

-- Cria o banco com configurações otimizadas
CREATE DATABASE IF NOT EXISTS bd 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE bd;

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE, -- O email deve ser único
    senha_hash VARCHAR(255) NOT NULL,    -- NUNCA armazene a senha em texto. Armazene um hash.
    url_foto_perfil VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE artistas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_artistico VARCHAR(150) NOT NULL,
    biografia TEXT,
    data_nascimento DATE,
    url_foto_perfil VARCHAR(255),
    links_redes_sociais JSON -- Usar JSON permite armazenar vários links de forma estruturada. Ex: {"instagram": "...", "twitter": "..."}
);

CREATE TABLE albums (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    data_lancamento DATE,
    url_capa VARCHAR(255), -- Link para a imagem de capa do álbum
    artista_id INT, -- Chave estrangeira para conectar ao artista
    FOREIGN KEY (artista_id) REFERENCES artistas(id)
);

CREATE TABLE musicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    duracao TIME, -- Formato 'HH:MM:SS'
    genero VARCHAR(50),
    url_audio VARCHAR(255) NOT NULL, -- Link para o arquivo de áudio (mp3, etc.)
    link_clipe VARCHAR(255),
    album_id INT, -- Chave estrangeira para conectar ao álbum
    FOREIGN KEY (album_id) REFERENCES albums(id)
);

CREATE TABLE musica_artistas (
    musica_id INT,
    artista_id INT,
    PRIMARY KEY (musica_id, artista_id), -- A chave primária é a combinação dos dois IDs
    FOREIGN KEY (musica_id) REFERENCES musicas(id),
    FOREIGN KEY (artista_id) REFERENCES artistas(id)
);

CREATE TABLE playlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao VARCHAR(300),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT, -- O "dono" da playlist
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

CREATE TABLE playlist_musicas (
    playlist_id INT,
    musica_id INT,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (playlist_id, musica_id),
    FOREIGN KEY (playlist_id) REFERENCES playlists(id),
    FOREIGN KEY (musica_id) REFERENCES musicas(id)
);

-- Kpop
INSERT INTO `artistas` (`id`, `nome_artistico`, `url_foto_perfil`) VALUES
(101, 'Chuu', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSJybAolHwkjt556GvzligLSyZZ3D0ugNHkiw&s'),
(102, 'Yves', 'https://images.genius.com/44af859da1063eef4fea29c0e4bcf381.1000x1000x1.png'),
(103, 'Jennie', 'https://i.scdn.co/image/ab6761610000e5eba8e3627e392a1d8f539cb575'),
(104, 'Lisa', 'https://i.scdn.co/image/ab6761610000e5eb8543b9b2b5d153d37c46606d'),
(105, 'Nayeon', 'https://i.scdn.co/image/ab67616100005174059dd5ac2b3b1a7218063e92'),
(106, 'Yuqi', 'https://i.scdn.co/image/ab6761610000e5eb4ef67d743edcd93e1b519f66');

INSERT INTO `albums` (`id`, `titulo`, `artista_id`, `url_capa`) VALUES
(106, 'YUQ1', 106, 'https://i.scdn.co/image/ab67616d00001e0273791f096b7c0037ba02a4f8'),
(105, 'NA', 105, 'https://i.scdn.co/image/ab67616d00001e0273f05e5c2f5ec5cd07c6b6d9'),
(104, 'Alter Ego', 104, 'https://image-cdn-fa.spotifycdn.com/image/ab67706c0000da8482ce75c1c94a72ed08bd25dd'),
(103, 'Ruby', 103, 'https://i.scdn.co/image/ab67616d00001e024dbec3d7fe316be4a9e08f2d'),
(102, 'Soft Error', 102, 'https://i.scdn.co/image/ab67616d00001e0245aa9482be54a027b1a0b992'),
(101, 'Strawberry Rush', 101, 'https://i.scdn.co/image/ab67616d0000b273cc681b43015ca45cd52e4625');

INSERT INTO `musicas` (`id`, `titulo`, `duracao`, `genero`, `url_audio`, `album_id`) VALUES
(101, 'Strawberry Rush', '00:03:08', 'Kpop', 'https://www.youtube.com/watch?v=JRbXa1w-Pa8&list=RDJRbXa1w-Pa8&start_radio=1', 101),
(102, 'White Cat', '00:03:25', 'Kpop', 'https://www.youtube.com/watch?v=XuBE8ns_Ato&list=RDXuBE8ns_Ato&start_radio=1', 102),
(104, 'NEW WOMAN', '00:03:11', 'Kpop', 'https://www.youtube.com/watch?v=UxXY_hR_wzo&list=RDUxXY_hR_wzo&start_radio=1', 104),
(103, 'Love Hangover', '00:03:36', 'Kpop', 'https://www.youtube.com/watch?v=23urWKmHS6o&list=RD23urWKmHS6o&start_radio=1', 103),
(105, 'ABCD', '00:03:41', 'Kpop', 'https://www.youtube.com/watch?v=oUZttxRcPZw&list=RDoUZttxRcPZw&start_radio=1', 105),
(106, 'FREAK', '00:03:05', 'Kpop', 'https://www.youtube.com/watch?v=UqJIBItJeyg&list=RDUqJIBItJeyg&start_radio=1', 106);

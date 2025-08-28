-- ------------------------------------------------------------------
-- SCRIPT DO BANCO DE DADOS ATUALIZADO
-- ------------------------------------------------------------------

-- Remove o banco de dados antigo para recomeçar do zero.
DROP DATABASE IF EXISTS bd;

-- Cria o novo banco de dados com as configurações corretas.
CREATE DATABASE IF NOT EXISTS bd 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco para os próximos comandos.
USE bd;

-- ------------------------------------------------------------------
-- ESTRUTURA DAS TABELAS
-- ------------------------------------------------------------------

CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    url_foto_perfil VARCHAR(255),
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE artistas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome_artistico VARCHAR(150) NOT NULL,
    biografia TEXT,
    data_nascimento DATE,
    url_foto_perfil VARCHAR(255),
    links_redes_sociais JSON
);

CREATE TABLE albums (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    data_lancamento DATE,
    url_capa VARCHAR(255),
    artista_id INT,
    FOREIGN KEY (artista_id) REFERENCES artistas(id) ON DELETE SET NULL
);

-- NOVA TABELA DE GÊNEROS
CREATE TABLE generos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(50) NOT NULL UNIQUE
);

-- TABELA DE MÚSICAS ATUALIZADA
CREATE TABLE musicas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titulo VARCHAR(200) NOT NULL,
    duracao TIME,
    url_clipe VARCHAR(255), -- url_audio foi trocada por url_clipe
    album_id INT,
    genero_id INT, -- coluna 'genero' foi trocada por 'genero_id'
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE SET NULL,
    FOREIGN KEY (genero_id) REFERENCES generos(id) ON DELETE SET NULL
);

CREATE TABLE musica_artistas (
    musica_id INT NOT NULL,
    artista_id INT NOT NULL,
    PRIMARY KEY (musica_id, artista_id),
    FOREIGN KEY (musica_id) REFERENCES musicas(id) ON DELETE CASCADE,
    FOREIGN KEY (artista_id) REFERENCES artistas(id) ON DELETE CASCADE
);

CREATE TABLE playlists (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(150) NOT NULL,
    descricao VARCHAR(300),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

CREATE TABLE playlist_musicas (
    playlist_id INT NOT NULL,
    musica_id INT NOT NULL,
    data_adicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (playlist_id, musica_id),
    FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    FOREIGN KEY (musica_id) REFERENCES musicas(id) ON DELETE CASCADE
);

-- Cria a nova tabela para armazenar as avaliações e comentários.
CREATE TABLE avaliacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    musica_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nota INT NOT NULL, -- A nota de 1 a 5 estrelas
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (musica_id) REFERENCES musicas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Cria a nova tabela para armazenar os artistas que cada usuário segue.
CREATE TABLE usuario_artistas_seguidos (
    usuario_id INT NOT NULL,
    artista_id INT NOT NULL,
    data_seguido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, artista_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (artista_id) REFERENCES artistas(id) ON DELETE CASCADE
);

-- Cria a nova tabela para armazenar as músicas que cada usuário curtiu.
CREATE TABLE usuario_musicas_curtidas (
    usuario_id INT NOT NULL,
    musica_id INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, musica_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (musica_id) REFERENCES musicas(id) ON DELETE CASCADE
);

CREATE TABLE usuario_albums_curtidos (
    usuario_id INT NOT NULL,
    album_id INT NOT NULL,
    data_curtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, album_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE
);

CREATE TABLE album_avaliacoes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    album_id INT NOT NULL,
    usuario_id INT NOT NULL,
    nota INT NOT NULL, -- A nota de 1 a 5 estrelas
    comentario TEXT,
    data_avaliacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY (album_id, usuario_id)
);


-- ------------------------------------------------------------------
-- INSERÇÃO DE DADOS (INSERTS)
-- ------------------------------------------------------------------

-- Insere os gêneros na nova tabela.
INSERT INTO `generos` (`id`, `nome`) VALUES
(1, 'K-pop'),
(2, 'Pop'),
(3, 'Pop Rock'),
(4, 'Dreampop');

-- Insere os artistas de K-pop.
INSERT INTO `artistas` (`id`, `nome_artistico`, `url_foto_perfil`) VALUES
(101, 'Chuu', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSJybAolHwkjt556GvzligLSyZZ3D0ugNHkiw&s'),
(102, 'Yves', 'https://images.genius.com/44af859da1063eef4fea29c0e4bcf381.1000x1000x1.png'),
(103, 'Jennie', 'https://i.scdn.co/image/ab6761610000e5eba8e3627e392a1d8f539cb575'),
(104, 'Lisa', 'https://i.scdn.co/image/ab6761610000e5eb8543b9b2b5d153d37c46606d'),
(105, 'Nayeon', 'https://i.scdn.co/image/ab67616100005174059dd5ac2b3b1a7218063e92'),
(106, 'Yuqi', 'https://i.scdn.co/image/ab6761610000e5eb4ef67d743edcd93e1b519f66');

-- Insere os álbuns de K-pop.
INSERT INTO `albums` (`id`, `titulo`, `data_lancamento`, `artista_id`, `url_capa`) VALUES
(101, 'Strawberry Rush', '2024-06-25', 101, 'https://i.scdn.co/image/ab67616d0000b273cc681b43015ca45cd52e4625'),
(102, 'Soft Error', '2024-05-29', 102, 'https://i.scdn.co/image/ab67616d00001e0245aa9482be54a027b1a0b992'),
(103, 'Ruby', '2023-10-06', 103, 'https://i.scdn.co/image/ab67616d00001e024dbec3d7fe316be4a9e08f2d'),
(104, 'Alter Ego', '2024-06-27', 104, 'https://image-cdn-fa.spotifycdn.com/image/ab67706c0000da8482ce75c1c94a72ed08bd25dd'),
(105, 'NA', '2024-06-14', 105, 'https://i.scdn.co/image/ab67616d00001e0273f05e5c2f5ec5cd07c6b6d9'),
(106, 'YUQ1', '2024-04-23', 106, 'https://i.scdn.co/image/ab67616d00001e0273791f096b7c0037ba02a4f8');

-- Insere as músicas de K-pop, agora com 'url_clipe' e 'genero_id' (1 = K-pop).
INSERT INTO `musicas` (`id`, `titulo`, `duracao`, `url_clipe`, `album_id`, `genero_id`) VALUES
(101, 'Strawberry Rush', '00:03:08', 'https://www.youtube.com/watch?v=JRbXa1w-Pa8', 101, 1),
(102, 'White Cat', '00:03:25', 'https://www.youtube.com/watch?v=XuBE8ns_Ato', 102, 1),
(103, 'Love Hangover', '00:03:36', 'https://www.youtube.com/watch?v=23urWKmHS6o', 103, 1),
(104, 'NEW WOMAN', '00:03:11', 'https://www.youtube.com/watch?v=UxXY_hR_wzo', 104, 1),
(105, 'ABCD', '00:03:41', 'https://www.youtube.com/watch?v=oUZttxRcPZw', 105, 1),
(106, 'FREAK', '00:03:05', 'https://www.youtube.com/watch?v=UqJIBItJeyg', 106, 1);


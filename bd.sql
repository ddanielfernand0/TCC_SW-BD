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
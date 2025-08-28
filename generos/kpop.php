<?php
// Inicia a sessão no topo do script
session_start();
// Inclui a conexão com o banco.
require_once "../config.php"; 

// --- VARIÁVEIS DE CONTROLE ---
$playlists = [];
$liked_song_ids = [];
$followed_artists_ids = [];
$liked_album_ids = [];

// --- BUSCA DADOS DO USUÁRIO (SE ESTIVER LOGADO) ---
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $user_id = $_SESSION["id"];
    
    // Busca as playlists do usuário
    $sql_playlists = "SELECT id, nome FROM playlists WHERE usuario_id = ?";
    if ($stmt_playlists = mysqli_prepare($link, $sql_playlists)) {
        mysqli_stmt_bind_param($stmt_playlists, "i", $user_id);
        if (mysqli_stmt_execute($stmt_playlists)) {
            $result = mysqli_stmt_get_result($stmt_playlists);
            $playlists = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt_playlists);
    }

    // Busca IDs das músicas curtidas
    $sql_liked_songs = "SELECT musica_id FROM usuario_musicas_curtidas WHERE usuario_id = ?";
    if ($stmt_liked = mysqli_prepare($link, $sql_liked_songs)) {
        mysqli_stmt_bind_param($stmt_liked, "i", $user_id);
        if (mysqli_stmt_execute($stmt_liked)) {
            $result = mysqli_stmt_get_result($stmt_liked);
            while ($row = mysqli_fetch_assoc($result)) {
                $liked_song_ids[] = $row['musica_id'];
            }
        }
        mysqli_stmt_close($stmt_liked);
    }

    // Busca IDs dos artistas seguidos
    $sql_followed_artists = "SELECT artista_id FROM usuario_artistas_seguidos WHERE usuario_id = ?";
    if ($stmt_followed = mysqli_prepare($link, $sql_followed_artists)) {
        mysqli_stmt_bind_param($stmt_followed, "i", $user_id);
        if (mysqli_stmt_execute($stmt_followed)) {
            $result = mysqli_stmt_get_result($stmt_followed);
            while ($row = mysqli_fetch_assoc($result)) {
                $followed_artists_ids[] = $row['artista_id'];
            }
        }
        mysqli_stmt_close($stmt_followed);
    }

    // Busca IDs dos álbuns curtidos
    $sql_liked_albums = "SELECT album_id FROM usuario_albums_curtidos WHERE usuario_id = ?";
    if ($stmt_albums = mysqli_prepare($link, $sql_liked_albums)) {
        mysqli_stmt_bind_param($stmt_albums, "i", $user_id);
        if (mysqli_stmt_execute($stmt_albums)) {
            $result = mysqli_stmt_get_result($stmt_albums);
            while ($row = mysqli_fetch_assoc($result)) {
                $liked_album_ids[] = $row['album_id'];
            }
        }
        mysqli_stmt_close($stmt_albums);
    }
}

// --- PROCESSA AS AÇÕES DO FORMULÁRIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["loggedin"])) {
    $user_id = $_SESSION["id"];

    // AÇÃO: Adicionar/Criar Playlist
    if (isset($_POST['add_to_playlist']) || isset($_POST['create_playlist'])) {
        $musica_id = $_POST['musica_id'] ?? 0;
        $playlist_id = $_POST['playlist_id'] ?? null;

        if (isset($_POST['create_playlist']) && !empty(trim($_POST['new_playlist_name']))) {
            $new_playlist_name = trim($_POST['new_playlist_name']);
            $sql_create = "INSERT INTO playlists (nome, usuario_id) VALUES (?, ?)";
            if ($stmt_create = mysqli_prepare($link, $sql_create)) {
                mysqli_stmt_bind_param($stmt_create, "si", $new_playlist_name, $user_id);
                if (mysqli_stmt_execute($stmt_create)) {
                    $playlist_id = mysqli_insert_id($link);
                }
                mysqli_stmt_close($stmt_create);
            }
        }

        if ($playlist_id && $musica_id) {
            $sql_add = "INSERT INTO playlist_musicas (playlist_id, musica_id) VALUES (?, ?) ON DUPLICATE KEY UPDATE musica_id=musica_id";
            if ($stmt_add = mysqli_prepare($link, $sql_add)) {
                mysqli_stmt_bind_param($stmt_add, "ii", $playlist_id, $musica_id);
                mysqli_stmt_execute($stmt_add);
                mysqli_stmt_close($stmt_add);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    
    // AÇÃO: Curtir/Descurtir Música
    if (isset($_POST['like_song']) || isset($_POST['dislike_song'])) {
        $musica_id = $_POST['musica_id'] ?? 0;
        if($musica_id > 0) {
            if(isset($_POST['like_song'])) {
                $sql = "INSERT INTO usuario_musicas_curtidas (usuario_id, musica_id) VALUES (?, ?)";
            } else {
                $sql = "DELETE FROM usuario_musicas_curtidas WHERE usuario_id = ? AND musica_id = ?";
            }
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $musica_id);
                if(!mysqli_stmt_execute($stmt)){
                    die('Erro ao executar a query de curtir/descurtir: ' . mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // AÇÃO: Seguir/Deixar de Seguir Artista
    if (isset($_POST['follow_artist']) || isset($_POST['unfollow_artist'])) {
        $artista_id = $_POST['artista_id'] ?? 0;
        if ($artista_id > 0) {
            if(isset($_POST['follow_artist'])) {
                $sql = "INSERT INTO usuario_artistas_seguidos (usuario_id, artista_id) VALUES (?, ?)";
            } else {
                $sql = "DELETE FROM usuario_artistas_seguidos WHERE usuario_id = ? AND artista_id = ?";
            }
            if($stmt = mysqli_prepare($link, $sql)){
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $artista_id);
                if(!mysqli_stmt_execute($stmt)){
                    die('Erro ao executar a query de seguir/deixar de seguir: ' . mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // AÇÃO: Curtir/Descurtir Álbum
    if (isset($_POST['like_album']) || isset($_POST['dislike_album'])) {
        $album_id = $_POST['album_id'] ?? 0;
        if($album_id > 0) {
            if(isset($_POST['like_album'])) {
                $sql = "INSERT INTO usuario_albums_curtidos (usuario_id, album_id) VALUES (?, ?)";
            } else {
                $sql = "DELETE FROM usuario_albums_curtidos WHERE usuario_id = ? AND album_id = ?";
            }
            if($stmt = mysqli_prepare($link, $sql)) {
                mysqli_stmt_bind_param($stmt, "ii", $user_id, $album_id);
                if(!mysqli_stmt_execute($stmt)){
                    die('Erro ao executar a query de curtir/descurtir álbum: ' . mysqli_stmt_error($stmt));
                }
                mysqli_stmt_close($stmt);
            }
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// --- BUSCA DADOS DO BANCO DE DADOS ---
$musicas_em_alta = [];
$sql_musicas = "SELECT m.id, m.titulo, m.duracao, a.nome_artistico as artista, al.url_capa as imagem FROM musicas m JOIN albums al ON m.album_id = al.id JOIN artistas a ON al.artista_id = a.id WHERE m.genero_id = 1 ORDER BY m.id DESC LIMIT 4";
$result_musicas = mysqli_query($link, $sql_musicas);
if($result_musicas) {
    $musicas_em_alta = mysqli_fetch_all($result_musicas, MYSQLI_ASSOC);
}

$albums_em_alta = [];
$sql_albums = "SELECT DISTINCT al.id, al.titulo, al.url_capa as imagem, a.nome_artistico as artista FROM albums al JOIN artistas a ON al.artista_id = a.id JOIN musicas m ON m.album_id = al.id WHERE m.genero_id = 1 ORDER BY al.data_lancamento DESC LIMIT 6";
$result_albums = mysqli_query($link, $sql_albums);
if($result_albums) {
    $albums_em_alta = mysqli_fetch_all($result_albums, MYSQLI_ASSOC);
}

$artistas_em_alta = [];
$sql_artistas = "SELECT DISTINCT ar.id, ar.nome_artistico as nome, ar.url_foto_perfil as imagem, 'Artista' as tipo FROM artistas ar JOIN albums al ON al.artista_id = ar.id JOIN musicas m ON m.album_id = al.id WHERE m.genero_id = 1 ORDER BY ar.id DESC LIMIT 6";
$result_artistas = mysqli_query($link, $sql_artistas);
if($result_artistas) {
    $artistas_em_alta = mysqli_fetch_all($result_artistas, MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player - K-Pop Edition</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root { --kpop-pink: #ff007f; --kpop-dark-purple: #1a1a2e; --kpop-darker-purple: #161625; }
        body { font-family: 'Inter', sans-serif; background-color: var(--kpop-dark-purple); }
        .main-content-bg { background-color: var(--kpop-dark-purple); }
        .sidebar-bg { background-color: var(--kpop-darker-purple); }
        .main-content::-webkit-scrollbar { width: 8px; }
        .main-content::-webkit-scrollbar-track { background: #181818; }
        .main-content::-webkit-scrollbar-thumb { background: var(--kpop-pink); border-radius: 10px; }
        .sidebar-link { display: flex; align-items: center; padding: 0.75rem 1rem; border-radius: 0.5rem; color: #adb5bd; text-decoration: none; transition: all 0.2s ease-in-out; }
        .sidebar-link:hover { background-color: rgba(255, 0, 127, 0.1); color: #ffffff; }
        .sidebar-link.active { background-color: var(--kpop-pink); color: #ffffff; font-weight: 600; box-shadow: 0 0 15px rgba(255, 0, 127, 0.5); }
        .card-custom { background-color: rgba(22, 22, 37, 0.7); transition: all 0.3s ease; border: 1px solid transparent; }
        .card-custom:hover { background-color: rgba(30, 30, 50, 0.8); border-color: rgba(255, 0, 127, 0.3); }
        .card-artist:hover, .card-album:hover { transform: translateY(-5px); }
        .card-album .action-button { opacity: 0; transition: all 0.3s ease; transform: translateY(10px); }
        .card-album:hover .action-button { opacity: 1; transform: translateY(0); }
        .text-logo-accent { color: var(--kpop-pink); }
        .btn-primary { background-color: var(--kpop-pink); border-color: var(--kpop-pink); }
        .btn-primary:hover { background-color: #ff3399; border-color: #ff3399; }
        .kpop-text { color:rgb(255, 255, 255); text-shadow: 0 0 5px #ff3399, 0 0 10px #ff3399; font-style: italic; font-size: 2rem; align-self: center; }
        .border-secondary { border-color: rgba(255, 255, 255, 0.1) !important; }
        a { color: inherit; text-decoration: none; } 
    </style>
</head>
<body>

    <div class="d-flex vh-100">
        <!-- Sidebar de Navegação -->
        <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3 sidebar-bg" style="width: 280px;">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <a href="../index.php"><span class="fs-4 fw-bold">Music<span class="text-logo-accent">Player</span></span></a> 
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1"><a href="../index.php" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Início</a></li>
                <li class="nav-item mb-1"><a href="../buscar.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Buscar</a></li>
            </ul>
            <hr class="border-secondary">
            <h6 class="text-secondary text-uppercase small px-2 mb-2">Sua Biblioteca</h6>
            <ul class="nav nav-pills flex-column mb-auto">
               <li class="nav-item mb-1"><a href="../curtidas.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>Músicas Curtidas</a></li>
               <li class="nav-item mb-1"><a href="../albums_curtidos.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" class="me-3"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>Albums Curtidos</a></li>
               <li class="nav-item mb-1"><a href="../biblioteca.php?theme=kpop" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>Biblioteca</a></li>
            </ul>
            <div class="mt-auto"><a href="../criar_playlist.php?theme=kpop" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Criar playlist</a></div>
        </aside>

        <!-- Conteúdo Principal -->
        <div class="flex-grow-1 overflow-y-auto main-content main-content-bg">
            <div class="container-fluid p-4">
                <header class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary sticky-top main-content-bg" style="z-index: 1020;">
                    <button class="btn d-lg-none" type="button">...</button>
                    <div class="d-none d-lg-block"></div>
                    <div class="d-flex align-items-center">
                        <span class="kpop-text fw-bold me-3">KPOP</span>
                        <div class="d-flex align-items-center">
                            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                                <a href="../perfil.php" class="text-decoration-none d-flex align-items-center">
<img src="<?php echo htmlspecialchars($_SESSION["profile_pic"] ?? 'https://placehold.co/40x40/6f42c1/ffffff?text=U'); ?>" alt="Foto de Perfil" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                    <span class="text-white me-3 fw-semibold"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                                </a>
                                <a href="../logout.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-4">Sair</a>
                            <?php else: ?>
                                <a href="../login.php" class="btn btn-light rounded-pill fw-semibold px-4">Login</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>

                <main class="mt-4">
                    <section id="trending-songs">
                        <h2 class="fw-bolder mb-4">Músicas em Alta</h2>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                            <?php foreach ($musicas_em_alta as $musica): 
                                $is_liked = in_array($musica['id'], $liked_song_ids);
                            ?>
                            <div class="col">
                                <div class="card-custom p-3 rounded d-flex align-items-center">
                                    <a href="../musica.php?id=<?php echo $musica['id']; ?>" class="d-flex align-items-center text-truncate">
<img src="<?php echo htmlspecialchars($musica['imagem']); ?>" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;">
                                        <div class="flex-grow-1 text-truncate">
                                            <h3 class="h6 fw-bold text-truncate mb-1"><?php echo htmlspecialchars($musica['titulo']); ?></h3>
                                            <p class="small text-secondary mb-1"><?php echo htmlspecialchars($musica['artista']); ?></p>
                                            <span class="small text-body-secondary"><?php echo htmlspecialchars(date('i:s', strtotime($musica['duracao']))); ?></span>
                                        </div>
                                    </a>
                                    <div class="ms-auto d-flex">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="me-2">
                                            <input type="hidden" name="musica_id" value="<?php echo $musica['id']; ?>">
                                            <button type="submit" name="<?php echo $is_liked ? 'dislike_song' : 'like_song'; ?>" class="btn btn-link text-secondary p-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="<?php echo $is_liked ? 'currentColor' : 'none'; ?>" class="<?php echo $is_liked ? 'text-danger' : ''; ?>" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" stroke="currentColor" stroke-width="1"/></svg>
                                            </button>
                                        </form>
                                        <button class="btn btn-link text-secondary p-1" data-bs-toggle="modal" data-bs-target="#playlistModal" data-musica-id="<?php echo $musica['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <section id="trending-albums" class="mt-5">
                        <h2 class="fw-bolder mb-4">Álbuns em Alta</h2>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                            <?php foreach ($albums_em_alta as $album): 
                                $is_album_liked = in_array($album['id'], $liked_album_ids);
                            ?>
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <a href="../album.php?id=<?php echo $album['id']; ?>">
<img src="<?php echo htmlspecialchars($album['imagem']); ?>" class="card-img-top" alt="Capa do Álbum">  
                                    </a>
                                    <div class="action-button position-absolute bottom-0 end-0 me-2 mb-2">
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                            <input type="hidden" name="album_id" value="<?php echo $album['id']; ?>">
                                            <button type="submit" name="<?php echo $is_album_liked ? 'dislike_album' : 'like_album'; ?>" class="btn btn-primary rounded-circle p-3 shadow-lg">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="<?php echo $is_album_liked ? 'currentColor' : 'none'; ?>" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314" stroke="currentColor" stroke-width="1"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($album['titulo']); ?></h6>
                                        <p class="card-text small text-secondary"><?php echo htmlspecialchars($album['artista']); ?></p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                    
                    <section id="trending-artists" class="mt-5">
                        <h2 class="fw-bolder mb-4">Artistas em Alta</h2>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                            <?php foreach ($artistas_em_alta as $artista): 
                                $is_followed = in_array($artista['id'], $followed_artists_ids);
                            ?>
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body d-flex flex-column">
 <img src="<?php echo htmlspecialchars($artista['imagem']); ?>" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($artista['nome']); ?></h6>
                                        <p class="card-text small text-secondary"><?php echo htmlspecialchars($artista['tipo']); ?></p>
                                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mt-auto">
                                            <input type="hidden" name="artista_id" value="<?php echo $artista['id']; ?>">
                                            <button type="submit" name="<?php echo $is_followed ? 'unfollow_artist' : 'follow_artist'; ?>" class="btn btn-sm <?php echo $is_followed ? 'btn-light' : 'btn-outline-light'; ?> w-100" <?php if(!isset($_SESSION["loggedin"])) echo 'disabled'; ?>>
                                                <?php echo $is_followed ? 'Seguindo' : 'Seguir'; ?>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <!-- Modal de Adicionar à Playlist -->
    <div class="modal fade" id="playlistModal" tabindex="-1" aria-labelledby="playlistModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sidebar-bg border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title" id="playlistModalLabel">Adicionar à playlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="mb-4">
                            <input type="hidden" name="musica_id" id="modal_musica_id_create">
                            <div class="input-group">
                                <input type="text" name="new_playlist_name" class="form-control" placeholder="Nome da nova playlist" required>
                                <button class="btn btn-primary" type="submit" name="create_playlist">Criar e Adicionar</button>
                            </div>
                        </form>
                        <hr>
                        <h6 class="text-secondary mb-3">Ou adicione a uma playlist existente:</h6>
                        <div class="list-group">
                            <?php if(!empty($playlists)): ?>
                                <?php foreach($playlists as $playlist): ?>
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                        <input type="hidden" name="musica_id" class="modal_musica_id_add">
                                        <input type="hidden" name="playlist_id" value="<?php echo $playlist['id']; ?>">
                                        <button type="submit" name="add_to_playlist" class="list-group-item list-group-item-action bg-transparent border-secondary text-white">
                                            <?php echo htmlspecialchars($playlist['nome']); ?>
                                        </button>
                                    </form>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-secondary">Você ainda não tem nenhuma playlist.</p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Você precisa estar logado para adicionar músicas a uma playlist.</p>
                        <a href="../login.php" class="btn btn-primary w-100">Fazer Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var playlistModal = document.getElementById('playlistModal');
            playlistModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var musicaId = button.getAttribute('data-musica-id');
                var musicaIdInputCreate = playlistModal.querySelector('#modal_musica_id_create');
                musicaIdInputCreate.value = musicaId;
                var musicaIdInputsAdd = playlistModal.querySelectorAll('.modal_musica_id_add');
                musicaIdInputsAdd.forEach(function(input) {
                    input.value = musicaId;
                });
            });
        });
    </script>
</body>
</html>

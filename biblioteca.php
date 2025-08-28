<?php
// Inicia a sessão no topo do script
session_start();
// Inclui a conexão com o banco.
require_once "config.php"; 

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// --- LÓGICA DO TEMA ---
$allowed_themes = ['kpop', 'pop', 'dreampop', 'rock'];
$theme = isset($_GET['theme']) && in_array($_GET['theme'], $allowed_themes) ? $_GET['theme'] : 'kpop';
$theme_css_path = "css/" . $theme . "-theme.css";

$user_id = $_SESSION["id"];

// --- BUSCA DADOS PARA EXIBIR NA PÁGINA ---

// Playlists do usuário
$playlists = [];
$sql_playlists = "SELECT id, nome, descricao FROM playlists WHERE usuario_id = ?";
if($stmt_playlists = mysqli_prepare($link, $sql_playlists)){
    mysqli_stmt_bind_param($stmt_playlists, "i", $user_id);
    if(mysqli_stmt_execute($stmt_playlists)){
        $result = mysqli_stmt_get_result($stmt_playlists);
        $playlists = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt_playlists);
}

// Álbuns curtidos
$albums_curtidos = [];
$sql_albums = "SELECT al.id, al.titulo, al.url_capa as imagem, a.nome_artistico as artista 
               FROM usuario_albums_curtidos uac
               JOIN albums al ON uac.album_id = al.id
               JOIN artistas a ON al.artista_id = a.id
               WHERE uac.usuario_id = ?
               ORDER BY uac.data_curtida DESC";
if($stmt_albums = mysqli_prepare($link, $sql_albums)){
    mysqli_stmt_bind_param($stmt_albums, "i", $user_id);
    if(mysqli_stmt_execute($stmt_albums)){
        $result = mysqli_stmt_get_result($stmt_albums);
        $albums_curtidos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt_albums);
}

// Artistas seguidos
$artistas_seguidos = [];
$sql_artistas = "SELECT ar.id, ar.nome_artistico, ar.url_foto_perfil FROM usuario_artistas_seguidos uas JOIN artistas ar ON uas.artista_id = ar.id WHERE uas.usuario_id = ?";
if($stmt_artistas = mysqli_prepare($link, $sql_artistas)){
    mysqli_stmt_bind_param($stmt_artistas, "i", $user_id);
    if(mysqli_stmt_execute($stmt_artistas)){
        $result = mysqli_stmt_get_result($stmt_artistas);
        $artistas_seguidos = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt_artistas);
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sua Biblioteca - Music Player</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Carrega o CSS do tema dinamicamente -->
    <?php if(file_exists($theme_css_path)): ?>
        <link href="<?php echo $theme_css_path; ?>" rel="stylesheet">
    <?php else: ?>
        <link href="css/kpop-theme.css" rel="stylesheet">
    <?php endif; ?>

    <style>
        .nav-tabs .nav-link { color: #adb5bd; border: none; }
        .nav-tabs .nav-link.active { color: #fff; background-color: var(--theme-bg-main, #1a1a2e); border-bottom: 2px solid var(--theme-color-primary, #ff007f); }
        .card-custom:hover { transform: translateY(-5px); }
    </style>
</head>
<body>

    <div class="d-flex vh-100">
        <!-- Sidebar -->
        <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3 sidebar-bg" style="width: 280px;">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <a href="index.php"><span class="fs-4 fw-bold">Music<span class="text-logo-accent">Player</span></span></a> 
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1"><a href="generos/<?php echo $theme; ?>.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Início</a></li>
                <li class="nav-item mb-1"><a href="buscar.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Buscar</a></li>
            </ul>
            <hr class="border-secondary">
            <h6 class="text-secondary text-uppercase small px-2 mb-2">Sua Biblioteca</h6>
            <ul class="nav nav-pills flex-column mb-auto">
               <li class="nav-item mb-1"><a href="curtidas.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>Músicas Curtidas</a></li>
               <li class="nav-item mb-1"><a href="albums_curtidos.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" class="me-3"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>Albums Curtidos</a></li>
               <li class="nav-item mb-1"><a href="biblioteca.php?theme=<?php echo $theme; ?>" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>Biblioteca</a></li>
            </ul>
            <div class="mt-auto"><a href="criar_playlist.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Criar playlist</a></div>
        </aside>

        <!-- Conteúdo Principal -->
        <div class="flex-grow-1 overflow-y-auto main-content main-content-bg">
            <div class="container-fluid p-4">
                <header class="d-flex justify-content-end align-items-center py-3 border-bottom border-secondary sticky-top main-content-bg" style="z-index: 1020;">
                    <div class="d-flex align-items-center">
                        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <a href="perfil.php" class="text-decoration-none d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($_SESSION["profile_pic"] ?? 'https://placehold.co/40x40/6f42c1/ffffff?text=U'); ?>" alt="Foto de Perfil" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <span class="text-white me-3 fw-semibold"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                            </a>
                            <a href="logout.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-4">Sair</a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-light rounded-pill fw-semibold px-4">Login</a>
                        <?php endif; ?>
                    </div>
                </header>

                <main class="mt-4">
                    <h1 class="fw-bolder mb-4">Sua Biblioteca</h1>

                    <ul class="nav nav-tabs mb-4" id="libraryTab" role="tablist">
                        <li class="nav-item" role="presentation"><button class="nav-link active" id="playlists-tab" data-bs-toggle="tab" data-bs-target="#playlists" type="button" role="tab">Playlists</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="albums-tab" data-bs-toggle="tab" data-bs-target="#albums" type="button" role="tab">Álbuns Curtidos</button></li>
                        <li class="nav-item" role="presentation"><button class="nav-link" id="artists-tab" data-bs-toggle="tab" data-bs-target="#artists" type="button" role="tab">Artistas</button></li>
                    </ul>

                    <div class="tab-content" id="libraryTabContent">
                        <!-- Aba de Playlists -->
                        <div class="tab-pane fade show active" id="playlists" role="tabpanel">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                                <?php if(!empty($playlists)): foreach($playlists as $playlist): ?>
                                <div class="col"><div class="card bg-dark card-custom"><div class="card-body"><h5 class="card-title text-truncate"><?php echo htmlspecialchars($playlist['nome']); ?></h5><p class="card-text text-secondary text-truncate"><?php echo htmlspecialchars($playlist['descricao'] ?? 'Sem descrição'); ?></p></div></div></div>
                                <?php endforeach; else: ?>
                                <p class="text-secondary col-12">Você ainda não criou nenhuma playlist.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Aba de Álbuns Curtidos -->
                        <div class="tab-pane fade" id="albums" role="tabpanel">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                                <?php if(!empty($albums_curtidos)): foreach($albums_curtidos as $album): ?>
                                <div class="col"><a href="album.php?id=<?php echo $album['id']; ?>&theme=<?php echo $theme; ?>" class="card card-custom h-100"><img src="<?php echo htmlspecialchars($album['imagem']); ?>" class="card-img-top" alt="Capa"><div class="card-body"><h6 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($album['titulo']); ?></h6><p class="card-text small text-secondary"><?php echo htmlspecialchars($album['artista']); ?></p></div></a></div>
                                <?php endforeach; else: ?>
                                <p class="text-secondary col-12">Você ainda não curtiu nenhum álbum.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Aba de Artistas Seguidos -->
                        <div class="tab-pane fade" id="artists" role="tabpanel">
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                                <?php if(!empty($artistas_seguidos)): foreach($artistas_seguidos as $artista): ?>
                                <div class="col"><div class="card card-custom h-100 text-center"><div class="card-body"><img src="<?php echo htmlspecialchars($artista['url_foto_perfil']); ?>" class="rounded-circle img-fluid p-3 mx-auto" style="width: 150px; height: 150px; object-fit: cover;"><h6 class="card-title fw-bold text-truncate mt-2"><?php echo htmlspecialchars($artista['nome_artistico']); ?></h6></div></div></div>
                                <?php endforeach; else: ?>
                                <p class="text-secondary col-12">Você ainda não segue nenhum artista.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

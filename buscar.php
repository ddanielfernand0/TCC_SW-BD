<?php
// Inicia a sessão no topo do script
session_start();
// Inclui a conexão com o banco.
require_once "config.php"; 

// --- LÓGICA DO TEMA ---
// Define os temas permitidos para segurança
$allowed_themes = ['kpop', 'pop', 'dreampop', 'rock'];
// Pega o tema da URL, ou usa 'kpop' como padrão se nada for passado
$theme = isset($_GET['theme']) && in_array($_GET['theme'], $allowed_themes) ? $_GET['theme'] : 'kpop';
// Monta o caminho para o arquivo CSS
$theme_css_path = "css/" . $theme . "-theme.css";

// --- VARIÁVEIS DE CONTROLE ---
$playlists = [];
$search_query = '';
$musicas_encontradas = [];
$albums_encontrados = [];
$artistas_encontrados = [];
$has_searched = false;

// --- BUSCA AS PLAYLISTS DO USUÁRIO (SE ESTIVER LOGADO) ---
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $user_id = $_SESSION["id"];
    $sql_playlists = "SELECT id, nome FROM playlists WHERE usuario_id = ?";
    if ($stmt_playlists = mysqli_prepare($link, $sql_playlists)) {
        mysqli_stmt_bind_param($stmt_playlists, "i", $user_id);
        if (mysqli_stmt_execute($stmt_playlists)) {
            $result = mysqli_stmt_get_result($stmt_playlists);
            $playlists = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt_playlists);
    }
}

// --- LÓGICA DA BUSCA ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['q'])) {
    $search_query = trim($_GET['q']);
    if (!empty($search_query)) {
        $has_searched = true;
        $param_query = "%" . $search_query . "%";

        // Busca por Músicas
        $sql_musicas = "SELECT m.id, m.titulo, m.duracao, a.nome_artistico as artista, al.url_capa as imagem FROM musicas m JOIN albums al ON m.album_id = al.id JOIN artistas a ON al.artista_id = a.id WHERE m.titulo LIKE ?";
        if($stmt = mysqli_prepare($link, $sql_musicas)){
            mysqli_stmt_bind_param($stmt, "s", $param_query);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $musicas_encontradas = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }

        // Busca por Álbuns
        $sql_albums = "SELECT al.id, al.titulo, al.url_capa as imagem, a.nome_artistico as artista FROM albums al JOIN artistas a ON al.artista_id = a.id WHERE al.titulo LIKE ?";
        if($stmt = mysqli_prepare($link, $sql_albums)){
            mysqli_stmt_bind_param($stmt, "s", $param_query);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $albums_encontrados = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }

        // Busca por Artistas
        $sql_artistas = "SELECT id, nome_artistico as nome, url_foto_perfil as imagem, 'Artista' as tipo FROM artistas WHERE nome_artistico LIKE ?";
        if($stmt = mysqli_prepare($link, $sql_artistas)){
            mysqli_stmt_bind_param($stmt, "s", $param_query);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $artistas_encontrados = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_stmt_close($stmt);
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar - Music Player</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Carrega o CSS do tema dinamicamente -->
    <?php if(file_exists($theme_css_path)): ?>
        <link href="<?php echo $theme_css_path; ?>" rel="stylesheet">
    <?php else: ?>
        <!-- Fallback para um tema padrão caso o arquivo não exista -->
        <link href="css/kpop-theme.css" rel="stylesheet">
    <?php endif; ?>

    <style>
        /* Estilos específicos da página que não mudam com o tema */
        .form-control { background-color: #282828; border-color: #444; }
        .form-control:focus { background-color: #282828; border-color: var(--theme-color-primary, #ff007f); box-shadow: 0 0 0 0.25rem rgba(255, 0, 127, 0.25); }
    </style>
</head>
<body>

    <div class="d-flex vh-100">
        <!-- Sidebar de Navegação -->
        <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3 sidebar-bg" style="width: 280px;">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <a href="index.php"><span class="fs-4 fw-bold">Music<span class="text-logo-accent">Player</span></span></a> 
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1"><a href="<?php echo $theme; ?>.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Início</a></li>
                <li class="nav-item mb-1"><a href="buscar.php?theme=<?php echo $theme; ?>" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Buscar</a></li>
            </ul>
            <hr class="border-secondary">
            <h6 class="text-secondary text-uppercase small px-2 mb-2">Sua Biblioteca</h6>
            <ul class="nav nav-pills flex-column mb-auto">
               <li class="nav-item mb-1"><a href="curtidas.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>Músicas Curtidas</a></li>
               <li class="nav-item mb-1"><a href="albums_curtidos.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" class="me-3"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>Albums Curtidos</a></li>
            </ul>
            <div class="mt-auto"><a href="#" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>Criar playlist</a></div>
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
                    <h1 class="fw-bolder mb-4">Buscar</h1>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="get" class="mb-5">
                        <input type="hidden" name="theme" value="<?php echo $theme; ?>">
                        <div class="input-group">
                            <input type="text" name="q" class="form-control form-control-lg" placeholder="O que você quer ouvir?" value="<?php echo htmlspecialchars($search_query); ?>">
                            <button class="btn btn-primary" type="submit">Buscar</button>
                        </div>
                    </form>

                    <?php if($has_searched): ?>
                        <!-- Seção de Músicas -->
                        <section id="search-songs">
                            <h2 class="fw-bolder mb-4">Músicas</h2>
                            <?php if(!empty($musicas_encontradas)): ?>
                                <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                                    <?php foreach ($musicas_encontradas as $musica): ?>
                                    <div class="col">
                                        <div class="card-custom p-3 rounded d-flex align-items-center">
                                            <a href="musica.php?id=<?php echo $musica['id']; ?>&theme=<?php echo $theme; ?>" class="d-flex align-items-center text-truncate">
                                                <img src="<?php echo htmlspecialchars($musica['imagem']); ?>" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;">
                                                <div class="flex-grow-1 text-truncate">
                                                    <h3 class="h6 fw-bold text-truncate mb-1"><?php echo htmlspecialchars($musica['titulo']); ?></h3>
                                                    <p class="small text-secondary mb-1"><?php echo htmlspecialchars($musica['artista']); ?></p>
                                                    <span class="small text-body-secondary"><?php echo htmlspecialchars(date('i:s', strtotime($musica['duracao']))); ?></span>
                                                </div>
                                            </a>
                                            <button class="btn btn-link text-secondary ms-auto" data-bs-toggle="modal" data-bs-target="#playlistModal" data-musica-id="<?php echo $musica['id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-secondary">Nenhuma música encontrada para "<?php echo htmlspecialchars($search_query); ?>".</p>
                            <?php endif; ?>
                        </section>

                        <!-- Seção de Álbuns -->
                        <section id="search-albums" class="mt-5">
                            <h2 class="fw-bolder mb-4">Álbuns</h2>
                            <?php if(!empty($albums_encontrados)): ?>
                                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                                    <?php foreach ($albums_encontrados as $album): ?>
                                    <div class="col">
                                        <a href="album.php?id=<?php echo $album['id']; ?>&theme=<?php echo $theme; ?>" class="card card-custom card-album h-100">
                                            <img src="<?php echo htmlspecialchars($album['imagem']); ?>" class="card-img-top" alt="Capa do Álbum">
                                            <div class="card-body">
                                                <h6 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($album['titulo']); ?></h6>
                                                <p class="card-text small text-secondary"><?php echo htmlspecialchars($album['artista']); ?></p>
                                            </div>
                                        </a>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-secondary">Nenhum álbum encontrado para "<?php echo htmlspecialchars($search_query); ?>".</p>
                            <?php endif; ?>
                        </section>

                        <!-- Seção de Artistas -->
                        <section id="search-artists" class="mt-5">
                            <h2 class="fw-bolder mb-4">Artistas</h2>
                             <?php if(!empty($artistas_encontrados)): ?>
                                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                                    <?php foreach ($artistas_encontrados as $artista): ?>
                                    <div class="col">
                                        <div class="card card-custom card-artist h-100 text-center">
                                            <div class="card-body">
                                                <img src="<?php echo htmlspecialchars($artista['imagem']); ?>" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                                <h6 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($artista['nome']); ?></h6>
                                                <p class="card-text small text-secondary"><?php echo htmlspecialchars($artista['tipo']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-secondary">Nenhum artista encontrado para "<?php echo htmlspecialchars($search_query); ?>".</p>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </div>

    <!-- Modal de Adicionar à Playlist -->
    <div class="modal fade" id="playlistModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content sidebar-bg border-secondary">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Adicionar à playlist</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <form action="buscar.php?q=<?php echo urlencode($search_query); ?>&theme=<?php echo $theme; ?>" method="post" class="mb-4">
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
                                    <form action="buscar.php?q=<?php echo urlencode($search_query); ?>&theme=<?php echo $theme; ?>" method="post">
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
                        <a href="login.php" class="btn btn-primary w-100">Fazer Login</a>
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

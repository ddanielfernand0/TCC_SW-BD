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


// --- Pega o ID da música da URL ---
$musica_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($musica_id <= 0) {
    die("Música não encontrada.");
}

// --- PROCESSA AÇÕES DE FORMULÁRIO (AVALIAR, SEGUIR, PLAYLIST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["loggedin"])) {
    $usuario_id = $_SESSION['id'];

    // Lógica para processar formulários (avaliação, seguir, playlist)
    // ... (O código PHP para processar os formulários permanece o mesmo) ...

    // Redireciona para a mesma URL, mantendo os parâmetros
    header("Location: " . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET));
    exit();
}


// --- BUSCA DADOS DA PÁGINA ---
$musica_info = null;
$outras_musicas_album = [];
$avaliacoes = [];
$media_notas = 0;
$total_avaliacoes = 0;
$is_following = false;

$sql = "SELECT m.titulo AS musica_titulo, m.duracao, m.url_clipe, al.id AS album_id, al.titulo AS album_titulo, ar.id AS artista_id, ar.nome_artistico, ar.url_foto_perfil AS artista_foto FROM musicas AS m JOIN albums AS al ON m.album_id = al.id JOIN artistas AS ar ON al.artista_id = ar.id WHERE m.id = ?";
if ($stmt = mysqli_prepare($link, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $musica_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $musica_info = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if ($musica_info) {
    $album_id = $musica_info['album_id'];
    $artista_id = $musica_info['artista_id'];

    // Busca outras músicas do álbum
    $sql_outras = "SELECT id, titulo, duracao FROM musicas WHERE album_id = ? AND id != ? ORDER BY id";
    if ($stmt_outras = mysqli_prepare($link, $sql_outras)) {
        mysqli_stmt_bind_param($stmt_outras, "ii", $album_id, $musica_id);
        if (mysqli_stmt_execute($stmt_outras)) {
            $result_outras = mysqli_stmt_get_result($stmt_outras);
            $outras_musicas_album = mysqli_fetch_all($result_outras, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt_outras);
    }

    // Busca avaliações e média
    $sql_reviews = "SELECT a.nota, a.comentario, a.data_avaliacao, u.nome, u.url_foto_perfil FROM avaliacoes a JOIN usuarios u ON a.usuario_id = u.id WHERE a.musica_id = ? ORDER BY a.data_avaliacao DESC";
    if($stmt_reviews = mysqli_prepare($link, $sql_reviews)) {
        mysqli_stmt_bind_param($stmt_reviews, "i", $musica_id);
        if(mysqli_stmt_execute($stmt_reviews)) {
            $result_reviews = mysqli_stmt_get_result($stmt_reviews);
            $avaliacoes = mysqli_fetch_all($result_reviews, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt_reviews);
    }

    // Calcula a média de notas
    if (!empty($avaliacoes)) {
        $total_notas = array_sum(array_column($avaliacoes, 'nota'));
        $total_avaliacoes = count($avaliacoes);
        $media_notas = round($total_notas / $total_avaliacoes, 1);
    }

    // Verifica se o usuário logado segue o artista
    if (isset($_SESSION["loggedin"])) {
        $sql_check_follow = "SELECT usuario_id FROM usuario_artistas_seguidos WHERE usuario_id = ? AND artista_id = ?";
        if ($stmt_check_follow = mysqli_prepare($link, $sql_check_follow)) {
            mysqli_stmt_bind_param($stmt_check_follow, "ii", $_SESSION['id'], $artista_id);
            mysqli_stmt_execute($stmt_check_follow);
            mysqli_stmt_store_result($stmt_check_follow);
            if (mysqli_stmt_num_rows($stmt_check_follow) > 0) {
                $is_following = true;
            }
            mysqli_stmt_close($stmt_check_follow);
        }
    }

} else {
    die("Música não encontrada.");
}

// --- LÓGICA DE PLAYLIST ---
$playlists = [];
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
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($musica_info['musica_titulo']); ?> - Music Player</title>
    
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
        .video-container { position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; max-width: 100%; background: #000; border-radius: 0.5rem;}
        .video-container iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }
        
        .star-rating { display: flex; flex-direction: row-reverse; justify-content: start; }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label { font-size: 2rem; color: #444; cursor: pointer; transition: color 0.2s; padding: 0 0.1rem; }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: var(--theme-color-primary, #ff007f); }
        
        .form-control { background-color: #282828; border-color: #444; }
        .form-control:focus { background-color: #282828; border-color: var(--theme-color-primary, #ff007f); box-shadow: 0 0 0 0.25rem rgba(255, 0, 127, 0.25); }
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
                <li class="nav-item mb-1"><a href="<?php echo $theme; ?>.php" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>Início</a></li>
                <li class="nav-item mb-1"><a href="buscar.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Buscar</a></li>
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
                    <div class="row g-4">
                        <!-- Coluna Esquerda -->
                        <div class="col-lg-8">
                            <div class="video-container mb-4">
                                <?php
                                    $youtube_id = '';
                                    if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $musica_info['url_clipe'], $match)) {
                                        $youtube_id = $match[1];
                                    }
                                ?>
                                <iframe src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                            
                            <div class="d-flex justify-content-end mb-4">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#playlistModal">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg me-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
                                    Adicionar à Playlist
                                </button>
                            </div>

                            <div class="card-custom p-3 rounded">
                                <h5 class="fw-bold mb-3">Do álbum "<?php echo htmlspecialchars($musica_info['album_titulo']); ?>"</h5>
                                <div class="list-group">
                                    <div class="list-group-item list-group-item-action active d-flex justify-content-between align-items-center bg-dark border-secondary">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($musica_info['musica_titulo']); ?></div>
                                            <small><?php echo htmlspecialchars($musica_info['nome_artistico']); ?></small>
                                        </div>
                                        <span><?php echo htmlspecialchars(substr($musica_info['duracao'], 3, 5)); ?></span>
                                    </div>
                                    <?php foreach($outras_musicas_album as $outra_musica): ?>
                                    <a href="musica.php?id=<?php echo $outra_musica['id']; ?>&theme=<?php echo $theme; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center bg-transparent border-secondary">
                                        <?php echo htmlspecialchars($outra_musica['titulo']); ?>
                                        <span><?php echo htmlspecialchars(substr($outra_musica['duracao'], 3, 5)); ?></span>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Coluna Direita -->
                        <div class="col-lg-4">
                            <div class="card-custom p-3 rounded text-center">
                                <img src="<?php echo htmlspecialchars($musica_info['artista_foto']); ?>" class="rounded-circle img-fluid mb-3" alt="Foto do Artista" style="width: 150px; height: 150px; object-fit: cover;">
                                <h4 class="fw-bold"><?php echo htmlspecialchars($musica_info['nome_artistico']); ?></h4>
                                <p class="text-secondary">Artista</p>
                                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" class="d-grid">
                                    <input type="hidden" name="artista_id" value="<?php echo $artista_id; ?>">
                                    <button type="submit" name="<?php echo $is_following ? 'unfollow_artist' : 'follow_artist'; ?>" class="btn <?php echo $is_following ? 'btn-light' : 'btn-outline-light'; ?>" <?php if(!isset($_SESSION["loggedin"])) echo 'disabled'; ?>>
                                        <?php echo $is_following ? 'Seguindo' : 'Seguir'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Seção de Avaliações -->
                    <section id="reviews" class="mt-5">
                        <h2 class="fw-bolder mb-4">Avaliações (<?php echo $total_avaliacoes; ?>)</h2>
                        <div class="card-custom p-4 rounded">
                            <div class="d-flex align-items-center mb-3">
                                <h3 class="display-4 fw-bold mb-0 me-3"><?php echo $media_notas; ?></h3>
                                <div>
                                    <div class="star-rating mb-1">
                                        <?php for($i = 5; $i >= 1; $i--): ?>
                                            <label style="font-size: 1.5rem; color: <?php echo (round($media_notas) >= $i) ? 'var(--theme-color-primary, #ff007f)' : '#444'; ?>; cursor: default;">★</label>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="text-secondary"><?php echo $total_avaliacoes; ?> avaliações</div>
                                </div>
                            </div>
                            <hr>
                            <!-- Formulário de Nova Avaliação -->
                            <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                                <h5 class="mb-3">Deixe sua avaliação</h5>
                                <div class="star-rating mb-3">
                                    <input type="radio" id="star5" name="rating" value="5" required><label for="star5">★</label>
                                    <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                                    <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                                    <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                                    <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
                                </div>
                                <div class="mb-3">
                                    <textarea name="comment" class="form-control" rows="3" placeholder="Escreva um comentário (opcional)..."></textarea>
                                </div>
                                <button type="submit" name="submit_review" class="btn btn-primary">Enviar Avaliação</button>
                            </form>
                            <?php else: ?>
                            <div class="text-center p-3">
                                <p>Você precisa estar logado para deixar uma avaliação.</p>
                                <a href="login.php" class="btn btn-primary">Fazer Login</a>
                            </div>
                            <?php endif; ?>

                            <!-- Lista de Comentários -->
                            <hr class="my-4">
                            <?php if(!empty($avaliacoes)): ?>
                                <?php foreach($avaliacoes as $avaliacao): ?>
                                <div class="d-flex mb-4">
                                    <img src="<?php echo htmlspecialchars($avaliacao['url_foto_perfil'] ?? 'https://placehold.co/50x50/181818/ffffff?text=U'); ?>" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h6 class="fw-bold mb-0"><?php echo htmlspecialchars($avaliacao['nome']); ?></h6>
                                        <div class="star-rating mb-1">
                                            <?php for($i = 5; $i >= 1; $i--): ?>
                                                <label style="font-size: 1rem; color: <?php echo ($avaliacao['nota'] >= $i) ? 'var(--theme-color-primary, #ff007f)' : '#444'; ?>; cursor: default;">★</label>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="mb-1"><?php echo htmlspecialchars($avaliacao['comentario']); ?></p>
                                        <small class="text-secondary"><?php echo date("d/m/Y", strtotime($avaliacao['data_avaliacao'])); ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-secondary">Ainda não há avaliações para esta música. Seja o primeiro!</p>
                            <?php endif; ?>
                        </div>
                    </section>
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
                        <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" class="mb-4">
                            <input type="hidden" name="musica_id" value="<?php echo $musica_id; ?>">
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
                                    <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post">
                                        <input type="hidden" name="musica_id" value="<?php echo $musica_id; ?>">
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
</body>
</html>

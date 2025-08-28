<?php
// Inicia a sessão no topo do script
session_start();
// Inclui a conexão com o banco.
require_once "config.php"; 

// --- LÓGICA DO TEMA ---
$allowed_themes = ['kpop', 'pop', 'dreampop', 'rock'];
$theme = isset($_GET['theme']) && in_array($_GET['theme'], $allowed_themes) ? $_GET['theme'] : 'kpop';
$theme_css_path = "css/" . $theme . "-theme.css";

// --- Pega o ID do álbum da URL ---
$album_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($album_id <= 0) {
    die("Álbum não encontrado.");
}

// --- PROCESSA AÇÕES DE FORMULÁRIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["loggedin"])) {
    $usuario_id = $_SESSION['id'];

    // AÇÃO: Enviar Avaliação do Álbum
    if (isset($_POST['submit_album_review'])) {
        $nota = $_POST['rating'] ?? 0;
        $comentario = trim($_POST['comment']);
        if ($nota > 0 && $nota <= 5) {
            // Usa INSERT ... ON DUPLICATE KEY UPDATE para inserir ou atualizar a avaliação
            $sql_review = "INSERT INTO album_avaliacoes (album_id, usuario_id, nota, comentario) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE nota=VALUES(nota), comentario=VALUES(comentario)";
            if ($stmt_review = mysqli_prepare($link, $sql_review)) {
                mysqli_stmt_bind_param($stmt_review, "iiis", $album_id, $usuario_id, $nota, $comentario);
                mysqli_stmt_execute($stmt_review);
                mysqli_stmt_close($stmt_review);
            }
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
    
    // (Outras ações como curtir música, adicionar à playlist, etc., podem ser adicionadas aqui se necessário)
}

// --- BUSCA DADOS DA PÁGINA ---
$album_info = null;
$musicas_do_album = [];
$avaliacoes = [];
$media_notas = 0;
$total_avaliacoes = 0;

// Busca informações do álbum
$sql_album = "SELECT al.titulo, al.url_capa, al.data_lancamento, ar.nome_artistico FROM albums al JOIN artistas ar ON al.artista_id = ar.id WHERE al.id = ?";
if ($stmt = mysqli_prepare($link, $sql_album)) {
    mysqli_stmt_bind_param($stmt, "i", $album_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $album_info = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if ($album_info) {
    // Busca músicas do álbum
    $sql_musicas = "SELECT id, titulo, duracao FROM musicas WHERE album_id = ? ORDER BY id";
    if ($stmt_musicas = mysqli_prepare($link, $sql_musicas)) {
        mysqli_stmt_bind_param($stmt_musicas, "i", $album_id);
        if (mysqli_stmt_execute($stmt_musicas)) {
            $result = mysqli_stmt_get_result($stmt_musicas);
            $musicas_do_album = mysqli_fetch_all($result, MYSQLI_ASSOC);
        }
        mysqli_stmt_close($stmt_musicas);
    }

    // Busca avaliações do álbum
    $sql_reviews = "SELECT a.nota, a.comentario, a.data_avaliacao, u.nome, u.url_foto_perfil FROM album_avaliacoes a JOIN usuarios u ON a.usuario_id = u.id WHERE a.album_id = ? ORDER BY a.data_avaliacao DESC";
    if($stmt_reviews = mysqli_prepare($link, $sql_reviews)) {
        mysqli_stmt_bind_param($stmt_reviews, "i", $album_id);
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
} else {
    die("Álbum não encontrado.");
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album_info['titulo']); ?> - Music Player</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Carrega o CSS do tema dinamicamente -->
    <?php if(file_exists($theme_css_path)): ?>
        <link href="<?php echo $theme_css_path; ?>" rel="stylesheet">
    <?php else: ?>
        <link href="css/kpop-theme.css" rel="stylesheet">
    <?php endif; ?>

    <style>
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
        <!-- Sidebar -->/
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
               <li class="nav-item mb-1"><a href="albums_curtidos.php?theme=<?php echo $theme; ?>" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" fill="currentColor" class="me-3"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/><path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/></svg>Albums Curtidos</a></li>
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
                    <div class="d-flex flex-column flex-md-row align-items-center mb-4">
                        <img src="<?php echo htmlspecialchars($album_info['url_capa']); ?>" class="rounded-3 me-md-4 mb-3 mb-md-0" style="width: 200px; height: 200px; object-fit: cover;">
                        <div class="text-center text-md-start">
                            <h1 class="display-5 fw-bolder mb-1"><?php echo htmlspecialchars($album_info['titulo']); ?></h1>
                            <h2 class="h4 text-secondary fw-normal"><?php echo htmlspecialchars($album_info['nome_artistico']); ?></h2>
                            <p class="text-secondary mb-0"><?php echo count($musicas_do_album); ?> músicas &bull; Lançado em <?php echo date("d/m/Y", strtotime($album_info['data_lancamento'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="list-group">
                        <?php if(!empty($musicas_do_album)): ?>
                            <?php foreach($musicas_do_album as $index => $musica): ?>
                                <div class="list-group-item bg-transparent border-secondary p-2 d-flex align-items-center">
                                    <span class="me-3 text-secondary"><?php echo $index + 1; ?></span>
                                    <a href="musica.php?id=<?php echo $musica['id']; ?>&theme=<?php echo $theme; ?>" class="d-flex align-items-center text-truncate flex-grow-1">
                                        <div class="text-truncate">
                                            <div class="fw-bold text-truncate"><?php echo htmlspecialchars($musica['titulo']); ?></div>
                                        </div>
                                    </a>
                                    <small class="text-secondary ms-auto me-3 d-none d-lg-block"><?php echo htmlspecialchars(date('i:s', strtotime($musica['duracao']))); ?></small>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-secondary text-center mt-5">Nenhuma música encontrada para este álbum.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Seção de Avaliações -->
                    <section id="reviews" class="mt-5">
                        <h2 class="fw-bolder mb-4">Avaliações do Álbum (<?php echo $total_avaliacoes; ?>)</h2>
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
                                <button type="submit" name="submit_album_review" class="btn btn-primary">Enviar Avaliação</button>
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
                                <p class="text-secondary">Ainda não há avaliações para este álbum. Seja o primeiro!</p>
                            <?php endif; ?>
                        </div>
                    </section>
                </main>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

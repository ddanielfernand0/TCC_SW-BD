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

// --- PROCESSA AÇÕES DE FORMULÁRIO (DESCURTIR, PLAYLIST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // AÇÃO: Descurtir uma música
    if (isset($_POST['dislike_song'])) {
        $musica_id_to_dislike = $_POST['musica_id'];
        $sql_dislike = "DELETE FROM usuario_musicas_curtidas WHERE usuario_id = ? AND musica_id = ?";
        if ($stmt_dislike = mysqli_prepare($link, $sql_dislike)) {
            mysqli_stmt_bind_param($stmt_dislike, "ii", $user_id, $musica_id_to_dislike);
            mysqli_stmt_execute($stmt_dislike);
            mysqli_stmt_close($stmt_dislike);
        }
        header("Location: " . $_SERVER['PHP_SELF'] . '?theme=' . $theme);
        exit();
    }
    
    // Lógica para adicionar à playlist (igual às outras páginas)
    // ... (código de adicionar/criar playlist aqui) ...
}


// --- BUSCA DADOS PARA EXIBIR NA PÁGINA ---
$musicas_curtidas = [];
$sql_musicas = "SELECT m.id, m.titulo, m.duracao, a.nome_artistico as artista, al.titulo as album_titulo, al.url_capa as imagem 
                FROM usuario_musicas_curtidas umc
                JOIN musicas m ON umc.musica_id = m.id
                JOIN albums al ON m.album_id = al.id
                JOIN artistas a ON al.artista_id = a.id
                WHERE umc.usuario_id = ?
                ORDER BY umc.data_curtida DESC";

if ($stmt = mysqli_prepare($link, $sql_musicas)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $musicas_curtidas = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Músicas Curtidas - Music Player</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Carrega o CSS do tema dinamicamente -->
    <?php if(file_exists($theme_css_path)): ?>
        <link href="<?php echo $theme_css_path; ?>" rel="stylesheet">
    <?php else: ?>
        <!-- Fallback para um tema padrão caso o arquivo não exista -->
        <link href="css/kpop-theme.css" rel="stylesheet">
    <?php endif; ?>
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
                <li class="nav-item mb-1"><a href="buscar.php?theme=<?php echo $theme; ?>" class="sidebar-link"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>Buscar</a></li>
            </ul>
            <hr class="border-secondary">
            <h6 class="text-secondary text-uppercase small px-2 mb-2">Sua Biblioteca</h6>
            <ul class="nav nav-pills flex-column mb-auto">
               <li class="nav-item mb-1"><a href="curtidas.php?theme=<?php echo $theme; ?>" class="sidebar-link active"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>Músicas Curtidas</a></li>
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
                    <div class="d-flex align-items-center mb-4">
                        <div class="p-4 rounded-3" style="background-color: var(--theme-color-primary, #ff007f);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/></svg>
                        </div>
                        <div class="ms-4">
                            <h1 class="fw-bolder mb-0">Músicas Curtidas</h1>
                            <p class="text-secondary mb-0"><?php echo count($musicas_curtidas); ?> músicas</p>
                        </div>
                    </div>
                    
                    <div class="list-group">
                        <?php if(!empty($musicas_curtidas)): ?>
                            <?php foreach($musicas_curtidas as $musica): ?>
                                <div class="list-group-item bg-transparent border-secondary p-2 d-flex align-items-center">
                                    <a href="musica.php?id=<?php echo $musica['id']; ?>&theme=<?php echo $theme; ?>" class="d-flex align-items-center text-truncate flex-grow-1">
                                        <img src="<?php echo htmlspecialchars($musica['imagem']); ?>" alt="Capa" class="rounded me-3" style="width: 50px; height: 50px;">
                                        <div class="text-truncate">
                                            <div class="fw-bold text-truncate"><?php echo htmlspecialchars($musica['titulo']); ?></div>
                                            <small class="text-secondary text-truncate"><?php echo htmlspecialchars($musica['artista']); ?></small>
                                        </div>
                                    </a>
                                    <small class="text-secondary mx-3 d-none d-md-block"><?php echo htmlspecialchars($musica['album_titulo']); ?></small>
                                    <small class="text-secondary ms-auto me-3 d-none d-lg-block"><?php echo htmlspecialchars(date('i:s', strtotime($musica['duracao']))); ?></small>

                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?theme=<?php echo $theme; ?>" method="post" class="me-2">
                                        <input type="hidden" name="musica_id" value="<?php echo $musica['id']; ?>">
                                        <button type="submit" name="dislike_song" class="btn btn-link text-danger p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/></svg>
                                        </button>
                                    </form>

                                    <button class="btn btn-link text-secondary p-1" data-bs-toggle="modal" data-bs-target="#playlistModal" data-musica-id="<?php echo $musica['id']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-secondary text-center mt-5">Você ainda não curtiu nenhuma música.</p>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Modal de Adicionar à Playlist -->
    <!-- (O código do modal é o mesmo das outras páginas e pode ser colado aqui) -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

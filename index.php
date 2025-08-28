<?php
// Inicia a sessão no topo do script
session_start();

// Inclui o arquivo de configuração do banco de dados
require_once "config.php";

$playlists = [];
// Se o usuário estiver logado, busca suas playlists
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    $user_id = $_SESSION["id"];
    
    // Prepara uma declaração SELECT para buscar as playlists do usuário
    $sql = "SELECT id, nome, descricao FROM playlists WHERE usuario_id = ?";
    
    if ($stmt = mysqli_prepare($link, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            // Armazena os resultados em um array
            while ($row = mysqli_fetch_assoc($result)) {
                $playlists[] = $row;
            }
        } else {
            echo "Oops! Algo deu errado. Tente novamente mais tarde.";
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player - Início</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Estilos customizados - Tema Neutro com Roxo */
        :root {
            --neutral-purple: #6f42c1;
            --neutral-bg: #121212;
            --neutral-sidebar-bg: #000000;
            --neutral-card-bg: #181818;
            --neutral-card-hover: #282828;
            --neutral-border: #282828;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--neutral-sidebar-bg);
        }

        .main-content-bg {
            background-color: var(--neutral-bg);
        }
        
        .sidebar-bg {
            background-color: var(--neutral-sidebar-bg);
        }

        .main-content::-webkit-scrollbar { width: 8px; }
        .main-content::-webkit-scrollbar-track { background: #181818; }
        .main-content::-webkit-scrollbar-thumb { background: #555; border-radius: 10px; }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }
        .sidebar-link:hover { background-color: var(--neutral-card-hover); color: #ffffff; }
        .sidebar-link.active { background-color: var(--neutral-card-hover); color: #ffffff; font-weight: 600; }
        
        .text-logo-accent { color: var(--neutral-purple); }

        .btn-primary {
            background-color: var(--neutral-purple);
            border-color: var(--neutral-purple);
            color: #fff;
            font-weight: 600;
        }
        .btn-primary:hover { background-color: #59369a; border-color: #59369a; }

        .genre-card {
            border: 1px solid var(--neutral-border);
            overflow: hidden;
            position: relative;
            height: 250px;
            border-radius: 0.75rem;
            transition: all 0.4s ease;
        }
        .genre-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.4); border-color: var(--neutral-purple); }
        .genre-card .card-img { height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .genre-card:hover .card-img { transform: scale(1.1); }
        .genre-card .card-img-overlay { background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.1) 100%); display: flex; flex-direction: column; justify-content: flex-end; padding: 1.5rem; }
        .genre-card .btn { width: fit-content; }

        .carousel-item { height: 400px; }
        .carousel-item img { object-fit: cover; height: 100%; filter: brightness(0.6); }
        .carousel-caption { bottom: 3rem; text-align: left; left: 5%; }
        .carousel-caption h5 { font-size: 3rem; font-weight: 900; text-shadow: 2px 2px 10px rgba(0,0,0,0.8); }

        .card-custom {
            background-color: var(--neutral-card-bg);
            transition: all 0.3s ease;
            border: 1px solid var(--neutral-border);
        }
        .card-custom:hover { background-color: var(--neutral-card-hover); }
    </style>
</head>
<body>

    <div class="d-flex vh-100">
        <!-- Sidebar Fixa para telas grandes -->
        <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3 sidebar-bg" style="width: 280px;">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <span class="fs-4 fw-bold text-white">Music<span class="text-logo-accent">Player</span></span>
            </div>
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1"><a href="#" class="sidebar-link active">Início</a></li>
                <li class="nav-item mb-1"><a href="#" class="sidebar-link">Buscar</a></li>
            </ul>
            <hr>
            <h6 class="text-secondary text-uppercase small px-2 mb-2">Sua Biblioteca</h6>
            <ul class="nav nav-pills flex-column">
                 <li class="nav-item mb-1"><a href="#" class="sidebar-link">Músicas Curtidas</a></li>
                 <li class="nav-item mb-1"><a href="#" class="sidebar-link">Artistas</a></li>
            </ul>
            <div class="mt-auto"><a href="#" class="sidebar-link">Criar playlist</a></div>
        </aside>

        <!-- Conteúdo Principal -->
        <div class="flex-grow-1 overflow-y-auto main-content main-content-bg">
            <div class="container-fluid p-4">
                <header class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary">
                    <button class="btn d-lg-none" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                    </button>
                    <div class="d-none d-lg-block"></div>
                    <div class="d-flex align-items-center">
                        <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                            <!-- Mostra se o usuário ESTÁ logado -->
                            <a href="perfil.php" class="text-decoration-none d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($_SESSION["profile_pic"] ?? 'https://placehold.co/40x40/6f42c1/ffffff?text=U'); ?>" alt="Foto de Perfil" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                <span class="text-white me-3 fw-semibold"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                            </a>
                            <a href="logout.php" class="btn btn-outline-secondary rounded-pill fw-semibold px-4">Sair</a>
                        <?php else: ?>
                            <!-- Mostra se o usuário NÃO ESTÁ logado -->
                            <a href="login.php" class="btn btn-light rounded-pill fw-semibold px-4">Login</a>
                        <?php endif; ?>
                    </div>
                </header>

                <main class="mt-4">
                    
                    <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <!-- SEÇÃO PARA USUÁRIO LOGADO -->
                        <section class="mb-5">
                            <h1 class="fw-bolder mb-4">Suas Playlists</h1>
                            <?php if(!empty($playlists)): ?>
                                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                                    <?php foreach($playlists as $playlist): ?>
                                    <div class="col">
                                        <div class="card text-white genre-card">
                                            <img src="https://placehold.co/600x400/181818/6f42c1?text=PLAY" class="card-img" alt="Playlist">
                                            <div class="card-img-overlay">
                                                <h5 class="card-title fs-4 fw-bolder"><?php echo htmlspecialchars($playlist['nome']); ?></h5>
                                                <p class="card-text small"><?php echo htmlspecialchars($playlist['descricao'] ?? ''); ?></p>
                                                <a href="#" class="btn btn-primary fw-bold mt-2">Ouvir</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <!-- Mensagem para criar a primeira playlist -->
                                <div class="card-custom p-4 p-md-5 rounded-3 text-center">
                                    <h2 class="fw-bolder">Sua biblioteca está vazia</h2>
                                    <p class="lead text-secondary mt-2 mb-4">Que tal começar criando sua primeira playlist?</p>
                                    <a href="#" class="btn btn-primary btn-lg rounded-pill px-5">Criar Playlist</a>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php else: ?>
                        <!-- SEÇÃO PARA VISITANTES -->
                        <section class="mb-5">
                            <div class="card-custom p-4 p-md-5 rounded-3 text-center">
                                <h1 class="fw-bolder">Bem-vindo ao MusicPlayer</h1>
                                <p class="lead text-secondary mt-2 mb-4">Crie uma conta para salvar suas músicas favoritas, montar playlists e seguir artistas.</p>
                                <a href="login.php" class="btn btn-primary btn-lg rounded-pill px-5">Criar Conta Grátis</a>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Carrossel -->
                    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded-3">
                            <div class="carousel-item active"><img src="https://placehold.co/1200x400/f8f9fa/000?text=+" class="d-block w-100" alt="..."><div class="carousel-caption"><h5>Energia Pop</h5><a href="pop.php" class="btn btn-light fw-bold">Explore Agora</a></div></div>
                            <div class="carousel-item"><img src="https://placehold.co/1200x400/6c757d/fff?text=+" class="d-block w-100" alt="..."><div class="carousel-caption"><h5>Vibes Dreampop</h5><a href="dreampop.php" class="btn btn-light fw-bold">Descubra</a></div></div>
                            <div class="carousel-item"><img src="https://placehold.co/1200x400/343a40/fff?text=+" class="d-block w-100" alt="..."><div class="carousel-caption"><h5>Atitude Pop Rock</h5><a href="rock.php" class="btn btn-light fw-bold">Sinta o Som</a></div></div>
                        </div>
                    </div>

                    <!-- Gêneros -->
                    <section class="mt-5">
                        <h2 class="fw-bolder mb-4">Navegue por Gêneros</h2>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                            <div class="col"><div class="card text-white genre-card"><img src="https://placehold.co/600x400/f8f9fa/000?text=POP" class="card-img" alt="Pop"><div class="card-img-overlay"><h5 class="card-title fs-2 fw-bolder">Pop</h5><a href="pop.php" class="btn btn-light fw-bold mt-2">Explorar</a></div></div></div>
                            <div class="col"><div class="card text-white genre-card"><img src="https://placehold.co/600x400/adb5bd/000?text=K-POP" class="card-img" alt="K-Pop"><div class="card-img-overlay"><h5 class="card-title fs-2 fw-bolder">K-Pop</h5><a href="generos/kpop.php" class="btn btn-light fw-bold mt-2">Explorar</a></div></div></div>
                            <div class="col"><div class="card text-white genre-card"><img src="https://placehold.co/600x400/6c757d/fff?text=DREAMPOP" class="card-img" alt="Dreampop"><div class="card-img-overlay"><h5 class="card-title fs-2 fw-bolder">Dreampop</h5><a href="dreampop.php" class="btn btn-light fw-bold mt-2">Explorar</a></div></div></div>
                            <div class="col"><div class="card text-white genre-card"><img src="https://placehold.co/600x400/343a40/fff?text=POP+ROCK" class="card-img" alt="Rock"><div class="card-img-overlay"><h5 class="card-title fs-2 fw-bolder">Pop Rock</h5><a href="rock.php" class="btn btn-light fw-bold mt-2">Explorar</a></div></div></div>
                        </div>
                    </section>

                    <!-- Novos Lançamentos -->
                    <section class="mt-5">
                        <h2 class="fw-bolder mb-4">Novos Lançamentos</h2>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                            <div class="col"><div class="card-custom p-3 rounded d-flex align-items-center"><img src="https://cdn-images.dzcdn.net/images/cover/53c2e22988a2a3a31c65fab33fbb1287/0x1900-000000-80-0-0.jpg" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;"><div class="flex-grow-1 text-truncate"><h3 class="h6 fw-bold text-truncate mb-1">Strawberry Rush</h3><p class="small text-secondary mb-1">Chuu</p></div></div></div>
                            <div class="col"><div class="card-custom p-3 rounded d-flex align-items-center"><img src="https://i.scdn.co/image/ab67616d0000b273c428f67b4a9b7e1114dfc117" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;"><div class="flex-grow-1 text-truncate"><h3 class="h6 fw-bold text-truncate mb-1">Washing Machine Heart</h3><p class="small text-secondary mb-1">Mitski</p></div></div></div>
                            <div class="col"><div class="card-custom p-3 rounded d-flex align-items-center"><img src="https://upload.wikimedia.org/wikipedia/pt/0/0a/Lady_Gaga_-_Mayhem.jpg" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;"><div class="flex-grow-1 text-truncate"><h3 class="h6 fw-bold text-truncate mb-1">MAYHEM</h3><p class="small text-secondary mb-1">Lady Gaga</p></div></div></div>
                            <div class="col"><div class="card-custom p-3 rounded d-flex align-items-center"><img src="https://m.media-amazon.com/images/I/81fxuK+kFsL.jpg" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;"><div class="flex-grow-1 text-truncate"><h3 class="h6 fw-bold text-truncate mb-1">Money For Nothing</h3><p class="small text-secondary mb-1">Dire Straits</p></div></div></div>
                        </div>
                    </section>
                </main>

                <footer class="mt-5 py-4 border-top border-secondary text-center text-secondary">
                    <p class="small">&copy; 2024 MusicPlayer. Todos os direitos reservados.</p>
                </footer>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

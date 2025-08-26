<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player - Dreampop Edition</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Estilos customizados - Tema Dreampop Psicodélico */
        :root {
            --dreampop-blue: #63a4ff;
            --dreampop-pink: #f498c8;
        }

        @keyframes gradient-animation {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        body {
            font-family: 'Inter', sans-serif;
            color: #e0e0e0;
            background: linear-gradient(-45deg, #2c3e50, #1d2b64, #3c1053, #24243e);
            background-size: 400% 400%;
            animation: gradient-animation 25s ease infinite;
        }

        a { color: inherit; } 

        .main-content-bg {
             background-color: #121212; /* Fundo do conteúdo principal sólido */
        }
        
        .sidebar-bg {
            background-color: transparent;
        }
        
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        .main-content::-webkit-scrollbar-track {
            background: transparent;
        }
        .main-content::-webkit-scrollbar-thumb {
            background: var(--dreampop-blue);
            border-radius: 10px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: #adb5bd;
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }
        .sidebar-link.active {
            background-color: var(--dreampop-blue);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 0 15px var(--dreampop-blue);
        }
        
        .card-custom {
            background-color: rgba(30, 30, 50, 0.7);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .card-custom:hover {
            border-color: var(--dreampop-blue);
            box-shadow: 0 0 20px rgba(99, 164, 255, 0.5);
        }
        .card-artist:hover, .card-album:hover {
            transform: translateY(-5px);
        }
        
        .card-album .play-button {
            opacity: 0;
            transition: all 0.3s ease;
            transform: translateY(10px);
        }
        .card-album:hover .play-button {
            opacity: 1;
            transform: translateY(0);
        }
        
        .text-logo-accent {
            color: var(--dreampop-blue);
            text-shadow: 0 0 10px var(--dreampop-blue);
        }

        .btn-primary {
            background-color: var(--dreampop-blue);
            border-color: var(--dreampop-blue);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #77b1ff;
            border-color: #77b1ff;
            box-shadow: 0 0 15px var(--dreampop-blue);
        }

        .dreampop-text {
            font-weight: 700;
            font-style: italic;
            font-size: 1.5rem;
            color: var(--dreampop-pink);
            text-shadow: 0 0 5px var(--dreampop-pink), 0 0 10px var(--dreampop-blue);
            align-self: center;
        }

        .border-secondary {
            border-color: rgba(255, 255, 255, 0.15) !important;
        }

        header.sticky-top {
            background-color: #121212 !important; /* Cor sólida para o header */
        }
    </style>
</head>
<body>

    <div class="d-flex vh-100">
        <!-- Sidebar de Navegação -->
        <aside class="d-none d-lg-flex flex-column flex-shrink-0 p-3 sidebar-bg" style="width: 280px;">
            <div class="d-flex align-items-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <a href="index.php" style="text-decoration: none;">
                <span class="fs-4 fw-bold">Music<span class="text-logo-accent">Player</span></span>
                </a> 
            </div>

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item mb-1">
                    <a href="#" class="sidebar-link active">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Início
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="#" class="sidebar-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        Buscar
                    </a>
                </li>
            </ul>
            
            <hr class="border-secondary">
            
            <h6 class="text-secondary text-uppercase small px-2 mb-2">Sua Biblioteca</h6>
            <ul class="nav nav-pills flex-column mb-auto">
                 <li class="nav-item mb-1">
                    <a href="#" class="sidebar-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        Músicas Curtidas
                    </a>
                </li>
                 <li class="nav-item mb-1">
                    <a href="#" class="sidebar-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Artistas
                    </a>
                </li>
            </ul>

            <div class="mt-auto">
                 <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Criar playlist
                </a>
            </div>
        </aside>

        <!-- Conteúdo Principal -->
        <div class="flex-grow-1 overflow-y-auto main-content main-content-bg">
            <div class="container-fluid p-4">

                <header class="d-flex justify-content-between align-items-center py-3 border-bottom border-secondary sticky-top" style="z-index: 1020;">
                    <button class="btn d-lg-none" type="button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="6" x2="20" y2="6"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
                    </button>
                    <div class="d-none d-lg-block"></div>
                    <div class="d-flex align-items-center">
                        <span class="dreampop-text me-3">DREAMPOP</span>
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
                    </div>
                </header>

                <main class="mt-4">
                    <section id="trending-songs">
                        <h2 class="fw-bolder mb-4">Músicas em Alta</h2>
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                            <div class="col">
                                <div class="card-custom p-3 rounded d-flex align-items-center">
                                    <img src="https://cdn-images.dzcdn.net/images/cover/53c2e22988a2a3a31c65fab33fbb1287/0x1900-000000-80-0-0.jpg" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;">
                                    <div class="flex-grow-1 text-truncate">
                                        <h3 class="h6 fw-bold text-truncate mb-1">Strawberry Rush</h3>
                                        <p class="small text-secondary mb-1">Chuu</p>
                                        <span class="small text-body-secondary">3:09</span>
                                    </div>
                                    <button class="btn btn-link text-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg></button>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card-custom p-3 rounded d-flex align-items-center">
                                    <img src="https://i1.sndcdn.com/artworks-5U3XWmtBKLsd-0-t500x500.jpg" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;">
                                    <div class="flex-grow-1 text-truncate">
                                        <h3 class="h6 fw-bold text-truncate mb-1">Somos Só Eu e Você...</h3>
                                        <p class="small text-secondary mb-1">hateyourmusic</p>
                                        <span class="small text-body-secondary">2:45</span>
                                    </div>
                                    <button class="btn btn-link text-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg></button>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card-custom p-3 rounded d-flex align-items-center">
                                    <img src="https://i.scdn.co/image/ab67616d0000b273c428f67b4a9b7e1114dfc117" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;">
                                    <div class="flex-grow-1 text-truncate">
                                        <h3 class="h6 fw-bold text-truncate mb-1">Washing Machine Heart</h3>
                                        <p class="small text-secondary mb-1">Mitski</p>
                                        <span class="small text-body-secondary">2:08</span>
                                    </div>
                                    <button class="btn btn-link text-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg></button>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card-custom p-3 rounded d-flex align-items-center">
                                    <img src="https://m.media-amazon.com/images/I/81fxuK+kFsL.jpg" alt="Capa" class="rounded me-3" style="width: 80px; height: 80px;">
                                    <div class="flex-grow-1 text-truncate">
                                        <h3 class="h6 fw-bold text-truncate mb-1">Money For Nothing</h3>
                                        <p class="small text-secondary mb-1">Dire Straits</p>
                                        <span class="small text-body-secondary">8:26</span>
                                    </div>
                                    <button class="btn btn-link text-secondary"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg></button>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="trending-albums" class="mt-5">
                        <h2 class="fw-bolder mb-4">Álbuns em Alta</h2>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <div class="position-relative">
                                        <img src="https://upload.wikimedia.org/wikipedia/pt/8/8f/220px-Mezmerize-LP.jpg" class="card-img-top" alt="Capa do Álbum">
                                        <button class="btn btn-success rounded-circle p-3 position-absolute bottom-0 end-0 me-2 mb-2 play-button shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate">Mezmerize</h6>
                                        <p class="card-text small text-secondary">System Of a Down</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <div class="position-relative">
                                        <img src="https://upload.wikimedia.org/wikipedia/pt/0/0a/Lady_Gaga_-_Mayhem.jpg" class="card-img-top" alt="Capa do Álbum">
                                        <button class="btn btn-success rounded-circle p-3 position-absolute bottom-0 end-0 me-2 mb-2 play-button shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate">MAYHEM</h6>
                                        <p class="card-text small text-secondary">Lady Gaga</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <div class="position-relative">
                                        <img src="https://f4.bcbits.com/img/a1847311626_10.jpg" class="card-img-top" alt="Capa do Álbum">
                                        <button class="btn btn-success rounded-circle p-3 position-absolute bottom-0 end-0 me-2 mb-2 play-button shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate">for the rest of your life</h6>
                                        <p class="card-text small text-secondary">twikipedia</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <div class="position-relative">
                                        <img src="https://i.scdn.co/image/ab67616d0000b2736a8c387e68b6e1e7201d138d" class="card-img-top" alt="Capa do Álbum">
                                        <button class="btn btn-success rounded-circle p-3 position-absolute bottom-0 end-0 me-2 mb-2 play-button shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate">Musica do Esquecimento</h6>
                                        <p class="card-text small text-secondary">Sophia Chablau...</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <div class="position-relative">
                                        <img src="https://m.media-amazon.com/images/I/71qfG9DrONL.jpg" class="card-img-top" alt="Capa do Álbum">
                                        <button class="btn btn-success rounded-circle p-3 position-absolute bottom-0 end-0 me-2 mb-2 play-button shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate">don't smile at me</h6>
                                        <p class="card-text small text-secondary">Billie Eilish</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-album h-100">
                                    <div class="position-relative">
                                        <img src="https://i.scdn.co/image/ab67616d0000b2736f2e5817230413edcd000cc6" class="card-img-top" alt="Capa do Álbum">
                                        <button class="btn btn-success rounded-circle p-3 position-absolute bottom-0 end-0 me-2 mb-2 play-button shadow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"></path></svg>
                                        </button>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate">the now now and never</h6>
                                        <p class="card-text small text-secondary">what is your name?</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section id="trending-artists" class="mt-5">
                        <h2 class="fw-bolder mb-4">Artistas em Alta</h2>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-4">
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body">
                                        <img src="https://i.scdn.co/image/ab6761610000e5eb60063d3451ade8f9fab397c2" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate">System Of a Down</h6>
                                        <p class="card-text small text-secondary">Artista</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body">
                                        <img src="https://i.scdn.co/image/ab6761610000e5ebaadc18cac8d48124357c38e6" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate">Lady Gaga</h6>
                                        <p class="card-text small text-secondary">Artista</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body">
                                        <img src="https://i.scdn.co/image/ab676161000051744e5deb8330ecc084d1aec0ab" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate">twikipedia</h6>
                                        <p class="card-text small text-secondary">Artista</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body">
                                        <img src="https://yt3.googleusercontent.com/pA0zfvJNbKitV5IEvRppdW-OUhmSIs00VKNAWkVES3hQlq-e6EhLqtpBz51hPcyO7meiVmET=s900-c-k-c0x00ffffff-no-rj" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate">Sophia Chablau...</h6>
                                        <p class="card-text small text-secondary">Artista</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body">
                                        <img src="https://i.scdn.co/image/ab6761610000e5eb4a21b4760d2ecb7b0dcdc8da" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate">Billie Eilish</h6>
                                        <p class="card-text small text-secondary">Artista</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card card-custom card-artist h-100 text-center">
                                    <div class="card-body">
                                        <img src="https://i.scdn.co/image/ab6761610000e5eb1acb903fd29ac701d45a5515" class="rounded-circle img-fluid mb-3" alt="Foto do Artista">
                                        <h6 class="card-title fw-bold text-truncate">what is your name?</h6>
                                        <p class="card-text small text-secondary">Artista</p>
                                    </div>
                                </div>
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

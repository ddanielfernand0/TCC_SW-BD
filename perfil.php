<?php
// Inicia a sessão
session_start();

// Se o usuário não estiver logado, redireciona para a página de login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Inclui o arquivo de configuração do banco de dados
require_once "config.php";

// Define variáveis
$update_success = $update_err = $photo_err = "";
$user_id = $_SESSION["id"];

// --- LÓGICA PARA ATUALIZAR O PERFIL (NOME E FOTO) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
    // Atualiza o nome se foi alterado
    $new_username = trim($_POST['username']);
    if (!empty($new_username) && $new_username !== $_SESSION["username"]) {
        $sql = "UPDATE usuarios SET nome = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $new_username, $user_id);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["username"] = $new_username;
                $update_success = "Perfil atualizado. ";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Atualiza a foto se um novo arquivo foi enviado
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed_types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        $file_name = $_FILES['profile_photo']['name'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (array_key_exists($ext, $allowed_types)) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $new_filename = "profile_" . $user_id . '_' . time() . '.' . $ext;
            $target_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
                $sql = "UPDATE usuarios SET url_foto_perfil = ? WHERE id = ?";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $target_path, $user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION["profile_pic"] = $target_path;
                        $update_success .= "Foto de perfil atualizada.";
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// --- BUSCA DADOS PARA EXIBIR NA PÁGINA ---
// Dados do usuário
$sql_user = "SELECT nome, email, url_foto_perfil FROM usuarios WHERE id = ?";
if($stmt_user = mysqli_prepare($link, $sql_user)){
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    if(mysqli_stmt_execute($stmt_user)){
        mysqli_stmt_bind_result($stmt_user, $username, $email, $profile_pic);
        mysqli_stmt_fetch($stmt_user);
    }
    mysqli_stmt_close($stmt_user);
}

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

// Avaliações feitas pelo usuário
$avaliacoes = [];
$sql_avaliacoes = "SELECT m.titulo, m.id as musica_id, a.nota, a.comentario, al.url_capa FROM avaliacoes a JOIN musicas m ON a.musica_id = m.id JOIN albums al ON m.album_id = al.id WHERE a.usuario_id = ? ORDER BY a.data_avaliacao DESC";
if($stmt_avaliacoes = mysqli_prepare($link, $sql_avaliacoes)){
    mysqli_stmt_bind_param($stmt_avaliacoes, "i", $user_id);
    if(mysqli_stmt_execute($stmt_avaliacoes)){
        $result = mysqli_stmt_get_result($stmt_avaliacoes);
        $avaliacoes = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    mysqli_stmt_close($stmt_avaliacoes);
}

mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Music Player</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root { --neutral-purple: #6f42c1; --neutral-bg: #121212; --neutral-card-bg: #181818; --neutral-border: #282828; }
        body { font-family: 'Inter', sans-serif; background-color: var(--neutral-bg); }
        .form-card { background-color: var(--neutral-card-bg); border: 1px solid var(--neutral-border); border-radius: 0.75rem; }
        .form-control { background-color: #282828; border-color: #444; }
        .form-control:focus { background-color: #282828; border-color: var(--neutral-purple); box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25); }
        .btn-primary { background-color: var(--neutral-purple); border-color: var(--neutral-purple); font-weight: 600; }
        .btn-primary:hover { background-color: #59369a; border-color: #59369a; }
        .text-logo-accent { color: var(--neutral-purple); }
        .nav-tabs .nav-link { color: #adb5bd; border: none; }
        .nav-tabs .nav-link.active { color: #fff; background-color: var(--neutral-card-bg); border-bottom: 2px solid var(--neutral-purple); }
        .star-rating label { font-size: 1rem; color: #ffc107; cursor: default; }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Cabeçalho do Perfil -->
                <div class="d-flex flex-column flex-md-row align-items-center mb-5">
                    <img src="<?php echo htmlspecialchars($profile_pic ?? 'https://placehold.co/150x150/181818/ffffff?text=Foto'); ?>" alt="Foto de Perfil" class="rounded-circle me-md-4 mb-3 mb-md-0" style="width: 150px; height: 150px; object-fit: cover;">
                    <div class="text-center text-md-start">
                        <h1 class="display-5 fw-bolder"><?php echo htmlspecialchars($username); ?></h1>
                        <p class="text-secondary">
                            <span><?php echo count($playlists); ?> Playlists</span> &bull;
                            <span><?php echo count($artistas_seguidos); ?> Artistas Seguidos</span>
                        </p>
                        <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editProfileModal">Editar Perfil</button>
                        <a href="index.php" class="btn btn-secondary">Voltar ao Início</a>
                    </div>
                </div>

                <!-- Abas de Navegação -->
                <ul class="nav nav-tabs mb-4" id="profileTab" role="tablist">
                    <li class="nav-item" role="presentation"><button class="nav-link active" id="playlists-tab" data-bs-toggle="tab" data-bs-target="#playlists" type="button" role="tab">Playlists</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="artists-tab" data-bs-toggle="tab" data-bs-target="#artists" type="button" role="tab">Artistas Seguidos</button></li>
                    <li class="nav-item" role="presentation"><button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Suas Avaliações</button></li>
                </ul>

                <!-- Conteúdo das Abas -->
                <div class="tab-content" id="profileTabContent">
                    <!-- Aba de Playlists -->
                    <div class="tab-pane fade show active" id="playlists" role="tabpanel">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                            <?php if(!empty($playlists)): foreach($playlists as $playlist): ?>
                            <div class="col"><div class="card bg-dark"><div class="card-body"><h5 class="card-title text-truncate"><?php echo htmlspecialchars($playlist['nome']); ?></h5><p class="card-text text-secondary text-truncate"><?php echo htmlspecialchars($playlist['descricao'] ?? 'Sem descrição'); ?></p></div></div></div>
                            <?php endforeach; else: ?>
                            <p class="text-secondary">Você ainda não criou nenhuma playlist.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Aba de Artistas Seguidos -->
                    <div class="tab-pane fade" id="artists" role="tabpanel">
                        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                            <?php if(!empty($artistas_seguidos)): foreach($artistas_seguidos as $artista): ?>
                            <div class="col"><div class="card bg-dark text-center"><img src="<?php echo htmlspecialchars($artista['url_foto_perfil']); ?>" class="rounded-circle img-fluid p-3 mx-auto" style="width: 120px; height: 120px; object-fit: cover;"><div class="card-body"><h6 class="card-title text-truncate"><?php echo htmlspecialchars($artista['nome_artistico']); ?></h6></div></div></div>
                            <?php endforeach; else: ?>
                            <p class="text-secondary">Você ainda não segue nenhum artista.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <!-- Aba de Avaliações -->
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="list-group">
                            <?php if(!empty($avaliacoes)): foreach($avaliacoes as $avaliacao): ?>
                            <a href="musica.php?id=<?php echo $avaliacao['musica_id']; ?>" class="list-group-item list-group-item-action bg-dark d-flex align-items-center">
                                <img src="<?php echo htmlspecialchars($avaliacao['url_capa']); ?>" class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <div class="fw-bold"><?php echo htmlspecialchars($avaliacao['titulo']); ?></div>
                                    <div class="star-rating">
                                        <?php for($i = 5; $i >= 1; $i--): ?><label style="color: <?php echo ($avaliacao['nota'] >= $i) ? '#ffc107' : '#444'; ?>;">★</label><?php endfor; ?>
                                    </div>
                                    <small class="text-secondary fst-italic">"<?php echo htmlspecialchars($avaliacao['comentario']); ?>"</small>
                                </div>
                            </a>
                            <?php endforeach; else: ?>
                            <p class="text-secondary">Você ainda não avaliou nenhuma música.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição de Perfil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content form-card">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bolder">Editar Perfil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($profile_pic ?? 'https://placehold.co/150x150/181818/ffffff?text=Foto'); ?>" alt="Foto de Perfil" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="mb-4">
                            <label for="profile_photo" class="form-label">Alterar foto de perfil</label>
                            <input class="form-control" type="file" name="profile_photo" id="profile_photo" accept="image/png, image/jpeg">
                        </div>
                        <hr class="my-4">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de usuário</label>
                            <input type="text" name="username" class="form-control p-2" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control p-2" id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" disabled readonly>
                            <div class="form-text">O email não pode ser alterado.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="save_profile" class="btn btn-primary">Salvar Perfil</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

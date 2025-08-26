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

// Define variáveis para mensagens
$update_success = $update_err = $photo_err = "";

// Busca os dados mais recentes do usuário no banco de dados
$user_id = $_SESSION["id"];
$sql_user = "SELECT nome, email, url_foto_perfil FROM usuarios WHERE id = ?";
if($stmt_user = mysqli_prepare($link, $sql_user)){
    mysqli_stmt_bind_param($stmt_user, "i", $user_id);
    if(mysqli_stmt_execute($stmt_user)){
        mysqli_stmt_store_result($stmt_user);
        mysqli_stmt_bind_result($stmt_user, $username, $email, $profile_pic);
        mysqli_stmt_fetch($stmt_user);
    }
    mysqli_stmt_close($stmt_user);
}

// --- LÓGICA PARA ATUALIZAR O PERFIL (NOME E FOTO) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_profile'])) {
    
    // --- ATUALIZAR NOME ---
    $new_username = trim($_POST['username']);
    if (empty($new_username)) {
        $update_err = "O nome de usuário não pode ficar em branco.";
    } elseif ($new_username !== $_SESSION["username"]) {
        $sql = "UPDATE usuarios SET nome = ? WHERE id = ?";
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $param_username, $param_id);
            $param_username = $new_username;
            $param_id = $_SESSION["id"];
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION["username"] = $new_username;
                $username = $new_username;
                $update_success = "Nome de usuário atualizado. ";
            } else {
                $update_err = "Oops! Algo deu errado ao atualizar o nome.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // --- ATUALIZAR FOTO DE PERFIL (se um arquivo foi enviado) ---
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed_types = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
        $file_name = $_FILES['profile_photo']['name'];
        $file_type = $_FILES['profile_photo']['type'];
        $file_size = $_FILES['profile_photo']['size'];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (!array_key_exists($ext, $allowed_types)) {
            $photo_err = "Erro: Formato de arquivo inválido (JPG, JPEG, PNG).";
        } elseif ($file_size > 5 * 1024 * 1024) { // 5MB max
            $photo_err = "Erro: O arquivo é maior que o limite de 5MB.";
        }

        if (empty($photo_err)) {
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $new_filename = "profile_" . $_SESSION['id'] . '_' . time() . '.' . $ext;
            $target_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
                $sql = "UPDATE usuarios SET url_foto_perfil = ? WHERE id = ?";
                if ($stmt = mysqli_prepare($link, $sql)) {
                    mysqli_stmt_bind_param($stmt, "si", $param_path, $param_id);
                    $param_path = $target_path;
                    $param_id = $_SESSION["id"];

                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION["profile_pic"] = $target_path;
                        $profile_pic = $target_path;
                        $update_success .= "Foto de perfil atualizada.";
                    } else {
                        $photo_err = "Erro ao salvar o caminho da imagem no banco de dados.";
                    }
                    mysqli_stmt_close($stmt);
                }
            } else {
                $photo_err = "Erro ao fazer upload do arquivo. Verifique as permissões da pasta.";
            }
        }
    }
    
    if(empty($update_err) && empty($photo_err) && empty($update_success)){
        $update_success = "Nenhuma alteração foi feita.";
    }
}

mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Music Player</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --neutral-purple: #6f42c1;
            --neutral-bg: #121212;
            --neutral-card-bg: #181818;
            --neutral-border: #282828;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--neutral-bg);
        }
        .form-card {
            background-color: var(--neutral-card-bg);
            border: 1px solid var(--neutral-border);
            border-radius: 0.75rem;
        }
        .form-control { background-color: #282828; border-color: #444; }
        .form-control:focus {
            background-color: #282828;
            border-color: var(--neutral-purple);
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
        }
        .btn-primary {
            background-color: var(--neutral-purple);
            border-color: var(--neutral-purple);
            font-weight: 600;
        }
        .btn-primary:hover { background-color: #59369a; border-color: #59369a; }
        .text-logo-accent { color: var(--neutral-purple); }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-4">
                    <a href="index.php" class="d-inline-flex align-items-center text-decoration-none">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                        <span class="fs-4 fw-bold text-white">Music<span class="text-logo-accent">Player</span></span>
                    </a>
                </div>

                <?php if(!empty($update_success)): ?>
                    <div class="alert alert-success"><?php echo $update_success; ?></div>
                <?php endif; ?>
                <?php if(!empty($update_err)): ?>
                    <div class="alert alert-danger"><?php echo $update_err; ?></div>
                <?php endif; ?>
                 <?php if(!empty($photo_err)): ?>
                    <div class="alert alert-danger"><?php echo $photo_err; ?></div>
                <?php endif; ?>

                <div class="form-card p-4 p-md-5">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="fw-bolder mb-0">Editar Perfil</h2>
                        <a href="index.php" class="btn btn-outline-secondary">Voltar para o Início</a>
                    </div>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <!-- Seção da Foto -->
                        <div class="text-center mb-4">
                            <img src="<?php echo htmlspecialchars($profile_pic ?? 'https://placehold.co/150x150/181818/ffffff?text=Foto'); ?>" alt="Foto de Perfil" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        </div>
                        <div class="mb-4">
                            <label for="profile_photo" class="form-label">Alterar foto de perfil</label>
                            <input class="form-control" type="file" name="profile_photo" id="profile_photo" accept="image/png, image/jpeg">
                        </div>

                        <hr class="my-4">

                        <!-- Seção de Nome e Email -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Nome de usuário</label>
                            <input type="text" name="username" class="form-control p-2" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control p-2" id="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" disabled readonly>
                            <div class="form-text">O email não pode ser alterado.</div>
                        </div>
                        
                        <!-- Botão Único para Salvar -->
                        <button type="submit" name="save_profile" class="btn btn-primary w-100 py-2 mt-3">Salvar Perfil</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

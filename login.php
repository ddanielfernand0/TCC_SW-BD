<?php
// Inicia a sessão no topo do script
session_start();

// Inclui o arquivo de configuração do banco de dados
require_once "config.php";

// Define variáveis para armazenar mensagens de erro ou sucesso
$register_err = $login_err = $register_success = "";

// --- LÓGICA DE CADASTRO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {

    // Validação do nome de usuário
    if (empty(trim($_POST["username"]))) {
        $register_err = "Por favor, insira um nome de usuário.";
    } else {
        $username = trim($_POST["username"]);
    }

    // Validação do email
    if (empty(trim($_POST["email"]))) {
        $register_err = "Por favor, insira um email.";
    } else {
        // Prepara uma declaração SELECT para verificar se o email já existe
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $register_err = "Este email já está em uso.";
                } else {
                    $email = trim($_POST["email"]);
                }
            } else {
                echo "Oops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }

    // Validação da senha
    if (empty(trim($_POST["password"]))) {
        $register_err = "Por favor, insira uma senha.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $register_err = "A senha deve ter pelo menos 6 caracteres.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Se não houver erros, insere o novo usuário no banco de dados
    if (empty($register_err)) {
        $sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
         
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_email, $param_password_hash);
            
            $param_username = $username;
            $param_email = $email;
            // Cria um hash da senha para segurança
            $param_password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            if (mysqli_stmt_execute($stmt)) {
                $register_success = "Conta criada com sucesso! Você já pode fazer login.";
            } else {
                echo "Oops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// --- LÓGICA DE LOGIN ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {

    // Validação do email
    if (empty(trim($_POST["email"]))) {
        $login_err = "Por favor, insira seu email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validação da senha
    if (empty(trim($_POST["password"]))) {
        $login_err = "Por favor, insira sua senha.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Se não houver erros, verifica as credenciais
    if (empty($login_err)) {
        $sql = "SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?";
        
        if ($stmt = mysqli_prepare($link, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = $email;
            
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $id, $username, $db_email, $hashed_password);
                    if (mysqli_stmt_fetch($stmt)) {
                        // Verifica se a senha corresponde ao hash
                        if (password_verify($password, $hashed_password)) {
                            // Senha correta, inicia uma nova sessão
                            session_start();
                            
                            // Armazena dados na sessão
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Redireciona para a página inicial
                            header("location: index.php");
                        } else {
                            $login_err = "Email ou senha inválidos.";
                        }
                    }
                } else {
                    $login_err = "Email ou senha inválidos.";
                }
            } else {
                echo "Oops! Algo deu errado. Por favor, tente novamente mais tarde.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Fecha a conexão com o banco de dados
mysqli_close($link);
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music Player - Login</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" xintegrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-container { max-width: 900px; width: 100%; }
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

    <div class="login-container p-3">
        <div class="text-center mb-5">
            <a href="index.php" class="d-inline-flex align-items-center text-decoration-none">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-logo-accent me-2"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                <span class="fs-4 fw-bold text-white">Music<span class="text-logo-accent">Player</span></span>
            </a>
        </div>

        <!-- Alertas de Erro/Sucesso -->
        <?php if(!empty($login_err)): ?>
            <div class="alert alert-danger"><?php echo $login_err; ?></div>
        <?php endif; ?>
        <?php if(!empty($register_err)): ?>
            <div class="alert alert-danger"><?php echo $register_err; ?></div>
        <?php endif; ?>
        <?php if(!empty($register_success)): ?>
            <div class="alert alert-success"><?php echo $register_success; ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Coluna de Login -->
            <div class="col-lg-6">
                <div class="form-card p-4 p-md-5">
                    <h2 class="fw-bolder mb-4">Login</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="loginEmail" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control p-2" id="loginEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="loginPassword" class="form-label">Senha</label>
                            <input type="password" name="password" class="form-control p-2" id="loginPassword" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100 py-2">Entrar</button>
                    </form>
                </div>
            </div>

            <!-- Coluna de Cadastro -->
            <div class="col-lg-6">
                <div class="form-card p-4 p-md-5">
                    <h2 class="fw-bolder mb-4">Criar Conta</h2>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="mb-3">
                            <label for="registerName" class="form-label">Nome de usuário</label>
                            <input type="text" name="username" class="form-control p-2" id="registerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerEmail" class="form-label">Email</label>
                            <input type="email" name="email" class="form-control p-2" id="registerEmail" required>
                        </div>
                        <div class="mb-3">
                            <label for="registerPassword" class="form-label">Senha</label>
                            <input type="password" name="password" class="form-control p-2" id="registerPassword" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-outline-light w-100 py-2 mt-3">Cadastrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

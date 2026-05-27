<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: /dashboard.php');
    exit();
}

require_once __DIR__ . '/config/database.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {

        $erro = 'Preencha todos os campos.';

    } else {

        $pdo = getConexao();

        $stmt = $pdo->prepare("SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($password, $usuario['password'])) {

            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nome'] = $usuario['name'];
            $_SESSION['usuario_email'] = $usuario['email'];

            header('Location: /dashboard.php');
            exit();

        } else {

            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfHub | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-area">
                <h1 class="title">ShelfHub</h1>
                <p class="subtitle">Seu universo literário organizado</p>
            </div>

            <?php if ($erro): ?>
                <div class="error-box">
                    <?= htmlspecialchars($erro) ?>
                </div>

            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="input" placeholder="Digite seu e-mail" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="input" placeholder="Digite sua senha">
                </div>

                <button type="submit" class="btn-login">
                    Entrar
                </button>

                <p class="register-link">
                    Não possui conta?
                    <a href="register.php">Criar conta</a>
                </p>

            </form>
            <p class="footer-text"> ShelfHub © 2026 </p>
        </div>

    </div>
    
</body>
</html>
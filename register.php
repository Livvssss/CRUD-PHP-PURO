<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: /dashboard.php');
    exit();
}

require_once __DIR__ . '/config/database.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmar_password'] ?? '');

    if (empty($nome) || empty($email) || empty($password) || empty($confirmPassword)) {

        $erro = 'Preencha todos os campos.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = 'Digite um e-mail válido.';

    } elseif ($password !== $confirmPassword) {

        $erro = 'As senhas não coincidem.';

    } elseif (strlen($password) < 6) {

        $erro = 'A senha deve ter no mínimo 6 caracteres.';

    } else {

        $pdo = getConexao();

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuarioExiste = $stmt->fetch();

        if ($usuarioExiste) {

            $erro = 'Este e-mail já está cadastrado.';

        } else {

            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (:name, :email, :password)");

            $stmt->execute([
                ':name' => $nome,
                ':email' => $email,
                ':password' => $passwordHash
            ]);

            $sucesso = 'Conta criada com sucesso!';

            header("Refresh: 2; url=/login.php");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ShelfHub | Cadastro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="css/register.css">

</head>

<body>

    <div class="container">

        <div class="card">
            <div class="logo-area">
                <h1 class="title">ShelfHub</h1>
                <p class="subtitle">Crie sua conta na plataforma</p>
            </div>

            <?php if ($erro): ?>
                <div class="message error">
                    <?= htmlspecialchars($erro) ?>
                </div>
            <?php endif; ?>

            <?php if ($sucesso): ?>
                <div class="message success">
                    <?= htmlspecialchars($sucesso) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="form-group">
                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="input" placeholder="Digite seu nome" value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" class="input" placeholder="Digite seu e-mail" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password" class="input" placeholder="Digite sua senha">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmar senha</label>
                    <input type="password" name="confirmPassword" class="input" placeholder="Confirme sua senha">
                </div>

                <button type="submit" class="btn"> Criar conta </button>

            </form>

            <p class="footer"> Já possui conta? <a href="/login.php">Entrar</a></p>

        </div>

    </div>
</body>
</html>
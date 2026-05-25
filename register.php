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

    if (
        empty($nome) ||
        empty($email) ||
        empty($password) ||
        empty($confirmPassword)
    ) {

        $erro = 'Preencha todos os campos.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = 'Digite um e-mail válido.';

    } elseif ($password !== $confirmPassword) {

        $erro = 'As senhas não coincidem.';

    } elseif (strlen($password) < 6) {

        $erro = 'A senha deve ter no mínimo 6 caracteres.';

    } else {

        $pdo = getConexao();

        $stmt = $pdo->prepare("
            SELECT id
            FROM users
            WHERE email = :email
            LIMIT 1
        ");

        $stmt->execute([
            ':email' => $email
        ]);

        $usuarioExiste = $stmt->fetch();

        if ($usuarioExiste) {

            $erro = 'Este e-mail já está cadastrado.';

        } else {

            $passwordHash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $pdo->prepare("
                INSERT INTO users (
                    name,
                    email,
                    password
                )
                VALUES (
                    :name,
                    :email,
                    :password
                )
            ");

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>ShelfHub | Cadastro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>

        :root {

            --bg: #fffcf2;
            --bg-soft: #fef9e7;

            --surface: rgba(255, 255, 255, 0.32);

            --primary: #d97706;

            --text: #3f3f46;
            --text-muted: #71717a;

            --border: rgba(245, 158, 11, 0.14);

            --radius: 28px;

            --transition:
                0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }

        * {

            margin: 0;
            padding: 0;

            box-sizing: border-box;
        }

        body {

            font-family: 'Plus Jakarta Sans', sans-serif;

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            overflow: hidden;

            background:
                linear-gradient(
                    rgba(255,252,242,0.82),
                    rgba(254,249,231,0.86)
                ),

                url('img/logo.png');

            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {

            width: 100%;
            max-width: 500px;

            padding: 24px;
        }

        .card {

            background: var(--surface);

            border:
                1px solid rgba(255,255,255,0.25);

            backdrop-filter: blur(20px);

            border-radius: var(--radius);

            padding: 42px;

            box-shadow:
                0 30px 60px rgba(0,0,0,0.06),
                0 10px 30px rgba(217,119,6,0.08);

            animation: fadeIn .7s ease;
        }

        .logo-area {

            text-align: center;

            margin-bottom: 32px;
        }

        .title {

            font-size: 42px;

            font-weight: 800;

            letter-spacing: -2px;

            color: #a16207;
        }

        .subtitle {

            margin-top: 10px;

            color: var(--text-muted);

            font-size: 15px;

            font-weight: 500;
        }

        .message {

            padding: 14px 16px;

            border-radius: 18px;

            margin-bottom: 22px;

            font-size: 14px;

            font-weight: 600;
        }

        .error {

            background:
                rgba(254, 226, 226, 0.65);

            color: #991b1b;

            border:
                1px solid rgba(248,113,113,0.20);
        }

        .success {

            background:
                rgba(220, 252, 231, 0.65);

            color: #166534;

            border:
                1px solid rgba(34,197,94,0.20);
        }

        .form-group {

            margin-bottom: 20px;
        }

        .form-label {

            display: block;

            margin-bottom: 10px;

            color: var(--text);

            font-size: 14px;

            font-weight: 700;
        }

        .input {

            width: 100%;

            padding: 16px 18px;

            border-radius: 18px;

            border:
                1px solid rgba(255,255,255,0.22);

            background:
                rgba(255,255,255,0.38);

            font-size: 15px;

            color: var(--text);

            outline: none;

            transition: var(--transition);
        }

        .input:focus {

            background:
                rgba(255,255,255,0.52);

            border-color:
                rgba(217,119,6,0.40);

            box-shadow:
                0 0 0 4px rgba(217,119,6,0.10);
        }

        .btn {

            width: 100%;

            border: none;

            padding: 16px;

            border-radius: 18px;

            background:
                linear-gradient(
                    135deg,
                    #f59e0b,
                    #d97706
                );

            color: white;

            font-size: 15px;

            font-weight: 700;

            cursor: pointer;

            transition: var(--transition);
        }

        .btn:hover {

            transform: translateY(-2px);

            box-shadow:
                0 14px 24px rgba(217,119,6,0.20);
        }

        .footer {

            text-align: center;

            margin-top: 22px;

            font-size: 14px;

            color: var(--text-muted);
        }

        .footer a {

            color: #b45309;

            font-weight: 700;

            text-decoration: none;
        }

        .footer a:hover {

            text-decoration: underline;
        }

        @keyframes fadeIn {

            from {

                opacity: 0;

                transform: translateY(24px);
            }

            to {

                opacity: 1;

                transform: translateY(0);
            }
        }

    </style>

</head>

<body>

    <div class="container">

        <div class="card">

            <div class="logo-area">

                <h1 class="title">
                    ShelfHub
                </h1>

                <p class="subtitle">
                    Crie sua conta na plataforma
                </p>

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

                    <label class="form-label">
                        Nome
                    </label>

                    <input
                        type="text"
                        name="nome"
                        class="input"
                        placeholder="Digite seu nome"
                        value="<?= htmlspecialchars($_POST['nome'] ?? '') ?>"
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        E-mail
                    </label>

                    <input
                        type="email"
                        name="email"
                        class="input"
                        placeholder="Digite seu e-mail"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Senha
                    </label>

                    <input
                        type="password"
                        name="password"
                        class="input"
                        placeholder="Digite sua senha"
                    >

                </div>

                <div class="form-group">

                    <label class="form-label">
                        Confirmar senha
                    </label>

                    <input
                        type="password"
                        name="confirmar_password"
                        class="input"
                        placeholder="Confirme sua senha"
                    >

                </div>

                <button
                    type="submit"
                    class="btn"
                >
                    Criar conta
                </button>

            </form>

            <p class="footer">
                Já possui conta?
                <a href="/login.php">
                    Entrar
                </a>
            </p>

        </div>

    </div>

</body>

</html>
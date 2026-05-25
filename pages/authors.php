<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$pdo = getConexao();
$usuarioId = $_SESSION['usuario_id'];

/*
|--------------------------------------------------------------------------
| CRIAR TABELA
|--------------------------------------------------------------------------
*/

$pdo->exec("
    CREATE TABLE IF NOT EXISTS authors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        bio TEXT NOT NULL,
        photo VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

/*
|--------------------------------------------------------------------------
| CADASTRAR AUTOR
|--------------------------------------------------------------------------
*/

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome = trim($_POST['nome'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    if (empty($nome) || empty($bio)) {

        $erro = 'Preencha todos os campos.';
    } else {

        $nomeFoto = null;

        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {

            $pastaUploads = __DIR__ . '/uploads/authors/';

            if (!is_dir($pastaUploads)) {
                mkdir($pastaUploads, 0777, true);
            }

            $extensao = pathinfo(
                $_FILES['foto']['name'],
                PATHINFO_EXTENSION
            );

            $nomeFoto = uniqid() . '.' . $extensao;

            move_uploaded_file(
                $_FILES['foto']['tmp_name'],
                $pastaUploads . $nomeFoto
            );
        }

        $stmt = $pdo->prepare("
            INSERT INTO authors (
                user_id,
                name,
                bio,
                photo
            )
            VALUES (
                :user_id,
                :name,
                :bio,
                :photo
            )
        ");

        $stmt->execute([
            ':user_id' => $usuarioId,
            ':name' => $nome,
            ':bio' => $bio,
            ':photo' => $nomeFoto
        ]);

        $mensagem = 'Autor cadastrado com sucesso!';
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR AUTORES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM authors
    WHERE user_id = :user_id
    ORDER BY id DESC
");

$stmt->execute([
    ':user_id' => $usuarioId
]);

$authors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>ShelfHub | Autores</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        :root {

            --bg: #fffcf2;
            --bg-soft: #fef9e7;

            --surface: rgba(255, 255, 255, 0.72);

            --primary: #d97706;
            --primary-soft: #fef3c7;

            --text: #3f3f46;
            --text-muted: #71717a;

            --border: rgba(245, 158, 11, 0.14);

            --shadow:
                0 20px 40px rgba(217, 119, 6, 0.08);

            --radius: 22px;

            --sidebar-width: 280px;

            --transition:
                0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }

        * {

            margin: 0;
            padding: 0;

            box-sizing: border-box;
        }

        body {

            font-family: 'Poppins', sans-serif;

            min-height: 100vh;

            background:
                linear-gradient(135deg,
                    var(--bg),
                    var(--bg-soft));

            display: flex;

            color: var(--text);

            overflow-x: hidden;

            position: relative;
        }

        body::before {

            content: '';

            position: fixed;

            inset: 0;

            background:

                radial-gradient(circle at top left,
                    rgba(251, 191, 36, 0.10),
                    transparent 30%),

                radial-gradient(circle at bottom right,
                    rgba(245, 158, 11, 0.08),
                    transparent 35%);

            z-index: -2;
        }

        .background-logo {

            position: fixed;

            inset: 0;

            display: flex;

            justify-content: center;

            align-items: center;

            opacity: 0.05;

            z-index: -1;
        }

        .background-logo img {

            width: 650px;

            max-width: 90%;
        }

        /* SIDEBAR */

        .sidebar {

            width: var(--sidebar-width);

            min-height: 100vh;

            background: rgba(255, 255, 255, 0.55);

            backdrop-filter: blur(18px);

            border-right:
                1px solid var(--border);

            padding: 28px 20px;

            position: fixed;

            left: 0;
            top: 0;
        }

        .sidebar-header {

            margin-bottom: 40px;
        }

        .sidebar-header h2 {

            font-size: 34px;

            font-weight: 800;

            color: var(--primary);
        }

        .sidebar-header p {

            color: var(--text-muted);

            margin-top: 8px;

            font-size: 14px;
        }

        .sidebar-menu {

            display: flex;

            flex-direction: column;

            gap: 10px;
        }

        .menu-item {

            text-decoration: none;

            color: var(--text);

            padding: 16px 18px;

            border-radius: 18px;

            font-weight: 600;

            transition: var(--transition);

            display: flex;

            align-items: center;
        }

        .menu-item:hover {

            background: var(--primary-soft);

            color: var(--primary);

            transform: translateX(4px);
        }

        .menu-item.active {

            background:
                linear-gradient(135deg,
                    #f59e0b,
                    #d97706);

            color: white;

            box-shadow:
                0 14px 24px rgba(217, 119, 6, 0.15);
        }

        /* CONTEÚDO */

        .page-wrapper {

            margin-left: var(--sidebar-width);

            width: calc(100% - var(--sidebar-width));

            padding: 40px;
        }

        .hero {

            margin-bottom: 40px;

            text-align: center;
        }

        .hero h1 {

            font-size: 54px;

            font-weight: 800;

            color: var(--primary);

            margin-bottom: 12px;
        }

        .subtitle {

            color: var(--text-muted);

            font-size: 16px;
        }

        .container {

            display: grid;

            grid-template-columns: 400px 1fr;

            gap: 22px;

            align-items: start;
        }

        .card {

            background:
                rgba(255, 255, 255, 0.55);

            border:
                1px solid rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(18px);

            border-radius: var(--radius);

            padding: 28px;

            box-shadow: var(--shadow);

            transition: var(--transition);
        }

        .card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 24px 40px rgba(217, 119, 6, 0.12);
        }

        .card-title {

            font-size: 28px;

            font-weight: 800;

            color: var(--primary);

            margin-bottom: 24px;
        }

        .form-group {

            margin-bottom: 22px;
        }

        label {

            display: block;

            margin-bottom: 10px;

            font-weight: 700;
        }

        input,
        textarea {

            width: 100%;

            border: 1px solid #e7e5e4;

            border-radius: 18px;

            padding: 16px;

            background: rgba(255, 255, 255, 0.92);

            font-family: inherit;

            font-size: 15px;

            outline: none;

            transition: var(--transition);
        }

        input:focus,
        textarea:focus {

            border-color: #f59e0b;

            box-shadow:
                0 0 0 4px rgba(245, 158, 11, 0.10);
        }

        textarea {

            resize: vertical;

            min-height: 140px;
        }

        .submit-btn {

            width: 100%;

            border: none;

            background:
                linear-gradient(135deg,
                    #f59e0b,
                    #d97706);

            color: white;

            padding: 18px;

            border-radius: 18px;

            font-size: 15px;

            font-weight: 800;

            cursor: pointer;

            transition: var(--transition);

            box-shadow:
                0 14px 28px rgba(217, 119, 6, 0.18);
        }

        .submit-btn:hover {

            transform: translateY(-3px);
        }

        .message {

            background: #dcfce7;

            color: #166534;

            padding: 16px;

            border-radius: 16px;

            margin-bottom: 20px;

            font-weight: 600;
        }

        .error {

            background: #fee2e2;

            color: #991b1b;

            padding: 16px;

            border-radius: 16px;

            margin-bottom: 20px;

            font-weight: 600;
        }

        /* AUTORES */

        .authors-grid {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(280px, 1fr));

            gap: 22px;
        }

        .author-card {

            background:
                rgba(255, 255, 255, 0.55);

            border:
                1px solid rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(18px);

            border-radius: var(--radius);

            padding: 28px;

            box-shadow: var(--shadow);

            transition: var(--transition);
        }

        .author-card:hover {

            transform: translateY(-6px);

            box-shadow:
                0 24px 40px rgba(217, 119, 6, 0.12);
        }

        .author-name {

            font-size: 24px;

            font-weight: 800;

            color: var(--primary);

            cursor: pointer;

            margin-bottom: 18px;

            transition: var(--transition);
        }

        .author-name:hover {

            transform: translateX(4px);
        }

        .author-photo {

            width: 100%;

            height: 280px;

            object-fit: cover;

            border-radius: 20px;

            margin-bottom: 18px;

            display: none;

            animation: fadeIn 0.4s ease;
        }

        .author-bio {

            color: var(--text-muted);

            line-height: 1.8;

            font-size: 14px;
        }

        .empty {

            color: var(--text-muted);

            font-size: 16px;
        }

        @keyframes fadeIn {

            from {

                opacity: 0;

                transform: translateY(10px);
            }

            to {

                opacity: 1;

                transform: translateY(0);
            }
        }

        /* RESPONSIVO */

        @media (max-width: 1000px) {

            body {

                flex-direction: column;
            }

            .sidebar {

                position: relative;

                width: 100%;

                min-height: auto;
            }

            .page-wrapper {

                margin-left: 0;

                width: 100%;

                padding: 20px;
            }

            .container {

                grid-template-columns: 1fr;
            }
        }
    </style>

</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar">

        <div class="sidebar-header">

            <h2>ShelfHub</h2>

            <p>Sistema de Biblioteca</p>

        </div>

        <div class="sidebar-menu">

            <a
                href="../dashboard.php"
                class="menu-item">
                Início
            </a>

            <a
                href="#"
                class="menu-item active">
                Autores
            </a>

            <a
                href="shelves.php"
                class="menu-item">
                Estantes
            </a>

            <a
                href="#"
                class="menu-item">
                Reviews
            </a>

            <a
                href="posts.php"
                class="menu-item">
                Posts
            </a>

        </div>

    </nav>

    <!-- LOGO FUNDO -->
    <div class="background-logo">

        <img
            src="../img/logo.png"
            alt="Logo ShelfHub">

    </div>

    <!-- CONTEÚDO -->
    <main class="page-wrapper">

        <div class="hero">

            <h1>Autores</h1>

            <p class="subtitle">
                Cadastre autores da sua biblioteca pessoal.
            </p>

        </div>

        <div class="container">

            <!-- FORM -->
            <div class="card">

                <h2 class="card-title">
                    Novo Autor
                </h2>

                <?php if ($mensagem): ?>

                    <div class="message">
                        <?= htmlspecialchars($mensagem) ?>
                    </div>

                <?php endif; ?>

                <?php if ($erro): ?>

                    <div class="error">
                        <?= htmlspecialchars($erro) ?>
                    </div>

                <?php endif; ?>

                <form
                    method="POST"
                    enctype="multipart/form-data">

                    <div class="form-group">

                        <label>
                            Nome do autor
                        </label>

                        <input
                            type="text"
                            name="nome"
                            placeholder="Digite o nome">

                    </div>

                    <div class="form-group">

                        <label>
                            Foto
                        </label>

                        <input
                            type="file"
                            name="foto"
                            accept="image/*">

                    </div>

                    <div class="form-group">

                        <label>
                            Biografia
                        </label>

                        <textarea
                            name="bio"
                            placeholder="Digite a biografia"></textarea>

                    </div>

                    <button
                        type="submit"
                        class="submit-btn">
                        Cadastrar Autor
                    </button>

                </form>

            </div>

            <!-- AUTORES -->
            <div class="authors-grid">

                <?php if (count($authors) > 0): ?>

                    <?php foreach ($authors as $author): ?>

                        <div class="author-card">

                            <div
                                class="author-name"
                                onclick="togglePhoto('foto<?= $author['id'] ?>')">

                                <?= htmlspecialchars($author['name']) ?>

                            </div>

                            <?php if ($author['photo']): ?>

                                <img
                                    id="foto<?= $author['id'] ?>"
                                    src="uploads/authors/<?= htmlspecialchars($author['photo']) ?>"
                                    class="author-photo"
                                    alt="Foto do autor">

                            <?php endif; ?>

                            <div class="author-bio">

                                <?= nl2br(htmlspecialchars($author['bio'])) ?>

                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php else: ?>

                    <p class="empty">
                        Nenhum autor cadastrado ainda.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </main>

    <script>
        function togglePhoto(id) {

            const foto =
                document.getElementById(id);

            if (!foto) return;

            if (foto.style.display === 'block') {

                foto.style.display = 'none';

            } else {

                foto.style.display = 'block';
            }
        }
    </script>

</body>

</html>
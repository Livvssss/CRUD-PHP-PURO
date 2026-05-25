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
| CRIAR TABELA POSTS
|--------------------------------------------------------------------------
*/

$pdo->exec("
    CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

/*
|--------------------------------------------------------------------------
| CADASTRAR POST
|--------------------------------------------------------------------------
*/

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');

    if (empty($titulo) || empty($conteudo)) {

        $erro = 'Preencha todos os campos.';
    } else {

        $nomeImagem = null;

        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {

            $pastaUploads = __DIR__ . '/uploads/posts/';

            if (!is_dir($pastaUploads)) {
                mkdir($pastaUploads, 0777, true);
            }

            $extensao = pathinfo(
                $_FILES['imagem']['name'],
                PATHINFO_EXTENSION
            );

            $nomeImagem = uniqid() . '.' . $extensao;

            move_uploaded_file(
                $_FILES['imagem']['tmp_name'],
                $pastaUploads . $nomeImagem
            );
        }

        $stmt = $pdo->prepare("
            INSERT INTO posts (
                user_id,
                title,
                content,
                image
            )
            VALUES (
                :user_id,
                :title,
                :content,
                :image
            )
        ");

        $stmt->execute([
            ':user_id' => $usuarioId,
            ':title' => $titulo,
            ':content' => $conteudo,
            ':image' => $nomeImagem
        ]);

        $mensagem = 'Post publicado com sucesso!';
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR POSTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM posts
    WHERE user_id = :user_id
    ORDER BY id DESC
");

$stmt->execute([
    ':user_id' => $usuarioId
]);

$posts = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>ShelfHub | Posts</title>

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

            z-index: -1;
        }

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

        .logo {

            font-size: 34px;

            font-weight: 800;

            color: var(--primary);

            margin-bottom: 8px;
        }

        .logo-subtitle {

            color: var(--text-muted);

            font-size: 14px;

            margin-bottom: 40px;
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

        .main-content {

            margin-left: var(--sidebar-width);

            width: calc(100% - var(--sidebar-width));

            padding: 40px;
        }

        .topbar {

            margin-bottom: 40px;
        }

        .topbar h1 {

            font-size: 42px;

            color: var(--primary);
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

            margin-bottom: 30px;
        }

        .card-title {

            font-size: 24px;

            font-weight: 700;

            margin-bottom: 20px;
        }

        .form-group {

            margin-bottom: 20px;
        }

        label {

            display: block;

            margin-bottom: 10px;

            font-weight: 600;
        }

        input,
        textarea {

            width: 100%;

            border: 1px solid #e7e5e4;

            border-radius: 18px;

            padding: 16px;

            font-family: inherit;

            outline: none;
        }

        textarea {

            min-height: 180px;

            resize: vertical;
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

            font-weight: 700;

            cursor: pointer;
        }

        .message {

            background: #dcfce7;

            color: #166534;

            padding: 16px;

            border-radius: 16px;

            margin-bottom: 20px;
        }

        .error {

            background: #fee2e2;

            color: #991b1b;

            padding: 16px;

            border-radius: 16px;

            margin-bottom: 20px;
        }

        .posts-grid {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(320px, 1fr));

            gap: 24px;
        }

        .post-card {

            background:
                rgba(255, 255, 255, 0.70);

            border-radius: 22px;

            overflow: hidden;

            box-shadow: var(--shadow);
        }

        .post-image {

            width: 100%;

            height: 240px;

            object-fit: cover;
        }

        .post-content {

            padding: 24px;
        }

        .post-title {

            font-size: 24px;

            font-weight: 800;

            margin-bottom: 12px;
        }

        .post-text {

            color: var(--text-muted);

            line-height: 1.7;
        }

        .post-date {

            margin-top: 18px;

            font-size: 13px;

            color: #a1a1aa;
        }

        @media (max-width: 900px) {

            body {

                flex-direction: column;
            }

            .sidebar {

                position: relative;

                width: 100%;

                min-height: auto;
            }

            .main-content {

                margin-left: 0;

                width: 100%;

                padding: 20px;
            }
        }
    </style>

</head>

<body>

    <aside class="sidebar">

        <h1 class="logo">
            ShelfHub
        </h1>

        <p class="logo-subtitle">
            Sistema de Biblioteca
        </p>

        <nav class="sidebar-menu">

            <a href="../dashboard.php" class="menu-item">
                Início
            </a>

            <a href="authors.php" class="menu-item">
                Autores
            </a>

            <a href="shelves.php" class="menu-item">
                Estantes
            </a>

            <a href="#" class="menu-item active">
                Posts
            </a>

        </nav>

    </aside>

    <main class="main-content">

        <div class="topbar">

            <h1>Posts</h1>

        </div>

        <div class="card">

            <h2 class="card-title">
                Novo Post
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
                        Título
                    </label>

                    <input
                        type="text"
                        name="titulo">

                </div>

                <div class="form-group">

                    <label>
                        Imagem
                    </label>

                    <input
                        type="file"
                        name="imagem"
                        accept="image/*">

                </div>

                <div class="form-group">

                    <label>
                        Conteúdo
                    </label>

                    <textarea
                        name="conteudo"></textarea>

                </div>

                <button
                    type="submit"
                    class="submit-btn">

                    Publicar Post

                </button>

            </form>

        </div>

        <div class="posts-grid">

            <?php foreach ($posts as $post): ?>

                <div class="post-card">

                    <?php if ($post['image']): ?>

                        <img
                            src="uploads/posts/<?= htmlspecialchars($post['image']) ?>"
                            class="post-image">

                    <?php endif; ?>

                    <div class="post-content">

                        <div class="post-title">

                            <?= htmlspecialchars($post['title']) ?>

                        </div>

                        <div class="post-text">

                            <?= nl2br(htmlspecialchars($post['content'])) ?>

                        </div>

                        <div class="post-date">

                            <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </main>

</body>

</html>
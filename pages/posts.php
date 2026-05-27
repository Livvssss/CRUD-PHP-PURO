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
        book_id INT NOT NULL,
        content TEXT NOT NULL,
        reading_progress VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

/*
|--------------------------------------------------------------------------
| PUBLICAR POST
|--------------------------------------------------------------------------
*/

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {

    $bookId = $_POST['book_id'] ?? '';
    $conteudo = trim($_POST['content'] ?? '');
    $readingProgress = trim($_POST['reading_progress'] ?? '');

    if (
        empty($bookId) ||
        empty($conteudo) ||
        empty($readingProgress)
    ) {

        $erro = 'Preencha todos os campos.';
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO posts (
                user_id,
                book_id,
                content,
                reading_progress
            )
            VALUES (
                :user_id,
                :book_id,
                :content,
                :reading_progress
            )
        ");

        $stmt->execute([
            ':user_id' => $usuarioId,
            ':book_id' => $bookId,
            ':content' => $conteudo,
            ':reading_progress' => $readingProgress
        ]);

        $mensagem = 'Comentário publicado!';
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR LIVROS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM shelves
    WHERE user_id = :user_id
    ORDER BY title ASC
");

$stmt->execute([
    ':user_id' => $usuarioId
]);

$books = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| LISTAR POSTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        posts.*,
        shelves.title,
        shelves.cover
    FROM posts
    INNER JOIN shelves
        ON posts.book_id = shelves.id
    WHERE posts.user_id = :user_id
    ORDER BY posts.id DESC
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

            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .sidebar-top {

            display: flex;
            flex-direction: column;
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

            font-weight: 800;
        }

        .topbar p {

            color: var(--text-muted);

            margin-top: 8px;
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

        .card h2 {

            color: var(--primary);

            margin-bottom: 24px;
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
        textarea,
        select {

            width: 100%;

            border: 1px solid #e7e5e4;

            border-radius: 18px;

            padding: 16px;

            font-family: inherit;

            outline: none;
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

        .posts {

            display: flex;

            flex-direction: column;

            gap: 24px;
        }

        .post-card {

            background:
                rgba(255, 255, 255, 0.55);

            border:
                1px solid rgba(255, 255, 255, 0.35);

            backdrop-filter: blur(18px);

            border-radius: var(--radius);

            padding: 24px;

            box-shadow: var(--shadow);
        }

        .book-info {

            display: flex;

            align-items: center;

            gap: 18px;

            margin-bottom: 20px;
        }

        .book-cover {

            width: 80px;

            height: 110px;

            object-fit: cover;

            border-radius: 14px;
        }

        .book-title {

            font-size: 22px;

            font-weight: 700;
        }

        .progress {

            display: inline-block;

            margin-top: 8px;

            background: var(--primary-soft);

            color: var(--primary);

            padding: 8px 14px;

            border-radius: 999px;

            font-size: 13px;

            font-weight: 700;
        }

        .post-content {

            line-height: 1.8;

            color: #52525b;
        }

        .post-date {

            margin-top: 18px;

            color: var(--text-muted);

            font-size: 14px;
        }

        @media(max-width:900px) {

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
            }

            .book-info {

                flex-direction: column;

                align-items: flex-start;
            }
        }
    </style>

</head>

<body>

    <aside class="sidebar">

        <div class="sidebar-top">

            <div>

                <h1 class="logo">
                    ShelfHub
                </h1>

                <p class="logo-subtitle">
                    Sistema de Biblioteca
                </p>

            </div>

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

                <a href="reviews.php" class="menu-item">
                    Reviews
                </a>

            </nav>

        </div>

    </aside>

    <main class="main-content">

        <div class="topbar">

            <h1>Posts de Leitura</h1>

            <p>
                Compartilhe comentários durante sua leitura.
            </p>

        </div>

        <div class="card">

            <h2>Novo comentário</h2>

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

            <form method="POST">

                <div class="form-group">

                    <label>
                        Livro
                    </label>

                    <select
                        name="book_id"
                        required>

                        <option value="">
                            Selecione um livro
                        </option>

                        <?php foreach ($books as $book): ?>

                            <option value="<?= $book['id'] ?>">

                                <?= htmlspecialchars($book['title']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label>
                        Em que parte da leitura você está?
                    </label>

                    <input
                        type="text"
                        name="reading_progress"
                        placeholder="Ex: Página 120, Capítulo 5..."
                        required>

                </div>

                <div class="form-group">

                    <label>
                        Comentário da leitura
                    </label>

                    <textarea
                        name="content"
                        placeholder="Escreva seu comentário..."
                        required></textarea>

                </div>

                <button
                    type="submit"
                    name="create_post"
                    class="submit-btn">

                    Publicar comentário

                </button>

            </form>

        </div>

        <div class="posts">

            <?php foreach ($posts as $post): ?>

                <div class="post-card">

                    <div class="book-info">

                        <?php if ($post['cover']): ?>

                            <img
                                src="uploads/books/<?= htmlspecialchars($post['cover']) ?>"
                                class="book-cover">

                        <?php endif; ?>

                        <div>

                            <div class="book-title">

                                <?= htmlspecialchars($post['title']) ?>

                            </div>

                            <div class="progress">

                                <?= htmlspecialchars($post['reading_progress']) ?>

                            </div>

                        </div>

                    </div>

                    <div class="post-content">

                        <?= nl2br(htmlspecialchars($post['content'])) ?>

                    </div>

                    <div class="post-date">

                        <?= date(
                            'd/m/Y H:i',
                            strtotime($post['created_at'])
                        ) ?>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>

    </main>

</body>

</html>
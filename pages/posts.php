<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$pdo = getConexao();
$usuarioId = $_SESSION['usuario_id'];

$mensagem = '';
$erro = '';

/*
|--------------------------------------------------------------------------
| CREATE - PUBLICAR POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_post'])) {

    $bookId = $_POST['book_id'] ?? '';
    $conteudo = trim($_POST['content'] ?? '');
    $readingProgress = trim($_POST['reading_progress'] ?? '');

    if (empty($bookId) || empty($conteudo) || empty($readingProgress)) {

        $erro = 'Preencha todos os campos.';

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO posts (
                user_id,
                book_id,
                content,
                reading_progress
            ) VALUES (
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
| DELETE - DELETAR POST
|--------------------------------------------------------------------------
*/

if (isset($_GET['deletar'])) {

    $postId = (int) $_GET['deletar'];

    $stmt = $pdo->prepare("
        DELETE FROM posts
        WHERE id = :id
        AND user_id = :user_id
    ");

    $stmt->execute([
        ':id' => $postId,
        ':user_id' => $usuarioId
    ]);

    header('Location: posts.php');
    exit();
}

/*
|--------------------------------------------------------------------------
| READ - LISTAR LIVROS
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
| READ - LISTAR POSTS
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfHub | Posts</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="../css/posts.css">
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-top">

            <div>
                <h1 class="logo">ShelfHub</h1>
                <p class="logo-subtitle">Sistema de Biblioteca</p>
            </div>

            <nav class="sidebar-menu">
                <a href="../dashboard.php" class="menu-item">Início</a>
                <a href="authors.php" class="menu-item">Autores</a>
                <a href="shelves.php" class="menu-item">Estantes</a>
                <a href="reviews.php" class="menu-item">Reviews</a>
                <a href="#" class="menu-item active">Posts</a>
            </nav>

            <form action="logout.php" method="POST">
                <button type="submit" class="logout-btn">Sair do Sistema</button>
            </form>

        </div>
    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Posts de Leitura</h1>
            <p>Compartilhe comentários durante sua leitura.</p>
        </div>

        <div class="card">
            <h2>Novo comentário</h2>
            <?php if ($mensagem): ?>
                <div class="message"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Livro</label>
                    <select name="book_id" required>
                        <option value="">Selecione um livro</option>

                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book['id'] ?>">
                                <?= htmlspecialchars($book['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Em que parte da leitura você está?</label>
                    <input type="text" name="reading_progress" placeholder="Ex: Página 120, Capítulo 5..." required>
                </div>

                <div class="form-group">
                    <label>Comentário da leitura</label>
                    <textarea name="content" placeholder="Escreva seu comentário..." required></textarea>
                </div>

                <button type="submit" name="create_post" class="submit-btn">
                    Publicar comentário
                </button>
            </form>
        </div>

        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">
                    <div class="book-info">
                        <?php if ($post['cover']): ?>
                            <img src="uploads/books/<?= htmlspecialchars($post['cover']) ?>" class="book-cover">
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
                        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                    </div>

                    <a href="?deletar=<?= $post['id'] ?>" class="delete-btn" onclick="return confirm('Deseja realmente excluir este post?')">
                        Excluir
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: /index.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$pdo         = getConexao();
$usuarioId   = $_SESSION['usuario_id'];
$usuarioNome = $_SESSION['usuario_nome'] ?? 'Usuário';

$mensagem    = '';
$erro        = '';
$postEditando = null;

if (isset($_SESSION['flash'])) {
    $mensagem = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

/* ============================================================
    CREATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {

    $bookId          = $_POST['book_id']          ?? '';
    $conteudo        = trim($_POST['content']          ?? '');
    $readingProgress = trim($_POST['reading_progress'] ?? '');

    if (empty($bookId) || empty($conteudo) || empty($readingProgress)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, book_id, content, reading_progress)
            VALUES (:user_id, :book_id, :content, :reading_progress)
        ");
        $stmt->execute([
            ':user_id'          => $usuarioId,
            ':book_id'          => $bookId,
            ':content'          => $conteudo,
            ':reading_progress' => $readingProgress,
        ]);
        $mensagem = 'Comentário publicado com sucesso!';
    }
}

/* ============================================================
    UPDATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {

    $postId          = (int) ($_POST['post_id']         ?? 0);
    $bookId          = $_POST['book_id']                ?? '';
    $conteudo        = trim($_POST['content']           ?? '');
    $readingProgress = trim($_POST['reading_progress']  ?? '');

    if (empty($bookId) || empty($conteudo) || empty($readingProgress) || $postId === 0) {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE posts
            SET book_id          = :book_id,
                content          = :content,
                reading_progress = :reading_progress
            WHERE id      = :id
            AND   user_id = :user_id
        ");
        $stmt->execute([
            ':book_id'          => $bookId,
            ':content'          => $conteudo,
            ':reading_progress' => $readingProgress,
            ':id'               => $postId,
            ':user_id'          => $usuarioId,
        ]);
        $mensagem = 'Post atualizado com sucesso!';
    }
}

/* ============================================================
    DELETE
   ============================================================ */
if (isset($_POST['deletar'])) {

    $postId = (int) $_POST['deletar'];

    $stmt = $pdo->prepare("
        DELETE FROM posts
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $postId, ':user_id' => $usuarioId]);

    $_SESSION['flash'] = 'Post excluído com sucesso!';
    header('Location: /pages/posts.php');
    exit();
}

/* ============================================================
    READ — post em edição (se houver)
   ============================================================ */
if (isset($_POST['editar'])) {
    $editarId = (int) $_POST['editar'];
    $stmt = $pdo->prepare("
        SELECT * FROM posts
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $editarId, ':user_id' => $usuarioId]);
    $postEditando = $stmt->fetch();
}

/* ============================================================
    READ — lista de livros
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT * FROM shelves
    WHERE user_id = :user_id
    ORDER BY title ASC
");
$stmt->execute([':user_id' => $usuarioId]);
$books = $stmt->fetchAll();

/* ============================================================
    READ — lista de posts
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT posts.*, shelves.title, shelves.cover
    FROM posts
    INNER JOIN shelves ON posts.book_id = shelves.id
    WHERE posts.user_id = :user_id
    ORDER BY posts.id DESC
");
$stmt->execute([':user_id' => $usuarioId]);
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
                <a href="posts.php" class="menu-item active">Posts</a>
            </nav>
        </div>
        <form action="/logout.php" method="POST">
            <button type="submit" class="logout-btn">Sair do Sistema</button>
        </form>
    </aside>

    <main class="main-content">

        <div class="topbar">
            <div class="welcome">
                <h1>Posts de Leitura</h1>
                <p>Olá, <?= htmlspecialchars($usuarioNome) ?>. Compartilhe comentários durante sua leitura.</p>
            </div>
            <div class="profile-badge">ShelfHub</div>
        </div>

        <!-- ===== CARD: NOVO POST ou EDITAR POST ===== -->
        <div class="card">

            <?php if ($postEditando): ?>
                <h2>Editar comentário</h2>

                <?php if ($mensagem): ?>
                    <div class="message"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <div class="error"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="post_id" value="<?= $postEditando['id'] ?>">

                    <div class="form-group">
                        <label>Livro</label>
                        <select name="book_id">
                            <option value="">Selecione um livro</option>
                            <?php foreach ($books as $book): ?>
                                <option value="<?= $book['id'] ?>" <?= $book['id'] == $postEditando['book_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($book['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Em que parte da leitura você está?</label>
                        <input type="text" name="reading_progress"
                            value="<?= htmlspecialchars($postEditando['reading_progress']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Comentário da leitura</label>
                        <textarea name="content"><?= htmlspecialchars($postEditando['content']) ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Salvar Alterações</button>
                    <a href="posts.php" class="cancel-btn">Cancelar</a>
                </form>

            <?php else: ?>
                <h2>Novo comentário</h2>

                <?php if ($mensagem): ?>
                    <div class="message"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <div class="error"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="create">

                    <div class="form-group">
                        <label>Livro</label>
                        <select name="book_id">
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
                        <input type="text" name="reading_progress" placeholder="Ex: Página 120, Capítulo 5...">
                    </div>

                    <div class="form-group">
                        <label>Comentário da leitura</label>
                        <textarea name="content" placeholder="Escreva seu comentário..."></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Publicar comentário</button>
                </form>

            <?php endif; ?>
        </div>

        <!-- ===== LISTA DE POSTS ===== -->
        <div class="posts">
            <?php foreach ($posts as $post): ?>
                <div class="post-card">

                    <div class="book-info">
                        <?php if ($post['cover']): ?>
                            <img src="uploads/books/<?= htmlspecialchars($post['cover']) ?>"
                                class="book-cover"
                                alt="Capa de <?= htmlspecialchars($post['title']) ?>">
                        <?php endif; ?>
                        <div>
                            <div class="book-title"><?= htmlspecialchars($post['title']) ?></div>
                            <div class="progress"><?= htmlspecialchars($post['reading_progress']) ?></div>
                        </div>
                    </div>

                    <div class="post-content">
                        <?= nl2br(htmlspecialchars($post['content'])) ?>
                    </div>

                    <div class="post-date" data-ts="<?= strtotime($post['created_at']) ?>">
                        <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                    </div>

                    <div class="post-actions">
                        <form method="POST">
                            <input type="hidden" name="editar" value="<?= $post['id'] ?>">
                            <button type="submit" class="edit-btn">Editar</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Deseja realmente excluir este post?')">
                            <input type="hidden" name="deletar" value="<?= $post['id'] ?>">
                            <button type="submit" class="delete-btn">Excluir</button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <script>
        document.querySelectorAll('.post-date[data-ts]').forEach(el => {
            const d = new Date(el.dataset.ts * 1000);
            el.textContent = d.toLocaleString('pt-BR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: false
            });
        });
    </script>
</body>
</html>
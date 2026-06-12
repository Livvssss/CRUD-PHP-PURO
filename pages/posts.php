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

$mensagem      = '';
$erro          = '';
$reviewEditando = null;

if (isset($_SESSION['flash'])) {
    $mensagem = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

/* IDs dos livros que o usuário já resenhrou — deve vir antes do CREATE */
$stmt = $pdo->prepare("
    SELECT book_id FROM reviews
    WHERE user_id = :user_id
");
$stmt->execute([':user_id' => $usuarioId]);
$livrosJaResenhados = $stmt->fetchAll(PDO::FETCH_COLUMN);

/* ============================================================
    CREATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {

    $livroId = $_POST['livro_id'] ?? '';
    $titulo  = trim($_POST['titulo'] ?? '');
    $texto   = trim($_POST['texto']  ?? '');
    $nota    = $_POST['nota'] ?? null;

    if (empty($livroId) || empty($titulo) || empty($texto)) {
        $erro = 'Preencha todos os campos.';
    } elseif (in_array($livroId, $livrosJaResenhados)) {
        $erro = 'Você já escreveu uma review para este livro.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO reviews (user_id, book_id, review_title, review_text, rating)
            VALUES (:user_id, :book_id, :review_title, :review_text, :rating)
        ");
        $stmt->execute([
            ':user_id'      => $usuarioId,
            ':book_id'      => $livroId,
            ':review_title' => $titulo,
            ':review_text'  => $texto,
            ':rating'       => !empty($nota) ? $nota : null,
        ]);
        // Recarrega lista para refletir o novo livro resenhado
        $stmt = $pdo->prepare("SELECT book_id FROM reviews WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $usuarioId]);
        $livrosJaResenhados = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $mensagem = 'Review publicada com sucesso!';
    }
}

/* ============================================================
    UPDATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {

    $reviewId = (int) ($_POST['review_id'] ?? 0);
    $livroId  = $_POST['livro_id'] ?? '';
    $titulo   = trim($_POST['titulo'] ?? '');
    $texto    = trim($_POST['texto']  ?? '');
    $nota     = $_POST['nota'] ?? null;

    if (empty($livroId) || empty($titulo) || empty($texto) || $reviewId === 0) {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE reviews
            SET book_id      = :book_id,
                review_title = :review_title,
                review_text  = :review_text,
                rating       = :rating
            WHERE id      = :id
            AND   user_id = :user_id
        ");
        $stmt->execute([
            ':book_id'      => $livroId,
            ':review_title' => $titulo,
            ':review_text'  => $texto,
            ':rating'       => !empty($nota) ? $nota : null,
            ':id'           => $reviewId,
            ':user_id'      => $usuarioId,
        ]);
        $mensagem = 'Review atualizada com sucesso!';
    }
}

/* ============================================================
    DELETE
   ============================================================ */
if (isset($_POST['deletar'])) {

    $reviewId = (int) $_POST['deletar'];

    $stmt = $pdo->prepare("
        DELETE FROM reviews
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $reviewId, ':user_id' => $usuarioId]);

    $_SESSION['flash'] = 'Review excluída com sucesso!';
    header('Location: /pages/reviews.php');
    exit();
}

/* ============================================================
    READ — review em edição (se houver)
   ============================================================ */
if (isset($_POST['editar'])) {
    $editarId = (int) $_POST['editar'];
    $stmt = $pdo->prepare("
        SELECT * FROM reviews
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $editarId, ':user_id' => $usuarioId]);
    $reviewEditando = $stmt->fetch();
}

/* ============================================================
    READ — lista de livros para o select
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT * FROM shelves
    WHERE user_id = :user_id
    ORDER BY title ASC
");
$stmt->execute([':user_id' => $usuarioId]);
$books = $stmt->fetchAll();

/* ============================================================
    READ — lista de reviews
   ============================================================ */
$stmt = $pdo->prepare("
    SELECT reviews.*, shelves.title AS book_title, shelves.cover AS book_cover
    FROM reviews
    INNER JOIN shelves ON reviews.book_id = shelves.id
    WHERE reviews.user_id = :user_id
    ORDER BY reviews.id DESC
");
$stmt->execute([':user_id' => $usuarioId]);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfHub | Reviews</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/reviews.css">
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
                <a href="reviews.php" class="menu-item active">Reviews</a>
                <a href="posts.php" class="menu-item">Posts</a>
            </nav>
        </div>
        <form action="/logout.php" method="POST">
            <button type="submit" class="logout-btn">Sair do Sistema</button>
        </form>
    </aside>

    <main class="main-content">

        <div class="topbar">
            <div class="welcome">
                <h1>Reviews</h1>
                <p>Olá, <?= htmlspecialchars($usuarioNome) ?>. Organize e gerencie suas reviews.</p>
            </div>
            <div class="profile-badge">ShelfHub</div>
        </div>

        <!-- ===== CARD: NOVA REVIEW ou EDITAR REVIEW ===== -->
        <div class="card">

            <?php if ($reviewEditando): ?>
                <h2 class="card-title">Editar Review</h2>

                <?php if ($mensagem): ?>
                    <div class="message"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>

                <?php if ($erro): ?>
                    <div class="error"><?= htmlspecialchars($erro) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="review_id" value="<?= $reviewEditando['id'] ?>">

                    <div class="form-group">
                        <label>Livro</label>
                        <select name="livro_id">
                            <option value="">Selecione um livro</option>
                            <?php foreach ($books as $book): ?>
                                <option value="<?= $book['id'] ?>" <?= $book['id'] == $reviewEditando['book_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($book['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Título da review</label>
                        <input type="text" name="titulo" value="<?= htmlspecialchars($reviewEditando['review_title']) ?>">
                    </div>

                    <div class="form-group">
                        <label>Nota</label>
                        <input type="number" step="0.5" min="0" max="5" name="nota"
                            value="<?= htmlspecialchars($reviewEditando['rating'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Review</label>
                        <textarea name="texto"><?= htmlspecialchars($reviewEditando['review_text']) ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Salvar Alterações</button>
                    <a href="reviews.php" class="cancel-btn">Cancelar</a>
                </form>

            <?php else: ?>
                <h2 class="card-title">Nova Review</h2>

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
                        <select name="livro_id">
                            <option value="">Selecione um livro</option>
                            <?php foreach ($books as $book): ?>
                                <?php if (!in_array($book['id'], $livrosJaResenhados)): ?>
                                    <option value="<?= $book['id'] ?>">
                                        <?= htmlspecialchars($book['title']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Título da review</label>
                        <input type="text" name="titulo" placeholder="Digite o título">
                    </div>

                    <div class="form-group">
                        <label>Nota</label>
                        <input type="number" step="0.5" min="0" max="5" name="nota" placeholder="0 – 5">
                    </div>

                    <div class="form-group">
                        <label>Review</label>
                        <textarea name="texto" placeholder="Escreva sua review"></textarea>
                    </div>

                    <button type="submit" class="submit-btn">Publicar Review</button>
                </form>

            <?php endif; ?>
        </div>

        <!-- ===== GRID DE REVIEWS ===== -->
        <div class="reviews-grid">
            <?php foreach ($reviews as $review): ?>
                <div class="review-card">

                    <?php if ($review['book_cover']): ?>
                        <img src="uploads/books/<?= htmlspecialchars($review['book_cover']) ?>"
                            class="review-cover"
                            alt="Capa de <?= htmlspecialchars($review['book_title']) ?>">
                    <?php endif; ?>

                    <div class="review-content">
                        <div class="review-book"><?= htmlspecialchars($review['book_title']) ?></div>
                        <div class="review-title"><?= htmlspecialchars($review['review_title']) ?></div>
                        <div class="review-text"><?= nl2br(htmlspecialchars($review['review_text'])) ?></div>

                        <?php if ($review['rating']): ?>
                            <div class="review-rating">⭐ <?= $review['rating'] ?>/5</div>
                        <?php endif; ?>

                        <div class="review-date" data-ts="<?= strtotime($review['created_at']) ?>">
                            <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                        </div>

                        <div class="review-actions">
                            <form method="POST">
                                <input type="hidden" name="editar" value="<?= $review['id'] ?>">
                                <button type="submit" class="edit-btn">Editar</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Deseja realmente excluir esta review?')">
                                <input type="hidden" name="deletar" value="<?= $review['id'] ?>">
                                <button type="submit" class="delete-btn">Excluir</button>
                            </form>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </main>

    <script>
        document.querySelectorAll('.review-date[data-ts]').forEach(el => {
            const d = new Date(el.dataset.ts * 1000);
            el.textContent = d.toLocaleString('pt-BR', {
                day: '2-digit', month: '2-digit', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: false
            });
        });
    </script>
</body>

</html>
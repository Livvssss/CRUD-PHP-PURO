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
| CRIAR TABELA REVIEWS
|--------------------------------------------------------------------------
*/

$pdo->exec("
    CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        review_title VARCHAR(255) NOT NULL,
        review_text TEXT NOT NULL,
        rating DECIMAL(2,1) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

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
| CADASTRAR REVIEW
|--------------------------------------------------------------------------
*/

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $livroId = $_POST['livro_id'] ?? '';
    $titulo = trim($_POST['titulo'] ?? '');
    $texto = trim($_POST['texto'] ?? '');
    $nota = $_POST['nota'] ?? null;

    if (empty($livroId) || empty($titulo) || empty($texto)) {

        $erro = 'Preencha todos os campos.';

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO reviews (
                user_id,
                book_id,
                review_title,
                review_text,
                rating
            )
            VALUES (
                :user_id,
                :book_id,
                :review_title,
                :review_text,
                :rating
            )
        ");

        $stmt->execute([
            ':user_id' => $usuarioId,
            ':book_id' => $livroId,
            ':review_title' => $titulo,
            ':review_text' => $texto,
            ':rating' => !empty($nota) ? $nota : null
        ]);

        $mensagem = 'Review publicada com sucesso!';
    }
}

/*
|--------------------------------------------------------------------------
| LISTAR REVIEWS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        reviews.*,
        shelves.title AS book_title,
        shelves.cover AS book_cover
    FROM reviews

    INNER JOIN shelves
        ON reviews.book_id = shelves.id

    WHERE reviews.user_id = :user_id

    ORDER BY reviews.id DESC
");

$stmt->execute([
    ':user_id' => $usuarioId
]);

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
        <h1 class="logo">ShelfHub</h1>
        <p class="logo-subtitle">Sistema de Biblioteca</p>

        <nav class="sidebar-menu">
            <a href="../dashboard.php" class="menu-item">Início</a>
            <a href="authors.php" class="menu-item">Autores</a>
            <a href="shelves.php" class="menu-item">Estantes</a>
            <a href="#" class="menu-item active">Reviews</a>
            <a href="posts.php" class="menu-item">Posts</a>
        </nav>

    </aside>

    <main class="main-content">
        <div class="topbar">
            <h1>Reviews</h1>
        </div>

        <div class="card">
            <h2 class="card-title">Nova Review</h2>

            <?php if ($mensagem): ?>
                <div class="message"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Livro</label>
                    <select name="livro_id">
                        <option value="">Selecione um livro</option>

                        <?php foreach ($books as $book): ?>
                            <option value="<?= $book['id'] ?>">
                                <?= htmlspecialchars($book['title']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Título da review</label>
                    <input type="text" name="titulo">
                </div>

                <div class="form-group">
                    <label>Nota</label>
                    <input type="number" step="0.5" min="0" max="5" name="nota">
                </div>

                <div class="form-group">
                    <label>Review</label>
                    <textarea name="texto"></textarea>
                </div>

                <button type="submit" class="submit-btn">
                    Publicar Review
                </button>

            </form>
        </div>

        <div class="reviews-grid">

            <?php foreach ($reviews as $review): ?>
                <div class="review-card">

                    <?php if ($review['book_cover']): ?>
                        <img src="uploads/books/<?= htmlspecialchars($review['book_cover']) ?>" class="review-cover">
                    <?php endif; ?>

                    <div class="review-content">

                        <div class="review-book">
                            <?= htmlspecialchars($review['book_title']) ?>
                        </div>

                        <div class="review-title">
                            <?= htmlspecialchars($review['review_title']) ?>
                        </div>

                        <div class="review-text">
                            <?= nl2br(htmlspecialchars($review['review_text'])) ?>
                        </div>

                        <?php if ($review['rating']): ?>
                            <div class="review-rating">
                                ⭐ <?= $review['rating'] ?>/5
                            </div>

                        <?php endif; ?>
                        <div class="review-date">
                            <?= date('d/m/Y H:i', strtotime($review['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

        </div>
    </main>

</body>
</html>
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

    if (
        empty($livroId) ||
        empty($titulo) ||
        empty($texto)
    ) {

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
            ':rating' => !empty($nota)
                ? $nota
                : null
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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>ShelfHub | Reviews</title>

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
        select,
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

        .reviews-grid {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(320px, 1fr));

            gap: 24px;
        }

        .review-card {

            background:
                rgba(255, 255, 255, 0.70);

            border-radius: 22px;

            overflow: hidden;

            box-shadow: var(--shadow);
        }

        .review-cover {

            width: 100%;

            height: 260px;

            object-fit: cover;
        }

        .review-content {

            padding: 24px;
        }

        .review-book {

            color: var(--primary);

            font-weight: 700;

            margin-bottom: 10px;
        }

        .review-title {

            font-size: 24px;

            font-weight: 800;

            margin-bottom: 14px;
        }

        .review-text {

            color: var(--text-muted);

            line-height: 1.7;
        }

        .review-rating {

            margin-top: 18px;

            font-weight: 700;

            color: #ca8a04;
        }

        .review-date {

            margin-top: 10px;

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

            <a href="posts.php" class="menu-item">
                Posts
            </a>

            <a href="#" class="menu-item active">
                Reviews
            </a>

        </nav>

    </aside>

    <main class="main-content">

        <div class="topbar">

            <h1>Reviews</h1>

        </div>

        <div class="card">

            <h2 class="card-title">
                Nova Review
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

            <form method="POST">

                <div class="form-group">

                    <label>
                        Livro
                    </label>

                    <select name="livro_id">

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
                        Título da review
                    </label>

                    <input
                        type="text"
                        name="titulo">

                </div>

                <div class="form-group">

                    <label>
                        Nota
                    </label>

                    <input
                        type="number"
                        step="0.5"
                        min="0"
                        max="5"
                        name="nota">

                </div>

                <div class="form-group">

                    <label>
                        Review
                    </label>

                    <textarea
                        name="texto"></textarea>

                </div>

                <button
                    type="submit"
                    class="submit-btn">

                    Publicar Review

                </button>

            </form>

        </div>

        <div class="reviews-grid">

            <?php foreach ($reviews as $review): ?>

                <div class="review-card">

                    <?php if ($review['book_cover']): ?>

                        <img
                            src="uploads/books/<?= htmlspecialchars($review['book_cover']) ?>"
                            class="review-cover">

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
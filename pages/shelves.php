<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/../config/database.php';

$pdo = getConexao();
$usuarioId = $_SESSION['usuario_id'];
$usuarioNome = $_SESSION['usuario_nome'] ?? 'Usuário';

/*
|--------------------------------------------------------------------------
| CADASTRAR LIVRO
|--------------------------------------------------------------------------
*/

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_livro'])) {

    $titulo = trim($_POST['titulo'] ?? '');
    $autor = $_POST['autor'] ?? '';
    $dataLancamento = $_POST['data_lancamento'] ?? '';

    if (empty($titulo) || empty($autor)) {

        $erro = 'Preencha os campos obrigatórios.';

    } else {

        $nomeCapa = null;

        if (isset($_FILES['capa']) && $_FILES['capa']['error'] === 0) {

            $pastaUploads = __DIR__ . '/uploads/books/';

            if (!is_dir($pastaUploads)) {
                mkdir($pastaUploads, 0777, true);
            }

            $extensao = pathinfo($_FILES['capa']['name'], PATHINFO_EXTENSION);
            $nomeCapa = uniqid() . '.' . $extensao;

            move_uploaded_file($_FILES['capa']['tmp_name'], $pastaUploads . $nomeCapa);
        }

        $stmt = $pdo->prepare("
            INSERT INTO shelves (user_id, author_id, title, cover, release_date)
            VALUES (:user_id, :author_id, :title, :cover, :release_date)
        ");

        $stmt->execute([
            ':user_id' => $usuarioId,
            ':author_id' => $autor,
            ':title' => $titulo,
            ':cover' => $nomeCapa,
            ':release_date' => $dataLancamento
        ]);

        $mensagem = 'Livro adicionado à estante!';
    }
}

/*
|--------------------------------------------------------------------------
| SALVAR OPÇÕES
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_opcoes'])) {

    $livroId = $_POST['livro_id'];

    $stmt = $pdo->prepare("
        UPDATE shelves
        SET
            reading_status = :reading_status,
            rating = :rating,
            reading_goal = :reading_goal,
            reading_time = :reading_time,
            tags = :tags,
            review = :review,
            quotes = :quotes,
            reading_history = :reading_history,
            reading_date = :reading_date,
            reading_start = :reading_start,
            reading_end = :reading_end
        WHERE id = :id
        AND user_id = :user_id
    ");

    $stmt->execute([
        ':reading_status' => $_POST['reading_status'],
        ':rating' => !empty($_POST['rating']) ? $_POST['rating'] : null,
        ':reading_goal' => $_POST['reading_goal'],
        ':reading_time' => $_POST['reading_time'],
        ':tags' => $_POST['tags'],
        ':review' => $_POST['review'],
        ':quotes' => $_POST['quotes'],
        ':reading_history' => $_POST['reading_history'],
        ':reading_date' => !empty($_POST['reading_date']) ? $_POST['reading_date'] : null,
        ':reading_start' => !empty($_POST['reading_start']) ? $_POST['reading_start'] : null,
        ':reading_end' => !empty($_POST['reading_end']) ? $_POST['reading_end'] : null,
        ':id' => $livroId,
        ':user_id' => $usuarioId
    ]);

    $mensagem = 'Livro atualizado!';
}

/*
|--------------------------------------------------------------------------
| DELETAR LIVRO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['deletar_livro'])) {

    $livroId = $_POST['livro_id'] ?? '';

    $stmt = $pdo->prepare("
        SELECT cover
        FROM shelves
        WHERE id = :id
        AND user_id = :user_id
        LIMIT 1
    ");

    $stmt->execute([
        ':id' => $livroId,
        ':user_id' => $usuarioId
    ]);

    $livro = $stmt->fetch();

    if ($livro) {

        if (!empty($livro['cover'])) {

            $caminhoCapa = __DIR__ . '/uploads/books/' . $livro['cover'];

            if (file_exists($caminhoCapa)) {
                unlink($caminhoCapa);
            }
        }

        $stmt = $pdo->prepare("
            DELETE FROM shelves
            WHERE id = :id
            AND user_id = :user_id
        ");

        $stmt->execute([
            ':id' => $livroId,
            ':user_id' => $usuarioId
        ]);

        $mensagem = 'Livro removido da estante!';
    }
}

/*
|--------------------------------------------------------------------------
| AUTORES
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT *
    FROM authors
    WHERE user_id = :user_id
    ORDER BY name ASC
");

$stmt->execute([
    ':user_id' => $usuarioId
]);

$authors = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| LIVROS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT shelves.*, authors.name AS author_name
    FROM shelves
    INNER JOIN authors ON shelves.author_id = authors.id
    WHERE shelves.user_id = :user_id
    ORDER BY shelves.id DESC
");

$stmt->execute([
    ':user_id' => $usuarioId
]);

$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ShelfHub | Estante</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/shelves.css">

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
                <a href="#" class="menu-item active">Estantes</a>
                <a href="reviews.php" class="menu-item">Reviews</a>
                <a href="posts.php" class="menu-item">Posts</a>
            </nav>
        </div>

        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Sair do Sistema</button>
        </form>
    </aside>

    <main class="main-content">
        <div class="topbar">

            <div class="welcome">
                <h1>Minha Estante</h1>
                <p>Olá, <?= htmlspecialchars($usuarioNome) ?>. Organize e gerencie suas leituras.</p>
            </div>

            <div class="profile-badge">ShelfHub</div>

        </div>

        <div class="card">
            <h2>Adicionar Livro</h2>

            <?php if ($mensagem): ?>
                <div class="message"><?= htmlspecialchars($mensagem) ?></div>
            <?php endif; ?>

            <?php if ($erro): ?>
                <div class="error"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome do livro</label>
                        <input type="text" name="titulo" required>
                    </div>

                    <div class="form-group">
                        <label>Autor</label>
                        <select name="autor" required>
                            <option value="">Selecione</option>
                            <?php foreach ($authors as $author): ?>
                                <option value="<?= $author['id'] ?>"><?= htmlspecialchars($author['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Capa</label>
                        <input type="file" name="capa" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label>Data de lançamento</label>
                        <input type="date" name="data_lancamento">
                    </div>
                </div>

                <button type="submit" name="cadastrar_livro" class="submit-btn">Adicionar na Estante</button>
            </form>
        </div>

        <div class="books-grid">
            <?php foreach ($books as $book): ?>
                <div class="book-card">
                    <?php if ($book['cover']): ?>
                        <img src="uploads/books/<?= htmlspecialchars($book['cover']) ?>" class="book-cover">
                    <?php endif; ?>

                    <div class="book-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="book-author"><?= htmlspecialchars($book['author_name']) ?></div>
                    <div class="book-status"><?= htmlspecialchars($book['reading_status']) ?></div>
                    <button class="options-btn" onclick="abrirModal('modal<?= $book['id'] ?>')">Opções do livro</button>
                </div>

                <div class="modal" id="modal<?= $book['id'] ?>">
                    <div class="modal-content">
                        <h2 style="margin-bottom:25px;"><?= htmlspecialchars($book['title']) ?></h2>
                        <form method="POST">
                            <input type="hidden" name="livro_id" value="<?= $book['id'] ?>">
                            <div class="modal-grid">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="reading_status">
                                        <option value="Lendo" <?= $book['reading_status'] === 'Lendo' ? 'selected' : '' ?>>Lendo</option>
                                        <option value="Lido" <?= $book['reading_status'] === 'Lido' ? 'selected' : '' ?>>Lido</option>
                                        <option value="Quero Ler" <?= $book['reading_status'] === 'Quero Ler' ? 'selected' : '' ?>>Quero Ler</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Avaliação</label>
                                    <input type="number" step="0.5" max="5" min="0" name="rating" value="<?= $book['rating'] ?>">
                                </div>

                                <div class="form-group">
                                    <label>Data de leitura</label>
                                    <input type="date" name="reading_date" value="<?= $book['reading_date'] ?>">
                                </div>

                                <div class="form-group">
                                    <label>Meta de leitura</label>
                                    <input type="text" name="reading_goal" value="<?= htmlspecialchars($book['reading_goal'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Início da leitura</label>
                                    <input type="date" name="reading_start" value="<?= htmlspecialchars($book['reading_start'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Fim da leitura</label>
                                    <input type="date" name="reading_end" value="<?= htmlspecialchars($book['reading_end'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Tempo de leitura</label>
                                    <input type="text" name="reading_time" value="<?= htmlspecialchars($book['reading_time'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label>Etiquetas</label>
                                    <input type="text" name="tags" value="<?= htmlspecialchars($book['tags'] ?? '') ?>">
                                </div>
                            </div>

                            <div class="form-group" style="margin-top:20px;">
                                <label>Resenha</label>
                                <textarea name="review"><?= htmlspecialchars($book['review'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Citações</label>
                                <textarea name="quotes"><?= htmlspecialchars($book['quotes'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Histórico de leitura</label>
                                <textarea name="reading_history"><?= htmlspecialchars($book['reading_history'] ?? '') ?></textarea>
                            </div>

                            <button type="submit" name="salvar_opcoes" class="submit-btn">Salvar Alterações</button>
                            <button type="submit" name="deletar_livro" class="submit-btn" style="background:#dc2626; margin-top:10px;" onclick="return confirm('Deseja realmente excluir este livro?')">Excluir Livro</button>
                            <button type="button" class="submit-btn" style="background:#ef4444; margin-top:10px;" onclick="fecharModal('modal<?= $book['id'] ?>')">Fechar</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        function abrirModal(id) {
            document.getElementById(id).style.display = 'flex';
        }

        function fecharModal(id) {
            document.getElementById(id).style.display = 'none';
        }
    </script>
</body>
</html>
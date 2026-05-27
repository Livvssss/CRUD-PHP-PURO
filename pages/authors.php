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
    <link rel="stylesheet" href="css/authors.css">

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
                href="reviews.php"
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
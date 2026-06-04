<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}
require_once __DIR__ . '/../config/database.php';
$pdo         = getConexao();
$usuarioId   = $_SESSION['usuario_id'];
$usuarioNome = $_SESSION['usuario_nome'] ?? 'Usuário';
$mensagem = '';
$erro     = '';
if (isset($_GET['atualizado'])) {
    $mensagem = 'Autor atualizado com sucesso!';
}

/* ============================================================
    HELPER - UPLOAD DE FOTO
   ============================================================ */
function uploadFoto(array $arquivo, string $pasta): ?string
{
    if ($arquivo['error'] !== 0) {
        return null;
    }
    if (!is_dir($pasta)) {
        mkdir($pasta, 0777, true);
    }
    $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
    $nomeFoto = uniqid() . '.' . $extensao;
    move_uploaded_file($arquivo['tmp_name'], $pasta . $nomeFoto);
    return $nomeFoto;
}
$pastaUploads = __DIR__ . '/uploads/authors/';

/* ============================================================
    CREATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    $nome = trim($_POST['nome'] ?? '');
    $bio  = trim($_POST['bio']  ?? '');
    if (empty($nome) || empty($bio)) {
        $erro = 'Preencha todos os campos.';
    } else {
        $nomeFoto = isset($_FILES['foto']) ? uploadFoto($_FILES['foto'], $pastaUploads) : null;
        $stmt = $pdo->prepare("
            INSERT INTO authors (user_id, name, bio, photo)
            VALUES (:user_id, :name, :bio, :photo)
        ");
        $stmt->execute([
            ':user_id' => $usuarioId,
            ':name'    => $nome,
            ':bio'     => $bio,
            ':photo'   => $nomeFoto,
        ]);
        $mensagem = 'Autor cadastrado com sucesso!';
    }
}

/* ============================================================
    UPDATE
   ============================================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $autorId = (int) ($_POST['autor_id'] ?? 0);
    $nome    = trim($_POST['nome'] ?? '');
    $bio     = trim($_POST['bio']  ?? '');
    if (empty($nome) || empty($bio) || $autorId === 0) {
        $erro = 'Preencha todos os campos.';
    } else {
        $stmtAtual = $pdo->prepare("
            SELECT photo FROM authors
            WHERE id = :id AND user_id = :user_id
        ");
        $stmtAtual->execute([':id' => $autorId, ':user_id' => $usuarioId]);
        $autorAtual = $stmtAtual->fetch();
        $novaFoto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
            if (!empty($autorAtual['photo'])) {
                $caminhoAntigo = $pastaUploads . $autorAtual['photo'];
                if (file_exists($caminhoAntigo)) {
                    unlink($caminhoAntigo);
                }
            }
            $novaFoto = uploadFoto($_FILES['foto'], $pastaUploads);
        }
        if ($novaFoto !== null) {
            $stmt = $pdo->prepare("
                UPDATE authors
                SET name = :name, bio = :bio, photo = :photo
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->execute([
                ':name'    => $nome,
                ':bio'     => $bio,
                ':photo'   => $novaFoto,
                ':id'      => $autorId,
                ':user_id' => $usuarioId,
            ]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE authors
                SET name = :name, bio = :bio
                WHERE id = :id AND user_id = :user_id
            ");
            $stmt->execute([
                ':name'    => $nome,
                ':bio'     => $bio,
                ':id'      => $autorId,
                ':user_id' => $usuarioId,
            ]);
        }
        header('Location: authors.php?atualizado=1');
        exit();
    }
}

/* ============================================================
    DELETE
   ============================================================ */
if (isset($_GET['deletar'])) {
    $autorId = (int) $_GET['deletar'];
    $stmt = $pdo->prepare("
        SELECT photo FROM authors
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $autorId, ':user_id' => $usuarioId]);
    $autor = $stmt->fetch();
    if ($autor) {
        if (!empty($autor['photo'])) {
            $caminhoFoto = $pastaUploads . $autor['photo'];
            if (file_exists($caminhoFoto)) {
                unlink($caminhoFoto);
            }
        }
        $stmt = $pdo->prepare("
            DELETE FROM authors
            WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute([':id' => $autorId, ':user_id' => $usuarioId]);
    }
    header('Location: authors.php');
    exit();
}

/* ============================================================
    READ - busca autor em edição (se houver) e lista todos
   ============================================================ */
$autorEditando = null;
if (isset($_GET['editar'])) {
    $editarId = (int) $_GET['editar'];
    $stmt = $pdo->prepare("
        SELECT * FROM authors
        WHERE id = :id AND user_id = :user_id
    ");
    $stmt->execute([':id' => $editarId, ':user_id' => $usuarioId]);
    $autorEditando = $stmt->fetch();
}
$stmt = $pdo->prepare("
    SELECT * FROM authors
    WHERE user_id = :user_id
    ORDER BY id DESC
");
$stmt->execute([':user_id' => $usuarioId]);
$authors = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShelfHub | Autores</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/authors.css">
</head>
<body>
    <!-- ===================== SIDEBAR ===================== -->
    <div class="sidebar">
        <div class="sidebar-top">
            <div>
                <h1 class="logo">ShelfHub</h1>
                <p class="logo-subtitle">Sistema de Biblioteca</p>
            </div>

            <nav class="sidebar-menu">
                <a href="../dashboard.php" class="menu-item">Início</a>
                <a href="authors.php" class="menu-item active">Autores</a>
                <a href="shelves.php" class="menu-item">Estantes</a>
                <a href="reviews.php" class="menu-item">Reviews</a>
                <a href="posts.php" class="menu-item">Posts</a>
            </nav>
        </div>

        <form action="/logout.php" method="POST">
            <button type="submit" class="logout-btn">Sair do Sistema</button>
        </form>
    </div>

    <!-- ===================== CONTEÚDO PRINCIPAL ===================== -->
    <main class="page-wrapper">

        <div class="topbar">
            <div class="welcome">
                <h1>Autores</h1>
                <p>Olá, <?= htmlspecialchars($usuarioNome) ?>. Organize e gerencie os Autores.</p>
            </div>
            <div class="profile-badge">ShelfHub</div>
        </div>

        <div class="container">

            <!-- ===== CARD: NOVO AUTOR ou EDITAR AUTOR ===== -->
            <div class="card">

                <?php if ($autorEditando): ?>
                    <!-- Formulário de edição -->
                    <h2 class="card-title">Editar Autor</h2>

                    <?php if ($erro): ?>
                        <div class="error"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action"   value="update">
                        <input type="hidden" name="autor_id" value="<?= $autorEditando['id'] ?>">

                        <div class="form-group">
                            <label>Nome do autor</label>
                            <input type="text" name="nome"
                                value="<?= htmlspecialchars($autorEditando['name']) ?>">
                        </div>

                        <?php if ($autorEditando['photo']): ?>
                            <div class="form-group">
                                <label>Foto atual</label>
                                <img src="uploads/authors/<?= htmlspecialchars($autorEditando['photo']) ?>"
                                    alt="Foto atual"
                                    style="display:block;max-width:120px;border-radius:8px;margin-top:6px;">
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Nova foto (opcional)</label>
                            <input type="file" name="foto" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label>Biografia</label>
                            <textarea name="bio"><?= htmlspecialchars($autorEditando['bio']) ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Salvar Alterações</button>
                        <a href="authors.php" class="cancel-btn">Cancelar</a>
                    </form>

                <?php else: ?>
                    <!-- Formulário de criação -->
                    <h2 class="card-title">Novo Autor</h2>

                    <?php if ($mensagem): ?>
                        <div class="message"><?= htmlspecialchars($mensagem) ?></div>
                    <?php endif; ?>

                    <?php if ($erro): ?>
                        <div class="error"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create">

                        <div class="form-group">
                            <label>Nome do autor</label>
                            <input type="text" name="nome" placeholder="Digite o nome">
                        </div>

                        <div class="form-group">
                            <label>Foto</label>
                            <input type="file" name="foto" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label>Biografia</label>
                            <textarea name="bio" placeholder="Digite a biografia"></textarea>
                        </div>

                        <button type="submit" class="submit-btn">Cadastrar Autor</button>
                    </form>

                <?php endif; ?>
            </div>

            <!-- ===== GRID DE AUTORES ===== -->
            <div class="authors-grid">
                <?php if (count($authors) > 0): ?>
                    <?php foreach ($authors as $author): ?>
                        <div class="author-card">

                            <div class="author-name" onclick="togglePhoto('foto<?= $author['id'] ?>')">
                                <?= htmlspecialchars($author['name']) ?>
                            </div>

                            <?php if ($author['photo']): ?>
                                <img
                                    id="foto<?= $author['id'] ?>"
                                    src="uploads/authors/<?= htmlspecialchars($author['photo']) ?>"
                                    class="author-photo"
                                    alt="Foto de <?= htmlspecialchars($author['name']) ?>">
                            <?php endif; ?>

                            <div class="author-bio">
                                <?= nl2br(htmlspecialchars($author['bio'])) ?>
                            </div>

                            <a href="?editar=<?= $author['id'] ?>" class="edit-author-btn">Editar</a>

                            <a
                                href="?deletar=<?= $author['id'] ?>"
                                class="delete-author-btn"
                                onclick="return confirm('Deseja realmente excluir este autor?')">
                                Excluir
                            </a>

                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty">Nenhum autor cadastrado ainda.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script>
        function togglePhoto(id) {
            const foto = document.getElementById(id);
            if (!foto) return;
            foto.style.display = foto.style.display === 'block' ? 'none' : 'block';
        }
    </script>

</body>
</html>
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
$mensagem = '';
$erro     = '';
if (isset($_GET['criado'])) {
    $mensagem = 'Autor cadastrado com sucesso!';
}
if (isset($_GET['atualizado'])) {
    $mensagem = 'Autor atualizado com sucesso!';
}
if (isset($_GET['deletado'])) {
    $mensagem = 'Autor excluído com sucesso!';
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
        $stmtDup = $pdo->prepare("
            SELECT COUNT(*) FROM authors
            WHERE user_id = :user_id AND LOWER(name) = LOWER(:name)
        ");
        $stmtDup->execute([':user_id' => $usuarioId, ':name' => $nome]);
        if ($stmtDup->fetchColumn() > 0) {
            $erro = 'Já existe um autor com esse nome.';
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
            header('Location: authors.php');
            exit();
        }
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
        $mensagem = 'Autor atualizado com sucesso!';
    }
}

/* ============================================================
    DELETE
   ============================================================ */
if (isset($_POST['deletar'])) {
    $autorId = (int) $_POST['deletar'];
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
$autorEditando = $autorEditando ?? null;
if (isset($_POST['editar'])) {
    $editarId = (int) $_POST['editar'];
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

                    <?php if ($mensagem): ?>
                        <div class="message"><?= htmlspecialchars($mensagem) ?></div>
                    <?php endif; ?>

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

                            <div class="author-name" onclick="toggleFoto(<?= $author['id'] ?>)">
                                <?= htmlspecialchars($author['name']) ?>
                            </div>

                            <?php if ($author['photo']): ?>
                                <!-- FIX: style="display:none" inline garante que cada foto começa oculta independentemente -->
                                <img
                                    id="foto-<?= $author['id'] ?>"
                                    src="uploads/authors/<?= htmlspecialchars($author['photo']) ?>"
                                    class="author-photo"
                                    style="display:none"
                                    alt="Foto de <?= htmlspecialchars($author['name']) ?>">
                            <?php endif; ?>

                            <!-- FIX: bio com wrapper colapsável -->
                            <div class="author-bio-wrapper" id="bio-wrapper-<?= $author['id'] ?>">
                                <div class="author-bio">
                                    <?= nl2br(htmlspecialchars($author['bio'])) ?>
                                </div>
                            </div>

                            <!-- Botão "Ver mais" só aparece via JS se a bio for longa -->
                            <button
                                class="bio-toggle-btn"
                                id="bio-btn-<?= $author['id'] ?>"
                                onclick="toggleBio(<?= $author['id'] ?>)"
                                style="display:none">
                                Ver mais
                            </button>

                            <form method="POST">
                                    <input type="hidden" name="editar" value="<?= $author['id'] ?>">
                                    <button type="submit" class="edit-author-btn">
                                        Editar
                                    </button>
                                </form>
                            <form method="POST" onsubmit="return confirm('Deseja realmente excluir este autor?')">
                                <input type="hidden" name="deletar" value="<?= $author['id'] ?>">
                                <button type="submit" class="delete-author-btn">Excluir</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="empty">Nenhum autor cadastrado ainda.</p>
                <?php endif; ?>
            </div>

        </div>
    </main>

    <script>
        /* ── Toggle foto: cada card é independente ── */
        function toggleFoto(id) {
            const foto = document.getElementById('foto-' + id);
            if (!foto) return;
            const visivel = foto.style.display === 'block';
            foto.style.display = visivel ? 'none' : 'block';
        }

        /* ── Bio colapsável ── */
        const BIO_MAX_HEIGHT = 80; // px visíveis quando colapsada

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.author-bio-wrapper').forEach(function (wrapper) {
                const bio = wrapper.querySelector('.author-bio');
                if (!bio) return;

                // Extrai o id do wrapper (bio-wrapper-123 → 123)
                const id = wrapper.id.replace('bio-wrapper-', '');
                const btn = document.getElementById('bio-btn-' + id);

                if (bio.scrollHeight > BIO_MAX_HEIGHT + 10) {
                    // Bio longa: colapsa e exibe o botão
                    wrapper.style.maxHeight = BIO_MAX_HEIGHT + 'px';
                    wrapper.style.overflow  = 'hidden';
                    if (btn) btn.style.display = 'block';
                }
            });
        });

        function toggleBio(id) {
            const wrapper = document.getElementById('bio-wrapper-' + id);
            const btn     = document.getElementById('bio-btn-'     + id);
            if (!wrapper) return;

            const colapsada = wrapper.style.maxHeight !== 'none' && wrapper.style.maxHeight !== '';

            if (colapsada) {
                wrapper.style.maxHeight = 'none';
                wrapper.style.overflow  = 'visible';
                if (btn) btn.textContent = 'Ver menos';
            } else {
                wrapper.style.maxHeight = BIO_MAX_HEIGHT + 'px';
                wrapper.style.overflow  = 'hidden';
                if (btn) btn.textContent = 'Ver mais';
            }
        }
    </script>

</body>
</html>
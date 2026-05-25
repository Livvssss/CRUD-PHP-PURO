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
| CRIAR TABELAS E CORRIGIR COLUNAS EM FALTA
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

$pdo->exec("
    CREATE TABLE IF NOT EXISTS shelves (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        author_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        cover VARCHAR(255) DEFAULT NULL,
        release_date DATE,
        reading_status VARCHAR(50) DEFAULT 'Lendo',
        rating DECIMAL(2,1) DEFAULT NULL,
        reading_date DATE DEFAULT NULL,
        reading_start DATE DEFAULT NULL,
        reading_end DATE DEFAULT NULL,
        reading_goal VARCHAR(255) DEFAULT NULL,
        reading_time VARCHAR(255) DEFAULT NULL,
        tags VARCHAR(255) DEFAULT NULL,
        review TEXT DEFAULT NULL,
        quotes TEXT DEFAULT NULL,
        reading_history TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// Garante que as colunas novas existam caso a tabela já tenha sido criada anteriormente
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN reading_start DATE DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN reading_end DATE DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN reading_goal VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN reading_time VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN tags VARCHAR(255) DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN review TEXT DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN quotes TEXT DEFAULT NULL");
} catch (Exception $e) {
}
try {
    $pdo->exec("ALTER TABLE shelves ADD COLUMN reading_history TEXT DEFAULT NULL");
} catch (Exception $e) {
}

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

        ':rating' => !empty($_POST['rating'])
            ? $_POST['rating']
            : null,

        ':reading_goal' => $_POST['reading_goal'],
        ':reading_time' => $_POST['reading_time'],
        ':tags' => $_POST['tags'],
        ':review' => $_POST['review'],
        ':quotes' => $_POST['quotes'],
        ':reading_history' => $_POST['reading_history'],

        ':reading_date' => !empty($_POST['reading_date'])
            ? $_POST['reading_date']
            : null,

        ':reading_start' => !empty($_POST['reading_start'])
            ? $_POST['reading_start']
            : null,

        ':reading_end' => !empty($_POST['reading_end'])
            ? $_POST['reading_end']
            : null,

        ':id' => $livroId,
        ':user_id' => $usuarioId
    ]);
    $mensagem = 'Livro atualizado!';
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
    SELECT
        shelves.*,
        authors.name AS author_name
    FROM shelves
    INNER JOIN authors
        ON shelves.author_id = authors.id
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
            --shadow: 0 20px 40px rgba(217, 119, 6, 0.08);
            --radius: 22px;
            --sidebar-width: 280px;
            --transition: 0.35s cubic-bezier(0.22, 1, 0.36, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--bg), var(--bg-soft));
            display: flex;
            color: var(--text);
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(251, 191, 36, 0.10), transparent 30%),
                radial-gradient(circle at bottom right, rgba(245, 158, 11, 0.08), transparent 35%);
            z-index: -1;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: rgba(255, 255, 255, 0.55);
            backdrop-filter: blur(18px);
            border-right: 1px solid var(--border);
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
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            box-shadow: 0 14px 24px rgba(217, 119, 6, 0.15);
        }

        .logout-btn {
            width: 100%;
            border: none;
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            padding: 16px 18px;
            border-radius: 18px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.14);
            transform: translateY(-2px);
        }

        /* MAIN CONTROLLER */
        .main-content {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
            padding: 40px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .welcome h1 {
            font-size: 38px;
            font-weight: 800;
        }

        .welcome p {
            margin-top: 8px;
            color: var(--text-muted);
        }

        .profile-badge {
            background: rgba(255, 255, 255, 0.60);
            border: 1px solid var(--border);
            padding: 14px 18px;
            border-radius: 18px;
            font-weight: 600;
            box-shadow: var(--shadow);
        }

        /* CARDS / CONTAINERS */
        .card {
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(18px);
            border-radius: var(--radius);
            padding: 28px;
            box-shadow: var(--shadow);
            margin-bottom: 40px;
        }

        .card h2 {
            font-size: 24px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 24px;
        }

        /* GRIDS & FORMULÁRIOS */
        .form-grid,
        .modal-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 10px;
            font-weight: 600;
            font-size: 14px;
        }

        input,
        select,
        textarea {
            width: 100%;
            border: 1px solid rgba(245, 158, 11, 0.2);
            background: rgba(255, 255, 255, 0.8);
            border-radius: 14px;
            padding: 14px;
            font-family: inherit;
            outline: none;
            color: var(--text);
            transition: var(--transition);
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: var(--primary);
            background: #fff;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .submit-btn {
            margin-top: 20px;
            border: none;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 16px;
            border-radius: 16px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
            box-shadow: 0 10px 20px rgba(217, 119, 6, 0.1);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px rgba(217, 119, 6, 0.2);
        }

        .message {
            background: #dcfce7;
            color: #166534;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 16px;
            border-radius: 16px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* GRID DE LIVROS */
        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 24px;
        }

        .book-card {
            background: rgba(255, 255, 255, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(18px);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
        }

        .book-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 24px 40px rgba(217, 119, 6, 0.12);
        }

        .book-cover {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 16px;
            margin-bottom: 16px;
        }

        .book-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.3;
        }

        .book-author {
            color: var(--text-muted);
            font-size: 14px;
            margin-bottom: 12px;
        }

        .book-status {
            align-self: flex-start;
            background: var(--primary-soft);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .options-btn {
            width: 100%;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.8);
            color: var(--text);
            padding: 12px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: auto;
            transition: var(--transition);
        }

        .options-btn:hover {
            background: var(--primary);
            color: white;
        }

        /* MODAL */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(63, 63, 70, 0.4);
            backdrop-filter: blur(6px);
            display: none;
            justify-content: center;
            align-items: center;
            padding: 30px;
            z-index: 999;
        }

        .modal-content {
            width: 100%;
            max-width: 750px;
            background: #fffcf8;
            border-radius: 28px;
            padding: 32px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.15);
        }

        /* RESPONSIVO */
        @media (max-width: 900px) {
            body {
                flex-direction: column;
            }

            .sidebar {
                position: relative;
                width: 100%;
                min-height: auto;
                padding: 20px;
            }

            .logo-subtitle {
                margin-bottom: 20px;
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                gap: 18px;
            }

            .form-grid,
            .modal-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                <a href="#" class="menu-item">Reviews</a>
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
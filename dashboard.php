<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$usuarioNome = $_SESSION['usuario_nome'];
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ShelfHub | Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
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

        /* SIDEBAR */

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

            background:
                linear-gradient(135deg,
                    #f59e0b,
                    #d97706);

            color: white;

            box-shadow:
                0 14px 24px rgba(217, 119, 6, 0.15);
        }

        .logout-btn {

            width: 100%;

            border: none;

            background:
                rgba(239, 68, 68, 0.08);

            color: #dc2626;

            padding: 16px 18px;

            border-radius: 18px;

            font-size: 15px;

            font-weight: 600;

            cursor: pointer;

            transition: var(--transition);
        }

        .logout-btn:hover {

            background:
                rgba(239, 68, 68, 0.14);

            transform: translateY(-2px);
        }

        /* MAIN */

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

            background:
                rgba(255, 255, 255, 0.60);
            border:
                1px solid var(--border);
            padding: 14px 18px;
            border-radius: 18px;
            font-weight: 600;
            box-shadow: var(--shadow);
        }

        /* LOGO */

        .logo-container {

            width: 100%;

            display: flex;

            justify-content: center;

            align-items: center;

            margin-bottom: 40px;
        }

        .logo-image {

            width: 100%;

            max-width: 500px;

            height: auto;

            object-fit: contain;

            opacity: 0.95;
        }

        /* CARDS */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(240px, 1fr));

            gap: 22px;
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

            transition: var(--transition);
        }

        .card:hover {

            transform: translateY(-6px);

            box-shadow:
                0 24px 40px rgba(217, 119, 6, 0.12);
        }

        .card-title {

            font-size: 20px;

            font-weight: 700;

            margin-bottom: 12px;
        }

        .card-text {

            color: var(--text-muted);

            line-height: 1.6;

            font-size: 14px;
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
            }

            .main-content {

                margin-left: 0;

                width: 100%;
            }

            .topbar {

                flex-direction: column;

                align-items: flex-start;

                gap: 18px;
            }
        }
    </style>

</head>

<body>

    <aside class="sidebar">

        <div class="sidebar-top">

            <div>

                <h1 class="logo">
                    ShelfHub
                </h1>

                <p class="logo-subtitle">
                    Sistema de Biblioteca
                </p>

            </div>

            <nav class="sidebar-menu">

                <a href="books.php" class="menu-item active">
                    Livros
                </a>

                <a href="pages/authors.php" class="menu-item">
                    Autores
                </a>

                <a href="pages/shelves.php" class="menu-item">
                    Estantes
                </a>

                <a href="#" class="menu-item">
                    Reviews
                </a>

                <a href="pages/posts.php" class="menu-item">
                    Posts
                </a>

            </nav>

        </div>

        <form action="logout.php" method="POST">

            <button type="submit" class="logout-btn">
                Sair do Sistema
            </button>

        </form>

    </aside>

    <main class="main-content">

        <div class="topbar">

            <div class="welcome">

                <h1>
                    Olá, <?= htmlspecialchars($usuarioNome) ?>
                </h1>

                <p>
                    Bem-vindo ao painel da biblioteca.
                </p>

            </div>

            <div class="profile-badge">
                ShelfHub
            </div>

        </div>

        <div class="logo-container">

            <img src="img/logo.png" alt="Logo da Biblioteca" class="logo-image">

        </div>

        <section class="cards">

            <div class="card">

                <h2 class="card-title">
                    Livros
                </h2>

                <p class="card-text">
                    Gerencie todos os livros cadastrados no sistema.
                </p>

            </div>

            <div class="card">

                <h2 class="card-title">
                    Autores
                </h2>

                <p class="card-text">
                    Organize os autores e suas obras cadastradas.
                </p>

            </div>

            <div class="card">

                <h2 class="card-title">
                    Estantes
                </h2>

                <p class="card-text">
                    Controle categorias e estantes da biblioteca.
                </p>

            </div>

            <div class="card">

                <h2 class="card-title">
                    Reviews
                </h2>

                <p class="card-text">
                    Visualize avaliações e comentários dos leitores.
                </p>

            </div>

        </section>

    </main>

</body>

</html>
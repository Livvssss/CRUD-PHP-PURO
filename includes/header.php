<?php
session_start();

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$tituloPagina = $tituloPagina ?? 'Sistema da Biblioteca';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina) ?> — Biblioteca</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #fffcf2;
            --bg-soft: #fef9e7;
            --surface: rgba(255,255,255,0.78);
            --primary: #d97706;
            --primary-soft: #fef3c7;
            --text: #3f3f46;
            --text-muted: #71717a;
            --border: rgba(245,158,11,0.15);
            --shadow: 0 20px 40px rgba(217,119,6,0.08);
            --radius: 22px;
            --transition: 0.4s cubic-bezier(0.22,1,0.36,1);
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter',sans-serif;
            min-height:100vh;
            display:flex;
            background:linear-gradient(135deg,var(--bg),var(--bg-soft));
            color:var(--text);
            overflow:hidden;
        }

        body::before{
            content:'';
            position:fixed;
            inset:0;
            background:
                radial-gradient(circle at 20% 20%, rgba(251,191,36,0.08), transparent 35%),
                radial-gradient(circle at 80% 80%, rgba(245,158,11,0.06), transparent 40%);
            z-index:-1;
        }

        .sidebar{
            width:280px;
            background:rgba(255,255,255,0.60);
            backdrop-filter:blur(18px);
            border-right:1px solid var(--border);
            box-shadow:var(--shadow);
            display:flex;
            flex-direction:column;
        }

        .sidebar-header{
            padding:28px;
            border-bottom:1px solid var(--border);
        }

        .logo{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .logo-icon{
            font-size:2rem;
        }

        .logo h1{
            font-size:1rem;
            color:var(--primary);
            font-weight:800;
        }

        .logo p{
            font-size:0.82rem;
            color:var(--text-muted);
        }

        .user-box{
            padding:20px 28px;
            border-bottom:1px solid var(--border);
            background:rgba(254,243,199,0.35);
        }

        .user-box small{
            display:block;
            text-transform:uppercase;
            letter-spacing:1px;
            font-size:0.7rem;
            color:var(--text-muted);
            margin-bottom:6px;
        }

        .user-box strong{
            font-size:0.95rem;
        }

        .menu{
            flex:1;
            padding:20px 16px;
        }

        .menu-title{
            padding:0 14px;
            margin-bottom:14px;
            font-size:0.72rem;
            text-transform:uppercase;
            letter-spacing:1px;
            color:var(--text-muted);
        }

        .menu a{
            display:flex;
            align-items:center;
            gap:14px;
            text-decoration:none;
            padding:14px 16px;
            border-radius:16px;
            color:var(--text);
            font-weight:600;
            transition:var(--transition);
            margin-bottom:8px;
        }

        .menu a:hover{
            background:var(--primary-soft);
            color:var(--primary);
            transform:translateX(4px);
        }

        .menu-icon{
            font-size:1.25rem;
        }

        .sidebar-footer{
            padding:18px 16px;
            border-top:1px solid var(--border);
        }

        .logout-btn{
            width:100%;
            border:none;
            background:rgba(220,38,38,0.08);
            color:#dc2626;
            padding:14px;
            border-radius:16px;
            font-weight:700;
            cursor:pointer;
            transition:var(--transition);
        }

        .logout-btn:hover{
            background:rgba(220,38,38,0.15);
            transform:translateY(-2px);
        }

        .main{
            flex:1;
            display:flex;
            flex-direction:column;
            overflow:hidden;
        }

        .topbar{
            height:82px;
            background:rgba(255,255,255,0.60);
            backdrop-filter:blur(16px);
            border-bottom:1px solid var(--border);
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 38px;
        }

        .topbar h2{
            color:var(--primary);
            font-size:1.1rem;
            font-weight:800;
        }

        .topbar span{
            color:var(--text-muted);
            font-size:0.9rem;
        }

        .content{
            flex:1;
            overflow-y:auto;
            padding:38px;
        }

        .content-card{
            background:rgba(255,255,255,0.68);
            backdrop-filter:blur(14px);
            border:1px solid var(--border);
            border-radius:var(--radius);
            box-shadow:var(--shadow);
            padding:32px;
            min-height:300px;
        }

        @media(max-width:900px){

            .sidebar{
                width:90px;
            }

            .logo div,
            .user-box,
            .menu-title,
            .menu a span:not(.menu-icon){
                display:none;
            }

            .menu a{
                justify-content:center;
            }

            .content{
                padding:20px;
            }

            .topbar{
                padding:0 20px;
            }
        }
    </style>
</head>

<body>

    <aside class="sidebar">

        <div class="sidebar-header">

            <div class="logo">

                <span class="logo-icon">📚</span>

                <div>
                    <h1>Biblioteca POO</h1>
                    <p>Sistema de Biblioteca</p>
                </div>

            </div>

        </div>

        <div class="user-box">

            <small>Logado como</small>

            <strong>
                <?= htmlspecialchars($nomeUsuario) ?>
            </strong>

        </div>

        <nav class="menu">

            <div class="menu-title">
                Navegação
            </div>

            <a href="/dashboard.php">
                <span class="menu-icon">🏠</span>
                <span>Dashboard</span>
            </a>

            <a href="/pages/books/index.php">
                <span class="menu-icon">📚</span>
                <span>Livros</span>
            </a>

            <a href="/pages/authors/index.php">
                <span class="menu-icon">✍️</span>
                <span>Autores</span>
            </a>

            <a href="/pages/shelves/index.php">
                <span class="menu-icon">🗂️</span>
                <span>Estantes</span>
            </a>

        </nav>

        <div class="sidebar-footer">

            <a href="/logout.php">
                <button class="logout-btn">
                    🚪 Sair da Conta
                </button>
            </a>

        </div>

    </aside>

    <div class="main">

        <header class="topbar">

            <h2>
                <?= htmlspecialchars($tituloPagina) ?>
            </h2>

            <span>
                Bem-vindo ao sistema 📖
            </span>

        </header>

        <main class="content">

            <div class="content-card">
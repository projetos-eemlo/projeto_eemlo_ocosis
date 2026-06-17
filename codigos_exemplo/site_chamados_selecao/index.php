<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OCOSIS - Sistema de Ocorrências</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        /* ===== BODY COM IMAGEM DE FUNDO ===== */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding-top: 90px; /* espaço para navbar fixa */

            /* Imagem ocupando todo o fundo, centralizada */
            background: url('imagens/ocorrencias.png') center center / cover no-repeat fixed;

            color: #333;
            scroll-behavior: smooth;
        }

        /* ===== NAVBAR TRANSPARENTE ===== */
        .navbar {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background: rgba(0, 86, 179, 0.9);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            backdrop-filter: blur(5px);
        }

        /* Estilização do Logo alinhado à esquerda na Navbar */
        .logo-navbar {
            max-height: 40px; /* tamanho discreto para o topo */
            width: auto;
            /* Remove o fundo branco mantendo o conteúdo visível */
            mix-blend-mode: multiply;
        }

        .navbar .btn {
            margin: 4px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        @media (hover: hover) and (pointer: fine) {
            .navbar .btn:hover {
                transform: translateY(-2px);
                background: linear-gradient(45deg, #00c0ff, #007bff);
                box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            }
        }

        /* ===== HEADER ===== */
        header {
            text-align: center;
            background-color: rgba(255, 255, 255, 0.95);
            color: #0056b3;
            padding: 20px 10px;
            margin: 0 auto 20px auto;
            max-width: 900px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        header h1 {
            margin: 0;
            font-size: 1.8em;
            font-weight: bold;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            padding: 0 20px 20px 20px;
            max-width: 900px;
            margin: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .content {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        p {
            line-height: 1.6;
            margin-bottom: 12px;
            font-size: 1.05em;
        }

        /* ===== FOOTER ===== */
        footer {
            text-align: center;
            background-color: rgba(0, 86, 179, 0.9);
            color: white;
            padding: 15px;
            font-size: 0.9em;
            margin-top: 40px;
            box-shadow: 0 -4px 8px rgba(0,0,0,0.1);
        }

        /* ===== RESPONSIVO ===== */
        @media (max-width: 768px) {
            header h1 { font-size: 1.5em; }
            .navbar .container-fluid { justify-content: center !important; }
            .navbar .btn { font-size: 0.9em; padding: 8px 12px; width: 100%; }
            .logo-navbar { margin-bottom: 10px; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid justify-content-between align-items-center flex-wrap">

        <a href="#" class="navbar-brand m-0">
            <img src="imagens/LOGOMLO.png" alt="Logo EEMLO" class="logo-navbar">
        </a>

        <div class="d-flex flex-wrap gap-1 justify-content-end">
            <button class="btn btn-light" onclick="window.location.href='ocorrencia.html'"><i class='bx bx-edit'></i> Abertura de Ocorrência</button>
            <button class="btn btn-outline-light" onclick="window.location.href='consultar.php'"><i class='bx bx-search-alt'></i> Consultar Status</button>
            <button class="btn btn-warning" onclick="window.location.href='login.php'"><i class='bx bx-lock'></i> Admin</button>
        </div>

    </div>
</nav>

<header>
    <h1>📋 Bem-vindo(a) ao OCOSIS</h1>
    <p class="text-muted" style="color: #4a7bb0 !important; margin-bottom: 0;">Sistema Integrado de Ocorrências Escolares</p>
</header>

<div class="main-content">
    <div class="content">
        <p>
            O <strong>OCOSIS</strong> é o portal dedicado à comunidade escolar para o registro, triagem e acompanhamento de ocorrências disciplinares e rotineiras.
        </p>
        <p>
            Nossa ferramenta foi criada para garantir um ambiente seguro e organizado para alunos, professores e coordenação, mantendo a comunicação clara e documentada. O sistema é totalmente responsivo, adaptando-se perfeitamente a qualquer dispositivo.
        </p>
        <hr>
        <p id="deviceInfo" style="font-weight:bold; color:#0056b3; margin-top: 15px; margin-bottom: 0;"></p>
    </div>
</div>

<footer>
    <p style="margin: 0;">
        OCOSIS – Sistema de Ocorrências Escolares ©. Todos os direitos reservados.
    </p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Detecta dispositivo automaticamente
    const info = document.getElementById("deviceInfo");
    if (/Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        info.textContent = "📱 Acesso detectado via CELULAR ou TABLET.";
    } else {
        info.textContent = "💻 Acesso detectado via COMPUTADOR.";
    }
</script>

</body>
</html>
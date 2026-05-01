<?php
if (session_status() === PHP_SESSION_NONE) session_start();

if (isset($_SESSION['empleado'])) {
    header($_SESSION['empleado']['rol'] == 1 ? 'Location: index.php' : 'Location: empleado.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario    = trim($_POST['usuario']    ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if ($usuario && $contrasena) {
        $apiBase = "http://127.0.0.1:8000/api";
        $payload = json_encode(['usuario' => $usuario, 'contrasena' => $contrasena]);
        $ctx = stream_context_create(['http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
            'content' => $payload,
        ]]);
        $response = @file_get_contents("$apiBase/login", false, $ctx);
        $data     = $response ? json_decode($response, true) : null;

        if ($data && ($data['status'] ?? '') === 'success') {
            $_SESSION['empleado'] = $data;
            header($data['rol'] == 1 ? 'Location: index.php' : 'Location: empleado.php');
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    } else {
        $error = 'Por favor completa todos los campos.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión · SellFlow</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        :root {
            --primary:      #4f46e5;
            --primary-dark: #3730a3;
            --primary-soft: #eef2ff;
            --blue-mid:     #1e40af;
            --text-dark:    #0f172a;
            --text-muted:   #64748b;
            --border:       #e2e8f0;
            --bg:           #f8faff;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Navbar ── */
        .top-bar {
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -0.02em;
        }

        .back-link {
            font-size: 0.83rem;
            color: var(--text-muted);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.2s;
        }
        .back-link:hover { color: var(--primary); }

        /* ── Contenedor principal ── */
        .page-body {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: calc(100vh - 57px);
        }

        /* ── Panel izquierdo ── */
        .left-panel {
            background: linear-gradient(160deg, var(--primary) 0%, #1e3a8a 60%, #0f172a 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 3.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Círculo decorativo sutil */
        .left-panel::after {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.07);
            top: -80px;
            right: -80px;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.05);
            bottom: 60px;
            left: -60px;
        }

        .left-logo {
            font-weight: 800;
            font-size: 1.3rem;
            color: white;
            text-decoration: none;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 1;
        }

        .left-content {
            position: relative;
            z-index: 1;
        }

        .left-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.15);
            border-radius: 100px;
            padding: 5px 13px;
            font-size: 0.72rem;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 1.5rem;
        }

        .left-pill::before {
            content: '';
            width: 6px; height: 6px;
            background: #4ade80;
            border-radius: 50%;
        }

        .left-title {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.035em;
            color: white;
            margin-bottom: 1rem;
        }

        .left-title span {
            color: rgba(255,255,255,0.45);
        }

        .left-desc {
            font-size: 0.88rem;
            color: rgba(255,255,255,0.55);
            line-height: 1.75;
            max-width: 300px;
        }

        .left-footer {
            display: flex;
            gap: 2.5rem;
            position: relative;
            z-index: 1;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .stat-val {
            font-size: 1.5rem;
            font-weight: 800;
            color: white;
        }

        .stat-lbl {
            font-size: 0.72rem;
            color: rgba(255,255,255,0.45);
            margin-top: 2px;
        }

        /* ── Panel derecho: form ── */
        .right-panel {
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            border-left: 1px solid var(--border);
        }

        .form-card {
            width: 100%;
            max-width: 380px;
            animation: fadeUp 0.45s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-eyebrow {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--primary);
            margin-bottom: 0.6rem;
        }

        .form-title {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--text-dark);
            margin-bottom: 0.4rem;
        }

        .form-sub {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 2.25rem;
        }

        /* Inputs */
        .field { margin-bottom: 1.1rem; }

        .field-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.45rem;
        }

        .field-wrap { position: relative; }

        .field-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.95rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .field-wrap:focus-within .field-icon { color: var(--primary); }

        .field-input {
            width: 100%;
            padding: 12px 13px 12px 38px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.9rem;
            color: var(--text-dark);
            background: #fafbff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        .field-input::placeholder { color: #b4bfcc; }

        .field-input:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(79,70,229,0.09);
        }

        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 0.95rem;
            padding: 0;
            transition: color 0.2s;
        }
        .toggle-pass:hover { color: var(--primary); }

        /* Error */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            border-radius: 10px;
            padding: 11px 14px;
            font-size: 0.83rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 1.25rem;
            animation: shake 0.35s ease;
        }

        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-5px); }
            75%      { transform: translateX(5px); }
        }

        /* Botón */
        .btn-submit {
            width: 100%;
            padding: 13px;
            margin-top: 0.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(79,70,229,0.28);
        }

        .btn-submit:active { transform: translateY(0); }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 1.5rem 0;
            font-size: 0.75rem;
            color: #cbd5e1;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        .store-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 0.83rem;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }
        .store-link:hover { color: var(--primary); }

        /* Responsive */
        @media (max-width: 720px) {
            .page-body { grid-template-columns: 1fr; }
            .left-panel { display: none; }
            .right-panel { border-left: none; }
            .top-bar { padding: 14px 20px; }
        }
    </style>
</head>
<body>

    <div class="top-bar">
        <a href="store.php" class="logo">SellFlow</a>
        <a href="store.php" class="back-link">
            <i class="bi bi-arrow-left"></i> Volver a la tienda
        </a>
    </div>

    <div class="page-body">

        <!-- Panel izquierdo -->
        <div class="left-panel">
            <a href="store.php" class="left-logo">SellFlow</a>

            <div class="left-content">
                <div class="left-pill">Sistema activo</div>
                <h2 class="left-title">
                    Bienvenido<br>
                    de <span>vuelta.</span>
                </h2>
                <p class="left-desc">
                    Tu acceso determina tu experiencia. Administradores gestionan la tienda; empleados atienden a los clientes.
                </p>
            </div>

            <div class="left-footer">
                <div>
                    <div class="stat-val">2</div>
                    <div class="stat-lbl">Roles de acceso</div>
                </div>
                <div>
                    <div class="stat-val">100%</div>
                    <div class="stat-lbl">Seguro</div>
                </div>
                <div>
                    <div class="stat-val">24/7</div>
                    <div class="stat-lbl">Disponible</div>
                </div>
            </div>
        </div>

        <!-- Panel derecho -->
        <div class="right-panel">
            <div class="form-card">

                <p class="form-eyebrow">Acceso al sistema</p>
                <h1 class="form-title">Iniciar sesión</h1>
                <p class="form-sub">Ingresa tus credenciales para continuar.</p>

                <?php if ($error): ?>
                    <div class="alert-error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php">

                    <div class="field">
                        <label class="field-label" for="usuario">Usuario</label>
                        <div class="field-wrap">
                            <i class="bi bi-person field-icon"></i>
                            <input
                                class="field-input"
                                type="text"
                                id="usuario"
                                name="usuario"
                                placeholder="Tu nombre de usuario"
                                value="<?= htmlspecialchars($_POST['usuario'] ?? '') ?>"
                                autocomplete="username"
                                required
                            >
                        </div>
                    </div>

                    <div class="field">
                        <label class="field-label" for="contrasena">Contraseña</label>
                        <div class="field-wrap">
                            <i class="bi bi-lock field-icon"></i>
                            <input
                                class="field-input"
                                type="password"
                                id="contrasena"
                                name="contrasena"
                                placeholder="Tu contraseña"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="toggle-pass" onclick="togglePass()">
                                <i class="bi bi-eye" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Entrar
                    </button>

                </form>

                <div class="divider">o</div>

                <a href="store.php" class="store-link">
                    <i class="bi bi-arrow-left"></i>
                    Continuar sin iniciar sesión
                </a>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePass() {
            const input = document.getElementById('contrasena');
            const icon  = document.getElementById('eye-icon');
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.className = input.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
        }
    </script>

</body>
</html>
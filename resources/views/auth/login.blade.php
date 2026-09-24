<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | HRD Sistem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --hrd-navy: #17324d;
            --hrd-blue: #287bb5;
            --hrd-sky: #eaf5fb;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--hrd-navy) 0%, #245d7d 52%, #d8eef5 100%);
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .login-card {
            width: min(100%, 430px);
            border: 0;
            border-radius: 18px;
            box-shadow: 0 24px 60px rgba(12, 35, 55, .24);
            overflow: hidden;
        }

        .login-brand {
            background: var(--hrd-sky);
            color: var(--hrd-navy);
            padding: 34px 34px 26px;
        }

        .brand-icon {
            width: 58px;
            height: 58px;
            display: grid;
            place-items: center;
            border-radius: 15px;
            background: var(--hrd-blue);
            color: #fff;
            font-size: 1.7rem;
        }

        .login-form {
            padding: 30px 34px 34px;
        }

        .form-control {
            padding: .75rem .9rem;
        }

        .btn-login {
            background: var(--hrd-blue);
            border: 0;
            padding: .75rem 1rem;
        }

        .btn-login:hover {
            background: #216797;
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-card bg-white" aria-labelledby="login-title">
            <div class="login-brand">
                <div class="brand-icon mb-3"><i class="bi bi-building"></i></div>
                <p class="text-uppercase fw-semibold small mb-2 text-secondary">HRD Sistem</p>
                <h1 id="login-title" class="h3 fw-bold mb-2">Selamat datang kembali</h1>
                <p class="mb-0 text-secondary">Masuk untuk mengelola data dan laporan HRD.</p>
            </div>
            <div class="login-form">
                @if (session('login_error'))
                    <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ session('login_error') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Username</label>
                        <input type="text" class="form-control" id="username" name="username" autocomplete="username" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">Password</label>
                        <input type="password" class="form-control" id="password" name="password" autocomplete="current-password" required>
                    </div>
                    <button type="submit" class="btn btn-login btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Dashboard
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | Enterprise HRD</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --hr-primary: #f59e0b;
            --hr-primary-hover: #d97706;
            --hr-dark: #0f172a;
            --hr-text: #334155;
            --font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font-family);
            min-height: 100vh;
            margin: 0;
            background-color: #ffffff;
            color: var(--hr-text);
        }

        .login-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Left side - Banner */
        .login-banner {
            flex: 1.2;
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            position: relative;
            overflow: hidden;
            display: none;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
            color: white;
        }
        
        @media (min-width: 992px) {
            .login-banner {
                display: flex;
            }
        }

        .banner-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(245, 158, 11, 0.08) 0%, transparent 50%),
                radial-gradient(circle at 85% 30%, rgba(245, 158, 11, 0.05) 0%, transparent 50%);
            z-index: 1;
        }
        
        .banner-content {
            position: relative;
            z-index: 2;
            max-width: 480px;
        }

        .banner-content h1 {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .banner-content p {
            font-size: 1.1rem;
            color: #94a3b8;
            line-height: 1.7;
        }

        .banner-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 4rem;
            letter-spacing: 1px;
        }
        
        .banner-logo i {
            color: var(--hr-primary);
            font-size: 2rem;
        }

        /* Right side - Form */
        .login-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2rem;
            background: #ffffff;
            position: relative;
        }

        @media (min-width: 992px) {
            .login-wrapper {
                padding: 4rem;
                max-width: 600px;
            }
        }

        .login-form-container {
            width: 100%;
            max-width: 420px;
            margin: 0 auto;
        }

        .mobile-logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--hr-dark);
            margin-bottom: 2.5rem;
        }
        
        @media (min-width: 992px) {
            .mobile-logo {
                display: none;
            }
        }
        
        .mobile-logo i {
            color: var(--hr-primary);
            font-size: 1.6rem;
        }

        .login-title {
            font-size: 1.85rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
            letter-spacing: -0.5px;
        }

        .login-subtitle {
            color: #64748b;
            margin-bottom: 2.5rem;
            font-size: 1rem;
        }

        /* Form Controls */
        .form-floating {
            margin-bottom: 1.25rem;
        }

        .form-control {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem 1rem;
            height: auto;
            font-size: 1rem;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.25s ease;
        }

        .form-floating > .form-control:focus, 
        .form-floating > .form-control:not(:placeholder-shown) {
            padding-top: 1.625rem;
            padding-bottom: 0.625rem;
        }

        .form-floating > label {
            padding: 1rem 1rem;
            color: #64748b;
            font-weight: 500;
        }

        .form-control:focus {
            background-color: #ffffff;
            border-color: var(--hr-primary);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.12);
        }

        .btn-login {
            background-color: var(--hr-dark);
            color: white;
            border: none;
            padding: 1.1rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-login:hover {
            background-color: var(--hr-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.25);
        }

        .alert-custom {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 1rem 1.25rem;
            border-radius: 8px;
            font-size: 0.95rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-custom i {
            font-size: 1.25rem;
            color: #ef4444;
        }
        
        .footer-copyright {
            position: absolute;
            bottom: 2rem;
            left: 0;
            width: 100%;
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <div class="login-layout">
        <!-- Left Side: Banner -->
        <div class="login-banner">
            <div class="banner-pattern"></div>
            <div class="banner-content">
                <div class="banner-logo">
                    <i class="bi bi-buildings-fill"></i>
                    Enterprise HRD
                </div>
                <h1>Empower Your <br><span style="color: var(--hr-primary);">Workforce</span></h1>
                <p>Sistem manajemen Sumber Daya Manusia dan Penggajian yang dirancang khusus untuk efisiensi, akurasi, dan skalabilitas bisnis Anda.</p>
            </div>
        </div>

        <!-- Right Side: Form -->
        <div class="login-wrapper">
            <div class="login-form-container">
                <!-- Logo for mobile view -->
                <div class="mobile-logo">
                    <i class="bi bi-buildings-fill"></i>
                    Enterprise HRD
                </div>

                <h2 class="login-title">Masuk ke Akun</h2>
                <p class="login-subtitle">Silakan masukkan kredensial Anda untuk melanjutkan.</p>

                @if (session('login_error'))
                    <div class="alert-custom">
                        <i class="bi bi-shield-x"></i>
                        <div>{{ session('login_error') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="username" placeholder="Username" autocomplete="username" required autofocus>
                        <label for="username">Username</label>
                    </div>
                    
                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" autocomplete="current-password" required>
                        <label for="password">Password</label>
                    </div>
                    
                    <button type="submit" class="btn btn-login">
                        Masuk Sekarang
                    </button>
                </form>
            </div>
            
            <div class="footer-copyright d-none d-lg-block">
                &copy; {{ date('Y') }} PT. Usaha Bakti Perkasa. All rights reserved.
            </div>
        </div>
    </div>

</body>
</html>

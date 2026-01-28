<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Insurance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 420px; width: 100%; }
        .login-header { background: #212529; color: #fff; padding: 2rem; text-align: center; border-radius: 16px 16px 0 0; }
        .login-header i { font-size: 3rem; }
        .login-body { padding: 2rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="bi bi-shield-check"></i>
            <h4 class="mb-0 mt-2">Insurance System</h4>
            <small class="text-white-50">Telesales Management</small>
        </div>
        <div class="login-body">
            @if($errors->any())<div class="alert alert-danger py-2"><i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required>
                    <label for="email"><i class="bi bi-envelope me-2"></i>Email address</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                </div>
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>

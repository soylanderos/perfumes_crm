<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Pay - Login</title>
    <link rel="icon" type="image/x-icon" href="assets/img/crm_icon.png">

    <!-- Tailwind CSS + DaisyUI -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daisyui@latest/dist/full.css">
    <link rel="stylesheet" href="assets/css/login_styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="utilities/sweetalert2/sweetalert2.min.css">
</head>

<body data-page="login" data-user-role="">
    <div class="animated-background">
        <div class="gradient-sphere sphere-1"></div>
        <div class="gradient-sphere sphere-2"></div>
        <div class="gradient-sphere sphere-3"></div>
        <div class="particles" id="particles"></div>
    </div>

    <div class="login-container">
        <div class="login-header">
            <h1>CRM Pay</h1>
            <p>
                Bienvenido de nuevo. Por favor, inicia sesión para continuar.
            </p>
        </div>

        <form id="loginForm">
            <div class="form-group">
                <input
                    type="text"
                    class="form-input"
                    id="email"
                    placeholder="Username"
                    required>
                <i class="input-icon fas fa-user"></i>
                <span class="error-message" id="emailError"></span>
            </div>

            <div class="form-group">
                <input
                    type="password"
                    class="form-input"
                    id="password"
                    placeholder="Password"
                    required>
                <i class="input-icon fas fa-lock"></i>
                <span class="error-message" id="passwordError"></span>
            </div>

            <button id="btn_login" class="submit-button">Log In</button>
        </form>
    </div>

    <div id="message_container"></div>
    <script src="shared/script-loader.js"></script>

</body>

</html>
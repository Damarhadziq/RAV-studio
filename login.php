<?php
        session_start();

        $error = '';
        $login_message = '';

        $valid_username = 'ravstudioandbuild@gmail.com';
        $valid_password = 'ravstudio';

        $timeout_duration = 2 * 60 * 60;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

          if ($email === $valid_username && $password === $valid_password) {
              $_SESSION['logged_in'] = true;
              $_SESSION['last_activity'] = time(); 
              
              // Redirect ke halaman yang diminta atau default ke admin_review.php
              $redirect = $_GET['redirect'] ?? 'admin_review.php';
              header("Location: " . $redirect);
              exit();
          } else {
              $error = 'Email atau password salah.';
          }
        }

        if (isset($_SESSION['login_message'])) {
            $login_message = $_SESSION['login_message'];
            unset($_SESSION['login_message']); 
        }

        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
                session_unset();
                session_destroy();
                header("Location: login.php");
                exit();
            }
            $_SESSION['last_activity'] = time(); 
        }
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>RAV Studio & Build - Login</title>
  <link href="https://unicons.iconscout.com/release/v4.0.8/css/line.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: linear-gradient(135deg, #4a90a4 0%, #5f7c8a 25%, #557c93 50%, #4682a9 75%, #3d6b7d 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      position: relative;
      overflow: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.05"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.05"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.03"/><circle cx="90" cy="40" r="0.8" fill="white" opacity="0.04"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
      pointer-events: none;
    }

    .login-container {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      padding: 3rem 2.5rem;
      border-radius: 20px;
      box-shadow: 
        0 25px 50px -12px rgba(0, 0, 0, 0.25),
        0 0 0 1px rgba(255, 255, 255, 0.1);
      width: 100%;
      max-width: 420px;
      position: relative;
      animation: slideUp 0.6s ease-out;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-header {
      text-align: center;
      margin-bottom: 2.5rem;
    }

    .login-title {
      font-size: 2.25rem;
      font-weight: 700;
      background: linear-gradient(135deg, #4a90a4, #3d6b7d);
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      margin-bottom: 0.5rem;
    }

    .login-subtitle {
      color: #64748b;
      font-size: 1rem;
      font-weight: 400;
    }

    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 0.75rem;
      font-weight: 600;
      color: #374151;
      font-size: 0.95rem;
    }

    .input-wrapper {
      position: relative;
    }

    .form-group input {
      width: 100%;
      padding: 1rem 1.25rem;
      padding-left: 3rem;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 400;
      transition: all 0.3s ease;
      background: #f8fafc;
      color: #1e293b;
    }

    .form-group input:focus {
      outline: none;
      border-color: #4a90a4;
      background: white;
      box-shadow: 0 0 0 4px rgba(74, 144, 164, 0.15);
      transform: translateY(-1px);
    }

    .input-icon {
      position: absolute;
      left: 1rem;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
      font-size: 1.2rem;
      transition: color 0.3s ease;
    }

    .form-group input:focus + .input-icon,
    .form-group input:not(:placeholder-shown) + .input-icon {
      color: #4a90a4;
    }

    .login-button {
      width: 100%;
      padding: 1rem;
      background: linear-gradient(135deg, #4a90a4 0%, #5f7c8a 25%, #3d6b7d 75%, #2c5763 100%);
      color: white;
      border: none;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      margin-top: 1rem;
    }

    .login-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .login-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(74, 144, 164, 0.4);
    }

    .login-button:hover::before {
      left: 100%;
    }

    .login-button:active {
      transform: translateY(0);
    }

    .error-message {
      background: linear-gradient(135deg, #fee2e2, #fed7d7);
      border: 1px solid #fca5a5;
      color: #dc2626;
      padding: 1rem;
      border-radius: 12px;
      text-align: center;
      margin-bottom: 1.5rem;
      font-weight: 500;
      position: relative;
      animation: shake 0.5s ease-in-out;
    }

    .success-message {
      background: linear-gradient(135deg, #d1fae5, #a7f3d0);
      border: 1px solid #6ee7b7;
      color: #059669;
      padding: 1rem;
      border-radius: 12px;
      text-align: center;
      margin-top: 1rem;
      font-weight: 500;
      position: fixed;
      top: 20px;
      right: 20px;
      min-width: 300px;
      animation: slideIn 0.5s ease-out;
      z-index: 1000;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateX(100%);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }

    .decorative-elements {
      position: absolute;
      width: 100%;
      height: 100%;
      pointer-events: none;
      overflow: hidden;
    }

    .floating-shape {
      position: absolute;
      opacity: 0.1;
      animation: float 6s ease-in-out infinite;
    }

    .shape-1 {
      top: 10%;
      left: 10%;
      width: 60px;
      height: 60px;
      background: linear-gradient(45deg, #4a90a4, #3d6b7d);
      border-radius: 50%;
      animation-delay: 0s;
    }

    .shape-2 {
      top: 20%;
      right: 15%;
      width: 40px;
      height: 40px;
      background: linear-gradient(45deg, #5f7c8a, #4a90a4);
      border-radius: 20px;
      animation-delay: 2s;
    }

    .shape-3 {
      bottom: 15%;
      left: 20%;
      width: 80px;
      height: 30px;
      background: linear-gradient(45deg, #4682a9, #557c93);
      border-radius: 15px;
      animation-delay: 4s;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      33% { transform: translateY(-20px) rotate(120deg); }
      66% { transform: translateY(10px) rotate(240deg); }
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 2rem 1.5rem;
        margin: 20px;
      }
      
      .login-title {
        font-size: 1.875rem;
      }
      
      .success-message {
        position: relative;
        right: auto;
        top: auto;
        min-width: auto;
        margin-top: 1rem;
      }
    }
  </style>
</head>
<body>
  <div class="decorative-elements">
    <div class="floating-shape shape-1"></div>
    <div class="floating-shape shape-2"></div>
    <div class="floating-shape shape-3"></div>
  </div>

  <div class="login-container">
    <div class="login-header">
      <h1 class="login-title">RAV Studio</h1>
      <p class="login-subtitle">Selamat datang kembali</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="error-message">
        <i class="uil uil-exclamation-triangle"></i>
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="username">Email Address</label>
        <div class="input-wrapper">
          <input 
            type="email" 
            id="username" 
            name="email" 
            placeholder="Masukkan alamat email Anda"
            required
          >
          <i class="uil uil-envelope input-icon"></i>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Kata Sandi</label>
        <div class="input-wrapper">
          <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Masukkan kata sandi Anda"
            required
          >
          <i class="uil uil-lock input-icon"></i>
        </div>
      </div>

      <button type="submit" class="login-button">
        <i class="uil uil-arrow-right" style="margin-right: 8px;"></i>
        Masuk ke Dashboard
      </button>
    </form>
  </div>

  <?php if (!empty($login_message)): ?>
    <div class="success-message">
      <i class="uil uil-check-circle" style="margin-right: 8px;"></i>
      <?= htmlspecialchars($login_message) ?>
    </div>
  <?php endif; ?>

</body>
</html>
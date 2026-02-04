<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';

// Redirect already logged in users to their dashboard
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin': header("Location: ../admin/dashboard.php"); break;
        case 'assessor': header("Location: ../assessor/dashboard.php"); break;
        case 'assessment_clerk': header("Location: ../assessment/dashboard.php"); break;
        case 'treasurer': header("Location: ../treasurer/dashboard.php"); break;
        case 'cashier': header("Location: ../cashier/dashboard.php"); break;
        case 'viewer': header("Location: ../viewer/dashboard.php"); break;
        default: header("Location: ../index.php");
    }
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username && $password) {
        $stmt = $mysqli->prepare("SELECT id, name, username, password, role FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'admin': header("Location: ../admin/dashboard.php"); break;
                    case 'assessor': header("Location: ../assessor/dashboard.php"); break;
                    case 'assessment_clerk': header("Location: ../assessment/dashboard.php"); break;
                    case 'treasurer': header("Location: ../treasurer/dashboard.php"); break;
                    case 'cashier': header("Location: ../cashier/dashboard.php"); break;
                    case 'viewer': header("Location: ../viewer/dashboard.php"); break;
                    default: header("Location: ../index.php");
                }
                exit;
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    } else {
        $error = "Please enter both username and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>eLGUHRMS Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body {
      background: url('../uploads/background.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      font-family: "Segoe UI", sans-serif;
      color: #fff;
      overflow: hidden;
    }

    /* Fade overlay */
    .fade-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to bottom right, rgba(0,0,0,0.65), rgba(0,0,0,0.25));
      z-index: 1;
    }

    /* Floating particles */
    .particle {
      position: absolute;
      width: 6px;
      height: 6px;
      background: rgba(255,255,255,0.7);
      border-radius: 50%;
      animation: floatUp 12s infinite linear;
      opacity: 0.6;
      filter: blur(1px);
      z-index: 2;
    }

    @keyframes floatUp {
      from { transform: translateY(50vh) scale(0.6); }
      to { transform: translateY(-60vh) scale(1); }
    }

    /* PH flag */
    .ph-flag {
      position: absolute;
      top: 20px;
      right: 20px;
      width: 150px;
      height: 90px;
/*      background: url('https://upload.wikimedia.org/wikipedia/commons/9/99/Flag_of_the_Philippines.svg') no-repeat center/cover;*/
      animation: wave 3s ease-in-out infinite;
      z-index: 4;
      border-radius: 5px;
    }

    @keyframes wave {
      0% { transform: perspective(300px) rotateY(0deg); }
      50% { transform: perspective(300px) rotateY(10deg); }
      100% { transform: perspective(300px) rotateY(0deg); }
    }

    /* Sun rays */
    .ray {
      position: absolute;
      width: 180px;
      height: 40px;
      background: rgba(255,221,0,0.25);
      top: 50%;
      left: 50%;
      transform-origin: left center;
      border-radius: 20px;
      animation: spin 14s linear infinite;
      z-index: 2;
    }

    .ray:nth-child(1){transform: rotate(0deg);}
    .ray:nth-child(2){transform: rotate(60deg);}
    .ray:nth-child(3){transform: rotate(120deg);}
    .ray:nth-child(4){transform: rotate(180deg);}
    .ray:nth-child(5){transform: rotate(240deg);}
    .ray:nth-child(6){transform: rotate(300deg);}
    @keyframes spin { from{transform:rotate(0deg);} to{transform:rotate(360deg);} }

    /* Glass card */
    .login-card {
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(14px);
      border-radius: 1.5rem;
      padding: 2.5rem 2rem;
      width: 360px;
      text-align: center;
      z-index: 10;
      border: 1px solid rgba(255,255,255,0.3);
      animation: fadeIn 1.2s ease-out;
      color: #fff;
    }

    @keyframes fadeIn { from{opacity:0; transform:translateY(20px);} to{opacity:1; transform:translateY(0);} }

    .login-card img {
      width: 90px;
      margin-bottom: 1rem;
      filter: drop-shadow(0 0 12px rgba(255,221,0,0.9))
              drop-shadow(0 0 18px rgba(0,56,168,0.8))
              drop-shadow(0 0 25px rgba(206,17,38,0.7));
      animation: glowPulse 3s infinite ease-in-out;
      border-radius:50%;
    }

    @keyframes glowPulse {
      0% { filter: drop-shadow(0 0 10px rgba(255,255,255,0.6)); }
      50% { filter: drop-shadow(0 0 20px rgba(255,255,255,1)); }
      100% { filter: drop-shadow(0 0 10px rgba(255,255,255,0.6)); }
    }

    .login-card h4 { font-weight: 800; margin-bottom:1rem; }

    .form-control { border-radius: 0.5rem; }

    .btn-ph {
      font-size: 1.1rem;
      font-weight: 700;
      border-radius: 50px;
      background: linear-gradient(135deg,#ffdd00,#0038a8,#ce1126);
      background-size: 200%;
      border: none;
      color: #000;
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
      transition: 0.4s;
    }
    .btn-ph:hover {
      background-position: right;
      transform: scale(1.05);
      box-shadow: 0 6px 20px rgba(0,0,0,0.55);
    }

    .alert-danger { font-size: 0.9rem; padding:0.4rem 0.6rem; }
  </style>
</head>
<body>

  <div class="fade-overlay"></div>
  <div class="ph-flag"></div>
  <?php for($i=1;$i<=20;$i++): ?>
    <div class="particle"></div>
  <?php endfor; ?>
  <div class="ray"></div><div class="ray"></div><div class="ray"></div>
  <div class="ray"></div><div class="ray"></div><div class="ray"></div>

  <div class="login-card">
    <img src="../uploads/logo.png" alt="LGU Logo">
    <h4>eLGUHRMS Login</h4>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" autocomplete="off">
      <div class="mb-3">
        <label for="username" class="form-label fw-semibold">Username</label>
        <input type="text" name="username" id="username" class="form-control" required autofocus>
      </div>
      <div class="mb-3">
        <label for="password" class="form-label fw-semibold">Password</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-ph w-100">Login</button>
    </form>

    <div class="text-center small mt-3">
      © <?= date('Y') ?> RPTMS | All Rights Reserved
    </div>
  </div>

</body>
</html>

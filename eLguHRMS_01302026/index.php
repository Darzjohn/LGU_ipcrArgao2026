<?php
session_start();

// 🔐 If user is already logged in, redirect to their respective dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
            break;
        case 'hr':
            header("Location: hr/dashboard.php");
            break;
        case 'hr_staff':
            header("Location: hr_staff/dashboard.php");
            break;
        case 'employee':
            header("Location: employee/dashboard.php");
            break;
        case 'assessor':
            header("Location: assessor/dashboard.php");
            break;
        case 'assessment_clerk':
            header("Location: assessment_clerk/dashboard.php");
            break;
        case 'treasurer':
            header("Location: treasurer/dashboard.php");
            break;
        case 'cashier':
            header("Location: cashier/dashboard.php");
            break;
        case 'viewer':
            header("Location: viewer/dashboard.php");
            break;
        default:
            header("Location: auth/login.php");
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>eLGU Human Resource Management System</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    body {
      background: url('uploads/background.jpg') no-repeat center center fixed;
      background-size: cover;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
      font-family: "Segoe UI", sans-serif;
      color: #fff;
    }

    /* ✨ Fade Overlay (Soft Gradient Overlay on Background) */
    .fade-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(
        to bottom right,
        rgba(0, 0, 0, 0.65),
        rgba(0, 0, 0, 0.25)
      );
      backdrop-filter: brightness(0.85);
      z-index: 1;
    }

    /* ✨ Floating PH Sun Rays Animation */
    .ray {
      position: absolute;
      width: 180px;
      height: 40px;
      background: rgba(255, 221, 0, 0.25);
      top: 50%;
      left: 50%;
      transform-origin: left center;
      border-radius: 20px;
      animation: spin 14s linear infinite;
      z-index: 2;
    }

    .ray:nth-child(1) { transform: rotate(0deg); }
    .ray:nth-child(2) { transform: rotate(60deg); }
    .ray:nth-child(3) { transform: rotate(120deg); }
    .ray:nth-child(4) { transform: rotate(180deg); }
    .ray:nth-child(5) { transform: rotate(240deg); }
    .ray:nth-child(6) { transform: rotate(300deg); }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    /* Glassmorphic Card */
    .welcome-container {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-radius: 1.5rem;
      padding: 3rem 2.5rem;
      width: 90%;
      max-width: 620px;
      text-align: center;
      z-index: 10;
      border: 1px solid rgba(255, 255, 255, 0.30);
      animation: fadeIn 1.2s ease-out;
      color: #fff;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .welcome-container img {
      width: 140px;
      margin-bottom: 1.3rem;
      filter: drop-shadow(0px 4px 8px rgba(0,0,0,0.35));
    }

    .welcome-title {
      font-size: 2rem;
      font-weight: 800;
      text-shadow: 0px 2px 6px rgba(0,0,0,0.4);
      margin-bottom: 1rem;
    }

    .welcome-description {
      font-size: 1.05rem;
      opacity: 0.95;
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    /* PH Gradient Button */
    .btn-ph {
      font-size: 1.15rem;
      padding: 0.75rem 3rem;
      border-radius: 50px;
      font-weight: 700;
      background: linear-gradient(135deg, #ffdd00, #0038a8, #ce1126);
      background-size: 200%;
      border: none;
      color: #000;
      box-shadow: 0 4px 15px rgba(0,0,0,0.4);
      transition: 0.4s;
    }

    .btn-ph:hover {
      background-position: right;
      transform: scale(1.06);
      box-shadow: 0 6px 20px rgba(0,0,0,0.55);
    }

  </style>
</head>

<body>

  <!-- Background Fade Overlay -->
  <div class="fade-overlay"></div>

  <!-- PH Rays -->
  <div class="ray"></div>
  <div class="ray"></div>
  <div class="ray"></div>
  <div class="ray"></div>
  <div class="ray"></div>
  <div class="ray"></div>

  <div class="welcome-container">
    <img src="uploads/logo.png" alt="LGU Logo">

    <h2 class="welcome-title">
      Welcome to the <br>
      eLGU Human Resource Management System
    </h2>

    <p class="welcome-description">
      A modern Philippine-inspired HR platform for Local Government Units,  
      designed to elevate transparency, efficiency, and digital governance.
    </p>

    <a href="auth/login.php" class="btn btn-ph">
      <i class="bi bi-box-arrow-in-right"></i> &nbsp;Enter System
    </a>
  </div>

</body>
</html>

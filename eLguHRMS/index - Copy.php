<?php
session_start();

// 🔐 If user is already logged in, redirect to their respective dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("Location: admin/dashboard.php");
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
  <style>
    body {
      background: url('uploads/background.jpg') no-repeat center center fixed;
      background-size: cover;
    }
    .welcome-container {
      background: rgba(255, 255, 255, 0.9);
      border-radius: 1rem;
      padding: 3rem;
      max-width: 550px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      text-align: center;
    }
    img {
      width: 120px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">
  <div class="welcome-container">
    <img src="uploads/logo.png" alt="LGU Logo">
    <h2 class="fw-bold mb-3">Welcome to the<br>eLGU Human Resource Management System</h2>
    <p class="text-muted mb-4">
      The eLGU Human Resource Management System (HRMS) is a digital platform designed to streamline and automate HR processes within local government units. It provides a centralized and efficient way to manage employee information, track attendance, monitor leave applications, process payroll, and handle performance evaluations. The system enhances transparency, accuracy, and productivity by reducing manual paperwork and ensuring that HR operations follow government standards. Through its user-friendly interface, the eLGU HRMS supports better decision-making and improves overall workforce management for the LGU.
    </p>
    <a href="auth/login.php" class="btn btn-primary btn-lg px-5">
      <i class="bi bi-box-arrow-in-right"></i> Login
    </a>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</body>
</html>

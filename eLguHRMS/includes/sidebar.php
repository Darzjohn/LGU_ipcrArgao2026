<div class="sidebar">
    <div class="text-center py-3 border-bottom">
        <img src="../uploads/logo.png" alt="Logo" class="logo-circle">
        <h6><?= htmlspecialchars($_SESSION['username']) ?></h6>
        <small><?= htmlspecialchars($_SESSION['role']) ?></small>
    </div>
    <ul class="nav flex-column mt-3">
        <li><a href="../admin/dashboard.php">Dashboard</a></li>
        <li><a href="../admin/users.php">Users</a></li>
        <li><a href="../payments_list.php">Payments</a></li>
        <li><a href="../reports/">Reports</a></li>
        <li><a href="../auth/logout.php" class="text-danger">Logout</a></li>
    </ul>
</div>

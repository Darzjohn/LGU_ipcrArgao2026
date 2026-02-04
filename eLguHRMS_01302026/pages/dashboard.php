<nav class="navbar navbar-light bg-white border-bottom px-3">
    <div class="d-flex align-items-center">
        <h5 class="mb-0"><?= isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard' ?></h5>
    </div>
    <div>
        <span class="me-3"><i class="fa fa-user"></i> <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
        <a href="../logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
</nav>

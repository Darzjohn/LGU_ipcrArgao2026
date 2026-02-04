<div class="container-fluid mt-5 text-center">
    <div class="card mx-auto shadow-sm" style="max-width:600px;">
        <div class="card-body py-5">
            <h1 class="text-danger mb-3"><i class="bi bi-shield-lock"></i> Unauthorized Access</h1>
            <p class="lead">Sorry, your account (<strong><?=htmlspecialchars($_SESSION['role'] ?? 'Unknown')?></strong>) does not have permission to access this page.</p>
            <p class="text-muted">Only <strong>Admin</strong>, <strong>Treasurer</strong>, or <strong>Cashier</strong> roles are allowed.</p>
            <div class="mt-4">
                <a href="../index.php" class="btn btn-primary"><i class="bi bi-house"></i> Return to Dashboard</a>
                <!-- <a href="../auth/logout.php" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-right"></i> Logout</a> -->
            </div>
        </div>
    </div>
</div>

<?php
$page_title = "User Management";
include '../header.php';

if ($_SESSION['role'] !== 'Admin') {
    echo "<div class='alert alert-danger mt-4'>Access denied. Admins only.</div>";
    include '../footer.php';
    exit;
}
?>
<div class="card p-4 shadow-sm">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i> Users</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fa fa-plus me-1"></i> Add User
        </button>
    </div>

    <table id="usersTable" class="table table-bordered table-striped w-100">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Role</th>
                <th>Date Created</th>
                <th width="180">Actions</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addUserForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i>Add User</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editUserForm">
                <input type="hidden" name="id">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Edit User</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label>Full Name</label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resetPasswordForm">
                <input type="hidden" name="id">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fa fa-key me-2"></i>Reset Password</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger">Reset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    const table = $('#usersTable').DataTable({
        ajax: '../api/users_list.php',
        columns: [
            { data: 'id' },
            { data: 'username' },
            { data: 'fullname' },
            { data: 'role' },
            { data: 'created_at' },
            {
                data: null,
                render: function(row) {
                    return `
                        <div class="btn-group">
                            <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}" data-username="${row.username}" data-fullname="${row.fullname}" data-role="${row.role}">
                                <i class="fa fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}">
                                <i class="fa fa-trash"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary reset-btn" data-id="${row.id}">
                                <i class="fa fa-key"></i>
                            </button>
                        </div>`;
                }
            }
        ]
    });

    // Add
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../api/users_add.php', $(this).serialize(), function(res) {
            showToast(res.message, res.status);
            if (res.status === 'success') {
                $('#addModal').modal('hide');
                table.ajax.reload();
            }
        }, 'json');
    });

    // Edit (open modal)
    $(document).on('click', '.edit-btn', function() {
        $('#editUserForm [name=id]').val($(this).data('id'));
        $('#editUserForm [name=username]').val($(this).data('username'));
        $('#editUserForm [name=fullname]').val($(this).data('fullname'));
        $('#editUserForm [name=role]').val($(this).data('role'));
        $('#editModal').modal('show');
    });

    // Update user
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../api/users_edit.php', $(this).serialize(), function(res) {
            showToast(res.message, res.status);
            if (res.status === 'success') {
                $('#editModal').modal('hide');
                table.ajax.reload();
            }
        }, 'json');
    });

    // Reset password
    $(document).on('click', '.reset-btn', function() {
        $('#resetPasswordForm [name=id]').val($(this).data('id'));
        $('#resetModal').modal('show');
    });

    $('#resetPasswordForm').on('submit', function(e) {
        e.preventDefault();
        $.post('../api/reset_password.php', $(this).serialize(), function(res) {
            showToast(res.message, res.status);
            if (res.status === 'success') {
                $('#resetModal').modal('hide');
            }
        }, 'json');
    });

    // Delete
    $(document).on('click', '.delete-btn', function() {
        if (confirm('Delete this user?')) {
            $.post('../api/users_delete.php', { id: $(this).data('id') }, function(res) {
                showToast(res.message, res.status);
                table.ajax.reload();
            }, 'json');
        }
    });
});
</script>

<?php
require_once '../header.php';
require_once '../db.php';
?>

<div class="container mt-4">
    <h4><i class="bi bi-gear"></i> LGU Settings</h4>
    <hr>

    <form id="lguForm" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-4 text-center">
                <label>Municipal Logo</label>
                <div class="mb-3">
                    <img id="previewLogo" src="../uploads/default_logo.png" alt="Logo" style="max-height:120px;" class="mb-2 rounded border p-2">
                    <input type="file" name="municipal_logo" id="municipal_logo" class="form-control">
                </div>
            </div>
            <div class="col-md-8">
                <div class="mb-3">
                    <label>Municipality Name</label>
                    <input type="text" class="form-control" name="municipality_name" id="municipality_name" required>
                </div>
                <div class="mb-3">
                    <label>Province Name</label>
                    <input type="text" class="form-control" name="province_name" id="province_name" required>
                </div>
            </div>
        </div>

        <h5 class="mt-4">Signatories</h5>
        <div class="row">
            <div class="col-md-4">
                <label>Prepared By</label>
                <input type="text" class="form-control" name="prepared_by" id="prepared_by" required>
                <label class="mt-2 small">Signature (Clerk)</label>
                <input type="file" class="form-control" name="clerk_signature" id="clerk_signature">
                <img id="previewClerkSig" src="../uploads/signatures/default_sig.png" alt="Clerk Signature" class="mt-2 border p-1 rounded" style="max-height:80px;">
            </div>
            <div class="col-md-4">
                <label>Reviewed By</label>
                <input type="text" class="form-control" name="reviewed_by" id="reviewed_by" required>
                <label class="mt-2 small">Signature (Accountant)</label>
                <input type="file" class="form-control" name="accountant_signature" id="accountant_signature">
                <img id="previewAccountantSig" src="../uploads/signatures/default_sig.png" alt="Accountant Signature" class="mt-2 border p-1 rounded" style="max-height:80px;">
            </div>
            <div class="col-md-4">
                <label>Approved By</label>
                <input type="text" class="form-control" name="approved_by" id="approved_by" required>
                <label class="mt-2 small">Signature (Treasurer)</label>
                <input type="file" class="form-control" name="treasurer_signature" id="treasurer_signature">
                <img id="previewTreasurerSig" src="../uploads/signatures/default_sig.png" alt="Treasurer Signature" class="mt-2 border p-1 rounded" style="max-height:80px;">
            </div>
        </div>

        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Changes</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load current settings
    fetch('api/lgu_settings_load.php')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const s = data.settings;
            document.getElementById('municipality_name').value = s.municipality_name || '';
            document.getElementById('province_name').value = s.province_name || '';
            document.getElementById('prepared_by').value = s.prepared_by || '';
            document.getElementById('reviewed_by').value = s.reviewed_by || '';
            document.getElementById('approved_by').value = s.approved_by || '';

            if (s.municipal_logo) document.getElementById('previewLogo').src = '../' + s.municipal_logo;
            if (s.clerk_signature) document.getElementById('previewClerkSig').src = '../' + s.clerk_signature;
            if (s.accountant_signature) document.getElementById('previewAccountantSig').src = '../' + s.accountant_signature;
            if (s.treasurer_signature) document.getElementById('previewTreasurerSig').src = '../' + s.treasurer_signature;
        }
    });

    // Preview images instantly
    const preview = (input, img) => input.addEventListener('change', e => {
        const f = e.target.files[0];
        if (f) document.getElementById(img).src = URL.createObjectURL(f);
    });
    preview(document.getElementById('municipal_logo'), 'previewLogo');
    preview(document.getElementById('clerk_signature'), 'previewClerkSig');
    preview(document.getElementById('accountant_signature'), 'previewAccountantSig');
    preview(document.getElementById('treasurer_signature'), 'previewTreasurerSig');

    // Save form
    document.getElementById('lguForm').addEventListener('submit', e => {
        e.preventDefault();
        const formData = new FormData(e.target);
        fetch('api/lgu_settings_save.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
    });
});
</script>

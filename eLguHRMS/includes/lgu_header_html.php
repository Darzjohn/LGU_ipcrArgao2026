<?php
require_once __DIR__ . '/../db.php';

// Fetch officials
function get_official($mysqli, $position) {
    $stmt = $mysqli->prepare("SELECT name FROM officials_list WHERE position = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("s", $position);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['name'] : '____________________';
}

// Fetch current officials
$prepared_by = get_official($mysqli, 'Records Officer / Encoder');
$reviewed_by = get_official($mysqli, 'Municipal Assessor');
$approved_by = get_official($mysqli, 'Municipal Treasurer');

// Optionally: dynamic municipality name from settings table
$municipality_name = 'Your Municipality';
$settings = $mysqli->query("SELECT value FROM settings WHERE name='municipality_name' LIMIT 1");
if ($settings && $settings->num_rows > 0) {
    $municipality_name = $settings->fetch_assoc()['value'];
}
?>

<!-- ✅ LGU Footer -->
<div style="margin-top:30px; text-align:center; font-size:12px;">
    <hr style="border:1px solid #000; margin-bottom:5px;">
    <table style="width:100%; text-align:center; border-collapse:collapse;">
        <tr>
            <td style="width:33%;">
                <strong>Prepared by:</strong><br><br><br>
                <u><strong><?= htmlspecialchars($prepared_by) ?></strong></u><br>
                <em>Records Officer / Encoder</em>
            </td>
            <td style="width:33%;">
                <strong>Reviewed by:</strong><br><br><br>
                <u><strong><?= htmlspecialchars($reviewed_by) ?></strong></u><br>
                <em>Municipal Assessor</em>
            </td>
            <td style="width:33%;">
                <strong>Approved by:</strong><br><br><br>
                <u><strong><?= htmlspecialchars($approved_by) ?></strong></u><br>
                <em>Municipal Treasurer</em>
            </td>
        </tr>
    </table>

    <div style="margin-top:10px; font-style:italic; font-size:11px;">
        <p>This document is system-generated from the Real Property Tax Management System (RPTMS).</p>
        <p style="margin:0;">© <?= date('Y') ?> Municipality of <?= htmlspecialchars($municipality_name) ?> — All Rights Reserved.</p>
    </div>
</div>

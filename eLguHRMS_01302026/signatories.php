<?php
require 'db.php';

$message = '';

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['assessor', 'treasurer'] as $position) {
        $name = $_POST[$position.'_name'] ?? '';
        $title = $_POST[$position.'_title'] ?? '';
        $name = $mysqli->real_escape_string($name);
        $title = $mysqli->real_escape_string($title);

        // Check if exists
        $check = $mysqli->query("SELECT id FROM signatories WHERE position='$position'");
        if ($check->num_rows > 0) {
            // Update
            $mysqli->query("UPDATE signatories SET name='$name', title='$title' WHERE position='$position'");
        } else {
            // Insert
            $mysqli->query("INSERT INTO signatories (position,name,title) VALUES ('$position','$name','$title')");
        }
    }
    $message = "✅ Signatories updated successfully!";
}

// ✅ Fetch current signatories
$signatories = [
    'assessor' => ['name'=>'','title'=>''],
    'treasurer'=> ['name'=>'','title'=>'']
];
$res = $mysqli->query("SELECT * FROM signatories WHERE position IN ('assessor','treasurer')");
while ($row = $res->fetch_assoc()) {
    $signatories[strtolower($row['position'])] = ['name'=>$row['name'], 'title'=>$row['title']];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Signatories</title>
<style>
    body { font-family: Arial, sans-serif; margin: 30px; background: #f9f9f9; }
    .container { max-width: 600px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px #ccc; }
    h2 { text-align: center; }
    form { margin-top: 20px; }
    label { display: block; margin-top: 10px; font-weight: bold; }
    input[type=text] { width: 100%; padding: 8px; margin-top: 5px; border-radius: 4px; border: 1px solid #ccc; }
    button { margin-top: 20px; padding: 10px 20px; font-size: 16px; border: none; border-radius: 5px; background: #28a745; color: #fff; cursor: pointer; }
    .message { text-align: center; margin-bottom: 15px; color: green; }
</style>
</head>
<body>
<div class="container">
    <h2>Manage Signatories</h2>

    <?php if($message): ?>
        <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <h3>Municipal Assessor</h3>
        <label>Name:</label>
        <input type="text" name="assessor_name" value="<?= htmlspecialchars($signatories['assessor']['name']) ?>" required>
        <label>Title:</label>
        <input type="text" name="assessor_title" value="<?= htmlspecialchars($signatories['assessor']['title']) ?>" required>

        <h3>Municipal Treasurer</h3>
        <label>Name:</label>
        <input type="text" name="treasurer_name" value="<?= htmlspecialchars($signatories['treasurer']['name']) ?>" required>
        <label>Title:</label>
        <input type="text" name="treasurer_title" value="<?= htmlspecialchars($signatories['treasurer']['title']) ?>" required>

        <button type="submit">Save Changes</button>
    </form>
</div>
</body>
</html>

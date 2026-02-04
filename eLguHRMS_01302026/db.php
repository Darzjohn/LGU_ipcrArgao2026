<!-- <?php
$config = require __DIR__ . '/config.php';

$mysqli = new mysqli(
    $config['db_host'],
    $config['db_user'],
    $config['db_pass'],
    $config['db_name']
);

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}
?>
 -->

 <?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'elguhrms_db'; // change to your actual DB name

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>

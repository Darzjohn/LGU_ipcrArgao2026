<?php
/**
 * Database connection for RPTMS (using .env)
 */

require_once __DIR__ . '/env_loader.php';

// Load environment variables
loadEnv(__DIR__ . '/../.env');

// Retrieve environment variables
$DB_HOST = getenv('DB_HOST');
$DB_USER = getenv('DB_USER');
$DB_PASS = getenv('DB_PASS');
$DB_NAME = getenv('DB_NAME');

// Create connection
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($mysqli->connect_errno) {
    error_log("eLGU Human Resource Management System DB Connection failed: " . $mysqli->connect_error);
    die('<h3 style="text-align:center; color:red; margin-top:20px;">
            Database connection failed. Please contact the administrator.
        </h3>');
}

$mysqli->set_charset('utf8mb4');
$mysqli->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

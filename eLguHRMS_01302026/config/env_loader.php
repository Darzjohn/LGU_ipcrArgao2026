<?php
/**
 * env_loader.php
 * ---------------------------------------
 * Loads environment variables from .env file into $_ENV and getenv()
 */

function loadEnv($filePath)
{
    if (!file_exists($filePath)) {
        error_log(".env file not found at $filePath");
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) {
            continue; // Skip comments and invalid lines
        }

        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value, " \t\n\r\0\x0B\"'");

        // Set in both $_ENV and actual environment
        $_ENV[$name] = $value;
        putenv("$name=$value");
    }
}

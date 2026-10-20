<?php

session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method Not Allowed");
}

$token = $_POST["csrf_token"] ?? null;

if (
    !is_string($token) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid CSRF token.");
}

$message = $_POST["message"] ?? null;

if (!is_string($message) || trim($message) === "") {
    http_response_code(422);
    exit("Message is required.");
}

if (strlen($message) > 500) {
    http_response_code(422);
    exit("Message is too long.");
}

$file = dirname(__DIR__) . "/storage/messages.txt";

$result = file_put_contents(
    $file,
    trim($message) . PHP_EOL,
    FILE_APPEND | LOCK_EX
);

if ($result === false) {
    http_response_code(500);
    exit("Failed to save message.");
}

header("Location: index.php", true, 303);
exit;
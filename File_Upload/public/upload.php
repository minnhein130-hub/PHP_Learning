<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . "/../db.php";


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


if (
    !isset($_FILES["document"]) ||
    !is_array($_FILES["document"])
) {
    exit("No file received.");
}

$file = $_FILES["document"];

if (
    !isset(
        $file["error"],
        $file["size"],
        $file["tmp_name"],
        $file["name"]
    ) ||
    !is_int($file["error"]) ||
    !is_int($file["size"]) ||
    !is_string($file["tmp_name"]) ||
    !is_string($file["name"])
) {
    exit("Invalid upload data.");
}

if ($file["error"] !== UPLOAD_ERR_OK) {
    exit("Upload error: " . $file["error"]);
}

$maxSize = 2 * 1024 * 1024;

if ($file["size"] <= 0 || $file["size"] > $maxSize) {
    exit("File must be between 1 byte and 2 MiB.");
}

$tmpName = $file["tmp_name"];

if (!is_uploaded_file($tmpName)) {
    exit("Invalid uploaded file.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);

$mimeType = $finfo->file($tmpName);

$allowedTypes = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "application/pdf" => "pdf"
];

if (
    !is_string($mimeType) ||
    !isset($allowedTypes[$mimeType])
) {
    exit("Only JPG, PNG and PDF are allowed.");
}

$fileData = file_get_contents($tmpName);

if ($fileData === false) {
    http_response_code(500);
    exit("Failed to read uploaded file.");
}

$originalName = basename(
    str_replace("\\", "/", $file["name"])
);

if ($originalName === "") {
    exit("Invalid filename.");
}

if (strlen($originalName) > 255) {
    exit("Filename is too long.");
}

try {

    $sql = "
        INSERT INTO uploaded_files
        (original_name, mime_type, file_size, file_data)
        VALUES (?, ?, ?, ?)
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->bindValue(1, $originalName, PDO::PARAM_STR);

    $stmt->bindValue(2, $mimeType, PDO::PARAM_STR);

    $stmt->bindValue(3, strlen($fileData), PDO::PARAM_INT);

    $stmt->bindValue(4, $fileData, PDO::PARAM_LOB);

    $stmt->execute();

} catch (PDOException $e) {

    http_response_code(500);
    exit("Failed to save file in database.");

}

header("Location: index.php?uploaded=1", true, 303);
exit;
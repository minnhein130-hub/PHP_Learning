<?php

declare(strict_types=1);

require_once __DIR__ . "/../db.php";


$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid file ID.");
}

$stmt = $pdo->prepare(
    "SELECT original_name, mime_type, file_size, file_data
     FROM uploaded_files
     WHERE id = ?"
);

$stmt->execute([$id]);

$file = $stmt->fetch();

if (!$file) {
    http_response_code(404);
    exit("File not found.");
}

$fileData = $file["file_data"];

if (is_resource($fileData)) {
    $fileData = stream_get_contents($fileData);
}

if (!is_string($fileData)) {
    http_response_code(500);
    exit("Failed to read file.");
}

$extensions = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "application/pdf" => "pdf"
];

$extension = $extensions[$file["mime_type"]] ?? "bin";

$fallbackName = "download-" . $id . "." . $extension;

header("Content-Type: application/octet-stream");

header("X-Content-Type-Options: nosniff");

header("Content-Disposition: attachment; filename=\""
    . $fallbackName
    . "\"; filename*=UTF-8''"
    . rawurlencode($file["original_name"])
);

header("Content-Length: " . strlen($fileData));

header("Cache-Control: private, no-store");

echo $fileData;
exit;
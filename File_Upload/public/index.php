<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . "/../db.php";


if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query(
    "SELECT id, original_name, mime_type, file_size, uploaded_at
     FROM uploaded_files
     ORDER BY id DESC"
);

$files = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PHP File Upload with MySQL</title>
</head>

<body>

<h1>Upload File to MySQL</h1>

<?php if (isset($_GET["uploaded"])): ?>
    <p>File uploaded successfully!</p>
<?php endif; ?>

<form
    action="upload.php"
    method="POST"
    enctype="multipart/form-data"
>

    <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars(
            $_SESSION["csrf_token"],
            ENT_QUOTES,
            "UTF-8"
        ) ?>"
    >

    <input
        type="file"
        name="document"
        accept=".jpg,.jpeg,.png,.pdf"
        required
    >

    <button type="submit">Upload</button>

</form>

<p>Allowed: JPG, PNG, PDF — Maximum 2 MiB</p>

<hr>

<h2>Uploaded Files</h2>

<table border="1" cellpadding="10">

    <tr>
        <th>ID</th>
        <th>File Name</th>
        <th>Type</th>
        <th>Size</th>
        <th>Uploaded At</th>
        <th>Action</th>
    </tr>

    <?php foreach ($files as $file): ?>

        <tr>

            <td>
                <?= (int) $file["id"] ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    $file["original_name"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </td>

            <td>
                <?= htmlspecialchars(
                    $file["mime_type"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </td>

            <td>
                <?= round($file["file_size"] / 1024, 2) ?> KiB
            </td>

            <td>
                <?= htmlspecialchars(
                    $file["uploaded_at"],
                    ENT_QUOTES,
                    "UTF-8"
                ) ?>
            </td>

            <td>
                <a href="download.php?id=<?= (int) $file["id"] ?>">
                    Download
                </a>
            </td>

        </tr>

    <?php endforeach; ?>

</table>

</body>
</html>
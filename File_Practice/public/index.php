<?php

session_start();

$_SESSION["csrf_token"] ??= bin2hex(random_bytes(32));

$file = dirname(__DIR__) . "/storage/messages.txt";

$messages = "";

if (is_file($file)) {
    $content = file_get_contents($file);

    if ($content !== false) {
        $messages = $content;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guestbook</title>
</head>
<body>

    <h1>My Guestbook</h1>

    <form action="message.php" method="POST">

        <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(
                $_SESSION["csrf_token"],
                ENT_QUOTES,
                "UTF-8"
            ) ?>"
        >

        <textarea name="message" required></textarea>

        <br><br>

        <button type="submit">Save</button>

    </form>

    <h2>Messages</h2>

    <?= nl2br(htmlspecialchars(
        $messages,
        ENT_QUOTES,
        "UTF-8"
    )) ?>

</body>
</html>
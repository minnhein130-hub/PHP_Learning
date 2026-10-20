<?php

declare(strict_types=1);
session_start();

if (($_SERVER["REQUEST_METHOD"] ?? "") !== "POST") {
    http_response_code(405);
    header("Allow: POST");
    exit("Method Not Allowed");
}

$submittedToken = $_POST["csrf_token"] ?? "";
$sessionToken = $_SESSION["csrf_token"] ?? "";

if(
    !is_string($submittedToken) ||
    !is_string($sessionToken) ||
    !hash_equals($sessionToken, $submittedToken)
) {
    http_response_code(403);
    exit("Invalid CSRF token");
}
$username = $_POST["username"] ?? null;
$email = $_POST["email"] ?? null;
$age = $_POST["age"] ?? null;
$password = $_POST["password"] ?? null;

$errors = [];

if (!is_string($username)) {
    $errors[] = "Username is missing or invalid.";
}

if (!is_string($email)) {
    $errors[] = "Email is missing or invalid.";
}

if (!is_string($age)) {
    $errors[] = "Age is missing or invalid.";
}

if (!is_string($password)) {
    $errors[] = "Password is missing or invalid.";
}


if (is_string($username)) {
    $username = trim($username);
}

if (is_string($email)) {
    $email = trim($email);
}

if (is_string($age)) {
    $age = trim($age);
}


if ($username === "") {
    $errors[] = "Username is required.";
}

if ($email === "") {
    $errors[] = "Email is required.";
}

if ($age === "") {
    $errors[] = "Age is required.";
}

if ($password === "") {
    $errors[] = "Password is required.";
}


if (
    is_string($email) &&
    $email !== "" &&
    filter_var($email, FILTER_VALIDATE_EMAIL) === false
) {
    $errors[] = "Invalid email format.";
}

$validAge = false;

if (is_string($age) && $age !== "") {

    $validAge = filter_var(
        $age,
        FILTER_VALIDATE_INT,
        [
            "options" => [
                "min_range" => 1,
                "max_range" => 120
            ]
        ]
    );

    if ($validAge === false) {
        $errors[] = "Age must be an integer between 1 and 120.";
    }
}

if (
    is_string($password) &&
    $password !== "" &&
    strlen($password) < 8
) {
    $errors[] = "Password must be at least 8 bytes long.";
}


if (empty($errors)) {
    try{
        $pdo = require_once "db.php";

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
         $sql = "INSERT INTO users (username, email, age, password_hash) VALUES (:username, :email, :age, :password_hash)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":username" => $username,
            ":email" => $email,
            ":age" => $age,
            ":password_hash" => $passwordHash
        ]);
        $registered=true;
        http_response_code(201);

    } catch (PDOException $e) {
        if((($e-> errorInfo[1] ?? 0 ) === 1062)){
            $errors[] = "Username or Email already exists.";
            http_response_code(409);
        } else {
            error_log($e->getMessage());
            $errors[] = "An unexpected error occurred. Please try again later.";
            http_response_code(500);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registration Result</title>
</head>

<body>

    <h1>Registration Result</h1>

    <?php if (!empty($errors)): ?>

        <h2 style="color: red;">
            Validation Failed
        </h2>

        <ul style="color: red;">

            <?php foreach ($errors as $error): ?>

                <li>
                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </li>

            <?php endforeach; ?>

        </ul>

        <a href="index.php">Back to Registration</a>

    <?php else: ?>

        <h2 style="color: green;">
            Validation Successful!
        </h2>

        <p>
            Username:
            <?= htmlspecialchars(
                $username,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>

        <p>
            Email:
            <?= htmlspecialchars(
                $email,
                ENT_QUOTES,
                "UTF-8"
            ) ?>
        </p>

        <p>
            Age:
            <?= $age ?>
        </p>

        <a href="index.php">Back to Registration</a>

    <?php endif; ?>

</body>
</html>
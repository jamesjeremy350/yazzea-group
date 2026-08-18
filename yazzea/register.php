<?php

include "config.php";

if (isset($_SESSION["user_id"])) {

    header("Location: index.php");
    exit();

}

$error = "";
$success = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST["fullname"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];


    if (
        $fullname === "" ||
        $username === "" ||
        $password === "" ||
        $confirm === ""
    ) {

        $error = "Please fill in all fields.";

    } elseif ($password !== $confirm) {

        $error = "Passwords do not match.";

    } elseif (strlen($password) < 4) {

        $error = "Password must be at least 4 characters.";

    } else {

        $check = $conn->prepare(
            "SELECT id
             FROM users
             WHERE username = ?"
        );

        $check->bind_param(
            "s",
            $username
        );

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $error = "Username already exists.";

        } else {

            $hashedPassword =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $stmt = $conn->prepare(
                "INSERT INTO users
                (fullname, username, password, role)
                VALUES (?, ?, ?, 'customer')"
            );


            $stmt->bind_param(
                "sss",
                $fullname,
                $username,
                $hashedPassword
            );


            if ($stmt->execute()) {

                $success =
                    "Account created! You can now login.";

            } else {

                $error =
                    "Registration failed.";

            }

            $stmt->close();
        }

        $check->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Yazzea - Register</title>

<link rel="stylesheet" href="style.css">

</head>

<body class="auth-page">

<div class="auth-card">

    <div class="logo">
        Yazzea
    </div>

    <h2>Create Account</h2>

    <p class="subtitle">
        Create your customer account
    </p>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <?php if ($success !== ""): ?>

        <div class="success">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Full Name</label>

        <input
            type="text"
            name="fullname"
            placeholder="Enter your full name"
            required
        >


        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Create username"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Create password"
            required
        >


        <label>Confirm Password</label>

        <input
            type="password"
            name="confirm"
            placeholder="Confirm password"
            required
        >


        <button type="submit">
            Create Account
        </button>

    </form>


    <p class="switch">

        Already have an account?

        <a href="login.php">
            Login
        </a>

    </p>

</div>

</body>
</html>
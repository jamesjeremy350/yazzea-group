<?php

include "config.php";

if (isset($_SESSION["user_id"])) {

    if ($_SESSION["role"] === "admin") {

        header("Location: admin.php");

    } else {

        header("Location: shop.php");

    }

    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);


    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, fullname, username, password, role
             FROM users
             WHERE username = ?"
        );

        $stmt->bind_param("s", $username);

        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();


            /*
             * The default admin from database.sql uses
             * admin123.
             *
             * Registered users use password_hash().
             */

            $validPassword = false;

            if (
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {

                $validPassword = true;

            } elseif (
                $password === $user["password"]
            ) {

                $validPassword = true;

            }


            if ($validPassword) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["fullname"] = $user["fullname"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];


                if ($user["role"] === "admin") {

                    header("Location: admin.php");

                } else {

                    header("Location: shop.php");

                }

                exit();

            } else {

                $error = "Incorrect password.";

            }

        } else {

            $error = "Username not found.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Yazzea - Login</title>

<link rel="stylesheet" href="style.css">

</head>

<body class="auth-page">

<div class="auth-card">

    <div class="logo">
        Yazzea
    </div>

    <h2>Welcome!</h2>

    <p class="subtitle">
        Login to your account
    </p>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form method="POST">

        <label>Username</label>

        <input
            type="text"
            name="username"
            placeholder="Enter username"
            required
        >


        <label>Password</label>

        <input
            type="password"
            name="password"
            placeholder="Enter password"
            required
        >


        <button type="submit">
            Login
        </button>

    </form>


    <p class="switch">

        Don't have an account?

        <a href="register.php">
            Sign Up
        </a>

    </p>


</div>

</body>
</html>
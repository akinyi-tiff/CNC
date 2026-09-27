<?php

session_start();

require_once "db.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (empty($email) || empty($password)) {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, email, password, role 
             FROM admins 
             WHERE email = ? 
             LIMIT 1"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $admin = $result->fetch_assoc();

            if (password_verify($password, $admin["password"])) {

                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_name"] = $admin["name"];
                $_SESSION["admin_email"] = $admin["email"];
                $_SESSION["admin_role"] = $admin["role"];

                header("Location: dashboard.php");
                exit;

            } else {

                $error = "Invalid email or password.";

            }

        } else {

            $error = "Invalid email or password.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | CNC Website</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

    <div class="login-container">

        <div class="login-card">

            <h1>CNC Admin</h1>

            <p class="login-subtitle">
                Sign in to manage the website
            </p>

            <?php if (!empty($error)): ?>

                <div class="login-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <form method="POST" action="">

                <div class="form-group">

                    <label for="email">Email Address</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        required
                    >

                </div>

                <div class="form-group">

                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >

                </div>

                <button type="submit" class="login-button">
                    Login
                </button>

            </form>

        </div>

    </div>

</body>

</html>
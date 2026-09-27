<?php
session_start();

require_once "../admin/db.php";

$message = "";
$message_type = "";

if (isset($_GET["registered"])) {

    $message = "Account created successfully. You can now login.";
    $message_type = "success";
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];


    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "SELECT
                id,
                full_name,
                email,
                password,
                phone,
                course,
                enrollment_status
             FROM students
             WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();


        if ($result->num_rows === 1) {

            $student = $result->fetch_assoc();


            if (
                password_verify(
                    $password,
                    $student["password"]
                )
            ) {

                $_SESSION["student_id"] =
                    $student["id"];

                $_SESSION["student_name"] =
                    $student["full_name"];

                $_SESSION["student_email"] =
                    $student["email"];

                $_SESSION["student_course"] =
                    $student["course"];

                $_SESSION["student_status"] =
                    $student["enrollment_status"];


                header("Location: dashboard.php");
                exit;

            } else {

                $message =
                    "Incorrect email or password.";

                $message_type = "error";
            }

        } else {

            $message =
                "Incorrect email or password.";

            $message_type = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Student Login | Ananki Visviva</title>

    <link
        rel="stylesheet"
        href="../style.css"
    >

</head>


<body>

<div class="student-auth-page">

    <div class="student-auth-box">

        <h1>Student Login</h1>

        <p>
            Login to access your CNC training account.
        </p>


        <?php if (!empty($message)): ?>

            <div class="auth-message <?php echo $message_type; ?>">

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="form-group">

                <label for="password">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button type="submit">
                Login
            </button>

        </form>


        <p class="auth-link">

            Don't have an account?

            <a href="register.php">
                Create an account
            </a>

        </p>

    </div>

</div>

</body>

</html>
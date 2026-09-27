<?php
session_start();
require_once "../admin/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $course = trim($_POST["course"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (
        empty($full_name) ||
        empty($email) ||
        empty($phone) ||
        empty($course) ||
        empty($password) ||
        empty($confirm_password)
    ) {
        $message = "Please fill in all fields.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = "error";

    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
        $message_type = "error";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM students WHERE email = ?"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {

            $message = "An account with this email already exists.";
            $message_type = "error";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $status = "Pending";

            $stmt = $conn->prepare(
                "INSERT INTO students
                (full_name, email, phone, password, course, enrollment_status)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssssss",
                $full_name,
                $email,
                $phone,
                $hashed_password,
                $course,
                $status
            );

            if ($stmt->execute()) {

                header("Location: login.php?registered=1");
                exit;

            } else {

                $message = "Something went wrong. Please try again.";
                $message_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Registration | Ananki Visviva</title>

    <link rel="stylesheet" href="../style.css">
</head>

<body>

<div class="student-auth-page">

    <div class="student-auth-box">

        <h1>Create Student Account</h1>

        <p>
            Register for CNC training and create your student account.
        </p>

        <?php if (!empty($message)): ?>

            <div class="auth-message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>

        <form method="POST">

            <div class="form-group">
                <label for="full_name">Full Name</label>

                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    placeholder="Enter your full name"
                    required
                >
            </div>


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
                <label for="phone">Phone Number</label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter your phone number"
                    required
                >
            </div>


            <div class="form-group">
                <label for="course">Training Course</label>

                <select id="course" name="course" required>

                    <option value="">Select a course</option>

                    <option value="Beginner">
                        Beginner
                    </option>

                    <option value="Intermediate">
                        Intermediate
                    </option>

                    <option value="Advanced">
                        Advanced
                    </option>

                </select>
            </div>


            <div class="form-group">
                <label for="password">Password</label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Create a password"
                    required
                >
            </div>


            <div class="form-group">
                <label for="confirm_password">
                    Confirm Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Confirm your password"
                    required
                >
            </div>


            <button type="submit">
                Create Account
            </button>

        </form>


        <p class="auth-link">

            Already have an account?

            <a href="login.php">
                Login
            </a>

        </p>

    </div>

</div>

</body>
</html>
<?php

session_start();

if (!isset($_SESSION["student_id"])) {

    header("Location: login.php");
    exit;
}

require_once "../admin/db.php";


$student_id = $_SESSION["student_id"];


$stmt = $conn->prepare(
    "SELECT
        full_name,
        email,
        phone,
        course,
        enrollment_status,
        created_at
     FROM students
     WHERE id = ?"
);

$stmt->bind_param("i", $student_id);

$stmt->execute();

$result = $stmt->get_result();

$student = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        My Profile | Ananki Visviva
    </title>

    <link
        rel="stylesheet"
        href="../style.css"
    >

</head>


<body>

<main class="student-dashboard">

    <a
        href="dashboard.php"
        class="back-link"
    >
        ← Back to Dashboard
    </a>


    <h1>
        My Profile
    </h1>


    <div class="profile-box">

        <div>

            <strong>
                Full Name
            </strong>

            <p>
                <?php
                echo htmlspecialchars(
                    $student["full_name"]
                );
                ?>
            </p>

        </div>


        <div>

            <strong>
                Email
            </strong>

            <p>
                <?php
                echo htmlspecialchars(
                    $student["email"]
                );
                ?>
            </p>

        </div>


        <div>

            <strong>
                Phone
            </strong>

            <p>
                <?php
                echo htmlspecialchars(
                    $student["phone"]
                );
                ?>
            </p>

        </div>


        <div>

            <strong>
                Course
            </strong>

            <p>
                <?php
                echo htmlspecialchars(
                    $student["course"]
                );
                ?>
            </p>

        </div>


        <div>

            <strong>
                Enrollment Status
            </strong>

            <p>
                <?php
                echo htmlspecialchars(
                    $student["enrollment_status"]
                );
                ?>
            </p>

        </div>


    </div>

</main>

</body>

</html>
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
        id,
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


/* Create initials */

$name_parts = explode(
    " ",
    trim($student["full_name"])
);

if (count($name_parts) >= 2) {

    $initials =
        strtoupper(
            substr($name_parts[0], 0, 1) .
            substr(
                $name_parts[count($name_parts) - 1],
                0,
                1
            )
        );

} else {

    $initials =
        strtoupper(
            substr($student["full_name"], 0, 2)
        );
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

    <title>
        Student Dashboard | Ananki Visviva
    </title>

    <link
        rel="stylesheet"
        href="../style.css"
    >

</head>


<body>


<header class="student-header">

    <a
        href="../index.html"
        class="student-logo"
    >
        Ananki Visviva
    </a>


    <div class="student-profile">

        <div class="student-avatar">

            <?php
            echo htmlspecialchars($initials);
            ?>

        </div>


        <div class="student-profile-name">

            <strong>
                <?php
                echo htmlspecialchars(
                    $student["full_name"]
                );
                ?>
            </strong>

            <small>
                Student
            </small>

        </div>


        <div class="student-dropdown">

            <a href="dashboard.php">
                My Dashboard
            </a>

            <a href="profile.php">
                My Profile
            </a>

            <a href="#">
                My Course
            </a>

            <a href="#">
                Training Materials
            </a>

            <a href="logout.php">
                Logout
            </a>

        </div>

    </div>

</header>


<main class="student-dashboard">


    <section class="dashboard-welcome">

        <h1>

            Welcome,
            <?php
            echo htmlspecialchars(
                $student["full_name"]
            );
            ?>
            👋

        </h1>

        <p>
            Welcome to your CNC student portal.
        </p>

    </section>



    <section class="student-cards">


        <div class="student-card">

            <span>
                My Course
            </span>

            <h2>

                <?php

                echo !empty($student["course"])
                    ? htmlspecialchars(
                        $student["course"]
                    )
                    : "Not selected";

                ?>

            </h2>

        </div>



        <div class="student-card">

            <span>
                Enrollment Status
            </span>

            <h2>

                <?php
                echo htmlspecialchars(
                    $student["enrollment_status"]
                );
                ?>

            </h2>

        </div>



        <div class="student-card">

            <span>
                Email
            </span>

            <h2>

                <?php
                echo htmlspecialchars(
                    $student["email"]
                );
                ?>

            </h2>

        </div>


    </section>


    <section class="dashboard-section">

        <h2>
            Your Training
        </h2>

        <p>
            Your course materials and training information
            will appear here.
        </p>

    </section>


</main>


</body>

</html>
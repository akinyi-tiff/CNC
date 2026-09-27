<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Training Students | CNC Admin";
$page_heading = "Training Students";

require_once "header.php";


// ==========================================
// UPDATE STUDENT STATUS
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_status"])
) {

    $student_id = (int) ($_POST["student_id"] ?? 0);
    $status = $_POST["status"] ?? "";

    $allowed_statuses = [
        "Pending",
        "Approved",
        "Completed",
        "Rejected"
    ];

    if (
        $student_id > 0 &&
        in_array($status, $allowed_statuses)
    ) {

        $stmt = $conn->prepare(
            "UPDATE students
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $student_id
        );

        if ($stmt->execute()) {

            $message = "Student status updated successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to update student status.";
            $message_type = "error";

        }

        $stmt->close();

    } else {

        $message = "Invalid student status.";
        $message_type = "error";

    }
}


// ==========================================
// DELETE STUDENT
// ==========================================

if (isset($_GET["delete"])) {

    $student_id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM students
         WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $student_id
    );

    if ($stmt->execute()) {

        $message = "Student registration deleted successfully.";
        $message_type = "success";

    } else {

        $message = "Unable to delete student registration.";
        $message_type = "error";

    }

    $stmt->close();
}


// ==========================================
// GET STUDENTS
// ==========================================

$students = [];

$result = $conn->query(
    "SELECT
        students.id,
        students.name,
        students.email,
        students.phone,
        students.status,
        students.created_at,
        courses.title AS course_title,
        courses.level AS course_level
     FROM students
     INNER JOIN courses
        ON students.course_id = courses.id
     ORDER BY students.created_at DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

}


// ==========================================
// STUDENT COUNTS
// ==========================================

$total_students = count($students);

$pending_students = 0;
$approved_students = 0;
$completed_students = 0;
$rejected_students = 0;

foreach ($students as $student) {

    if ($student["status"] === "Pending") {
        $pending_students++;
    }

    if ($student["status"] === "Approved") {
        $approved_students++;
    }

    if ($student["status"] === "Completed") {
        $completed_students++;
    }

    if ($student["status"] === "Rejected") {
        $rejected_students++;
    }

}

?>


<!-- ==========================================
     PAGE HEADER
========================================== -->

<div class="page-header">

    <div>

        <p class="page-label">
            TRAINING MANAGEMENT
        </p>

        <h2>Training Students</h2>

        <p>
            View and manage students who have registered for CNC training.
        </p>

    </div>

</div>


<?php if (isset($message)): ?>

    <div class="admin-message <?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>


<!-- ==========================================
     STUDENT STATISTICS
========================================== -->

<section class="dashboard-cards">


    <div class="dashboard-card">

        <h3>Total Students</h3>

        <strong>
            <?php echo $total_students; ?>
        </strong>

        <p>
            All registered students
        </p>

    </div>


    <div class="dashboard-card">

        <h3>Pending</h3>

        <strong>
            <?php echo $pending_students; ?>
        </strong>

        <p>
            Awaiting review
        </p>

    </div>


    <div class="dashboard-card">

        <h3>Approved</h3>

        <strong>
            <?php echo $approved_students; ?>
        </strong>

        <p>
            Approved students
        </p>

    </div>


    <div class="dashboard-card">

        <h3>Completed</h3>

        <strong>
            <?php echo $completed_students; ?>
        </strong>

        <p>
            Completed training
        </p>

    </div>


</section>



<!-- ==========================================
     STUDENT LIST
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Registered Students</h3>

            <p>
                Manage student training registrations.
            </p>

        </div>

    </div>


    <?php if (empty($students)): ?>

        <div class="empty-state">

            <h3>No Students Yet</h3>

            <p>
                Student registrations will appear here once customers
                register for training.
            </p>

        </div>

    <?php else: ?>


        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Student</th>

                        <th>Contact</th>

                        <th>Course</th>

                        <th>Status</th>

                        <th>Registered</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($students as $student): ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <?php
                                echo $student["id"];
                                ?>

                            </td>


                            <!-- STUDENT -->

                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $student["name"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <!-- CONTACT -->

                            <td>

                                <div>

                                    <?php
                                    echo htmlspecialchars(
                                        $student["email"]
                                    );
                                    ?>

                                </div>

                                <small>

                                    <?php
                                    echo htmlspecialchars(
                                        $student["phone"]
                                    );
                                    ?>

                                </small>

                            </td>


                            <!-- COURSE -->

                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $student["course_title"]
                                    );
                                    ?>

                                </strong>

                                <small>

                                    <?php
                                    echo htmlspecialchars(
                                        $student["course_level"]
                                    );
                                    ?>

                                </small>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="student_id"
                                        value="<?php
                                        echo $student["id"];
                                        ?>"
                                    >

                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        class="status-select"
                                    >

                                        <option
                                            value="Pending"
                                            <?php
                                            echo $student["status"] === "Pending"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Pending
                                        </option>


                                        <option
                                            value="Approved"
                                            <?php
                                            echo $student["status"] === "Approved"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Approved
                                        </option>


                                        <option
                                            value="Completed"
                                            <?php
                                            echo $student["status"] === "Completed"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Completed
                                        </option>


                                        <option
                                            value="Rejected"
                                            <?php
                                            echo $student["status"] === "Rejected"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Rejected
                                        </option>

                                    </select>

                                    <input
                                        type="hidden"
                                        name="update_status"
                                        value="1"
                                    >

                                </form>

                            </td>


                            <!-- REGISTERED DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $student["created_at"]
                                    )
                                );

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="?delete=<?php
                                    echo $student["id"];
                                    ?>"
                                    class="delete-link"
                                    onclick="return confirm('Are you sure you want to delete this student registration?');"
                                >
                                    Delete
                                </a>

                            </td>


                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>


<?php

require_once "footer.php";

?>
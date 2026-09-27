<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Training Courses | CNC Admin";
$page_heading = "Training Courses";

require_once "header.php";


// ==========================================
// DELETE COURSE
// ==========================================

if (isset($_GET["delete"])) {

    $course_id = (int) $_GET["delete"];

    $stmt = $conn->prepare("DELETE FROM courses WHERE id = ?");
    $stmt->bind_param("i", $course_id);

    if ($stmt->execute()) {
        $message = "Course deleted successfully.";
        $message_type = "success";
    } else {
        $message = "Unable to delete the course.";
        $message_type = "error";
    }

    $stmt->close();
}


// ==========================================
// ADD COURSE
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_course"])) {

    $title = trim($_POST["title"] ?? "");
    $level = $_POST["level"] ?? "";
    $description = trim($_POST["description"] ?? "");
    $duration = trim($_POST["duration"] ?? "");
    $status = $_POST["status"] ?? "Draft";


    if (empty($title) || empty($level)) {

        $message = "Please enter the course title and level.";
        $message_type = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO courses 
            (title, level, description, duration, status)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "sssss",
            $title,
            $level,
            $description,
            $duration,
            $status
        );

        if ($stmt->execute()) {

            $message = "Course added successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to add the course.";
            $message_type = "error";
        }

        $stmt->close();
    }
}


// ==========================================
// GET ALL COURSES
// ==========================================

$courses = [];

$result = $conn->query(
    "SELECT *
     FROM courses
     ORDER BY created_at DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

}

?>

<div class="page-header">

    <div>
        <p class="page-label">TRAINING MANAGEMENT</p>

        <h2>Training Courses</h2>

        <p>
            Create and manage the CNC training courses offered to students.
        </p>
    </div>

</div>


<?php if (isset($message)): ?>

    <div class="admin-message <?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>


<!-- ==========================================
     ADD COURSE
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>
            <h3>Add New Course</h3>

            <p>
                Create a new CNC training course.
            </p>
        </div>

    </div>


    <form method="POST" class="admin-form">

        <div class="form-row">

            <div class="form-group">

                <label for="title">
                    Course Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="e.g. CNC Machine Operation"
                    required
                >

            </div>


            <div class="form-group">

                <label for="level">
                    Course Level
                </label>

                <select id="level" name="level" required>

                    <option value="">
                        Select Level
                    </option>

                    <option value="Beginner">
                        Beginner
                    </option>

                    <option value="Intermediate">
                        Intermediate
                    </option>

                    <option value="Expert">
                        Expert
                    </option>

                </select>

            </div>

        </div>


        <div class="form-row">

            <div class="form-group">

                <label for="duration">
                    Duration
                </label>

                <input
                    type="text"
                    id="duration"
                    name="duration"
                    placeholder="e.g. 4 weeks"
                >

            </div>


            <div class="form-group">

                <label for="status">
                    Status
                </label>

                <select id="status" name="status">

                    <option value="Draft">
                        Draft
                    </option>

                    <option value="Published">
                        Published
                    </option>

                </select>

            </div>

        </div>


        <div class="form-group">

            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"
                placeholder="Describe what students will learn in this course..."
            ></textarea>

        </div>


        <button
            type="submit"
            name="add_course"
            class="admin-button"
        >
            Add Course
        </button>

    </form>

</section>



<!-- ==========================================
     COURSE LIST
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Existing Courses</h3>

            <p>
                Courses currently stored in the system.
            </p>

        </div>

        <div class="section-count">

            <?php echo count($courses); ?> Courses

        </div>

    </div>


    <?php if (empty($courses)): ?>

        <div class="empty-state">

            <h3>No Courses Yet</h3>

            <p>
                Add your first CNC training course using the form above.
            </p>

        </div>

    <?php else: ?>


        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Course</th>

                        <th>Level</th>

                        <th>Duration</th>

                        <th>Status</th>

                        <th>Created</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($courses as $course): ?>

                        <tr>

                            <td>
                                <?php echo $course["id"]; ?>
                            </td>


                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $course["title"]
                                    );
                                    ?>
                                </strong>

                                <?php if (!empty($course["description"])): ?>

                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            substr(
                                                $course["description"],
                                                0,
                                                80
                                            )
                                        );

                                        if (
                                            strlen(
                                                $course["description"]
                                            ) > 80
                                        ) {
                                            echo "...";
                                        }
                                        ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <td>

                                <span class="level-badge">

                                    <?php
                                    echo htmlspecialchars(
                                        $course["level"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo !empty($course["duration"])
                                    ? htmlspecialchars($course["duration"])
                                    : "Not specified";
                                ?>

                            </td>


                            <td>

                                <span
                                    class="status-badge
                                    <?php
                                    echo strtolower(
                                        $course["status"]
                                    );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $course["status"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $course["created_at"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <a
                                    href="?delete=<?php echo $course["id"]; ?>"
                                    class="delete-link"
                                    onclick="return confirm('Are you sure you want to delete this course?');"
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
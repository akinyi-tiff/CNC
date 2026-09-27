<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Project Submissions";
$page_heading = "Project Submissions";


// =========================================
// UPDATE SUBMISSION STATUS
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $submission_id = intval($_POST["submission_id"]);
    $status = $_POST["status"];

    $allowed_statuses = [
        "Pending",
        "Reviewed",
        "Contacted",
        "Closed"
    ];

    if (in_array($status, $allowed_statuses, true)) {

        $stmt = $conn->prepare("
            UPDATE project_submissions
            SET status = ?
            WHERE id = ?
        ");

        $stmt->bind_param("si", $status, $submission_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: project-submissions.php");
    exit;
}


// =========================================
// GET STATUS COUNTS
// =========================================

$pending_count = 0;
$reviewed_count = 0;
$contacted_count = 0;
$closed_count = 0;

$count_query = "
    SELECT status, COUNT(*) AS total
    FROM project_submissions
    GROUP BY status
";

$count_result = $conn->query($count_query);

if ($count_result) {

    while ($row = $count_result->fetch_assoc()) {

        switch ($row["status"]) {

            case "Pending":
                $pending_count = $row["total"];
                break;

            case "Reviewed":
                $reviewed_count = $row["total"];
                break;

            case "Contacted":
                $contacted_count = $row["total"];
                break;

            case "Closed":
                $closed_count = $row["total"];
                break;
        }
    }
}


// =========================================
// GET ALL PROJECT SUBMISSIONS
// =========================================

$query = "
    SELECT
        id,
        name,
        email,
        phone,
        title,
        description,
        file_path,
        status,
        created_at
    FROM project_submissions
    ORDER BY created_at DESC
";

$result = $conn->query($query);


require_once "header.php";

?>

<div class="admin-content">

    <!-- =========================================
         PAGE HEADER
         ========================================= -->

    <div class="page-header">

        <div>

            <h2>Project Submissions</h2>

            <p>
                View and manage project submissions sent by customers.
            </p>

        </div>

    </div>


    <!-- =========================================
         STATUS CARDS
         ========================================= -->

    <div class="stats-grid">

        <div class="stat-card">
            <h3><?php echo $pending_count; ?></h3>
            <p>Pending</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $reviewed_count; ?></h3>
            <p>Reviewed</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $contacted_count; ?></h3>
            <p>Contacted</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $closed_count; ?></h3>
            <p>Closed</p>
        </div>

    </div>


    <!-- =========================================
         SUBMISSIONS TABLE
         ========================================= -->

    <div class="admin-card">

        <div class="card-header">

            <div>

                <h3>Customer Project Submissions</h3>

                <p>
                    Review project information and manage customer follow-up.
                </p>

            </div>

        </div>


        <div class="table-container">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Customer</th>

                        <th>Contact</th>

                        <th>Project</th>

                        <th>Description</th>

                        <th>File</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($submission = $result->fetch_assoc()): ?>

                        <tr>

                            <!-- ID -->

                            <td>
                                #<?php echo $submission["id"]; ?>
                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $submission["name"]
                                    );
                                    ?>
                                </strong>

                            </td>


                            <!-- CONTACT -->

                            <td>

                                <div>
                                    <?php
                                    echo htmlspecialchars(
                                        $submission["email"]
                                    );
                                    ?>
                                </div>

                                <?php if (!empty($submission["phone"])): ?>

                                    <small>
                                        <?php
                                        echo htmlspecialchars(
                                            $submission["phone"]
                                        );
                                        ?>
                                    </small>

                                <?php endif; ?>

                            </td>


                            <!-- PROJECT TITLE -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $submission["title"]
                                );
                                ?>

                            </td>


                            <!-- DESCRIPTION -->

                            <td class="description-cell">

                                <?php

                                $description = $submission["description"];

                                if (strlen($description) > 100) {

                                    echo htmlspecialchars(
                                        substr($description, 0, 100)
                                    );

                                    echo "...";

                                } else {

                                    echo htmlspecialchars($description);

                                }

                                ?>

                            </td>


                            <!-- FILE -->

                            <td>

                                <?php if (!empty($submission["file_path"])): ?>

                                    <a
                                        href="../<?php echo htmlspecialchars($submission["file_path"]); ?>"
                                        target="_blank"
                                        class="table-link"
                                    >
                                        View File
                                    </a>

                                <?php else: ?>

                                    <span>—</span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="submission_id"
                                        value="<?php echo $submission["id"]; ?>"
                                    >

                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        class="status-select"
                                    >

                                        <option
                                            value="Pending"
                                            <?php
                                            if ($submission["status"] === "Pending") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="Reviewed"
                                            <?php
                                            if ($submission["status"] === "Reviewed") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Reviewed
                                        </option>

                                        <option
                                            value="Contacted"
                                            <?php
                                            if ($submission["status"] === "Contacted") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Contacted
                                        </option>

                                        <option
                                            value="Closed"
                                            <?php
                                            if ($submission["status"] === "Closed") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Closed
                                        </option>

                                    </select>

                                    <input
                                        type="hidden"
                                        name="update_status"
                                        value="1"
                                    >

                                </form>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($submission["created_at"])
                                );
                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="8" class="empty-state">

                            <p>
                                No project submissions found.
                            </p>

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

require_once "footer.php";

?>
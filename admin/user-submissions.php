<?php

require_once "auth.php";
require_once "db.php";

$page_title = "User Submissions";
$page_heading = "User Submissions";


// =========================================
// UPDATE SUBMISSION STATUS
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $submission_id = intval($_POST["submission_id"]);
    $status = $_POST["status"];

    $allowed_statuses = [
        "Pending",
        "Approved",
        "Rejected"
    ];

    if (in_array($status, $allowed_statuses, true)) {

        $admin_id = $_SESSION["admin_id"];

        $stmt = $conn->prepare("
            UPDATE user_submissions
            SET
                status = ?,
                reviewed_by = ?,
                reviewed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->bind_param(
            "sii",
            $status,
            $admin_id,
            $submission_id
        );

        $stmt->execute();
        $stmt->close();
    }

    header("Location: user-submissions.php");
    exit;
}


// =========================================
// GET STATUS COUNTS
// =========================================

$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;

$count_query = "
    SELECT status, COUNT(*) AS total
    FROM user_submissions
    GROUP BY status
";

$count_result = $conn->query($count_query);

if ($count_result) {

    while ($row = $count_result->fetch_assoc()) {

        switch ($row["status"]) {

            case "Pending":
                $pending_count = $row["total"];
                break;

            case "Approved":
                $approved_count = $row["total"];
                break;

            case "Rejected":
                $rejected_count = $row["total"];
                break;
        }
    }
}


// =========================================
// FILTER
// =========================================

$filter = "";

if (isset($_GET["status"])) {
    $filter = $_GET["status"];
}


// =========================================
// GET SUBMISSIONS
// =========================================

if (
    $filter === "Pending" ||
    $filter === "Approved" ||
    $filter === "Rejected"
) {

    $stmt = $conn->prepare("
        SELECT
            user_submissions.id,
            user_submissions.name,
            user_submissions.email,
            user_submissions.title,
            user_submissions.content,
            user_submissions.status,
            user_submissions.moderation_note,
            user_submissions.reviewed_at,
            user_submissions.created_at,
            admins.name AS reviewer_name
        FROM user_submissions
        LEFT JOIN admins
            ON user_submissions.reviewed_by = admins.id
        WHERE user_submissions.status = ?
        ORDER BY user_submissions.created_at DESC
    ");

    $stmt->bind_param("s", $filter);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $query = "
        SELECT
            user_submissions.id,
            user_submissions.name,
            user_submissions.email,
            user_submissions.title,
            user_submissions.content,
            user_submissions.status,
            user_submissions.moderation_note,
            user_submissions.reviewed_at,
            user_submissions.created_at,
            admins.name AS reviewer_name
        FROM user_submissions
        LEFT JOIN admins
            ON user_submissions.reviewed_by = admins.id
        ORDER BY user_submissions.created_at DESC
    ";

    $result = $conn->query($query);
}


require_once "header.php";

?>

<div class="admin-content">

    <!-- =========================================
         PAGE HEADER
         ========================================= -->

    <div class="page-header">

        <div>

            <h2>User Submissions</h2>

            <p>
                Review and manage content submitted by website users.
            </p>

        </div>

    </div>


    <!-- =========================================
         STATUS CARDS
         ========================================= -->

    <div class="stats-grid">

        <div class="stat-card">

            <h3>
                <?php echo $pending_count; ?>
            </h3>

            <p>
                Pending
            </p>

        </div>


        <div class="stat-card">

            <h3>
                <?php echo $approved_count; ?>
            </h3>

            <p>
                Approved
            </p>

        </div>


        <div class="stat-card">

            <h3>
                <?php echo $rejected_count; ?>
            </h3>

            <p>
                Rejected
            </p>

        </div>

    </div>


    <!-- =========================================
         FILTERS
         ========================================= -->

    <div class="admin-card">

        <div class="filter-bar">

            <a
                href="user-submissions.php"
                class="<?php echo ($filter === "") ? "active-filter" : ""; ?>"
            >
                All Submissions
            </a>


            <a
                href="user-submissions.php?status=Pending"
                class="<?php echo ($filter === "Pending") ? "active-filter" : ""; ?>"
            >
                Pending
            </a>


            <a
                href="user-submissions.php?status=Approved"
                class="<?php echo ($filter === "Approved") ? "active-filter" : ""; ?>"
            >
                Approved
            </a>


            <a
                href="user-submissions.php?status=Rejected"
                class="<?php echo ($filter === "Rejected") ? "active-filter" : ""; ?>"
            >
                Rejected
            </a>

        </div>

    </div>


    <!-- =========================================
         SUBMISSIONS TABLE
         ========================================= -->

    <div class="admin-card">

        <div class="card-header">

            <div>

                <h3>Submitted Content</h3>

                <p>
                    Review content before approving it for use on the website.
                </p>

            </div>

        </div>


        <div class="table-container">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>User</th>

                        <th>Email</th>

                        <th>Title</th>

                        <th>Content</th>

                        <th>Status</th>

                        <th>Date</th>

                        <th>Action</th>

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


                            <!-- USER -->

                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $submission["name"]
                                    );
                                    ?>
                                </strong>

                            </td>


                            <!-- EMAIL -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $submission["email"]
                                );
                                ?>

                            </td>


                            <!-- TITLE -->

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $submission["title"]
                                );
                                ?>

                            </td>


                            <!-- CONTENT -->

                            <td class="description-cell">

                                <?php

                                $content =
                                    $submission["content"];

                                if (strlen($content) > 100) {

                                    echo htmlspecialchars(
                                        substr($content, 0, 100)
                                    );

                                    echo "...";

                                } else {

                                    echo htmlspecialchars(
                                        $content
                                    );

                                }

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ($submission["status"] === "Pending"): ?>

                                    <span class="status-badge status-pending">
                                        Pending
                                    </span>

                                <?php elseif ($submission["status"] === "Approved"): ?>

                                    <span class="status-badge status-approved">
                                        Approved
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge status-rejected">
                                        Rejected
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $submission["created_at"]
                                    )
                                );
                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="submission-view.php?id=<?php echo $submission["id"]; ?>"
                                    class="table-link"
                                >
                                    View
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="8"
                            class="empty-state"
                        >

                            <p>
                                No user submissions found.
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
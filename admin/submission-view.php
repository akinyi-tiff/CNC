
<?php

require_once "auth.php";
require_once "db.php";

$page_title = "View Submission";
$page_heading = "View Submission";


// =========================================
// GET SUBMISSION ID
// =========================================

$submission_id = isset($_GET["id"])
    ? intval($_GET["id"])
    : 0;

$error_message = "";
$success_message = "";


// =========================================
// CHECK SUBMISSION ID
// =========================================

if ($submission_id <= 0) {

    $error_message = "Invalid submission.";

}


// =========================================
// UPDATE SUBMISSION
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_submission"]) &&
    $submission_id > 0
) {

    $status = $_POST["status"] ?? "Pending";
    $moderation_note = trim(
        $_POST["moderation_note"] ?? ""
    );


    // =========================================
    // VALIDATE STATUS
    // =========================================

    $allowed_statuses = [
        "Pending",
        "Approved",
        "Rejected"
    ];

    if (!in_array($status, $allowed_statuses, true)) {

        $error_message = "Invalid submission status.";

    } else {

        $admin_id = $_SESSION["admin_id"];


        // =========================================
        // UPDATE DATABASE
        // =========================================

        $stmt = $conn->prepare("
            UPDATE user_submissions
            SET
                status = ?,
                moderation_note = ?,
                reviewed_by = ?,
                reviewed_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->bind_param(
            "ssii",
            $status,
            $moderation_note,
            $admin_id,
            $submission_id
        );


        if ($stmt->execute()) {

            $success_message =
                "Submission updated successfully.";

        } else {

            $error_message =
                "Failed to update submission.";
        }

        $stmt->close();
    }
}


// =========================================
// GET SUBMISSION
// =========================================

$submission = null;

if ($submission_id > 0) {

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
        WHERE user_submissions.id = ?
        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $submission_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $submission = $result->fetch_assoc();

    } else {

        $error_message =
            "Submission not found.";
    }

    $stmt->close();
}


require_once "header.php";

?>

<div class="admin-content">

    <!-- =========================================
         PAGE HEADER
         ========================================= -->

    <div class="page-header">

        <div>

            <h2>View Submission</h2>

            <p>
                Review the submitted content and manage its status.
            </p>

        </div>


        <div>

            <a
                href="user-submissions.php"
                class="admin-button secondary-button"
            >
                ← Back to Submissions
            </a>

        </div>

    </div>


    <!-- =========================================
         SUCCESS MESSAGE
         ========================================= -->

    <?php if (!empty($success_message)): ?>

        <div class="alert success-alert">

            <?php
            echo htmlspecialchars(
                $success_message
            );
            ?>

        </div>

    <?php endif; ?>


    <!-- =========================================
         ERROR MESSAGE
         ========================================= -->

    <?php if (!empty($error_message)): ?>

        <div class="alert error-alert">

            <?php
            echo htmlspecialchars(
                $error_message
            );
            ?>

        </div>

    <?php endif; ?>


    <?php if ($submission): ?>

        <!-- =========================================
             SUBMISSION INFORMATION
             ========================================= -->

        <div class="admin-card">

            <div class="card-header">

                <div>

                    <h3>
                        Submission Information
                    </h3>

                </div>

            </div>


            <div class="details-grid">

                <!-- USER NAME -->

                <div class="detail-item">

                    <span class="detail-label">
                        Submitted By
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $submission["name"]
                        );
                        ?>
                    </strong>

                </div>


                <!-- EMAIL -->

                <div class="detail-item">

                    <span class="detail-label">
                        Email
                    </span>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $submission["email"]
                        );
                        ?>
                    </strong>

                </div>


                <!-- SUBMISSION ID -->

                <div class="detail-item">

                    <span class="detail-label">
                        Submission ID
                    </span>

                    <strong>
                        #<?php echo $submission["id"]; ?>
                    </strong>

                </div>


                <!-- DATE -->

                <div class="detail-item">

                    <span class="detail-label">
                        Submitted On
                    </span>

                    <strong>

                        <?php
                        echo date(
                            "d M Y, H:i",
                            strtotime(
                                $submission["created_at"]
                            )
                        );
                        ?>

                    </strong>

                </div>

            </div>

        </div>


        <!-- =========================================
             SUBMITTED CONTENT
             ========================================= -->

        <div class="admin-card">

            <div class="card-header">

                <div>

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $submission["title"]
                        );
                        ?>
                    </h3>

                    <p>
                        Submitted content
                    </p>

                </div>

            </div>


            <div class="submission-content">

                <?php

                echo nl2br(
                    htmlspecialchars(
                        $submission["content"]
                    )
                );

                ?>

            </div>

        </div>


        <!-- =========================================
             REVIEW INFORMATION
             ========================================= -->

        <div class="admin-card">

            <div class="card-header">

                <div>

                    <h3>
                        Review Submission
                    </h3>

                    <p>
                        Update the submission status and add an internal moderation note.
                    </p>

                </div>

            </div>


            <form
                method="POST"
                class="admin-form"
            >

                <input
                    type="hidden"
                    name="update_submission"
                    value="1"
                >


                <!-- STATUS -->

                <div class="form-group">

                    <label for="status">
                        Status
                    </label>

                    <select
                        id="status"
                        name="status"
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
                            value="Approved"
                            <?php
                            if ($submission["status"] === "Approved") {
                                echo "selected";
                            }
                            ?>
                        >
                            Approved
                        </option>

                        <option
                            value="Rejected"
                            <?php
                            if ($submission["status"] === "Rejected") {
                                echo "selected";
                            }
                            ?>
                        >
                            Rejected
                        </option>

                    </select>

                </div>


                <!-- MODERATION NOTE -->

                <div class="form-group">

                    <label for="moderation_note">
                        Moderation Note
                    </label>

                    <textarea
                        id="moderation_note"
                        name="moderation_note"
                        rows="5"
                        placeholder="Add an internal note about this submission..."
                    ><?php
                    echo htmlspecialchars(
                        $submission["moderation_note"] ?? ""
                    );
                    ?></textarea>

                    <small>
                        This note is for the administrator and can be used to record why a submission was approved or rejected.
                    </small>

                </div>


                <!-- REVIEW INFORMATION -->

                <?php if (!empty($submission["reviewer_name"])): ?>

                    <div class="review-information">

                        <p>

                            <strong>
                                Reviewed by:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $submission["reviewer_name"]
                            );
                            ?>

                        </p>


                        <?php if (!empty($submission["reviewed_at"])): ?>

                            <p>

                                <strong>
                                    Reviewed on:
                                </strong>

                                <?php
                                echo date(
                                    "d M Y, H:i",
                                    strtotime(
                                        $submission["reviewed_at"]
                                    )
                                );
                                ?>

                            </p>

                        <?php endif; ?>

                    </div>

                <?php endif; ?>


                <!-- BUTTON -->

                <div class="form-actions">

                    <button
                        type="submit"
                        class="admin-button"
                    >
                        Save Review
                    </button>


                    <a
                        href="user-submissions.php"
                        class="admin-button secondary-button"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    <?php endif; ?>

</div>


<?php

require_once "footer.php";

?>
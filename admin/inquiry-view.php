<?php

require_once "auth.php";
require_once "db.php";

$page_title = "View Inquiry | CNC Admin";
$page_heading = "View Inquiry";

require_once "header.php";


// ==========================================
// GET INQUIRY ID
// ==========================================

$inquiry_id = (int) ($_GET["id"] ?? 0);

if ($inquiry_id <= 0) {

    echo '
        <div class="admin-message error">
            Invalid inquiry ID.
        </div>
    ';

    require_once "footer.php";
    exit;
}


// ==========================================
// GET INQUIRY
// ==========================================

$stmt = $conn->prepare(
    "SELECT *
     FROM inquiries
     WHERE id = ?
     LIMIT 1"
);

$stmt->bind_param("i", $inquiry_id);
$stmt->execute();

$result = $stmt->get_result();
$inquiry = $result->fetch_assoc();

$stmt->close();


// ==========================================
// CHECK IF INQUIRY EXISTS
// ==========================================

if (!$inquiry) {

    echo '
        <div class="admin-message error">
            Inquiry not found.
        </div>
    ';

    require_once "footer.php";
    exit;
}


// ==========================================
// UPDATE STATUS
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_status"])
) {

    $status = $_POST["status"] ?? "";

    $allowed_statuses = [
        "New",
        "Reviewed",
        "Resolved"
    ];

    if (in_array($status, $allowed_statuses)) {

        $update_stmt = $conn->prepare(
            "UPDATE inquiries
             SET status = ?
             WHERE id = ?"
        );

        $update_stmt->bind_param(
            "si",
            $status,
            $inquiry_id
        );

        if ($update_stmt->execute()) {

            $inquiry["status"] = $status;

            $message = "Inquiry status updated successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to update inquiry status.";
            $message_type = "error";

        }

        $update_stmt->close();

    } else {

        $message = "Invalid inquiry status.";
        $message_type = "error";

    }
}

?>


<!-- ==========================================
     PAGE HEADER
========================================== -->

<div class="page-header">

    <div>

        <p class="page-label">
            CUSTOMER REQUEST
        </p>

        <h2>Inquiry Details</h2>

        <p>
            View the complete information submitted by the customer.
        </p>

    </div>


    <div>

        <a
            href="inquiries.php"
            class="admin-button secondary"
        >
            Back to Inquiries
        </a>

    </div>

</div>


<?php if (isset($message)): ?>

    <div class="admin-message <?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>


<!-- ==========================================
     INQUIRY INFORMATION
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>
                Inquiry #<?php echo $inquiry["id"]; ?>
            </h3>

            <p>
                Submitted on
                <?php
                echo date(
                    "d M Y, H:i",
                    strtotime(
                        $inquiry["created_at"]
                    )
                );
                ?>
            </p>

        </div>

    </div>


    <div class="details-grid">


        <!-- CUSTOMER DETAILS -->

        <div class="details-card">

            <h3>Customer Information</h3>


            <div class="detail-item">

                <span class="detail-label">
                    Name
                </span>

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $inquiry["name"]
                    );
                    ?>
                </strong>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Email
                </span>

                <strong>

                    <a
                        href="mailto:<?php
                        echo htmlspecialchars(
                            $inquiry["email"]
                        );
                        ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $inquiry["email"]
                        );
                        ?>

                    </a>

                </strong>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Phone
                </span>

                <strong>

                    <?php

                    if (!empty($inquiry["phone"])) {

                        echo htmlspecialchars(
                            $inquiry["phone"]
                        );

                    } else {

                        echo "Not provided";

                    }

                    ?>

                </strong>

            </div>

        </div>



        <!-- INQUIRY DETAILS -->

        <div class="details-card">

            <h3>Inquiry Information</h3>


            <div class="detail-item">

                <span class="detail-label">
                    Type
                </span>

                <span class="level-badge">

                    <?php
                    echo htmlspecialchars(
                        $inquiry["type"]
                    );
                    ?>

                </span>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Subject
                </span>

                <strong>

                    <?php

                    if (!empty($inquiry["subject"])) {

                        echo htmlspecialchars(
                            $inquiry["subject"]
                        );

                    } else {

                        echo "No subject";

                    }

                    ?>

                </strong>

            </div>


            <div class="detail-item">

                <span class="detail-label">
                    Status
                </span>

                <span class="status-badge
                    <?php
                    echo strtolower(
                        $inquiry["status"]
                    );
                    ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $inquiry["status"]
                    );
                    ?>

                </span>

            </div>

        </div>

    </div>

</section>



<!-- ==========================================
     MESSAGE
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Customer Message</h3>

        </div>

    </div>


    <div class="message-content">

        <?php

        echo nl2br(
            htmlspecialchars(
                $inquiry["message"]
            )
        );

        ?>

    </div>

</section>



<!-- ==========================================
     UPDATE STATUS
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Update Inquiry Status</h3>

            <p>
                Change the status as the inquiry is handled.
            </p>

        </div>

    </div>


    <form
        method="POST"
        class="admin-form"
    >

        <div class="form-group">

            <label for="status">
                Inquiry Status
            </label>

            <select
                id="status"
                name="status"
            >

                <option
                    value="New"
                    <?php
                    echo $inquiry["status"] === "New"
                        ? "selected"
                        : "";
                    ?>
                >
                    New
                </option>


                <option
                    value="Reviewed"
                    <?php
                    echo $inquiry["status"] === "Reviewed"
                        ? "selected"
                        : "";
                    ?>
                >
                    Reviewed
                </option>


                <option
                    value="Resolved"
                    <?php
                    echo $inquiry["status"] === "Resolved"
                        ? "selected"
                        : "";
                    ?>
                >
                    Resolved
                </option>

            </select>

        </div>


        <button
            type="submit"
            name="update_status"
            class="admin-button"
        >
            Update Status
        </button>

    </form>

</section>


<?php

require_once "footer.php";

?>
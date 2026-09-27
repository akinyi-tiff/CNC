
<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Inquiries | CNC Admin";
$page_heading = "Inquiries";

require_once "header.php";


// ==========================================
// UPDATE INQUIRY STATUS
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_status"])
) {

    $inquiry_id = (int) ($_POST["inquiry_id"] ?? 0);
    $status = $_POST["status"] ?? "";

    $allowed_statuses = [
        "New",
        "Reviewed",
        "Resolved"
    ];

    if (
        $inquiry_id > 0 &&
        in_array($status, $allowed_statuses)
    ) {

        $stmt = $conn->prepare(
            "UPDATE inquiries
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $inquiry_id
        );

        if ($stmt->execute()) {

            $message = "Inquiry status updated successfully.";
            $message_type = "success";

        } else {

            $message = "Unable to update inquiry status.";
            $message_type = "error";

        }

        $stmt->close();

    } else {

        $message = "Invalid inquiry status.";
        $message_type = "error";

    }
}


// ==========================================
// GET INQUIRIES
// ==========================================

$inquiries = [];

$result = $conn->query(
    "SELECT *
     FROM inquiries
     ORDER BY created_at DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $inquiries[] = $row;
    }

}


// ==========================================
// COUNT INQUIRIES
// ==========================================

$total_inquiries = count($inquiries);

$new_inquiries = 0;
$reviewed_inquiries = 0;
$resolved_inquiries = 0;

foreach ($inquiries as $inquiry) {

    if ($inquiry["status"] === "New") {
        $new_inquiries++;
    }

    if ($inquiry["status"] === "Reviewed") {
        $reviewed_inquiries++;
    }

    if ($inquiry["status"] === "Resolved") {
        $resolved_inquiries++;
    }

}

?>


<!-- ==========================================
     PAGE HEADER
========================================== -->

<div class="page-header">

    <div>

        <p class="page-label">
            CUSTOMER REQUESTS
        </p>

        <h2>Inquiries</h2>

        <p>
            View and manage questions and information requests
            submitted by customers.
        </p>

    </div>

</div>


<?php if (isset($message)): ?>

    <div class="admin-message <?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>


<!-- ==========================================
     INQUIRY STATISTICS
========================================== -->

<section class="dashboard-cards">

    <div class="dashboard-card">

        <h3>Total Inquiries</h3>

        <strong>
            <?php echo $total_inquiries; ?>
        </strong>

        <p>
            All customer inquiries
        </p>

    </div>


    <div class="dashboard-card">

        <h3>New</h3>

        <strong>
            <?php echo $new_inquiries; ?>
        </strong>

        <p>
            Awaiting review
        </p>

    </div>


    <div class="dashboard-card">

        <h3>Reviewed</h3>

        <strong>
            <?php echo $reviewed_inquiries; ?>
        </strong>

        <p>
            Currently being handled
        </p>

    </div>


    <div class="dashboard-card">

        <h3>Resolved</h3>

        <strong>
            <?php echo $resolved_inquiries; ?>
        </strong>

        <p>
            Completed inquiries
        </p>

    </div>

</section>



<!-- ==========================================
     INQUIRIES TABLE
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Customer Inquiries</h3>

            <p>
                Recent inquiries submitted through the website.
            </p>

        </div>

        <div class="section-count">

            <?php echo $total_inquiries; ?> Inquiries

        </div>

    </div>


    <?php if (empty($inquiries)): ?>

        <div class="empty-state">

            <h3>No Inquiries Yet</h3>

            <p>
                Customer inquiries will appear here when submitted
                through the website.
            </p>

        </div>

    <?php else: ?>


        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Customer</th>

                        <th>Type</th>

                        <th>Subject</th>

                        <th>Status</th>

                        <th>Date</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($inquiries as $inquiry): ?>

                        <tr>


                            <!-- ID -->

                            <td>

                                <?php
                                echo $inquiry["id"];
                                ?>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $inquiry["name"]
                                    );
                                    ?>

                                </strong>

                                <small>

                                    <?php
                                    echo htmlspecialchars(
                                        $inquiry["email"]
                                    );
                                    ?>

                                </small>

                            </td>


                            <!-- TYPE -->

                            <td>

                                <span class="level-badge">

                                    <?php
                                    echo htmlspecialchars(
                                        $inquiry["type"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- SUBJECT -->

                            <td>

                                <?php

                                if (!empty($inquiry["subject"])) {

                                    echo htmlspecialchars(
                                        $inquiry["subject"]
                                    );

                                } else {

                                    echo "No subject";

                                }

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="inquiry_id"
                                        value="<?php
                                        echo $inquiry["id"];
                                        ?>"
                                    >

                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        class="status-select"
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
                                    strtotime(
                                        $inquiry["created_at"]
                                    )
                                );

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="inquiry-view.php?id=<?php
                                    echo $inquiry["id"];
                                    ?>"
                                    class="view-link"
                                >
                                    View
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
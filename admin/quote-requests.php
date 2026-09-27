<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Quote Requests";
$page_heading = "Quote Requests";


// =========================================
// UPDATE QUOTE STATUS
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_status"])) {

    $quote_id = intval($_POST["quote_id"]);
    $status = $_POST["status"];

    $allowed_statuses = [
        "Pending",
        "Under Review",
        "Quoted",
        "Accepted",
        "Rejected"
    ];

    if (in_array($status, $allowed_statuses, true)) {

        $stmt = $conn->prepare("
            UPDATE quote_requests
            SET status = ?
            WHERE id = ?
        ");

        $stmt->bind_param("si", $status, $quote_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: quote-requests.php");
    exit;
}


// =========================================
// GET QUOTE REQUEST COUNTS
// =========================================

$pending_count = 0;
$review_count = 0;
$quoted_count = 0;
$accepted_count = 0;
$rejected_count = 0;

$count_query = "
    SELECT status, COUNT(*) AS total
    FROM quote_requests
    GROUP BY status
";

$count_result = $conn->query($count_query);

if ($count_result) {

    while ($row = $count_result->fetch_assoc()) {

        switch ($row["status"]) {

            case "Pending":
                $pending_count = $row["total"];
                break;

            case "Under Review":
                $review_count = $row["total"];
                break;

            case "Quoted":
                $quoted_count = $row["total"];
                break;

            case "Accepted":
                $accepted_count = $row["total"];
                break;

            case "Rejected":
                $rejected_count = $row["total"];
                break;
        }
    }
}


// =========================================
// GET ALL QUOTE REQUESTS
// =========================================

$query = "
    SELECT
        id,
        name,
        email,
        phone,
        service,
        material,
        quantity,
        project_description,
        status,
        created_at
    FROM quote_requests
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
            <h2>Quote Requests</h2>

            <p>
                View and manage customer requests for quotations.
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
            <h3><?php echo $review_count; ?></h3>
            <p>Under Review</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $quoted_count; ?></h3>
            <p>Quoted</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $accepted_count; ?></h3>
            <p>Accepted</p>
        </div>

        <div class="stat-card">
            <h3><?php echo $rejected_count; ?></h3>
            <p>Rejected</p>
        </div>

    </div>


    <!-- =========================================
         QUOTE REQUESTS TABLE
         ========================================= -->

    <div class="admin-card">

        <div class="card-header">

            <div>
                <h3>Customer Quote Requests</h3>

                <p>
                    Manage quotation requests submitted through the website.
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

                        <th>Service</th>

                        <th>Material</th>

                        <th>Quantity</th>

                        <th>Project Description</th>

                        <th>Status</th>

                        <th>Date</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($quote = $result->fetch_assoc()): ?>

                        <tr>

                            <!-- ID -->

                            <td>
                                #<?php echo $quote["id"]; ?>
                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <strong>
                                    <?php echo htmlspecialchars($quote["name"]); ?>
                                </strong>

                            </td>


                            <!-- CONTACT -->

                            <td>

                                <div>
                                    <?php echo htmlspecialchars($quote["email"]); ?>
                                </div>

                                <?php if (!empty($quote["phone"])): ?>

                                    <small>
                                        <?php echo htmlspecialchars($quote["phone"]); ?>
                                    </small>

                                <?php endif; ?>

                            </td>


                            <!-- SERVICE -->

                            <td>

                                <?php

                                if (!empty($quote["service"])) {
                                    echo htmlspecialchars($quote["service"]);
                                } else {
                                    echo "—";
                                }

                                ?>

                            </td>


                            <!-- MATERIAL -->

                            <td>

                                <?php

                                if (!empty($quote["material"])) {
                                    echo htmlspecialchars($quote["material"]);
                                } else {
                                    echo "—";
                                }

                                ?>

                            </td>


                            <!-- QUANTITY -->

                            <td>

                                <?php

                                if ($quote["quantity"] !== null && $quote["quantity"] !== "") {
                                    echo htmlspecialchars($quote["quantity"]);
                                } else {
                                    echo "—";
                                }

                                ?>

                            </td>


                            <!-- PROJECT DESCRIPTION -->

                            <td class="description-cell">

                                <?php

                                $description = $quote["project_description"];

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


                            <!-- STATUS -->

                            <td>

                                <form method="POST">

                                    <input
                                        type="hidden"
                                        name="quote_id"
                                        value="<?php echo $quote["id"]; ?>"
                                    >

                                    <select
                                        name="status"
                                        onchange="this.form.submit()"
                                        class="status-select"
                                    >

                                        <option
                                            value="Pending"
                                            <?php
                                            if ($quote["status"] === "Pending") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="Under Review"
                                            <?php
                                            if ($quote["status"] === "Under Review") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Under Review
                                        </option>

                                        <option
                                            value="Quoted"
                                            <?php
                                            if ($quote["status"] === "Quoted") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Quoted
                                        </option>

                                        <option
                                            value="Accepted"
                                            <?php
                                            if ($quote["status"] === "Accepted") {
                                                echo "selected";
                                            }
                                            ?>
                                        >
                                            Accepted
                                        </option>

                                        <option
                                            value="Rejected"
                                            <?php
                                            if ($quote["status"] === "Rejected") {
                                                echo "selected";
                                            }
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


                            <!-- DATE -->

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($quote["created_at"])
                                );
                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="9" class="empty-state">

                            <p>No quote requests found.</p>

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
<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Dashboard | CNC Admin";
$page_heading = "CNC Admin Dashboard";

require_once "header.php";

?>

<div class="dashboard-content">

    <p>Welcome, <?php echo htmlspecialchars($_SESSION["admin_name"]); ?></p>

            <h2>Overview</h2>

            <div class="dashboard-cards">

                <div class="dashboard-card">
                    <h3>Training Courses</h3>
                    <p>Manage CNC training courses.</p>
                    <a href="training.php">View Training</a>
                </div>


                <div class="dashboard-card">
                    <h3>Inquiries</h3>
                    <p>View customer inquiries.</p>
                    <a href="inquiries.php">View Inquiries</a>
                </div>


                <div class="dashboard-card">
                    <h3>Quote Requests</h3>
                    <p>Review customer quote requests.</p>
                    <a href="quote-requests.php">View Quotes</a>
                </div>


                <div class="dashboard-card">
                    <h3>Project Submissions</h3>
                    <p>Review submitted projects.</p>
                    <a href="project-submissions.php">
                        View Projects
                    </a>
                </div>


                <div class="dashboard-card">
                    <h3>Blog</h3>
                    <p>Create and manage blog posts.</p>
                    <a href="blog.php">Manage Blog</a>
                </div>


                <div class="dashboard-card">
                    <h3>User Submissions</h3>
                    <p>Review customer-submitted content.</p>
                    <a href="user-submissions.php">
                        View Submissions
                    </a>
                </div>

            </div>

</div>

</main>

</div>

</div>

</body>

</html>
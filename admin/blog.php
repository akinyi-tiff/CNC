<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Blog";
$page_heading = "Blog";


// =========================================
// DELETE BLOG POST
// =========================================

if (isset($_GET["delete"])) {

    $blog_id = intval($_GET["delete"]);

    if ($blog_id > 0) {

        // Get featured image before deleting the post
        $stmt = $conn->prepare("
            SELECT featured_image
            FROM blogs
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $blog_id);
        $stmt->execute();

        $image_result = $stmt->get_result();
        $image = $image_result->fetch_assoc();

        $stmt->close();


        // Delete the blog post
        $stmt = $conn->prepare("
            DELETE FROM blogs
            WHERE id = ?
        ");

        $stmt->bind_param("i", $blog_id);
        $stmt->execute();
        $stmt->close();


        // Delete featured image if it exists
        if ($image && !empty($image["featured_image"])) {

            $image_path = "../" . $image["featured_image"];

            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
    }

    header("Location: blog.php");
    exit;
}


// =========================================
// GET BLOG COUNTS
// =========================================

$draft_count = 0;
$published_count = 0;

$count_query = "
    SELECT status, COUNT(*) AS total
    FROM blogs
    GROUP BY status
";

$count_result = $conn->query($count_query);

if ($count_result) {

    while ($row = $count_result->fetch_assoc()) {

        if ($row["status"] === "Draft") {
            $draft_count = $row["total"];
        }

        if ($row["status"] === "Published") {
            $published_count = $row["total"];
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
// GET BLOG POSTS
// =========================================

if ($filter === "Draft" || $filter === "Published") {

    $stmt = $conn->prepare("
        SELECT
            blogs.id,
            blogs.title,
            blogs.slug,
            blogs.excerpt,
            blogs.category,
            blogs.status,
            blogs.featured_image,
            blogs.created_at,
            blogs.updated_at,
            admins.name AS admin_name
        FROM blogs
        LEFT JOIN admins
            ON blogs.admin_id = admins.id
        WHERE blogs.status = ?
        ORDER BY blogs.created_at DESC
    ");

    $stmt->bind_param("s", $filter);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $query = "
        SELECT
            blogs.id,
            blogs.title,
            blogs.slug,
            blogs.excerpt,
            blogs.category,
            blogs.status,
            blogs.featured_image,
            blogs.created_at,
            blogs.updated_at,
            admins.name AS admin_name
        FROM blogs
        LEFT JOIN admins
            ON blogs.admin_id = admins.id
        ORDER BY blogs.created_at DESC
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

            <h2>Blog</h2>

            <p>
                Manage articles and updates published on the website.
            </p>

        </div>


        <div>

            <a href="blog-create.php" class="admin-button">
                + Create Blog Post
            </a>

        </div>

    </div>


    <!-- =========================================
         BLOG STATUS CARDS
         ========================================= -->

    <div class="stats-grid">

        <div class="stat-card">

            <h3>
                <?php echo $published_count; ?>
            </h3>

            <p>
                Published
            </p>

        </div>


        <div class="stat-card">

            <h3>
                <?php echo $draft_count; ?>
            </h3>

            <p>
                Drafts
            </p>

        </div>

    </div>


    <!-- =========================================
         FILTERS
         ========================================= -->

    <div class="admin-card">

        <div class="filter-bar">

            <a
                href="blog.php"
                class="<?php echo ($filter === "") ? "active-filter" : ""; ?>"
            >
                All Posts
            </a>


            <a
                href="blog.php?status=Published"
                class="<?php echo ($filter === "Published") ? "active-filter" : ""; ?>"
            >
                Published
            </a>


            <a
                href="blog.php?status=Draft"
                class="<?php echo ($filter === "Draft") ? "active-filter" : ""; ?>"
            >
                Drafts
            </a>

        </div>

    </div>


    <!-- =========================================
         BLOG POSTS TABLE
         ========================================= -->

    <div class="admin-card">

        <div class="card-header">

            <div>

                <h3>Blog Posts</h3>

                <p>
                    View, manage and remove blog posts.
                </p>

            </div>

        </div>


        <div class="table-container">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Image</th>

                        <th>Title</th>

                        <th>Category</th>

                        <th>Author</th>

                        <th>Status</th>

                        <th>Date</th>

                        <th>Actions</th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>

                    <?php while ($blog = $result->fetch_assoc()): ?>

                        <tr>

                            <!-- ID -->

                            <td>
                                #<?php echo $blog["id"]; ?>
                            </td>


                            <!-- IMAGE -->

                            <td>

                                <?php if (!empty($blog["featured_image"])): ?>

                                    <img
                                        src="../<?php echo htmlspecialchars($blog["featured_image"]); ?>"
                                        alt="<?php echo htmlspecialchars($blog["title"]); ?>"
                                        class="blog-thumbnail"
                                    >

                                <?php else: ?>

                                    <span>—</span>

                                <?php endif; ?>

                            </td>


                            <!-- TITLE -->

                            <td>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $blog["title"]
                                    );
                                    ?>
                                </strong>


                                <?php if (!empty($blog["excerpt"])): ?>

                                    <small class="table-description">

                                        <?php

                                        $excerpt = $blog["excerpt"];

                                        if (strlen($excerpt) > 80) {

                                            echo htmlspecialchars(
                                                substr($excerpt, 0, 80)
                                            );

                                            echo "...";

                                        } else {

                                            echo htmlspecialchars($excerpt);

                                        }

                                        ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <!-- CATEGORY -->

                            <td>

                                <?php

                                if (!empty($blog["category"])) {

                                    echo htmlspecialchars(
                                        $blog["category"]
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- AUTHOR -->

                            <td>

                                <?php

                                if (!empty($blog["admin_name"])) {

                                    echo htmlspecialchars(
                                        $blog["admin_name"]
                                    );

                                } else {

                                    echo "Unknown";

                                }

                                ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ($blog["status"] === "Published"): ?>

                                    <span class="status-badge status-published">
                                        Published
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge status-draft">
                                        Draft
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- DATE -->

                            <td>

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime($blog["created_at"])
                                );

                                ?>

                            </td>


                            <!-- ACTIONS -->

                            <td>

                                <div class="table-actions">

                                    <a
                                        href="blog-view.php?id=<?php echo $blog["id"]; ?>"
                                        class="action-button view-button"
                                    >
                                        View
                                    </a>


                                    <a
                                        href="blog-create.php?id=<?php echo $blog["id"]; ?>"
                                        class="action-button manage-button"
                                    >
                                        Manage
                                    </a>


                                    <a
                                        href="blog.php?delete=<?php echo $blog["id"]; ?>"
                                        class="action-button remove-button"
                                        onclick="return confirm('Are you sure you want to delete this blog post?');"
                                    >
                                        Remove
                                    </a>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td colspan="8" class="empty-state">

                            <p>
                                No blog posts found.
                            </p>

                            <a
                                href="blog-create.php"
                                class="admin-button"
                            >
                                Create Your First Blog Post
                            </a>

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
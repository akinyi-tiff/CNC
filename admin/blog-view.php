<?php

require_once "auth.php";
require_once "db.php";

$page_title = "View Blog Post";
$page_heading = "View Blog Post";
$blog_id = intval($_GET["id"] ?? 0);
$blog = null;

if ($blog_id > 0) {
    $stmt = $conn->prepare("\n        SELECT\n            blogs.title,\n            blogs.excerpt,\n            blogs.content,\n            blogs.category,\n            blogs.status,\n            blogs.featured_image,\n            blogs.created_at,\n            admins.name AS admin_name\n        FROM blogs\n        LEFT JOIN admins ON blogs.admin_id = admins.id\n        WHERE blogs.id = ?\n        LIMIT 1\n    ");
    $stmt->bind_param("i", $blog_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $blog = $result->fetch_assoc();
    $stmt->close();
}

require_once "header.php";

?>

<div class="admin-content">
    <div class="page-header">
        <div>
            <h2><?php echo $blog ? htmlspecialchars($blog["title"]) : "Blog Post Not Found"; ?></h2>
            <p>Review the complete blog post before managing it.</p>
        </div>
        <div class="page-actions">
            <a href="blog.php" class="action-button manage-button">Back to Blog</a>
            <?php if ($blog): ?>
                <a href="blog-create.php?id=<?php echo $blog_id; ?>" class="action-button view-button">Manage Post</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($blog): ?>
        <article class="blog-view-card">
            <?php if (!empty($blog["featured_image"])): ?>
                <img
                    src="../<?php echo htmlspecialchars($blog["featured_image"]); ?>"
                    alt="<?php echo htmlspecialchars($blog["title"]); ?>"
                    class="blog-view-image"
                >
            <?php endif; ?>

            <div class="blog-view-meta">
                <span class="status-badge <?php echo $blog["status"] === "Published" ? "status-published" : "status-draft"; ?>">
                    <?php echo htmlspecialchars($blog["status"]); ?>
                </span>
                <?php if (!empty($blog["category"])): ?>
                    <span><?php echo htmlspecialchars($blog["category"]); ?></span>
                <?php endif; ?>
                <span><?php echo date("d M Y", strtotime($blog["created_at"])); ?></span>
                <span>By <?php echo htmlspecialchars($blog["admin_name"] ?: "Unknown"); ?></span>
            </div>

            <?php if (!empty($blog["excerpt"])): ?>
                <p class="blog-view-excerpt"><?php echo htmlspecialchars($blog["excerpt"]); ?></p>
            <?php endif; ?>

            <div class="blog-view-content">
                <?php echo nl2br(htmlspecialchars($blog["content"])); ?>
            </div>
        </article>
    <?php else: ?>
        <div class="empty-state">
            <p>This blog post could not be found.</p>
            <a href="blog.php" class="action-button manage-button">Return to Blog</a>
        </div>
    <?php endif; ?>
</div>

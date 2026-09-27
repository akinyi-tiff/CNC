<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Create Blog Post";
$page_heading = "Create Blog Post";


// =========================================
// VARIABLES
// =========================================

$blog_id = 0;

$title = "";
$slug = "";
$excerpt = "";
$content = "";
$category = "";
$status = "Draft";
$featured_image = "";

$success_message = "";
$error_message = "";


// =========================================
// CHECK IF EDITING
// =========================================

if (isset($_GET["id"])) {

    $blog_id = intval($_GET["id"]);

    if ($blog_id > 0) {

        $stmt = $conn->prepare("
            SELECT
                title,
                slug,
                excerpt,
                content,
                category,
                status,
                featured_image
            FROM blogs
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param("i", $blog_id);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $blog = $result->fetch_assoc();

            $title = $blog["title"];
            $slug = $blog["slug"];
            $excerpt = $blog["excerpt"];
            $content = $blog["content"];
            $category = $blog["category"];
            $status = $blog["status"];
            $featured_image = $blog["featured_image"];

        } else {

            $error_message = "Blog post not found.";

            $blog_id = 0;
        }

        $stmt->close();
    }
}


// =========================================
// PROCESS FORM
// =========================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $blog_id = intval($_POST["blog_id"] ?? 0);

    $title = trim($_POST["title"] ?? "");
    $slug = trim($_POST["slug"] ?? "");
    $excerpt = trim($_POST["excerpt"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $status = $_POST["status"] ?? "Draft";


    // =========================================
    // VALIDATE STATUS
    // =========================================

    $allowed_statuses = [
        "Draft",
        "Published"
    ];

    if (!in_array($status, $allowed_statuses, true)) {
        $status = "Draft";
    }


    // =========================================
    // BASIC VALIDATION
    // =========================================

    if ($title === "") {

        $error_message = "Please enter a blog title.";

    } elseif ($content === "") {

        $error_message = "Please enter the blog content.";

    } else {


        // =========================================
        // GENERATE SLUG IF EMPTY
        // =========================================

        if ($slug === "") {

            $slug = strtolower($title);

            $slug = preg_replace(
                "/[^a-z0-9]+/",
                "-",
                $slug
            );

            $slug = trim($slug, "-");
        }


        // =========================================
        // CHECK SLUG
        // =========================================

        if ($blog_id > 0) {

            $stmt = $conn->prepare("
                SELECT id
                FROM blogs
                WHERE slug = ?
                AND id != ?
                LIMIT 1
            ");

            $stmt->bind_param("si", $slug, $blog_id);

        } else {

            $stmt = $conn->prepare("
                SELECT id
                FROM blogs
                WHERE slug = ?
                LIMIT 1
            ");

            $stmt->bind_param("s", $slug);
        }

        $stmt->execute();

        $slug_result = $stmt->get_result();

        if ($slug_result->num_rows > 0) {

            $error_message = "That slug is already being used. Please choose another one.";

        }

        $stmt->close();


        // =========================================
        // IMAGE UPLOAD
        // =========================================

        $new_image_path = $featured_image;

        if (
            empty($error_message) &&
            isset($_FILES["featured_image"]) &&
            $_FILES["featured_image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["featured_image"]["error"] === UPLOAD_ERR_OK) {

                $allowed_extensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];

                $file_name = $_FILES["featured_image"]["name"];
                $file_tmp = $_FILES["featured_image"]["tmp_name"];
                $file_size = $_FILES["featured_image"]["size"];

                $extension = strtolower(
                    pathinfo(
                        $file_name,
                        PATHINFO_EXTENSION
                    )
                );


                if (!in_array($extension, $allowed_extensions, true)) {

                    $error_message =
                        "Invalid image type. Use JPG, JPEG, PNG or WEBP.";

                } elseif ($file_size > 5 * 1024 * 1024) {

                    $error_message =
                        "Image size must not exceed 5MB.";

                } else {


                    // =========================================
                    // CREATE UPLOAD DIRECTORY
                    // =========================================

                    $upload_directory = "../uploads/blog/";

                    if (!is_dir($upload_directory)) {

                        mkdir(
                            $upload_directory,
                            0777,
                            true
                        );
                    }


                    // =========================================
                    // CREATE UNIQUE FILE NAME
                    // =========================================

                    $new_file_name =
                        uniqid("blog_", true)
                        . "."
                        . $extension;

                    $destination =
                        $upload_directory
                        . $new_file_name;


                    // =========================================
                    // MOVE IMAGE
                    // =========================================

                    if (
                        move_uploaded_file(
                            $file_tmp,
                            $destination
                        )
                    ) {

                        $new_image_path =
                            "uploads/blog/"
                            . $new_file_name;


                        // Delete old image when editing
                        if (
                            $blog_id > 0 &&
                            !empty($featured_image)
                        ) {

                            $old_image =
                                "../"
                                . $featured_image;

                            if (file_exists($old_image)) {
                                unlink($old_image);
                            }
                        }

                    } else {

                        $error_message =
                            "Failed to upload the featured image.";
                    }
                }

            } else {

                $error_message =
                    "There was a problem uploading the image.";
            }
        }


        // =========================================
        // SAVE BLOG POST
        // =========================================

        if (empty($error_message)) {


            // =========================================
            // UPDATE EXISTING POST
            // =========================================

            if ($blog_id > 0) {

                $stmt = $conn->prepare("
                    UPDATE blogs
                    SET
                        title = ?,
                        slug = ?,
                        excerpt = ?,
                        content = ?,
                        featured_image = ?,
                        category = ?,
                        status = ?
                    WHERE id = ?
                ");

                $stmt->bind_param(
                    "sssssssi",
                    $title,
                    $slug,
                    $excerpt,
                    $content,
                    $new_image_path,
                    $category,
                    $status,
                    $blog_id
                );

                if ($stmt->execute()) {

                    $success_message =
                        "Blog post updated successfully.";

                    $featured_image =
                        $new_image_path;

                } else {

                    $error_message =
                        "Failed to update the blog post.";
                }

                $stmt->close();

            }


            // =========================================
            // CREATE NEW POST
            // =========================================

            else {

                $admin_id =
                    $_SESSION["admin_id"];

                $stmt = $conn->prepare("
                    INSERT INTO blogs (
                        admin_id,
                        title,
                        slug,
                        excerpt,
                        content,
                        featured_image,
                        category,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $stmt->bind_param(
                    "isssssss",
                    $admin_id,
                    $title,
                    $slug,
                    $excerpt,
                    $content,
                    $new_image_path,
                    $category,
                    $status
                );

                if ($stmt->execute()) {

                    $success_message =
                        "Blog post created successfully.";

                    $blog_id =
                        $stmt->insert_id;

                    $featured_image =
                        $new_image_path;

                } else {

                    $error_message =
                        "Failed to create the blog post.";
                }

                $stmt->close();
            }
        }
    }
}


require_once "header.php";

?>

<div class="admin-content">

    <!-- =========================================
         PAGE HEADER
         ========================================= -->

    <div class="page-header">

        <div>

            <h2>

                <?php

                if ($blog_id > 0) {
                    echo "Edit Blog Post";
                } else {
                    echo "Create Blog Post";
                }

                ?>

            </h2>

            <p>
                Write and manage articles for the CNC website.
            </p>

        </div>


        <div>

            <a
                href="blog.php"
                class="admin-button secondary-button"
            >
                ← Back to Blog
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


    <!-- =========================================
         BLOG FORM
         ========================================= -->

    <div class="admin-card">

        <form
            method="POST"
            enctype="multipart/form-data"
            class="admin-form"
        >

            <input
                type="hidden"
                name="blog_id"
                value="<?php echo $blog_id; ?>"
            >


            <!-- TITLE -->

            <div class="form-group">

                <label for="title">
                    Blog Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="<?php echo htmlspecialchars($title); ?>"
                    placeholder="Enter blog title"
                    required
                >

            </div>


            <!-- SLUG -->

            <div class="form-group">

                <label for="slug">
                    Slug
                </label>

                <input
                    type="text"
                    id="slug"
                    name="slug"
                    value="<?php echo htmlspecialchars($slug); ?>"
                    placeholder="example-cnc-machining-guide"
                >

                <small>
                    Used as the blog post URL. Leave empty to generate it automatically.
                </small>

            </div>


            <!-- CATEGORY -->

            <div class="form-group">

                <label for="category">
                    Category
                </label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    value="<?php echo htmlspecialchars($category); ?>"
                    placeholder="CNC Machining"
                >

            </div>


            <!-- EXCERPT -->

            <div class="form-group">

                <label for="excerpt">
                    Short Excerpt
                </label>

                <textarea
                    id="excerpt"
                    name="excerpt"
                    rows="4"
                    placeholder="Write a short summary of the blog post..."
                ><?php echo htmlspecialchars($excerpt); ?></textarea>

                <small>
                    A short description that can be displayed on the blog listing page.
                </small>

            </div>


            <!-- CONTENT -->

            <div class="form-group">

                <label for="content">
                    Blog Content
                </label>

                <textarea
                    id="content"
                    name="content"
                    rows="15"
                    placeholder="Write your blog post here..."
                    required
                ><?php echo htmlspecialchars($content); ?></textarea>

            </div>


            <!-- FEATURED IMAGE -->

            <div class="form-group">

                <label for="featured_image">
                    Featured Image
                </label>

                <input
                    type="file"
                    id="featured_image"
                    name="featured_image"
                    accept=".jpg,.jpeg,.png,.webp"
                >

                <small>
                    JPG, JPEG, PNG or WEBP. Maximum size: 5MB.
                </small>


                <?php if (!empty($featured_image)): ?>

                    <div class="current-image">

                        <p>
                            Current Featured Image:
                        </p>

                        <img
                            src="../<?php echo htmlspecialchars($featured_image); ?>"
                            alt="Current featured image"
                            class="blog-preview-image"
                        >

                    </div>

                <?php endif; ?>

            </div>


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
                        value="Draft"
                        <?php
                        if ($status === "Draft") {
                            echo "selected";
                        }
                        ?>
                    >
                        Draft
                    </option>

                    <option
                        value="Published"
                        <?php
                        if ($status === "Published") {
                            echo "selected";
                        }
                        ?>
                    >
                        Published
                    </option>

                </select>

            </div>


            <!-- FORM BUTTONS -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >

                    <?php

                    if ($blog_id > 0) {
                        echo "Update Blog Post";
                    } else {
                        echo "Save Blog Post";
                    }

                    ?>

                </button>


                <a
                    href="blog.php"
                    class="admin-button secondary-button"
                >
                    Cancel
                </a>

            </div>

        </form>

    </div>

</div>


<?php

require_once "footer.php";

?>
<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Training Materials | CNC Admin";
$page_heading = "Training Materials";

require_once "header.php";


// ==========================================
// UPLOAD DIRECTORY
// ==========================================

$upload_directory = "../uploads/training/";

if (!is_dir($upload_directory)) {
    mkdir($upload_directory, 0777, true);
}


// ==========================================
// DELETE MATERIAL
// ==========================================

if (isset($_GET["delete"])) {

    $material_id = (int) $_GET["delete"];

    // Get the file path before deleting the database record
    $stmt = $conn->prepare(
        "SELECT file_path FROM training_materials WHERE id = ?"
    );

    $stmt->bind_param("i", $material_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $material = $result->fetch_assoc();

    $stmt->close();


    // Delete database record
    $stmt = $conn->prepare(
        "DELETE FROM training_materials WHERE id = ?"
    );

    $stmt->bind_param("i", $material_id);

    if ($stmt->execute()) {

        // Delete physical file if it exists
        if (
            $material &&
            !empty($material["file_path"]) &&
            file_exists("../" . $material["file_path"])
        ) {
            unlink("../" . $material["file_path"]);
        }

        $message = "Training material deleted successfully.";
        $message_type = "success";

    } else {

        $message = "Unable to delete the training material.";
        $message_type = "error";
    }

    $stmt->close();
}


// ==========================================
// ADD TRAINING MATERIAL
// ==========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["add_material"])
) {

    $course_id = (int) ($_POST["course_id"] ?? 0);
    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $material_type = $_POST["material_type"] ?? "Other";


    if (
        empty($course_id) ||
        empty($title) ||
        empty($_FILES["material_file"]["name"])
    ) {

        $message = "Please select a course, enter a title and upload a file.";
        $message_type = "error";

    } else {

        $file = $_FILES["material_file"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $message = "There was a problem uploading the file.";
            $message_type = "error";

        } else {

            $original_name = $file["name"];
            $temporary_name = $file["tmp_name"];

            $file_extension = strtolower(
                pathinfo($original_name, PATHINFO_EXTENSION)
            );


            // Allowed file types
            $allowed_extensions = [
                "pdf",
                "mp4",
                "avi",
                "mov",
                "doc",
                "docx",
                "xls",
                "xlsx",
                "ppt",
                "pptx",
                "jpg",
                "jpeg",
                "png",
                "gif"
            ];


            if (!in_array($file_extension, $allowed_extensions)) {

                $message = "This file type is not allowed.";
                $message_type = "error";

            } else {

                // Create a unique file name
                $new_file_name =
                    time() . "_" .
                    uniqid() . "." .
                    $file_extension;

                $destination =
                    $upload_directory . $new_file_name;


                if (move_uploaded_file(
                    $temporary_name,
                    $destination
                )) {

                    // Store relative path in database
                    $database_path =
                        "uploads/training/" .
                        $new_file_name;


                    $stmt = $conn->prepare(
                        "INSERT INTO training_materials
                        (
                            course_id,
                            title,
                            description,
                            material_type,
                            file_path
                        )
                        VALUES (?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        "issss",
                        $course_id,
                        $title,
                        $description,
                        $material_type,
                        $database_path
                    );


                    if ($stmt->execute()) {

                        $message =
                            "Training material uploaded successfully.";

                        $message_type = "success";

                    } else {

                        // Remove uploaded file if database insertion fails
                        if (file_exists($destination)) {
                            unlink($destination);
                        }

                        $message =
                            "The file was uploaded but could not be saved to the database.";

                        $message_type = "error";
                    }

                    $stmt->close();

                } else {

                    $message =
                        "Unable to save the uploaded file.";

                    $message_type = "error";
                }
            }
        }
    }
}


// ==========================================
// GET COURSES
// ==========================================

$courses = [];

$result = $conn->query(
    "SELECT id, title
     FROM courses
     ORDER BY title ASC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }

}


// ==========================================
// GET TRAINING MATERIALS
// ==========================================

$materials = [];

$result = $conn->query(
    "SELECT
        training_materials.*,
        courses.title AS course_title
     FROM training_materials
     INNER JOIN courses
        ON training_materials.course_id = courses.id
     ORDER BY training_materials.uploaded_at DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $materials[] = $row;
    }

}

?>


<div class="page-header">

    <div>

        <p class="page-label">
            TRAINING MANAGEMENT
        </p>

        <h2>Training Materials</h2>

        <p>
            Upload and manage learning materials for CNC training courses.
        </p>

    </div>

</div>


<?php if (isset($message)): ?>

    <div class="admin-message <?php echo $message_type; ?>">

        <?php echo htmlspecialchars($message); ?>

    </div>

<?php endif; ?>


<!-- ==========================================
     UPLOAD MATERIAL
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Upload Training Material</h3>

            <p>
                Add a learning resource to one of the available courses.
            </p>

        </div>

    </div>


    <?php if (empty($courses)): ?>

        <div class="empty-state">

            <h3>No Courses Available</h3>

            <p>
                You need to create a training course before uploading
                training materials.
            </p>

            <a href="training.php" class="admin-button">
                Create Course
            </a>

        </div>

    <?php else: ?>


        <form
            method="POST"
            enctype="multipart/form-data"
            class="admin-form"
        >


            <div class="form-row">


                <div class="form-group">

                    <label for="course_id">
                        Course
                    </label>

                    <select
                        id="course_id"
                        name="course_id"
                        required
                    >

                        <option value="">
                            Select Course
                        </option>

                        <?php foreach ($courses as $course): ?>

                            <option
                                value="<?php echo $course["id"]; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $course["title"]
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="form-group">

                    <label for="material_type">
                        Material Type
                    </label>

                    <select
                        id="material_type"
                        name="material_type"
                        required
                    >

                        <option value="PDF">
                            PDF
                        </option>

                        <option value="Video">
                            Video
                        </option>

                        <option value="Document">
                            Document
                        </option>

                        <option value="Image">
                            Image
                        </option>

                        <option value="Other">
                            Other
                        </option>

                    </select>

                </div>

            </div>


            <div class="form-group">

                <label for="title">
                    Material Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="e.g. CNC Safety Guide"
                    required
                >

            </div>


            <div class="form-group">

                <label for="description">
                    Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    placeholder="Briefly describe this material..."
                ></textarea>

            </div>


            <div class="form-group">

                <label for="material_file">
                    Upload File
                </label>

                <input
                    type="file"
                    id="material_file"
                    name="material_file"
                    required
                >

                <small>
                    Allowed: PDF, videos, documents and images.
                </small>

            </div>


            <button
                type="submit"
                name="add_material"
                class="admin-button"
            >
                Upload Material
            </button>


        </form>

    <?php endif; ?>

</section>



<!-- ==========================================
     EXISTING MATERIALS
========================================== -->

<section class="admin-section">

    <div class="section-heading">

        <div>

            <h3>Existing Materials</h3>

            <p>
                Training resources currently stored in the system.
            </p>

        </div>

        <div class="section-count">

            <?php echo count($materials); ?> Materials

        </div>

    </div>


    <?php if (empty($materials)): ?>

        <div class="empty-state">

            <h3>No Training Materials Yet</h3>

            <p>
                Uploaded materials will appear here.
            </p>

        </div>

    <?php else: ?>


        <div class="admin-table-wrapper">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Material</th>

                        <th>Course</th>

                        <th>Type</th>

                        <th>Uploaded</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($materials as $material): ?>

                        <tr>

                            <td>
                                <?php echo $material["id"]; ?>
                            </td>


                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $material["title"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $material["course_title"]
                                );
                                ?>

                            </td>


                            <td>

                                <span class="level-badge">

                                    <?php
                                    echo htmlspecialchars(
                                        $material["material_type"]
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $material["uploaded_at"]
                                    )
                                );
                                ?>

                            </td>


                            <td>

                                <a
                                    href="../<?php
                                    echo htmlspecialchars(
                                        $material["file_path"]
                                    );
                                    ?>"
                                    target="_blank"
                                    class="view-link"
                                >
                                    View
                                </a>


                                <a
                                    href="?delete=<?php
                                    echo $material["id"];
                                    ?>"
                                    class="delete-link"
                                    onclick="return confirm('Are you sure you want to delete this training material?');"
                                >
                                    Delete
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
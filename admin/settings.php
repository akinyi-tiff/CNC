<?php

require_once "auth.php";
require_once "db.php";

$page_title = "Settings";
$page_heading = "Settings";


// =========================================
// VARIABLES
// =========================================

$success_message = "";
$error_message = "";

$admin_id = $_SESSION["admin_id"];


// =========================================
// GET CURRENT ADMIN DETAILS
// =========================================

$stmt = $conn->prepare("
    SELECT
        name,
        email,
        role
    FROM admins
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $admin_id);
$stmt->execute();

$result = $stmt->get_result();

$admin = $result->fetch_assoc();

$stmt->close();


if (!$admin) {

    die("Administrator account not found.");

}


// =========================================
// UPDATE PROFILE
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_profile"])
) {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");


    if ($name === "") {

        $error_message = "Please enter your name.";

    } elseif ($email === "") {

        $error_message = "Please enter your email address.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error_message = "Please enter a valid email address.";

    } else {


        // =========================================
        // CHECK IF EMAIL IS ALREADY USED
        // =========================================

        $stmt = $conn->prepare("
            SELECT id
            FROM admins
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "si",
            $email,
            $admin_id
        );

        $stmt->execute();

        $email_result = $stmt->get_result();

        if ($email_result->num_rows > 0) {

            $error_message =
                "That email address is already being used by another administrator.";

        }

        $stmt->close();


        // =========================================
        // UPDATE PROFILE
        // =========================================

        if (empty($error_message)) {

            $stmt = $conn->prepare("
                UPDATE admins
                SET
                    name = ?,
                    email = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "ssi",
                $name,
                $email,
                $admin_id
            );


            if ($stmt->execute()) {

                $success_message =
                    "Profile updated successfully.";

                // Update session information
                $_SESSION["admin_name"] = $name;
                $_SESSION["admin_email"] = $email;

                // Update local admin information
                $admin["name"] = $name;
                $admin["email"] = $email;

            } else {

                $error_message =
                    "Failed to update your profile.";
            }

            $stmt->close();
        }
    }
}


// =========================================
// CHANGE PASSWORD
// =========================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["change_password"])
) {

    $current_password =
        $_POST["current_password"] ?? "";

    $new_password =
        $_POST["new_password"] ?? "";

    $confirm_password =
        $_POST["confirm_password"] ?? "";


    if ($current_password === "") {

        $error_message =
            "Please enter your current password.";

    } elseif ($new_password === "") {

        $error_message =
            "Please enter a new password.";

    } elseif (strlen($new_password) < 8) {

        $error_message =
            "Your new password must be at least 8 characters long.";

    } elseif ($new_password !== $confirm_password) {

        $error_message =
            "The new passwords do not match.";

    } else {


        // =========================================
        // GET CURRENT PASSWORD
        // =========================================

        $stmt = $conn->prepare("
            SELECT password
            FROM admins
            WHERE id = ?
            LIMIT 1
        ");

        $stmt->bind_param(
            "i",
            $admin_id
        );

        $stmt->execute();

        $password_result =
            $stmt->get_result();

        $password_data =
            $password_result->fetch_assoc();

        $stmt->close();


        if (
            !$password_data ||
            !password_verify(
                $current_password,
                $password_data["password"]
            )
        ) {

            $error_message =
                "Your current password is incorrect.";

        } else {


            // =========================================
            // HASH NEW PASSWORD
            // =========================================

            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            // =========================================
            // UPDATE PASSWORD
            // =========================================

            $stmt = $conn->prepare("
                UPDATE admins
                SET password = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "si",
                $hashed_password,
                $admin_id
            );


            if ($stmt->execute()) {

                $success_message =
                    "Password changed successfully.";

            } else {

                $error_message =
                    "Failed to change your password.";
            }

            $stmt->close();
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

            <h2>Settings</h2>

            <p>
                Manage your administrator profile and password.
            </p>

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
         PROFILE SETTINGS
         ========================================= -->

    <div class="admin-card">

        <div class="card-header">

            <div>

                <h3>
                    Administrator Profile
                </h3>

                <p>
                    Update your name and email address.
                </p>

            </div>

        </div>


        <form
            method="POST"
            class="admin-form"
        >

            <input
                type="hidden"
                name="update_profile"
                value="1"
            >


            <!-- NAME -->

            <div class="form-group">

                <label for="name">
                    Name
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?php echo htmlspecialchars($admin["name"]); ?>"
                    required
                >

            </div>


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo htmlspecialchars($admin["email"]); ?>"
                    required
                >

            </div>


            <!-- ROLE -->

            <div class="form-group">

                <label>
                    Role
                </label>

                <input
                    type="text"
                    value="<?php echo htmlspecialchars($admin["role"]); ?>"
                    readonly
                >

                <small>
                    Your administrator role cannot be changed here.
                </small>

            </div>


            <!-- BUTTON -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >
                    Update Profile
                </button>

            </div>

        </form>

    </div>


    <!-- =========================================
         PASSWORD SETTINGS
         ========================================= -->

    <div class="admin-card">

        <div class="card-header">

            <div>

                <h3>
                    Change Password
                </h3>

                <p>
                    Update your administrator login password.
                </p>

            </div>

        </div>


        <form
            method="POST"
            class="admin-form"
        >

            <input
                type="hidden"
                name="change_password"
                value="1"
            >


            <!-- CURRENT PASSWORD -->

            <div class="form-group">

                <label for="current_password">
                    Current Password
                </label>

                <input
                    type="password"
                    id="current_password"
                    name="current_password"
                    required
                >

            </div>


            <!-- NEW PASSWORD -->

            <div class="form-group">

                <label for="new_password">
                    New Password
                </label>

                <input
                    type="password"
                    id="new_password"
                    name="new_password"
                    minlength="8"
                    required
                >

                <small>
                    Password must contain at least 8 characters.
                </small>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="form-group">

                <label for="confirm_password">
                    Confirm New Password
                </label>

                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    minlength="8"
                    required
                >

            </div>


            <!-- BUTTON -->

            <div class="form-actions">

                <button
                    type="submit"
                    class="admin-button"
                >
                    Change Password
                </button>

            </div>

        </form>

    </div>

</div>


<?php

require_once "footer.php";

?>
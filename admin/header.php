<?php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo isset($page_title) ? $page_title : "CNC Admin"; ?></title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="admin-layout">

        <aside class="admin-sidebar">

            <div class="admin-logo">
                <h2>CNC</h2>
            </div>

            <nav class="admin-nav">

                <div class="nav-menu nav-primary">
                    <button type="button" class="nav-menu-button" aria-expanded="false">
                        MAIN <span class="nav-arrow">&#9662;</span>
                    </button>

                    <div class="nav-dropdown">
                        <a href="dashboard.php">Dashboard</a>
                    </div>
                </div>

                <div class="nav-menu">
                    <button type="button" class="nav-menu-button" aria-expanded="false">
                        TRAINING <span class="nav-arrow">&#9662;</span>
                    </button>

                    <div class="nav-dropdown">
                        <a href="training.php">Courses</a>
                        <a href="training-materials.php">Training Materials</a>
                        <a href="training-students.php">Students</a>
                    </div>
                </div>

                <div class="nav-menu">
                    <button type="button" class="nav-menu-button" aria-expanded="false">
                        REQUESTS <span class="nav-arrow">&#9662;</span>
                    </button>

                    <div class="nav-dropdown">
                        <a href="inquiries.php">Inquiries</a>
                        <a href="quote-requests.php">Quote Requests</a>
                        <a href="project-submissions.php">Project Submissions</a>
                    </div>
                </div>

                <div class="nav-menu">
                    <button type="button" class="nav-menu-button" aria-expanded="false">
                        CONTENT <span class="nav-arrow">&#9662;</span>
                    </button>

                    <div class="nav-dropdown">
                        <a href="blog.php">Blog</a>
                        <a href="user-submissions.php">User Submissions</a>
                    </div>
                </div>

            </nav>

            <div class="admin-utility">
                <a href="settings.php">Settings</a>
                <a href="logout.php">Logout</a>
            </div>

        </aside>


        <div class="admin-main">

            <header class="admin-topbar">

                <div>
                    <h1>
                        <?php echo isset($page_heading) ? $page_heading : "Dashboard"; ?>
                    </h1>
                </div>

                <div class="admin-user">
                    <span class="admin-user-label">Administrator</span>
                    <span class="admin-avatar" aria-label="Signed-in administrator">
                        <?php
                        $admin_name = trim($_SESSION["admin_name"] ?? "Administrator");
                        $admin_letters = preg_replace("/[^a-zA-Z]/", "", $admin_name);
                        echo htmlspecialchars(strtoupper(substr($admin_letters, 0, 2)));
                        ?>
                    </span>
                </div>

            </header>

            <main class="admin-content">

            <script>
                document.querySelectorAll(".nav-menu-button").forEach(function (button) {
                    button.addEventListener("click", function () {
                        var menu = button.parentElement;
                        var isOpen = menu.classList.contains("is-open");

                        document.querySelectorAll(".nav-menu.is-open").forEach(function (openMenu) {
                            openMenu.classList.remove("is-open");
                            openMenu.querySelector(".nav-menu-button").setAttribute("aria-expanded", "false");
                        });

                        if (!isOpen) {
                            menu.classList.add("is-open");
                            button.setAttribute("aria-expanded", "true");
                        }
                    });
                });

                document.addEventListener("click", function (event) {
                    if (!event.target.closest(".nav-menu")) {
                        document.querySelectorAll(".nav-menu.is-open").forEach(function (openMenu) {
                            openMenu.classList.remove("is-open");
                            openMenu.querySelector(".nav-menu-button").setAttribute("aria-expanded", "false");
                        });
                    }
                });

                document.addEventListener("keydown", function (event) {
                    if (event.key === "Escape") {
                        document.querySelectorAll(".nav-menu.is-open").forEach(function (openMenu) {
                            openMenu.classList.remove("is-open");
                            openMenu.querySelector(".nav-menu-button").setAttribute("aria-expanded", "false");
                        });
                    }
                });
            </script>
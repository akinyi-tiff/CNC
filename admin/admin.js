document.addEventListener("DOMContentLoaded", function () {

    // =========================================
    // DELETE CONFIRMATION
    // =========================================

    const deleteLinks = document.querySelectorAll(".delete-link");

    deleteLinks.forEach(function (link) {

        link.addEventListener("click", function (event) {

            const confirmed = confirm(
                "Are you sure you want to delete this item?"
            );

            if (!confirmed) {
                event.preventDefault();
            }

        });

    });


    // =========================================
    // PASSWORD VISIBILITY
    // =========================================

    const passwordInputs = document.querySelectorAll(
        'input[type="password"]'
    );

    passwordInputs.forEach(function (input) {

        const wrapper = input.parentElement;

        const toggleButton = document.createElement("button");

        toggleButton.type = "button";
        toggleButton.className = "password-toggle";
        toggleButton.textContent = "Show";

        wrapper.appendChild(toggleButton);


        toggleButton.addEventListener("click", function () {

            if (input.type === "password") {

                input.type = "text";
                toggleButton.textContent = "Hide";

            } else {

                input.type = "password";
                toggleButton.textContent = "Show";

            }

        });

    });


    // =========================================
    // AUTO-HIDE ALERT MESSAGES
    // =========================================

    const alerts = document.querySelectorAll(".alert");

    alerts.forEach(function (alert) {

        setTimeout(function () {

            alert.style.opacity = "0";

            setTimeout(function () {
                alert.remove();
            }, 500);

        }, 4000);

    });


    // =========================================
    // MOBILE SIDEBAR TOGGLE
    // =========================================

    const menuButton =
        document.querySelector(".menu-toggle");

    const sidebar =
        document.querySelector(".admin-sidebar");

    if (menuButton && sidebar) {

        menuButton.addEventListener("click", function () {

            sidebar.classList.toggle("sidebar-open");

        });

    }


    // =========================================
    // CLOSE SIDEBAR WHEN LINK IS CLICKED
    // =========================================

    const sidebarLinks =
        document.querySelectorAll(".admin-sidebar a");

    sidebarLinks.forEach(function (link) {

        link.addEventListener("click", function () {

            if (sidebar) {
                sidebar.classList.remove("sidebar-open");
            }

        });

    });


    // =========================================
    // CONFIRM FORM SUBMISSIONS
    // =========================================

    const confirmForms =
        document.querySelectorAll("[data-confirm]");

    confirmForms.forEach(function (form) {

        form.addEventListener("submit", function (event) {

            const message =
                form.getAttribute("data-confirm");

            if (!confirm(message)) {
                event.preventDefault();
            }

        });

    });

});
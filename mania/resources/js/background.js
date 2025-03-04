document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const overlay = document.getElementById("background-dark-overlay");

    function updateBackground(route) {
        // Reset styles
        body.style.backgroundImage = "";
        body.style.backgroundColor = "";
        overlay.style.display = "none"; // Hide overlay by default

        // Apply backgrounds based on route
        switch (route) {
            case "/":
                body.style.backgroundImage = "url('/img/Coder_RoltonsLV.png')";
                break;

            case "/about":
                body.style.backgroundImage = "url('/img/Hostage_Adventure.png')";
                overlay.style.display = "block"; // Show dark overlay
                break;

            case "/contacts":
                body.style.backgroundImage = "url('/img/Hostage_Adventure.png')";
                overlay.style.display = "block"; // Show dark overlay
                break;

            case "/shop":
                body.style.backgroundColor = "rgb(255, 255, 255)";
                break;

            default:
                body.style.backgroundImage = "";
        }
    }

    // Detect initial page load
    updateBackground(window.location.pathname);

    // Listen for page changes in a Laravel Inertia.js app (if applicable)
    if (window.Inertia) {
        document.addEventListener("inertia:navigate", (event) => {
            updateBackground(event.detail.page.url);
        });
    }
});

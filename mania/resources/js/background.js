export default function updateBackground(route) {
    const body = document.body;
    const overlay = document.getElementById("background-dark-overlay");

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
        case "/contacts":
        case "/login":
        case "/register":
            body.style.backgroundImage = "url('/img/Hostage_Adventure.png')";
            overlay.style.display = "block"; // Show dark overlay
            break;

        case "/shop":
            // body.style.backgroundColor = "rgb(255, 255, 255)";
            body.style.backgroundImage = "url('/img/Shop1.jpg')";
            overlay.style.display = "block"; // Show dark overlay
            break;

        default:
            body.style.backgroundImage = "";
    }
}

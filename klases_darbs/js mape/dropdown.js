document.querySelectorAll('.dropdown-content a').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent default anchor behavior

        // Get the target element by its ID
        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);

        if (targetElement) {
            // Smooth scroll to the element
            targetElement.scrollIntoView({
                behavior: 'smooth'
            });

            // Apply highlight color first
            targetElement.style.backgroundColor = 'yellow';

            // Apply a delay to allow the highlight color to be visible
            setTimeout(() => {
                // Transition back to original background
                targetElement.style.transition = 'background-color 0.5s ease';
                targetElement.style.backgroundColor = ''; // Reset to original background
            }, 1500);  // Highlight stays for 1.5 seconds
        }
    });
});
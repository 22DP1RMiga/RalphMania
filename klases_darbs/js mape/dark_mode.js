const toggleButton = document.getElementById('dark-mode-toggle');
const body = document.body;

// Load dark mode preference from local storage if exists
const isDarkMode = localStorage.getItem('darkMode') === 'true';
if (isDarkMode) {
    body.classList.add('dark-mode');
    toggleButton.textContent = 'Light Mode';
}

toggleButton.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    
    // Store the user's preference for dark mode in local storage
    const darkModeEnabled = body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', darkModeEnabled);

    if (darkModeEnabled) {
        toggleButton.textContent = 'Light Mode'; // Change to "Light Mode" when dark mode is active
    } else {
        toggleButton.textContent = 'Dark Mode';  // Change to "Dark Mode" when dark mode is off
    }
});
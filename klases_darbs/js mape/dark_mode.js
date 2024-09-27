const toggleButton = document.getElementById('dark-mode-toggle');
const body = document.body;

// Load dark mode preference from local storage if exists
const isDarkMode = localStorage.getItem('darkMode') === 'true';
if (isDarkMode) {
    body.classList.add('dark-mode');
}

toggleButton.addEventListener('click', () => {
    body.classList.toggle('dark-mode');
    
    // Store the user's preference for dark mode in local storage
    const darkModeEnabled = body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', darkModeEnabled);
});
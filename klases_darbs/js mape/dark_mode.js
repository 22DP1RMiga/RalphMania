const toggleButton = document.getElementById('dark-mode-toggle');
const body = document.body;

// Load dark mode preference from local storage if exists
const isDarkMode = localStorage.getItem('darkMode') === 'true';
const selectedLang = document.getElementById('language-dropdown').value;

function setDarkModeText(lang) {
    const text = isDarkMode ? translations[lang].lightmode : translations[lang].darkmode;
    toggleButton.textContent = text;
}

// Initial setup for dark mode
if (isDarkMode) {
    body.classList.add('dark-mode');
    toggleButton.textContent = 'Light Mode';
    setDarkModeText(selectedLang);
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
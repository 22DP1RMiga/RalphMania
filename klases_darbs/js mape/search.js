document.getElementById('search-button').addEventListener('click', function() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const buttons = document.querySelectorAll('.flex-container a button');
    const messageDiv = document.getElementById('message');
    let found = false;

    // Clear previous message and highlighting
    messageDiv.style.display = 'none';
    buttons.forEach(button => {
        button.style.backgroundColor = ''; // Reset background
    });

    buttons.forEach(button => {
        const foodName = button.querySelector('div > div').textContent.toLowerCase();
        if (foodName.includes(searchTerm)) {
            button.style.backgroundColor = 'yellow'; // Highlight button
            found = true;
        }
    });

    if (!found) {
        messageDiv.textContent = 'Nothing here, sorry';
        messageDiv.style.display = 'block'; // Show message
    }
});

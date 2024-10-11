document.getElementById('search-button').addEventListener('click', function() {
    const normalizeString = (str) => {
        return str
            .replace(/ā/gi, 'a') // Replace "ā" and "Ā" with "a"
            .replace(/ē/gi, 'e') // Replace "ē" and "Ē" with "e" (add more as needed)
            .replace(/ī/gi, 'i') // Replace "ī" and "Ī" with "i"
            .replace(/ū/gi, 'u') // Replace "ū" and "Ū" with "u"
            .replace(/č/gi, 'c') // Replace "č" and "Č" with "c"
            .replace(/ģ/gi, 'g') // Replace "ģ" and "Ģ" with "g"
            .replace(/ķ/gi, 'k') // Replace "ķ" and "Ķ" with "k"
            .replace(/ļ/gi, 'l') // Replace "ļ" and "Ļ" with "l"
            .replace(/ņ/gi, 'n') // Replace "ņ" and "Ņ" with "n"
            .replace(/š/gi, 's') // Replace "š" and "Š" with "s"
            .replace(/ž/gi, 'z'); // Replace "ž" and "Ž" with "z"
    };
    
    const searchTerm = normalizeString(document.getElementById('search-input').value.toLowerCase());
    const buttons = document.querySelectorAll('.flex-container a button');
    const messageDiv = document.getElementById('message');
    let found = false;

    // Clear previous message and highlighting
    messageDiv.style.display = 'none';
    buttons.forEach(button => {
        button.style.backgroundColor = ''; // Reset background
    });

    if (searchTerm.trim() === '') {
        // If the search term is empty, clear the message and return
        messageDiv.textContent = 'Please enter something to search!';
        messageDiv.style.display = 'block'; // Show message
        return; // Exit the function early
    }

    buttons.forEach(button => {
        const foodName = normalizeString(button.querySelector('div > div').textContent.toLowerCase());
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

// Function to load Font Awesome dynamically
function loadFontAwesome() {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css';
    document.head.appendChild(link);
}

// Load Font Awesome on script load
loadFontAwesome();

// All available translations
const translations = {
    en: {
        home: "Home",
        about: "About",
        contacts: "Contacts",
        menu: "Hello! Here is your uhh.. lovely MENU!",
        chooseMeal: "Choose your meal for the day!",
        searchbar: "Search here...",
        searchbutton: "Go",
        originTitle: "ORIGIN",
        originText: "This website was made for a task purpose to make at least 3 tickets. There are 4 card-shaped tickets that will lead to YouTube videos about them.",
        howToTitle: "HOW TO USE THE WEBSITE",
        howToText: "If you want to search for something, search 'Apple' for instance to see it being highlighted. Switch to Dark Mode if you wish to have a darker screen and style."
        // Add more translations for other texts
    },
    lv: {
        home: "Mājas",
        about: "Par",
        contacts: "Kontakti",
        menu: "Sveiki! Šeit ir jūsu... jaukais MENU!",
        chooseMeal: "Izvēlieties savu ēdienu šodien!",
        searchbar: "Meklē šeit...",
        searchbutton: "Meklēt",
        originTitle: "IZCELSME",
        originText: "Šī mājaslapa tika izveidota uzdevuma mērķiem, lai izveidotu vismaz 3 biļetes. Ir 4 kartīšu formas biļetes, kas novadīs uz YouTube videoklipiem par tām.",
        howToTitle: "KĀ LIETOT MĀJASLAPU",
        howToText: "Ja vēlaties meklēt kaut ko, meklējiet 'Apple', piemēram, lai to izceltu. Pārejiet uz tumšo režīmu, ja vēlaties, lai ekrāns un stils būtu tumšāks."
        // Add more translations for Latvian
    },
    ru: {
        home: "Дом",
        about: "Про",
        contacts: "Контакты",
        menu: "Привет! Вот ваше ммм.. красивое меню!",
        chooseMeal: "Выберите еду на день!",
        searchbar: "Искать здесь...",
        searchbutton: "Давай",
        originTitle: "ИСТОЧНИК",
        originText: "Этот сайт был создан с целью сделать как минимум 3 билета. Есть 4 билета в форме карточек, которые ведут к видеороликам о них на YouTube.",
        howToTitle: "КАК ПsОЛЬЗОВАТЬСЯ ВЕБ-САЙТОМ",
        howToText: "Если вы хотите что-то найти, введите, например, «Apple», чтобы увидеть, что оно выделено. Переключитесь в темный режим, если вы хотите иметь более темный экран и стиль."
        // Add more translations for Russian
    },
    de: {
        home: "Heim",
        about: "Um",
        contacts: "Kontakte",
        menu: "Hallo! Hier ist Ihr emm.. Liebes Menü!",
        chooseMeal: "Wählen Sie Ihr Essen für den Tag!",
        searchbar: "Suchen Sie hier...",
        searchbutton: "Gehen",
        originTitle: "HERKUNFT",
        originText: "Diese Website wurde mit dem Ziel erstellt, mindestens 3 Tickets zu erstellen. Es gibt 4 kartenförmige Tickets, die zu YouTube-Videos darüber führen.",
        howToTitle: "SO BENUTZEN SIE DIE WEBSITE",
        howToText: "Wenn Sie nach etwas suchen möchten, suchen Sie beispielsweise nach „Apple“, um zu sehen, dass es hervorgehoben wird. Wechseln Sie in den Dunkelmodus, wenn Sie einen dunkleren Bildschirm und Stil wünschen."
        // Add more translations for German
    }
};

// Translation function
function updateLanguage(lang) {
    document.querySelectorAll('.translate').forEach((element) => {
        const key = element.parentElement.getAttribute('data-translate');
        if (translations[lang][key]) {
            element.textContent = translations[lang][key];
        }
    });

    // Update other texts
    document.querySelector('#origin-section h1').textContent = translations[lang].originTitle;
    document.querySelector('#origin-section p').textContent = translations[lang].originText;
    document.querySelector('#how-to-section h1').textContent = translations[lang].howToTitle;
    document.querySelector('#how-to-section p').textContent = translations[lang].howToText;
    document.querySelector('#search-input').textContent = translations[lang].searchbar;
    document.querySelector('#search-button').textContent = translations[lang].searchbutton + ' <i class="fa-solid fa-magnifying-glass"></i>';
}

// Dropdown functionality for languages
document.getElementById('language-dropdown').addEventListener('change', (event) => {
    updateLanguage(event.target.value);
});


// Default language
window.onload = () => {
    updateLanguage('en'); // Default language
};

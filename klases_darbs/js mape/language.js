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
        originText: "This website was made for a task purpose to make at least 3 tickets.\n\nThere are 4 card-shaped tickets that will lead to YouTube videos about them.",
        howToTitle: "HOW TO USE THE WEBSITE",
        howToText: "If you want to search for something, search 'Apple' for instance to see it being highlighted.\n\nSwitch to Dark Mode if you wish to have a darker screen and style.",
        apple: "Apple",
        banana: "Banana",
        lychee: "Lychee",
        chickentenders: "Chicken tenders",
        appletext: "Red, just not the green Newton-discovered apple",
        bananatext: "Yellow, monkeys' favorite fruit",
        lycheetext: "A very juicy and normal fruit, kind of exotic",
        tendertext: "Mmmm.. chicken fingers",
        origin_drop: "Origin",
        howTo_drop: "How to use the website",
        darkmode: "Dark Mode",
        lightmode: "Light Mode",
        minigames_title: "MINIGAMES",
        minigames_description: "Introducing the newest Addon to the website!",
        minigames_text: "Well hello there! Here we have a set of games for you to try out!",
        cookietext: "Everytime you click the cookie, you higher up a number!",
        cookieclicker: "Cookie Clicker"
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
        originText: "Šī mājaslapa tika izveidota uzdevuma mērķiem, lai izveidotu vismaz 3 biļetes.\n\nIr 4 kartīšu formas biļetes, kas novadīs uz YouTube videoklipiem par tām.",
        howToTitle: "KĀ LIETOT MĀJASLAPU",
        howToText: "Ja vēlaties meklēt kaut ko, meklējiet 'Ābols', piemēram, lai to izceltu.\n\nPārejiet uz tumšo režīmu, ja vēlaties, lai ekrāns un stils būtu tumšāks.",
        apple: "Ābols",
        banana: "Banāns",
        lychee: "Līčijs",
        chickentenders: "Vistu strēmeles",
        appletext: "Sarkarns, tikai ne zaļš Ņūtona atklātais ābols",
        bananatext: "Dzeltens, pērtiķiem mīļākais auglis",
        lycheetext: "Ļoti sulīgs un normāls auglis, diezgan eksotisks",
        tendertext: "Mmmm.. vistu pirkstiņi",
        origin_drop: "Izcelsme",
        howTo_drop: "Kā lietot mājaslapu",
        darkmode: "Tumšais režīms",
        lightmode: "Gaišais režīms",
        minigames_title: "MINISPĒLES",
        minigames_description: "Iepazīstinām ar jaunāko papildinājumu mājaslapā!",
        minigames_text: "Nu sveiki! Šeit mums ir spēļu komplekts, ko varat izmēģināt!",
        cookietext: "Katru reizi, uzklišķinot uz cepuma, jūs paaugstināsiet skaitli!",
        cookieclicker: "Cepuma klikšķinātājs"
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
        originText: "Этот сайт был создан с целью сделать как минимум 3 билета.\n\nЕсть 4 билета в форме карточек, которые ведут к видеороликам о них на YouTube.",
        howToTitle: "КАК ПОЛЬЗОВАТЬСЯ ВЕБ-САЙТОМ",
        howToText: "Если вы хотите что-то найти, введите, например, «Яблоко», чтобы увидеть, что оно выделено.\n\nПереключитесь в темный режим, если вы хотите иметь более темный экран и стиль.",
        apple: "Яблоко",
        banana: "Банан",
        lychee: "Личи",
        chickentenders: "Куриные тендеры",
        appletext: "Красное, но не зеленое яблоко, открытое Ньютоном",
        bananatext: "Желтый, любимый фрукт обезьян",
        lycheetext: "Очень сочный и нормальный фрукт, какая-то экзотика",
        tendertext: "Мммм.. куриные палочки",
        origin_drop: "Источник",
        howTo_drop: "Как пользоваться веб-сайтом",
        darkmode: "Тёмный режим",
        lightmode: "Светлый режим",
        minigames_title: "МИНИ-ИГРЫ",
        minigames_description: "Представляем новейший аддон на сайте!",
        minigames_text: "Ну, здарова! У нас есть набор игр, которые вы можете попробовать!",
        cookietext: "Каждый раз, когда вы нажимаете на печенье, вы увеличиваете число!",
        cookieclicker: "Печенье-кликер"
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
        originText: "Diese Website wurde mit dem Ziel erstellt, mindestens 3 Tickets zu erstellen.\n\nEs gibt 4 kartenförmige Tickets, die zu YouTube-Videos darüber führen.",
        howToTitle: "SO BENUTZEN SIE DIE WEBSITE",
        howToText: "Wenn Sie nach etwas suchen möchten, suchen Sie beispielsweise nach „Apfel“, um zu sehen, dass es hervorgehoben wird.\n\nWechseln Sie in den Dunkelmodus, wenn Sie einen dunkleren Bildschirm und Stil wünschen.",
        apple: "Apfel",
        banana: "Banane",
        lychee: "Litschi",
        chickentenders: "Hähnchenfilets",
        appletext: "Rot, nur nicht der grüne, von Newton entdeckte Apfel",
        bananatext: "Gelb, die Lieblingsfrucht der Affen",
        lycheetext: "Eine sehr saftige und normale Frucht, irgendwie exotisch",
        tendertext: "Mmmm.. Hähnchenstäbchen",
        origin_drop: "Herkunft",
        howTo_drop: "So benutzen Sie die Website",
        darkmode: "Dunkler Modus",
        lightmode: "Lichtmodus",
        minigames_title: "MINISPIELEN",
        minigames_description: "Wir stellen das neueste Add-on auf der Website vor!",
        minigames_text: "Na, hallo! Hier haben wir eine Reihe von Spielen zum Ausprobieren für Sie!",
        cookietext: "Jedes Mal, wenn Sie auf der Keks klicken, erhöhen Sie eine Zahl!",
        cookieclicker: "Cookie-Clicker"
    }
};

// Translation function
function updateLanguage(lang) {
    document.querySelectorAll('.translate').forEach((element) => {
        const key = element.getAttribute('data-translate'); // Corrected attribute reference
        if (translations[lang][key]) {
            element.textContent = translations[lang][key];
            element.innerHTML = translations[lang][key].replace(/\n\n/g, '<br><br>');
        }
    });

    // Update input placeholders and button text separately
    document.querySelector('#search-input').placeholder = translations[lang].searchbar;
    document.querySelector('#search-button').innerHTML = translations[lang].searchbutton + ' <i class="fa-solid fa-magnifying-glass"></i>';
}

// Dropdown functionality for languages
document.getElementById('language-dropdown').addEventListener('change', (event) => {
    updateLanguage(event.target.value);
});

// Default language
window.onload = () => {
    updateLanguage('en'); // Default language
};

// resources/js/i18n.js
import { createI18n } from 'vue-i18n';

const messages = {
    lv: {
        nav: {
            home: 'Sākumlapa',
            about: 'Par Mums',
            contact: 'Kontakti',
            shop: 'Veikals',
            content: 'Saturs',
            dashboard: 'Panelis',
            profile: 'Profils',
        },
        auth: {
            login: 'Pieteikties',
            register: 'Reģistrēties',
            logout: 'Iziet',
            dashboard: 'Kontrolpanelis',
            username: 'Lietotājvārds',
            email: 'E-pasts',
            email_or_username: 'E-pasts vai lietotājvārds',
            email_or_username_placeholder: 'Ievadiet e-pastu vai lietotājvārdu',
            password: 'Parole',
            password_confirmation: 'Apstipriniet paroli',
            phone: 'Tālrunis (neobligāts)',
            birth_date: 'Dzimšanas datums (neobligāts)',
            remember_me: 'Atcerēties mani',
            forgot_password: 'Aizmirsi paroli?',
            already_registered: 'Jau reģistrēts?',
            no_account: 'Nav konta?',
            welcome_back: 'Laipni lūdzam atpakaļ!',
            login_description: 'Piesakieties, lai apmeklētu mūsu jauko iestādi!\n' +
                'Ja esat aizmirsis paroli, noklikšķiniet uz "Aizmirsāt paroli?"',
            logging_in: 'Piesakās..',
            join_us: 'Pievienojies mums!',
            register_description: 'Izveidojiet kontu un sāciet iepirkties!',
            confirm_password: 'Apstipriniet paroli',
            new_password: 'Jaunā parole',
            confirm_new_password: 'Apstiprināt jauno paroli',
            registering: 'Reģistrējas...',
            resetting: 'Atjauno...',
            confirming: 'Apstiprina...',
            create_account: 'Izveidot kontu',
            reset_password: 'Atjaunot paroli',
            reset_password_title: 'Paroles atjaunošana',
            reset_description: 'Nav problēmu! Mēs palīdzēsim atjaunot jūsu kontu',
            forgot_password_help: 'Aizmirsi paroli? Nav problēmu. Vienkārši norādiet savu e-pasta adresi, un mēs nosūtīsim jums paroles atiestatīšanas saiti.',
            send_reset_link: 'Nosūtīt atiestatīšanas saiti',
            back_to_login: 'Atpakaļ uz pieslēgšanos',
            new_password_title: 'Jauna parole',
            new_password_description: 'Ievadiet savu jauno paroli',
            secure_area: 'Drošā zona',
            confirm_password_description: 'Šī ir droša vieta. Lūdzu, apstipriniet savu paroli pirms turpināšanas.',
            confirm_password_help: 'Šī ir droša aplikācijas vieta. Lūdzu, apstipriniet savu paroli pirms turpināšanas.',
            confirm: 'Apstiprināt',
            verify_email: 'Verificēt e-pastu',
            verify_email_title: 'Verificējiet savu e-pastu',
            verify_email_description: 'Pārbaudiet savu e-pasta adresi, lai aktivizētu kontu',
            verify_email_help: 'Paldies, ka reģistrējāties! Pirms sākšanas, vai varētu verificēt savu e-pasta adresi, noklikšķinot uz saites, ko tikko nosūtījām jums?',
            verification_link_sent: 'Jauna verificēšanas saite ir nosūtīta uz jūsu e-pasta adresi.',
            resend_verification: 'Nosūtīt saiti vēlreiz',
        },
        dashboard: {
            welcome: 'Sveiki',
            subtitle: 'Pārvaldiet savu kontu un pasūtījumus',
            nav: {
                overview: 'Pārskats',
                profile: 'Profils',
                orders: 'Pasūtījumi',
                addresses: 'Adreses',
                reviews: 'Atsauksmes',
                settings: 'Iestatījumi',
                back_home: 'Atpakaļ uz sākumlapu',
            },
            stats: {
                orders: 'Pasūtījumi',
                reviews: 'Atsauksmes',
                favorites: 'Favorīti',
                cart: 'Grozā',
            },
            sections: {
                overview: {
                    title: 'Pārskats',
                    recent_orders: 'Jaunākie pasūtījumi',
                    quick_actions: 'Ātrās darbības',
                    no_orders: 'Jums vēl nav neviena pasūtījuma',
                },
                profile: {
                    title: 'Mans Profils',
                },
                orders: {
                    title: 'Mani Pasūtījumi',
                    no_orders: 'Jums vēl nav neviena pasūtījuma',
                },
                addresses: {
                    title: 'Manas Adreses',
                    no_addresses: 'Jums nav pievienota neviena adrese',
                },
                reviews: {
                    title: 'Manas Atsauksmes',
                    no_reviews: 'Jūs vēl neesat uzrakstījis nevienu atsauksmi',
                },
                settings: {
                    title: 'Iestatījumi',
                },
            },
            profile: {
                phone: 'Tālrunis',
                birth_date: 'Dzimšanas datums',
                member_since: 'Reģistrēts kopš',
                last_login: 'Pēdējā pieteikšanās',
            },
            actions: {
                shop: 'Iepirkties',
                cart: 'Grozs',
                edit_profile: 'Rediģēt profilu',
                change_password: 'Mainīt paroli',
                add_address: 'Pievienot adresi',
            },
            settings: {
                notifications: 'Paziņojumi',
                email_notifications: 'E-pasta paziņojumi',
                order_updates: 'Pasūtījumu atjauninājumi',
                privacy: 'Privātums',
                show_profile: 'Publisks profils',
            },
        },
        hero: {
            title: 'Laipni lūdzam RalphMania!',
            subtitle: 'Tavs galamērķis saturam un zīmola produktiem',
            cta_content: 'Skatīt Saturu',
            cta_shop: 'Apmeklēt Veikalu',
        },
        about: {
            hero: {
                title: 'Par Mums',
                subtitle: 'Iepazīstieties ar mūsu stāstu un vērtībām',
            },
            story: {
                title: 'Mūsu Stāsts',
                paragraph_1: 'RalphMania ir radīta ar mērķi apvienot saturu un produktus vienā platformā. Mēs ticam, ka kvalitāte un autentiskums ir vissvarīgākais.',
                paragraph_2: 'Mūsu ceļojums sākās ar vienkāršu ideju - radīt kopienu, kas dalās ar mūsu aizraušanos un vērtībām. Šodien esam lepni piedāvāt gan unikālu saturu, gan ekskluzīvus produktus.',
                paragraph_3: 'Katrs mūsu produkts ir rūpīgi izvēlēts un katrs satura gabals ir radīts ar mīlestību. Mēs vēlamies būt vairāk nekā tikai veikals - mēs vēlamies būt kopiena.',
            },
            mission: {
                title: 'Mūsu Misija',
                content: {
                    title: 'Kvalitatīvs Saturs',
                    text: 'Radām oriģinālu un iesaistošu saturu, kas iedvesmo un izklaidē mūsu kopienu.',
                },
                products: {
                    title: 'Unikāli Produkti',
                    text: 'Piedāvājam ekskluzīvus produktus, kas atspoguļo mūsu zīmolu un vērtības.',
                },
                community: {
                    title: 'Stipra Kopiena',
                    text: 'Veidojam kopienu, kur katra balss tiek dzirdēta un novērtēta.',
                },
            },
            values: {
                title: 'Mūsu Vērtības',
                quality: {
                    title: 'Kvalitāte',
                    text: 'Mēs nekad neapmierinamies ar viduvējību. Katrs produkts un satura gabals ir rūpīgi pārdomāts.',
                },
                authenticity: {
                    title: 'Autentiskums',
                    text: 'Mēs esam patiesi un atklāti visā, ko darām. Nav slēpto nolūku vai viltus solījumu.',
                },
                passion: {
                    title: 'Aizraušanās',
                    text: 'Mūsu darbs ir mūsu aizraušanās. Mēs mīlam to, ko darām, un tas ir redzams rezultātos.',
                },
                innovation: {
                    title: 'Inovācija',
                    text: 'Mēs vienmēr meklējam jaunus veidus, kā uzlabot un attīstīties kopā ar savu kopienu.',
                },
            },
            cta: {
                title: 'Pievienojies Mūsu Kopienai!',
                text: 'Atrodi savus iecienītākos produktus un saturu vienuviet',
                shop: 'Iepirkties',
                contact: 'Sazināties',
            },
        },
        contact: {
            hero: {
                title: 'Sazinies ar Mums',
                subtitle: 'Mēs labprāt atbildēsim uz visiem taviem jautājumiem',
            },
            form: {
                title: 'Sūti Mums Ziņu',
                subtitle: 'Aizpildi formu un mēs ar tevi sazināsimies tuvākajā laikā',
                name: 'Vārds',
                email: 'E-pasts',
                subject: 'Temats',
                message: 'Ziņojums',
                send: 'Nosūtīt',
                sending: 'Sūta...',
                success: 'Paldies! Jūsu ziņojums ir nosūtīts.',
            },
            info: {
                title: 'Kontaktinformācija',
                email: 'E-pasts',
                phone: 'Tālrunis',
                address: 'Adrese',
                social: 'Sociālie Tīkli',
            },
        },
        shop: {
            hero: {
                title: 'RalphMania Veikals',
                subtitle: 'Atklāj mūsu unikālo produktu kolekciju',
            },
            category: 'Kategorija',
            categories: 'Kategorijas',
            all_products: 'Visi Produkti',
            products_count: 'produkti',
            no_products: 'Šobrīd nav pieejamu produktu',
            no_products_in_category: 'Šajā kategorijā nav produktu',
            back_to_shop: 'Atpakaļ uz veikalu',
            sale: 'Atlaide',
            filter: {
                newest: 'Jaunākie',
                price_low: 'Cena: Zems → Augsts',
                price_high: 'Cena: Augsts → Zems',
                popular: 'Populārākie',
            },
        },
        content: {
            hero: {
                title: 'RalphMania Saturs',
                subtitle: 'Atklāj mūsu jaunāko video un rakstu kolekciju',
            },
            filter: {
                all: 'Viss Saturs',
                videos: 'Video',
                blogs: 'Raksti',
            },
            views: 'Skatījumi',
            latest: 'Jaunākais',
            title: 'Saturs',
            subtitle: 'Video un raksti no visa izveidotā RoltonsLV satura',

            // Filtri
            all: 'Viss',
            videos: 'Video',
            blogs: 'Raksti',
            video: 'Video',
            blog: 'Raksts',

            // Meklēšanai
            search_placeholder: 'Meklēt pēc nosaukuma vai apraksta...',

            // Uzlaboti filtri
            platform: 'Platforma',
            category: 'Kategorija',
            sort_by: 'Kārtot pēc',
            newest: 'Jaunākie',
            oldest: 'Vecākie',
            most_liked: 'Visvairāk patīk',
            most_viewed: 'Visvairāk skatījumu',

            // Rezultāti
            results: 'rezultāti',
            showing: 'Rāda',
            of: 'no',

            // Darbības
            view: 'Skatīt',
            watch_on: 'Skatīties uz',
            load_more: 'Ielādēt vairāk',

            // Tukšs stāvoklis
            no_content: 'Nav satura',
            no_content_description: 'Nav atrasts neviens saturs pēc izvēlētajiem filtriem.',

            // Parādīšanas lapa (Content/Show.vue)
            back_to_list: 'Atpakaļ uz sarakstu',
            share: 'Dalīties',
            login_required: 'Lūdzu piesakieties, lai veiktu šo darbību',
            link_copied: 'Saite nokopēta!',

            // Reitings
            rate_this: 'Novērtēt',
            write_review: 'Rakstīt atsauksmi',
            review_placeholder: 'Dalieties ar savu viedokli...',
            select_rating: 'Lūdzu izvēlieties vērtējumu',
            submit_review: 'Nosūtīt atsauksmi',
            submitting: 'Nosūta...',
            cancel: 'Atcelt',

            // Komentāri
            comments: 'Komentāri',
            comment_placeholder: 'Pievienot komentāru...',
            post_comment: 'Publicēt',
            posting: 'Publicē...',
            login_to_comment: 'Piesakieties, lai komentētu',
            loading_comments: 'Ielādē komentārus...',
            no_comments: 'Pagaidām nav komentāru',

            // Vēl papildus
            description: 'Apraksts',
            comment_submitted: 'Jūsu komentārs tika iesniegts un gaida apstiprinājumu',
            review_submitted: 'Jūsu atsauksme tika iesniegta un gaida apstiprinājumu',
            liked_success: 'Pievienots pie iecienītākajiem!',
            unliked_success: 'Noņemts no iecienītākajiem',
        },
        common: {
            loading: 'Lādē...',
            view_more: 'Skatīt Vairāk',
            view_all: 'Skatīt visu',
            add_to_cart: 'Pievienot grozam',
            search: 'Meklēt...',
            close: 'Aizvērt',
            adding: 'Pievieno...',
            save: 'Saglabāt',
            saving: 'Saglabā...',
            cancel: 'Atcelt',
            sending: 'Sūta...',
            deleting: 'Dzēš...',
        },
        sections: {
            featured_content: 'Jaunākais Saturs',
            featured_products: 'Populārākie Produkti',
            stats: {
                views: 'Skatījumi',
                followers: 'Sekotāji',
                videos: 'Video',
                products: 'Produkti',
            },
            noProductsAvailable: 'Nav pieejams neviens produkts',
        },
        product: {
            price: 'Cena',
            in_stock: 'Noliktavā',
            out_of_stock: 'Nav noliktavā',
            quantity: 'Daudzums',
            discount: 'Atlaide',
            save: 'Ietaupi',
            details: 'Detaļas',
            category: 'Kategorija',
            availability: 'Pieejamība',
            related_products: 'Līdzīgi Produkti',
            not_found: 'Produkts nav atrasts',
            stock_quantity: 'Krājumu daudzums',
        },
        cart: {
            title: 'Iepirkšanās grozs',
            empty: 'Jūsu grozs ir tukšs',
            subtotal: 'Starpsumma',
            shipping: 'Piegāde',
            total: 'Kopā',
            checkout: 'Noformēt pasūtījumu',
            items_in_cart: 'preces grozā',
            continue_shopping: 'Turpināt iepirkties',
            empty_text: "Tukšs",
        },
        footer: {
            // "Par mums" sadaļa
            about_title: 'Par RalphMania',
            about_description: 'Tavs uzticamākais avots unikālam saturam un ekskluzīviem produktiem. Pievienojies mūsu kopienai!',

            // Veikala sauklis
            shop_tagline: 'Tavs uzticamākais avots ekskluzīviem RalphMania produktiem',

            // Navigācijas virsraksti
            quick_links: 'Ātrās saites',
            categories: 'Kategorijas',
            contact_info: 'Kontaktinformācija',
            shop: 'Veikals',
            customers: 'Klientiem',
            information: 'Informācija',
            follow_us: 'Seko mums',

            // Saites
            video: 'Video',
            blog: 'Blogs',
            apparel: 'Apģērbi',
            souvenirs: 'Suvenīri',
            gift_cards: 'Dāvanu kartes',
            all_products: 'Visi produkti',

            // Klientu saites
            faq: 'Biežāk uzdotie jautājumi',
            shipping: 'Piegāde',
            returns: 'Atgriešana',
            contact: 'Kontakti',

            // Informācijas saites
            about_us: 'Par mums',
            privacy_policy: 'Privātuma politika',
            terms_of_use: 'Lietošanas noteikumi',

            // Kontakta informācija
            location: 'Rīga, Latvija',

            // Biļetens (Newsletter)
            subscribe_description: 'Uzzini pirmais par jauniem produktiem un akcijām!',
            email_placeholder: 'Tavs e-pasts',
            subscribe_success: 'Paldies par abonēšanu!',

            // Maksājums
            payment_methods: 'Pieņemam:',

            // Autortiesības
            copyright: '© {year} RalphMania. Visas tiesības aizsargātas.',
        },
        profile: {
            profile: 'Profils',
            profile_settings: 'Profila iestatījumi',
            manage_your_account: 'Pārvaldiet savu kontu un iestatījumus',
            profile_information: 'Profila informācija',
            update_profile_description: 'Atjauniniet sava konta profila informāciju un e-pasta adresi.',
            first_name: 'Vārds',
            last_name: 'Uzvārds',
            email_unverified: 'Jūsu e-pasta adrese nav verificēta.',
            resend_verification: 'Nosūtīt verificēšanas e-pastu vēlreiz',
            saved: 'Saglabāts.',
            update_password: 'Atjaunot paroli',
            update_password_description: 'Pārliecinieties, ka jūsu konts izmanto garu, nejaušu paroli, lai saglabātu drošību.',
            current_password: 'Pašreizējā parole',
            new_password: 'Jaunā parole',
            confirm_new_password: 'Apstiprināt jauno paroli',
            password: 'Parole',
            password_updated: 'Parole atjaunota.',
            delete_account: 'Dzēst kontu',
            delete_account_description: 'Neatgriezeniski dzēsiet savu kontu.',
            delete_warning: 'Kad jūsu konts tiek izdzēsts, visi tā resursi un dati tiks neatgriezeniski izdzēsti. Pirms konta dzēšanas, lūdzu, lejupielādējiet visus datus vai informāciju, ko vēlaties saglabāt.',
            confirm_deletion: 'Vai tiešām vēlaties dzēst savu kontu?',
            deletion_confirmation_text: 'Kad jūsu konts tiek izdzēsts, visi tā resursi un dati tiks neatgriezeniski izdzēsti. Lūdzu, ievadiet savu paroli, lai apstiprinātu, ka vēlaties neatgriezeniski dzēst savu kontu.',
            username: 'Lietotājvārds',
            email: 'E-pasts',
            phone: 'Tālrunis',
            birth_date: 'Dzimšanas datums',
            country: 'Valsts',
            city: 'Pilsēta',
            address: 'Adrese',
            postal_code: 'Pasta indekss',
            profile_picture: 'Profila bilde',
            other: 'Cits',
            select_photo: 'Izvēlēties bildi',
            delete_photo: 'Dzēst bildi',
            photo_hint: 'JPG, PNG vai GIF. Maksimālais izmērs: 2MB.',
            profile_updated: 'Profils atjaunināts.',
        },
        home: {
            no_featured_content: 'Pagaidām nav izcelto saturu',
            no_featured_products: 'Pagaidām nav izcelto produktu',
            about_title: 'Par RalphMania',
            about_description: 'RalphMania ir vieta, kur atradīsiet labākos Geometry Dash video, rakstus un unikālus produktus. Pievienojieties mūsu komūnai!',
            about_button: 'Uzzināt vairāk',
        },
    },
    en: {
        nav: {
            home: 'Home',
            about: 'About',
            contact: 'Contact',
            shop: 'Shop',
            content: 'Content',
            dashboard: 'Dashboard',
            profile: 'Profile',
        },
        auth: {
            login: 'Login',
            register: 'Sign Up',
            logout: 'Logout',
            dashboard: 'Dashboard',
            username: 'Username',
            email: 'Email',
            email_or_username: 'Email or Username',
            email_or_username_placeholder: 'Enter email or username',
            password: 'Password',
            password_confirmation: 'Confirm Password',
            phone: 'Phone (optional)',
            birth_date: 'Birth Date (optional)',
            remember_me: 'Remember me',
            forgot_password: 'Forgot password?',
            already_registered: 'Already registered?',
            no_account: 'Don\'t have an account?',
            welcome_back: 'Welcome back!',
            login_description: 'Log in to visit Our lovely establishment!\n' +
                'In case you forgot your password, click on "Forgot Password?"',
            logging_in: 'Logging in..',
            join_us: 'Join us!',
            register_description: 'Create an account and start shopping!',
            confirm_password: 'Confirm Password',
            new_password: 'New Password',
            confirm_new_password: 'Confirm New Password',
            registering: 'Registering...',
            resetting: 'Resetting...',
            confirming: 'Confirming...',
            create_account: 'Create Account',
            reset_password: 'Reset Password',
            reset_password_title: 'Password Recovery',
            reset_description: 'No problem! We will help you recover your account',
            forgot_password_help: 'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link.',
            send_reset_link: 'Email Password Reset Link',
            back_to_login: 'Back to Login',
            new_password_title: 'New Password',
            new_password_description: 'Enter your new password',
            secure_area: 'Secure Area',
            confirm_password_description: 'This is a secure area. Please confirm your password before continuing.',
            confirm_password_help: 'This is a secure area of the application. Please confirm your password before continuing.',
            confirm: 'Confirm',
            verify_email: 'Verify Email',
            verify_email_title: 'Verify Your Email',
            verify_email_description: 'Check your email address to activate your account',
            verify_email_help: 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?',
            verification_link_sent: 'A new verification link has been sent to your email address.',
            resend_verification: 'Resend Verification Email',
        },
        dashboard: {
            welcome: 'Welcome',
            subtitle: 'Manage your account and orders',
            nav: {
                overview: 'Overview',
                profile: 'Profile',
                orders: 'Orders',
                addresses: 'Addresses',
                reviews: 'Reviews',
                settings: 'Settings',
                back_home: 'Back to Home',
            },
            stats: {
                orders: 'Orders',
                reviews: 'Reviews',
                favorites: 'Favorites',
                cart: 'In Cart',
            },
            sections: {
                overview: {
                    title: 'Overview',
                    recent_orders: 'Recent Orders',
                    quick_actions: 'Quick Actions',
                    no_orders: 'You don\'t have any orders yet',
                },
                profile: {
                    title: 'My Profile',
                },
                orders: {
                    title: 'My Orders',
                    no_orders: 'You don\'t have any orders yet',
                },
                addresses: {
                    title: 'My Addresses',
                    no_addresses: 'You don\'t have any addresses saved',
                },
                reviews: {
                    title: 'My Reviews',
                    no_reviews: 'You haven\'t written any reviews yet',
                },
                settings: {
                    title: 'Settings',
                },
            },
            profile: {
                phone: 'Phone',
                birth_date: 'Birth Date',
                member_since: 'Member Since',
                last_login: 'Last Login',
            },
            actions: {
                shop: 'Shop',
                cart: 'Cart',
                edit_profile: 'Edit Profile',
                change_password: 'Change Password',
                add_address: 'Add Address',
            },
            settings: {
                notifications: 'Notifications',
                email_notifications: 'Email Notifications',
                order_updates: 'Order Updates',
                privacy: 'Privacy',
                show_profile: 'Public Profile',
            },
        },
        hero: {
            title: 'Welcome to RalphMania!',
            subtitle: 'Your destination for content and branded products',
            cta_content: 'View Content',
            cta_shop: 'Visit Shop',
        },
        about: {
            hero: {
                title: 'About Us',
                subtitle: 'Get to know our story and values',
            },
            story: {
                title: 'Our Story',
                paragraph_1: 'RalphMania was created with the goal of combining content and products in one platform. We believe that quality and authenticity are paramount.',
                paragraph_2: 'Our journey began with a simple idea - create a community that shares our passions and values. Today we proudly offer both unique content and exclusive products.',
                paragraph_3: 'Each of our products is carefully selected and each piece of content is created with love. We want to be more than just a store - we want to be a community.',
            },
            mission: {
                title: 'Our Mission',
                content: {
                    title: 'Quality Content',
                    text: 'Creating original and engaging content that inspires and entertains our community.',
                },
                products: {
                    title: 'Unique Products',
                    text: 'Offering exclusive products that reflect our brand and values.',
                },
                community: {
                    title: 'Strong Community',
                    text: 'Building a community where every voice is heard and valued.',
                },
            },
            values: {
                title: 'Our Values',
                quality: {
                    title: 'Quality',
                    text: 'We never settle for mediocrity. Every product and piece of content is carefully thought out.',
                },
                authenticity: {
                    title: 'Authenticity',
                    text: 'We are genuine and transparent in everything we do. No hidden agendas or false promises.',
                },
                passion: {
                    title: 'Passion',
                    text: 'Our work is our passion. We love what we do and it shows in the results.',
                },
                innovation: {
                    title: 'Innovation',
                    text: 'We are always looking for new ways to improve and grow with our community.',
                },
            },
            cta: {
                title: 'Join Our Community!',
                text: 'Find your favorite products and content in one place',
                shop: 'Shop Now',
                contact: 'Get in Touch',
            },
        },
        contact: {
            hero: {
                title: 'Get in Touch',
                subtitle: 'We\'d love to hear from you',
            },
            form: {
                title: 'Send Us a Message',
                subtitle: 'Fill out the form and we\'ll get back to you soon',
                name: 'Name',
                email: 'Email',
                subject: 'Subject',
                message: 'Message',
                send: 'Send Message',
                sending: 'Sending...',
                success: 'Thank you! Your message has been sent.',
            },
            info: {
                title: 'Contact Information',
                email: 'Email',
                phone: 'Phone',
                address: 'Address',
                social: 'Social Media',
            },
        },
        shop: {
            hero: {
                title: 'RalphMania Shop',
                subtitle: 'Discover our unique product collection',
            },
            category: 'Category',
            categories: 'Categories',
            all_products: 'All Products',
            products_count: 'products',
            no_products: 'No products available at this time',
            no_products_in_category: 'No products in this category',
            back_to_shop: 'Back to Shop',
            sale: 'Sale',
            filter: {
                newest: 'Newest',
                price_low: 'Price: Low → High',
                price_high: 'Price: High → Low',
                popular: 'Most Popular',
            },
        },
        content: {
            hero: {
                title: 'RalphMania Content',
                subtitle: 'Discover our latest collection of videos and articles',
            },
            filter: {
                all: 'All Content',
                videos: 'Videos',
                blogs: 'Blogs',
            },
            views: 'Views',
            latest: 'Latest',
            title: 'Content',
            subtitle: 'Videos and blogs from all made RoltonsLV content',

            // Filtri
            all: 'All',
            videos: 'Videos',
            blogs: 'Blogs',
            video: 'Video',
            blog: 'Blog',

            // Meklēšanai
            search_placeholder: 'Search by name or description...',

            // Uzlaboti filtri
            platform: 'Platform',
            category: 'Category',
            sort_by: 'Sort by',
            newest: 'Newest',
            oldest: 'Oldest',
            most_liked: 'Most liked',
            most_viewed: 'Most viewed',

            // Rezultāti
            results: 'results',
            showing: 'Showing',
            of: 'of',

            // Darbības
            view: 'View',
            watch_on: 'Watch on',
            load_more: 'Load more',

            // Tukšs stāvoklis
            no_content: 'No content',
            no_content_description: 'No content was found according to the selected filters.',

            // Parādīšanas lapa (Content/Show.vue)
            back_to_list: 'Back to list',
            share: 'Share',
            login_required: 'Please login to perform this action',
            link_copied: 'Link copied!',

            // Reitings
            rate_this: 'Rate This',
            write_review: 'Write Review',
            review_placeholder: 'Share your thoughts...',
            select_rating: 'Please select a rating',
            submit_review: 'Submit Review',
            submitting: 'Submitting...',
            cancel: 'Cancel',

            // Komentāri
            comments: 'Comments',
            comment_placeholder: 'Add a comment...',
            post_comment: 'Post',
            posting: 'Posting...',
            login_to_comment: 'Login to comment',
            loading_comments: 'Loading comments...',
            no_comments: 'No comments yet',

            // Vēl papildus
            description: 'Description',
            comment_submitted: 'Your comment has been submitted and is awaiting approval',
            review_submitted: 'Your review has been submitted and is awaiting approval',
            liked_success: 'Added to favorites!',
            unliked_success: 'Removed from favorites',
        },
        common: {
            loading: 'Loading...',
            view_more: 'View More',
            view_all: 'View All',
            add_to_cart: 'Add to Cart',
            search: 'Search...',
            close: 'Close',
            adding: 'Adding...',
            save: 'Save',
            saving: 'Saving...',
            cancel: 'Cancel',
            sending: 'Sending...',
            deleting: 'Deleting...',
        },
        sections: {
            featured_content: 'Latest Content',
            featured_products: 'Popular Products',
            stats: {
                views: 'Views',
                followers: 'Followers',
                videos: 'Videos',
                products: 'Products',
            },
            noProductsAvailable: 'No products available',
        },
        product: {
            price: 'Price',
            in_stock: 'In Stock',
            out_of_stock: 'Out of Stock',
            quantity: 'Quantity',
            discount: 'Discount',
            save: 'Save',
            details: 'Details',
            category: 'Category',
            availability: 'Availability',
            related_products: 'Related Products',
            not_found: 'Product not found',
            stock_quantity: 'Stock quantity',
        },
        cart: {
            title: 'Shopping Cart',
            empty: 'Your cart is empty',
            subtotal: 'Subtotal',
            shipping: 'Shipping',
            total: 'Total',
            checkout: 'Checkout',
            items_in_cart: 'items in cart',
            continue_shopping: 'Continue shopping',
            empty_text: "Empty",
        },
        footer: {
            // "Par mums" sadaļa
            about_title: 'About RalphMania',
            about_description: 'Your most trusted source for unique content and exclusive products. Join our community!',

            // Veikala sauklis
            shop_tagline: 'Your most trusted source for exclusive RalphMania products',

            // Navigācijas virsraksti
            quick_links: 'Quick Links',
            categories: 'Categories',
            contact_info: 'Contact Information',
            shop: 'Shop',
            customers: 'Customers',
            information: 'Information',
            follow_us: 'Follow Us',

            // Saites
            video: 'Videos',
            blog: 'Blog',
            apparel: 'Apparel',
            souvenirs: 'Souvenirs',
            gift_cards: 'Gift Cards',
            all_products: 'All Products',

            // Klientu saites
            faq: 'Frequently Asked Questions',
            shipping: 'Shipping',
            returns: 'Returns',
            contact: 'Contact',

            // Informācijas saites
            about_us: 'About Us',
            privacy_policy: 'Privacy Policy',
            terms_of_use: 'Terms of Use',

            // Kontakta informācija
            location: 'Riga, Latvia',

            // Biļetens (Newsletter)
            subscribe_description: 'Be the first to know about new products and promotions!',
            email_placeholder: 'Your email',
            subscribe_success: 'Thank you for subscribing!',

            // Maksājums
            payment_methods: 'We Accept:',

            // Autortiesības
            copyright: '© {year} RalphMania. All rights reserved.',
        },
        profile: {
            profile: 'Profile',
            profile_settings: 'Profile Settings',
            manage_your_account: 'Manage your account and settings',
            profile_information: 'Profile Information',
            update_profile_description: "Update your account's profile information and email address.",
            first_name: 'First Name',
            last_name: 'Last Name',
            email_unverified: 'Your email address is unverified.',
            resend_verification: 'Resend Verification Email',
            saved: 'Saved.',
            update_password: 'Update Password',
            update_password_description: 'Ensure your account is using a long, random password to stay secure.',
            current_password: 'Current Password',
            new_password: 'New Password',
            confirm_new_password: 'Confirm New Password',
            password: 'Password',
            password_updated: 'Password updated.',
            delete_account: 'Delete Account',
            delete_account_description: 'Permanently delete your account.',
            delete_warning: 'Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.',
            confirm_deletion: 'Are you sure you want to delete your account?',
            deletion_confirmation_text: 'Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.',
            // -------------------
            username: 'Username',
            email: 'Email',
            phone: 'Phone',
            birth_date: 'Birth Date',
            country: 'Country',
            city: 'City',
            address: 'Address',
            postal_code: 'Postal Code',
            profile_picture: 'Profile Picture',
            other: 'Other',
            select_photo: 'Select Photo',
            delete_photo: 'Delete Photo',
            photo_hint: 'JPG, PNG or GIF. Maximum size: 2MB.',
            profile_updated: 'Profile updated.',
        },
        home: {
            no_featured_content: 'No featured content yet',
            no_featured_products: 'No featured products yet',
            about_title: 'About RalphMania',
            about_description: 'RalphMania is the place to find the best Geometry Dash videos, articles, and unique products. Join our community!',
            about_button: 'Learn More',
        },
    }
};

const i18n = createI18n({
    legacy: false,
    locale: localStorage.getItem('lang') || 'lv',
    fallbackLocale: 'en',
    messages,
    globalInjection: true,
});

export default i18n;

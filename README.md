<div align="center">

<img src="public/img/RoltonsLV_Icon.png" alt="RalphMania Icon" width="100"/>

<img src="public/img/name_logo.png" alt="RalphMania" width="320"/>

**Sociālo mediju atbalstītāju saliedēšanās platforma un e-komercijas veikals no Latvijas YouTube satura veidotāja "RoltonsLV"**

[![Laravel](https://img.shields.io/github/composer-package/v/laravel/framework?style=flat-square&logo=laravel&logoColor=white&label=Laravel&color=FF2D20)](https://laravel.com)
[![Vue.js](https://img.shields.io/github/package-json/dependency-version/22DP1RMiga/ralphmania/vue?style=flat-square&logo=vue.js&logoColor=white&label=Vue.js&color=4FC08D)](https://vuejs.org)
[![PHP](https://img.shields.io/badge/dynamic/json?url=https://raw.githubusercontent.com/22DP1RMiga/ralphmania/main/composer.json&query=%24.require.php&style=flat-square&logo=php&logoColor=white&label=PHP&color=777BB4)](https://php.net)
[![Licence](https://img.shields.io/github/license/22DP1RMiga/ralphmania?style=flat-square&label=Licence&color=blue)](LICENSE)

</div>

---

## Par projektu

**RalphMania** ir pilnvērtīga tīmekļa platforma, kas apvieno divas galvenās funkcijas vienā sistēmā:

- 🎬 **Satura centrs** — video, emuāri, ziņas un paziņojumi no satura veidotāja **RoltonsLV**, ar komentāriem, atbildēm, noskaņojuma vērtējumiem un atsauksmēm.
- 🛒 **Zīmola veikals** — RalphMania zīmola apģērbi, aksesuāri, suvenīri un dāvanu kartes ar pilnu e-komercijas funkcionalitāti.

Platforma ir veidota kā kvalifikācijas darbs, izmantojot modernu tehnoloģiju kopu: **Laravel 11 + Vue.js 3 + Inertia.js**.

---

## 📸 Ekrānuzņēmumi
Atverot tīmekļa lietotnes sākumlapu, lietotājs ir laipni lūgts 
RalphMania sistēmā un tiek piedāvāta iespēja apmeklēt satura lapu 
un internetveikalu ar divu pogu palīdzību (skat. 1. att.). 
No sākumlapas līdz saturam (ieskaitot) ir kopīgā navigācijas josla, kur var pāriet 
no lapas uz citu, uzklikšķināt uz sava profila un pārslēgt no latviešu uz angļu valodu.

> ![Sakumlapa](public/img/screenshots/home.png)
<div align="center"><i>1. att. Sākumlapa</i></div>

### 🔴 Satura sadaļa
#### ~Sākums~
Atverot tīmekļa lietotnes satura lapu, lietotājam ir iespēja apskatīt četru veidu saturu 
– video, emuāri, ziņas un paziņojumi, un ir iespēja meklēt pēc filtriem, meklētājā vai 
ritināt satura bagātu katalogu (skat. 2. att.).

> ![Saturs1](public/img/screenshots/content1.png)
<div align="center"><i>2. att. Satura lapas sākums</i></div>

#### ~Filtrēta satura lapa~
Filtrējot pēc video, katalogā pēc piemēra var pamanīt trīs rezultātus no 62 esošajiem ierakstiem. 
“Ielādēt vairāk” poga paplašinās katalogu par sešām vienībām vairāk (skat. 3. att.).

> ![Saturs2](public/img/screenshots/content2.png)
<div align="center"><i>3. att. Satura lapa filtrēta pēc video</i></div>

### 🛍️ Veikals
#### ~Sākums~
Atverot tīmekļa lietotnes internetveikalu, lietotājam ir iespēja aplūkot zīmola preču katalogu, 
filtrējot pēc kategorijām, pēc cenām, pēc jaunākajiem un populārākajiem produktiem un meklējot pēc 
to nosaukumiem latviešu vai angļu valodā. Internetveikalam ir sava navigācijas josla, kur mājas poga 
noved atpakaļ uz sākumlapu, “Veikals” poga parāda kategorijas, “Kontakti” poga noved uz atsevišķo veikala 
saistīto kontaktlapu, palielināmā stikla poga – pie meklētāja, nākamā poga – uz savu profilu, groziņš -
uz groza lapu un pašā galā ir LV/EN tulkotājs (skat. 4. att.).

> ![Veikals1](public/img/screenshots/shop1.png)
<div align="center"><i>4. att. Internetveikals ar pirmajiem četriem zīmola produktiem</i></div>

#### ~Detalizēta produkta lapa~
Atverot tīmekļa lietotnes internetveikalā informatīvu zīmola produkta lapu, ir atkarīgs, 
vai lietotājs skatās produktu, kam loģiskā vērtība datubāzē ir piešķirta, lai varētu izvēlēties 
konkrēto izmēru no XS līdz XXL. Tāpēc šajā piemērā var redzēt T-kreklu, kuram var nomainīt 
izmēru un daudzumu (skat. 5. att.).

> ![Veikals2](public/img/screenshots/shop2.png)
<div align="center"><i>5. att. Internetveikalā informatīva zīmola produkta lapa T-kreklam</i></div>

#### ~Produkta lapa ar atsauksmēm~
Paritinot zemāk var redzēt atsauksmes un līdzīgus produktus (skat. 6. att.).

> ![Veikals3](public/img/screenshots/shop3.png)
<div align="center"><i>6. att. Internetveikalā informatīva zīmola produkta lapa ar atsauksmēm</i></div>

### Kontrolpanelis
Atverot tīmekļa lietotnes kontrolpaneli, lietotājam tiek piedāvāts aplūkot savu profilu, 
pasūtījumus, adreses, komentārus, atsauksmes, jaunumus (biežāk abonentiem), un iestatījumus. 
Ja ir vēlme iziet no esošās lapas, uzklikšķina uz “Atpakaļ uz sākumlapu”. Šīs lapas pārskats kalpo 
kā ātrā pieeja visam, lai apskatītu jaunākos veiktos pasūtījumus un trīs pogas, kuras noved 
uz internetveikalu, groza lapu un profila rediģēšanas lapu (skat. 7. att.).

> ![Panelis](public/img/screenshots/dashboard.png)
<div align="center"><i>7. att. Kontrolpaneļa pārskats</i></div>

---

## ✨ Galvenās funkcijas

### 🎬 Satura platforma
| Funkcija                  | Apraksts                                                |
|---------------------------|---------------------------------------------------------|
| **4 satura veidi**        | Video, emuāri, ziņas, paziņojumi                        |
| **Divvalodu atbalsts**    | Latviešu un angļu valoda visam saturam                  |
| **Komentāri ar atbildēm** | "Thread" sistēma ar hierarhisku struktūru               |
| **Noskaņojuma slīdnis**   | No katra lietotāja 0–100% vērtējums katram komentāram   |
| **Vidējais noskaņojums**  | Aprēķināts vidējais vērtējums redzams visiem            |
| **"Patīk" sistēma**       | Polimorfiskas atzīmes saturam un produktiem             |
| **Atsauksmes**            | 1–5 zvaigznes ar tekstu                                 |
| **Privātuma kontrole**    | Privāti profili - komentāri neredzami citiem            |
| **Noskaņojuma filtrs**    | Filtrēt saturu pēc pozitīva/neitrāla/negatīva noskaņojuma |

### 🛒 E-komercijas veikals
| Funkcija | Apraksts                                                                 |
|---|--------------------------------------------------------------------------|
| **Produktu katalogs** | Ar kategorijām, filtriem un meklēšanu                                    |
| **Izmēri** | Apģērbu izmēru atbalsts                                                  |
| **Iepirkumu grozs** | Viesiem un reģistrētiem lietotājiem                                      |
| **Pasūtījumu sistēma** | 9 statusi no `pending` līdz `delivered`                                  |
| **Kuponu sistēma** | Procentuālas un fiksētas atlaides, atdzišanas periodi (cooldown periods) |
| **Kurjeru pārvaldība** | Pasūtījumu piešķiršana kurjeriem                                         |
| **Maksājumi** | Karšu un citu maksājumu veidu atbalsts                                   |
| **Abonementu piedāvājumi** | Ekskluzīvi piedāvājumi jaunumu abonentiem                                |

### 👤 Lietotāju sistēma
| Funkcija | Apraksts |
|---|---|
| **Reģistrācija/autentifikācija** | E-pasta verifikācija, paroles atjaunošana |
| **4 lomas** | Viesis, lietotājs, administrators, kurjers |
| **Personīgais panelis** | Pasūtījumi, komentāri, atsauksmes, iestatījumi |
| **Privātuma iestatījumi** | Publiski/privāti profili |
| **Aktivitāšu žurnāls** | Sistēmas darbību reģistrācija |

### 🛡️ Administratora panelis
| Sadaļa | Iespējas                                |
|---|-----------------------------------------|
| **Saturs** | C.R.U.D., publicēšana, statistika       |
| **Produkti & kategorijas** | C.R.U.D., krājumu pārvaldība            |
| **Pasūtījumi** | Statusa mainīšana, eksports             |
| **Komentāri & atsauksmes** | Moderēšana, apstiprināšana              |
| **Lietotāji** | Pārvaldība, bloķēšana                   |
| **Kurjeri** | Pievienošana, pasūtījumu piešķiršana    |
| **Kontakti** | Saņemtie ziņojumi, atbildes             |
| **Administratori** | Moderēšanas atļaujas (44 atļauju veidi) |
| **Žurnāls** | Aktivitāšu monitorings, CSV eksports    |

---

## 🛠️ Tehnoloģiju steks

### Backend
- **[Laravel 11](https://laravel.com)** — PHP 8.3+ ietvars
- **[Inertia.js](https://inertiajs.com)** — SPA bez REST API
- **MySQL 8** — relāciju datubāze
- **Laravel Sanctum** — autentifikācija
- **Gmail SMTP** — e-pasta sūtīšana

### Frontend
- **[Vue.js 3](https://vuejs.org)** — Composition API
- **[Tailwind CSS](https://tailwindcss.com)** — utilītklašu CSS
- **[Vite](https://vitejs.dev)** — build rīks
- **vue-i18n** — internacionalizācija (LV/EN)

### Izstrādes rīki
- **[PhpStorm](https://www.jetbrains.com/phpstorm/)** — IDE
- **[XAMPP](https://www.apachefriends.org/)** — lokālā izstrādes vide
- **[Git](https://git-scm.com/)** — versiju kontrole

---

## 🗄️ Datubāzes shēma

Sistēma izmanto **27 tabulas**:

```
roles                    — Lietotāju lomas (guest, user, administrator, courier)
users                    — Reģistrētie lietotāji
administrators           — Administratoru profili ar atļaujām
couriers                 — Kurjeru profili
courier_assignments      — Pasūtījumu piešķiršana kurjeriem

categories               — Produktu kategorijas (ar ligzdošanu)
products                 — Zīmola produkti
carts / cart_items       — Iepirkumu grozi
orders / order_items     — Pasūtījumi un preces
payments                 — Maksājumu dati
coupons / coupon_usages  — Kuponi un to izmantošana

content                  — Saturs (video, blogi, ziņas, paziņojumi)
comments                 — Komentāri ar thread atbalstu
comment_moods            — Per-lietotāja noskaņojuma vērtējumi
likes                    — Polimorfiskas atzīmes
reviews                  — Polimorfiskas atsauksmes

newsletter_subscribers   — Jaunumu abonenti
subscriber_offers        — Abonenta piedāvājumi
contact_messages         — Kontaktformas ziņojumi
activity_logs            — Sistēmas aktivitāšu žurnāls
settings                 — Sistēmas iestatījumi

sessions / cache / cache_locks / password_reset_tokens / migrations  — Laravel sistēmas tabulas
```

---

## 🚀 Uzstādīšana

### Priekšnoteikumi
- PHP 8.3+
- Composer
- Node.js 18+
- MySQL 8+

### 1. Klonēt repozitoriju

```bash
git clone https://github.com/22DP1RMiga/RalphMania.git
cd ralphmania
```

### 2. Instalēt atkarības

```bash
composer install
npm install
```

### 3. Konfigurēt vidi

```bash
cp .env.example .env
php artisan key:generate
```

Aizpildi `.env` failu:

```env
APP_NAME=RalphMania
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ralphmania
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tavs-epasts@gmail.com
MAIL_PASSWORD=gmail-app-password
MAIL_FROM_ADDRESS=tavs-epasts@gmail.com
MAIL_FROM_NAME="RalphMania"
```

### 4. Datubāze un sākotnējie dati

```bash
php artisan migrate
php artisan db:seed
```

Pēc seeding izveidojas šie konti (parole visiem: `password`):

| Loma | E-pasts |
|---|---|
| Super Admin | `superadmin@ralphmania.lv` |
| Kurjers | `courier@ralphmania.lv` |
| Klients | `client@ralphmania.lv` |

> ⚠️ **Svarīgi:** Pirms ražošanas izvietošanas (*production deployment*) nomainiet `superadmin@ralphmania.lv` uz īsto e-pastu!

### 5. Storage un build

```bash
php artisan storage:link
npm run build
# vai izstrādei:
npm run dev
```

### 6. Palaist serveri

```bash
php artisan serve
```

Atver [http://localhost:8000](http://localhost:8000)

---

## 📁 Projekta struktūra

```
ralphmania/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/              # Administratora kontrolieri
│   │   │   ├── AdminActivityLogController.php
│   │   │   ├── ...
│   │   │   └── AdminUserController.php
│   │   │
│   │   ├── Auth/
│   │   │   ├── AuthenticatedSessionController.php
│   │   │   ├── ...
│   │   │   └── VerifyEmailController.php
│   │   │
│   │   ├── Courier/
│   │   │   └── CourierController.php
│   │   ├── AuthController.php
│   │   ├── ...
│   │   └── ReviewController.php
│   │
│   ├── Http/Middleware/
│   │   ├── AdminMiddleware.php
│   │   ├── ...
│   │   └── SuperAdminMiddleware.php
│   │
│   ├── Http/Requests/
│   │   ├── Auth/
│   │   │   └── LoginRequest.php
│   │   └── ProfileUpdateRequest.php
│   │
│   ├── Listeners/
│   │   └── MergeGuestCartOnLogin.php
│   │
│   ├── Mail/
│   │   ├── ContactMessageReceived.php
│   │   ├── ...
│   │   └── VerifyEmailMail.php
│   │
│   ├── Models/                 # Eloquent modeļi
│   │   ├── ActivityLog.php
│   │   ├── ...
│   │   └── User.php
│   │
│   ├── Notifications/          # E-pasta notifikācijas
│   │   └── VerifyEmailNotification.php
│   │
│   └── Providers/          # E-pasta notifikācijas
│       ├── AppServiceProvider.php
│       └── EventServiceProvider.php
│
├── database/
│   ├── migrations/             # 13 migrācijas faili
│   └── seeders/
│       └── DatabaseSeeder.php
│
├── public/
│   ├── build/
│   │   ├── assets/
│   │   │   ├── _plugin-vue_export-helper-DlAUqK2U.js
│   │   │   ├── ...
│   │   │   └── Welcome-DjSJBkoy.js
│   │   └── manifest.json
│   │
│   ├── img/
│   │   ├── Announcements/              # satura tipam: paziņojumiem
│   │   │   └── (attēli .png formātā)
│   │   │
│   │   ├── Blogs/                      # satura tipam: emuāriem
│   │   │   └── (attēli .png formātā)
│   │   │
│   │   ├── Picture Edits/              # attēlu veidošanai (netiek attēlots mājaslapā)
│   │   │   └── ("paint.net" redakcijas faili .pdn formātā - domāts kā alternatīvs PhotoShop programmai)
│   │   │
│   │   ├── Posts/                      # satura tipam: ziņām
│   │   │   └── (attēli .png formātā)
│   │   │
│   │   ├── Products/                   # produktiem
│   │   │   └── (attēli .png formātā)
│   │   │
│   │   ├── screenshots/                # README.md failam - demonstrējošo ekrānuzņēmumu direktorija
│   │   │   └── (attēli .png formātā)
│   │   │
│   │   ├── thumbnails/                 # satura tipam: video
│   │   │   └── (attēli .png formātā)
│   │   ├── about-logo.png
│   │   ├── about-okay-logo.png
│   │   ├── default-avatar.png
│   │   ├── default-avatar1.png
│   │   ├── name_logo.png               # tagadējā stila virsraksts (sarkans)
│   │   ├── name_logo1.png              # vecā stila virsraksts
│   │   ├── name_logo2.png              # tagadējā stila virsraksts (balts) 
│   │   ├── no-content-placeholder      # ja nav galvenā attēla satura materiālam, aizvieto ar šo attēlu
│   │   ├── RoltonsLV_icon.png          # zīmols      
│   │   └── manifest.json
│   ├── storage/                        # īsceļš uz public/storage/
│   │   ├── avatars/
│   │   │   └── (attēli .png formātā)   # glabātuve profilu bildēm
│   │   ├── blogs/
│   │   │   └── (attēli .png formātā)   # glabātuve emuāru attēliem
│   │   ├── products/
│   │   │   └── (attēli .png formātā)   # glabātuve produktu attēliem
│   │   └── .gitignore
│   ├── .htaccess
│   ├── favicon.ico
│   ├── index.php
│   └── robots.txt
│
├── resources/
│   ├── css/
│   │   └── app.css
│   │
│   ├── js/
│   │   ├── Components/
│   │   │   ├── ApplicationLogo.vue
│   │   │   ├── ...
│   │   │   └── UnauthorizedModal.vue
│   │   │
│   │   ├── Composables/
│   │   │   └── useAdminPermission.js
│   │   │
│   │   ├── Layouts/
│   │   │   ├── AdminLayout.vue
│   │   │   ├── ...
│   │   │   └── ShopLayout.vue
│   │   │
│   │   ├── Pages/
│   │   │   ├── Admin/          # Administratora Vue lapas
│   │   │   │   ├── Adminstrators/
│   │   │   │   │   └── Index.vue
│   │   │   │   ├── .../
│   │   │   │   │   └── Create.vue, Edit.vue, Index.vue, Show.vue       # kā kurai sadaļai ietrāpās (visiem Index.vue)
│   │   │   │   ├── Users/
│   │   │   │   │   └── Edit.vue, Index.vue, Show.vue
│   │   │   │   ├── Dashboard.vue
│   │   │   │   └── Unauthorized.vue
│   │   │   │
│   │   │   ├── Auth/
│   │   │   │   ├── ConfirmPassword.vue
│   │   │   │   ├── ...
│   │   │   │   └── VerifyEmail.vue
│   │   │   │
│   │   │   ├── Cart/
│   │   │   │   └── Index.vue
│   │   │   │
│   │   │   ├── Content/        # Satura Vue lapas
│   │   │   │   ├── Index.vue
│   │   │   │   └── Show.vue
│   │   │   │
│   │   │   ├── Courier/
│   │   │   │   ├── Dashboard.vue
│   │   │   │   ├── ...
│   │   │   │   └── Unauthorized.vue
│   │   │   │
│   │   │   ├── Orders/
│   │   │   │   ├── Index.vue
│   │   │   │   └── Show.vue
│   │   │   │
│   │   │   ├── Profile/
│   │   │   │   ├── Partials/
│   │   │   │   │   ├── DeleteUserForm.vue
│   │   │   │   │   ├── UpdatePasswordForm.vue
│   │   │   │   │   └── UpdateProfileInformationForm.vue
│   │   │   │   ├── Edit.vue
│   │   │   │   └── Password.vue
│   │   │   │
│   │   │   ├── Shop/           # Veikala Vue lapas
│   │   │   │   ├── Category.vue
│   │   │   │   ├── ...
│   │   │   │   └── Shipping.vue
│   │   │   ├── About.vue
│   │   │   ├── ...
│   │   │   └── Welcome.vue
│   │   │
│   │   ├── stores/
│   │   │   ├── auth.js
│   │   │   ├── cart.js
│   │   │   └── locale.js
│   │   │
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── i18n.js
│   │
│   └── views/
│       ├── emails/
│       │   ├── contact-message.blade.php
│       │   ├── ...
│       │   └── verify-email.blade.php
│       │
│       ├── invoices/
│       │   └── order.blade.php
│       └── app.blade.php
│
├── routes/
│   ├── api.php
│   ├── auth.php
│   ├── console.php
│   └── web.php
│
├── storage/
│   ├── app/
│   ├── framework/
│   │   ├── cache/
│   │   │   ├── data/
│   │   │   │    └── .gitignore
│   │   │   └── .gitignore
│   │   ├── sessions/
│   │   │   └── .gitignore
│   │   ├── testing/
│   │   │   └── .gitignore
│   │   ├── views/
│   │   │   ├── .gitignore
│   │   │   └── (.php faili)
│   │   └── .gitignore
│   └── logs/
│       ├── .gitignore
│       └── laravel.log
│
├── .env                                # galvenā "pults" sistēmas konfigurācijai
├── .env.example                        # parauga fails sistēmas konfigurācijai
│
└── (visi pārējie konfigurācijas faili, t.sk. README.md fails)
```

---

## 🌐 Galvenie maršruti

```
GET  /                          # Sākumlapa
GET  /content                   # Satura katalogs
GET  /content/{slug}            # Satura materiāls
GET  /shop                      # Veikals
GET  /shop/product/{slug}       # Produkta lapa
GET  /dashboard                 # Lietotāja panelis
GET  /admin/dashboard           # Administratora panelis
GET  /courier/dashboard         # Kurjera panelis
```

---

## 👥 Autors

**Ralfs Migals** - Rīgas Valsts tehnikums, Datorikas nodaļa

Projekts izstrādāts kā kvalifikācijas darbs 2025./2026. mācību gadā.

---

## 📄 Licence

Šis projekts ir licencēts saskaņā ar [MIT Licence](LICENSE) nosacījumiem.

---

<div align="center">

<img src="public/img/RoltonsLV_Icon.png" alt="RalphMania" width="48"/>

*Izstrādāts ar  rūpi️ Latvijā*

</div>

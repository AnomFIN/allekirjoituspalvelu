# Changelog

## [2.0.0] – 2025-01-01 — Täydellinen PHP-uudelleenkirjoitus

### Uutta
- **OOP-arkkitehtuuri**: luokkapohjainen rakenne (`classes/`, `includes/`, `config/`, `pages/`, `actions/`)
- **6-taulun kantarakenne**: `documents`, `signers`, `signing_tokens`, `document_events`, `users`, `settings`
- **Idempotent installer**: `installer.php` + lukitustiedosto `installer.lock`; lisää puuttuvat sarakkeet ALTER TABLE -komennolla, ei tuhoa dataa
- **7 sivua**: dashboard, upload, add_signers, sent, documents, document_detail, sign (julkinen)
- **6 action-käsittelijää**: upload_document, save_signers, send_request, sign_document, reject_document, remind_signer
- **CSRF-suojaus** jokaisessa POST-lomakkeessa
- **UploadService**: MIME-validointi `finfo`-kirjastolla, satunnaistettu tiedostonimi, chmod 0640
- **MailerService**: sähköpostipohjainen allekirjoituspyyntö + muistutus + valmistumisilmoitus (dev-tilassa logitetaan)
- **ValidationService**: fluent-validointilanka, virhelistat
- **Kattava SaaS-CSS** (~700 riviä): CSS custom properties, sidebar, kaikki komponentit, responsiivisuus
- **jQuery UI** (`assets/js/app.js`): dropzone drag & drop, allekirjoittajarivienhallinta, IntersectionObserver animaatiot, toast-ilmoitukset
- **QA-työkalu**: `scripts/check_project.php` — tarkistaa 35+ tiedostoa, PHP-syntaksin, laajennukset, DB-yhteyden, taulut ja sarakkeet
- **Tietoturva**: X-Frame-Options, X-Content-Type-Options, CSP, PDO prepared statements, uploads-hakemiston PHP-esto
- React/Vite-koodi siirretty kansioon `legacy-react-demo/` (referenssi)

### Muutettu
- `index.php`: täysi front controller `?page=`-reitityksellä (korvaa vanhan yksinkertaisen rooterin)
- `assets/css/style.css`: täysin uusittu (ei Tailwind-riippuvuutta)
- `assets/js/app.js`: täysin uusittu (jQuery 3.7.1, ei React/Vite)
- `installer.php`: laajennettu 6-tauluiseksi, lisää lock-tiedoston

### Poistettu
- `api/` — korvattu `actions/`-käsittelijöillä
- React/Vite-buildijärjestelmä päärakenteesta (siirretty `legacy-react-demo/`)
- Tailwind CSS -riippuvuus

---

## [1.0.0] – 2026-04-29 — Ensimmäinen PHP-migraatio

- Migrated application runtime from React/Vite to PHP + MySQL + jQuery.
- Added robust PDO data layer with prepared statements.
- Added idempotent installer at installer.php.
- Added API endpoints with JSON error responses and try/catch handling.
- Added CSRF protection and output escaping helpers.
- Added secure PDF upload validation (MIME, extension, max size, randomized filename).
- Added SaaS-style responsive UI in assets/css/style.css.
- Added jQuery interactions and effects in assets/js/app.js.
- Marked old React/Vite stack as legacy in README.

# Allekirjoituspalvelu

> **Sähköinen allekirjoituspalvelu** — PHP 8.3 + MariaDB + jQuery, ei React-riippuvuuksia.

## Rakenne

```
/
├── index.php                # Front controller (reitti ?page=…)
├── installer.php            # Idempotent DB-asentaja (lukittuu lock-tiedostolla)
├── config/
│   ├── config.php           # Sovelluksen vakiot
│   └── database.php         # DB DSN, credentials (env. vars tai default)
├── includes/
│   ├── bootstrap.php        # Alustus: config, session, autoload, security-headerit
│   ├── error_handler.php    # PHP error/exception handler
│   ├── functions.php        # Yhteinen apufunktikirjasto
│   ├── security.php         # CSRF, sanitointi, redirect-apurit
│   ├── layout.php           # layout_start() / layout_end()
│   ├── header.php           # Sivupalkki + topbar HTML
│   └── footer.php           # jQuery-scriptit + body/html sulkeminen
├── classes/
│   ├── Database.php         # PDO-singleton
│   ├── DocumentRepository.php
│   ├── SignerRepository.php
│   ├── ActivityRepository.php
│   ├── UploadService.php
│   ├── MailerService.php
│   └── ValidationService.php
├── pages/
│   ├── dashboard.php
│   ├── upload.php
│   ├── add_signers.php
│   ├── sent.php
│   ├── documents.php
│   ├── document_detail.php
│   ├── sign.php             # Julkinen allekirjoitussivu (token-URL)
│   ├── error404.php
│   └── error500.php
├── actions/                 # POST-käsittelijät
│   ├── upload_document.php
│   ├── save_signers.php
│   ├── send_request.php
│   ├── sign_document.php
│   ├── reject_document.php
│   └── remind_signer.php
├── assets/
│   ├── css/style.css        # Täysi SaaS-tyylinen CSS (ei frameworkia)
│   └── js/app.js            # jQuery UI-interaktiot
├── uploads/                 # Ladatut tiedostot (www-tunnus EI saa selata)
├── logs/
│   └── app.log
├── scripts/
│   └── check_project.php   # QA-tarkistustyökalu
├── legacy-react-demo/       # Vanha React/Vite-koodi (referenssi)
├── installer.lock           # Luodaan onnistuneen asennuksen jälkeen
└── .htaccess
```

## Nopea aloitus

```bash
# 1. Asenna PHP 8.3 + MariaDB
apt install php8.3-cli php8.3-mysql php8.3-mbstring

# 2. Luo tietokanta ja käyttäjä
mysql -u root -e "CREATE DATABASE IF NOT EXISTS allekirjoituspalvelu CHARACTER SET utf8mb4;
  CREATE USER IF NOT EXISTS 'app'@'127.0.0.1' IDENTIFIED BY 'app';
  GRANT ALL ON allekirjoituspalvelu.* TO 'app'@'127.0.0.1';"

# 3. Käynnistä kehityspalvelin
/usr/bin/php -S 127.0.0.1:8000 -t .

# 4. Aja asentaja
open http://127.0.0.1:8000/installer.php

# 5. Tarkista projektin tila
/usr/bin/php scripts/check_project.php
```

## Ympäristömuuttujat

| Muuttuja   | Oletus              | Kuvaus                |
|------------|---------------------|-----------------------|
| DB_HOST    | 127.0.0.1           | Tietokantapalvelin    |
| DB_PORT    | 3306                | Tietokantaportti      |
| DB_NAME    | allekirjoituspalvelu| Tietokannan nimi      |
| DB_USER    | app                 | Tietokantakäyttäjä    |
| DB_PASS    | app                 | Tietokantasalasana    |
| APP_ENV    | development         | development/production|
| APP_URL    | http://127.0.0.1:8000 | Sovelluksen URL     |

## Tietokantarakenne

6 taulua: `documents`, `signers`, `signing_tokens`, `document_events`, `users`, `settings`

## Turvallisuus

- CSRF-tokenit kaikissa POST-lomakkeissa
- PDO prepared statements kaikkialle
- Tiedoston MIME-validointi `finfo`-kirjastolla
- `uploads/` estää PHP-ajon `.htaccess`-tiedostolla
- Security headers (X-Frame, X-Content-Type, XSS-Protection)
- Allekirjoituslinkit vanhenevat 72 tunnin kuluttua


SaaS-tyylinen demo sähköiseen dokumenttien allekirjoittamiseen.

Nykyinen toteutus on migroitu PHP + MySQL + jQuery -pinolle.

## Stack

- Backend: PHP 8+, PDO (MySQL)
- Frontend: HTML + CSS + jQuery
- Tietokanta: MySQL / MariaDB
- Turvallisuus: CSRF-tokenit, output escaping, prepared statements, PDF-uploadin tarkistukset

## Pääkansiot

- `index.php` = sovelluksen pääentry ja sivurenderöinti
- `installer.php` = idempotentti tietokanta-asennin
- `api/` = JSON-endpointit
- `includes/` = config, bootstrap, security, repository, db
- `assets/css/style.css` = UI-tyylit
- `assets/js/app.js` = jQuery-toiminnallisuudet
- `uploads/` = tallennetut PDF-tiedostot satunnaisella nimellä
- `logs/app.log` = sovellusloki

## Asennus

1. Varmista, että PHP ja MySQL ovat saatavilla.
2. Aseta tarvittaessa ympäristömuuttujat:
	 - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`
3. Käynnistä PHP-palvelin:

```bash
php -S 0.0.0.0:8000
```

4. Aja installer selaimessa:

```text
http://localhost:8000/installer.php
```

5. Avaa sovellus:

```text
http://localhost:8000/index.php?page=dashboard
```

## NPM-komennot

`package.json` sisältää nyt vain hyötykomennot:

```bash
npm run serve
npm run check:php
```

## Turvallisuus

- Kaikki SQL-kyselyt prepared statementeina PDO:lla
- CSRF-suojaus kaikissa POST-lomakkeissa
- XSS-suojaus output escapingilla (`h`)
- Upload-suojaus:
	- vain PDF-extension
	- MIME-tyyppi `application/pdf`
	- kokoraja 20 Mt
	- tallennus vain `uploads/`-kansioon satunnaistetulla nimellä

## Demo-rajaus

Pankkitunnistautuminen on tarkoituksella placeholder/demo-tasolla.
Se ei integroi oikeita pankkeja eikä tee oikeaa vahvaa tunnistautumista.

## Legacy-huomio

Vanha React/Vite toteutus on edelleen lähdekoodissa kansiossa `src/` ja tiedostoissa kuten `vite.config.js`.
Nämä ovat legacy-artefakteja vertailua varten, eivätkä ole aktiivisessa ajossa.

## Tarkistukset

Suorita automatisoidut PHP-tarkistukset:

```bash
bash scripts/checks.sh
```

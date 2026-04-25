# Allekirjoituspalvelu

Moderni SaaS-verkkosovellus sähköiseen dokumenttien allekirjoittamiseen.

## Ominaisuudet

- 📄 **PDF-lataus** – Drag & drop -toiminnolla
- 👁️ **PDF-esikatselu** – Selaimessa suoraan
- �� **Allekirjoittajien hallinta** – Lisää sähköpostilla
- 📧 **Allekirjoituspyynnöt** – Automaattiset sähköpostit
- 📊 **Dokumenttien tila** – Odottaa / Allekirjoitettu / Hylätty
- 🔒 **Pankkitunnistautuminen** – Placeholder (OP, Nordea, Danske Bank, S-Pankki, Aktia, POP Pankki, OmaSP, Ålandsbanken)
- 📱 **Mobiiliresponsiivinen** käyttöliittymä

## Teknologiat

- [React](https://react.dev/) + [Vite](https://vitejs.dev/)
- [Tailwind CSS](https://tailwindcss.com/)
- [React Router](https://reactrouter.com/)
- [React Dropzone](https://react-dropzone.js.org/)

## Kehitysympäristö

```bash
npm install
npm run dev
```

## Tuotantobuild

```bash
npm run build
```

## Käyttöliittymänäkymät

| Näkymä | URL |
|--------|-----|
| Dashboard | `/` |
| Lataa dokumentti | `/upload` |
| Lisää allekirjoittajat | `/upload/signers` |
| Pyyntö lähetetty | `/upload/sent` |
| Dokumenttilista | `/documents` |
| Dokumentin tiedot | `/documents/:id` |
| Allekirjoitusnäkymä | `/sign/:token` |

## Huomio

Pankkitunnistautumisintegraatio (OP, Nordea, Danske Bank jne.) on toteutettu placeholder-tasolla. Varsinainen integraatio lisätään myöhemmin.

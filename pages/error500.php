<?php
declare(strict_types=1);
/** Generic 500 error page — no DB access, minimal deps */
$appName = defined('APP_NAME') ? APP_NAME : 'Allekirjoituspalvelu';
?><!DOCTYPE html>
<html lang="fi">
<head>
<meta charset="UTF-8">
<title>Palvelinvirhe — <?= htmlspecialchars($appName) ?></title>
<style>
body{font-family:system-ui,sans-serif;display:flex;align-items:center;justify-content:center;
min-height:100vh;margin:0;background:#f8f9fa;color:#333}
.box{text-align:center;padding:3rem;max-width:400px}
h1{font-size:2rem;color:#dc2626}
</style>
</head>
<body>
<div class="box">
  <div style="font-size:4rem">⚠️</div>
  <h1>500 — Palvelinvirhe</h1>
  <p>Palvelimella tapahtui odottamaton virhe. Yritä myöhemmin uudelleen.</p>
  <a href="/" style="color:#4f46e5">← Takaisin etusivulle</a>
</div>
</body>
</html>

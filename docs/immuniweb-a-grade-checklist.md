# ImmuniWeb A minosites cPanel checklist

Utolsó frissítés: 2026-05-11

Forrás: `new.localcapital.ro Website Security Test _ ImmuniWeb.pdf`, tesztidő: 2026-05-11 12:31 GMT+3.

## Kódban javított pontok

- Kötelező biztonsági HTTP fejlécek PHP-ból, root `.htaccess` és `public/.htaccess` szinten:
  - `Content-Security-Policy`
  - `Strict-Transport-Security`
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Referrer-Policy`
  - `Permissions-Policy`
  - `Cross-Origin-Opener-Policy`
  - `Cross-Origin-Resource-Policy`
  - `X-Robots-Tag`
- `Content-Security-Policy` dinamikusan, PHP-ból megy ki nonce-szal. LiteSpeed alatt ne használj `.htaccess` `Header setifempty Content-Security-Policy` sort, mert egyes konfigurációk hibás `setifempty:` HTTP headert küldenek.
- Alap HTTP method hardening: csak `GET`, `HEAD`, `POST` engedélyezett.
- Root és `public` `.htaccess` tiltja a nem engedélyezett HTTP metódusokat, hogy a cPanel dokumentumgyökér beállításától függetlenül is védjen.
- Alap URL-szintű támadásblokkolás tipikus XSS, path traversal, SQL injection és Log4Shell payloadokra.
- AI scraping védelem: ismert training/scraping botok tiltása `robots.txt`, `.htaccess` és PHP szinten.
- Publikus oldalak robot meta értéke `noai,noimageai` jelzést is kap.
- Privacy Policy link felismerhetőbb szöveggel és `rel="privacy-policy"` attribútummal, plusz alias URL-ek:
  - `/privacy`
  - `/privacy-policy`
  - `/politica-de-confidentialitate`
  - `/cookies`
  - `/cookie-policy`
- Fallback jogi oldalak, hogy `GDPR`, `Privacy Policy`, `Terms` és `Accessibility` ne legyen 404 akkor sem, ha a DB import hiányos.
- `/.well-known/security.txt` publikálva.

## A PDF riportban feltárt fő hibák

- Hiányzó security headerek: `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, `Permissions-Policy`, `Content-Security-Policy`.
- Túl sok HTTP method engedélyezett: `OPTIONS`, `DELETE`, `PUT`, `TRACK`, custom methodok.
- Privacy Policy nem volt könnyen felismerhető.
- No WAF detected.
- Server header verziót árul el: `openresty/1.29.2.3`.
- DNSSEC nincs bekapcsolva.
- Több AI crawlerhez nem volt robots/server-side korlátozás.
- ImmuniWeb fingerprinting részben blokkolt volt, ezért a riport nem teljes.

## cPanel / tárhely oldali kötelező lépések

1. Ellenőrizd a dokumentumgyökeret.
   - Ideális: a domain document rootja a projekt `public/` mappája legyen.
   - Ha ez cPanelen nem állítható, maradhat a projekt gyökere, de a root `.htaccess` fájlnak kötelezően fent kell lennie.
   - Ne a `/public/index.php` URL legyen a publikus belépési pont, hanem `https://new.localcapital.ro/`.

2. Engedélyezd a ModSecurity / WAF védelmet a domainre.
   - cPanel: `Security > ModSecurity`
   - Kapcsold be a `new.localcapital.ro` domainre.
   - Ha van OWASP CRS ruleset opció, azt is aktiváld.
   - Ha nincs WAF opció, kérd a szolgáltatótól, mert az ImmuniWeb PCI DSS 6.4 pontját alkalmazásból nem lehet teljesen javítani.

3. Kapcsold be a DNSSEC-et a DNS zónán.
   - cPanel vagy domain/DNS szolgáltató felületén.
   - Ha a DNS-t Cloudflare kezeli, ott a `DNS > Settings > DNSSEC` résznél.
   - A registrar felületén is jóvá kell hagyni/át kell venni a DS rekordot.

4. Rejtsd el a szerver verzióját, amennyire a tárhely engedi.
   - LiteSpeed/cPanel esetén ez sokszor szolgáltatói beállítás.
   - Kérhető a hostingtól: `ServerTokens ProductOnly`, `ServerSignature Off`, OpenResty verzió elrejtése.
   - Ha nginx/openresty proxy van Apache előtt, kérd: `server_tokens off;`.

5. Tiltsd szerver/proxy szinten is a veszélyes HTTP metódusokat.
   - Apache/cPanel oldalon: csak `GET`, `POST`, `HEAD`.
   - OpenResty/nginx proxyban kérhető:

```nginx
if ($request_method !~ ^(GET|POST|HEAD)$) {
    return 405;
}
```

6. Ha a security headerek továbbra sem jelennek meg a tesztben, a proxyban is be kell állítani őket.
   - OpenResty/nginx példa, szolgáltatónak küldhető:

```nginx
add_header X-Content-Type-Options "nosniff" always;
add_header X-Frame-Options "DENY" always;
add_header Referrer-Policy "strict-origin-when-cross-origin" always;
add_header Permissions-Policy "accelerometer=(), autoplay=(), camera=(), display-capture=(), encrypted-media=(), fullscreen=(self), geolocation=(), gyroscope=(), microphone=(), midi=(), payment=(), usb=(), interest-cohort=()" always;
add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self' https://www.google.com/recaptcha/; frame-src 'self' https://www.google.com/recaptcha/ https://recaptcha.google.com/recaptcha/; manifest-src 'self'; object-src 'none'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'; upgrade-insecure-requests" always;
```

7. Whitelist ImmuniWeb scanner IP tartományok, ha a riport továbbra is fingerprinting blokkolást jelez:
   - `192.175.111.224/27`
   - `64.15.129.96/27`
   - `70.38.27.240/28`
   - `72.55.136.144/28`

8. Importáld a friss adatbázis tartalmat vagy futtasd az install scriptet deploy után.
   - A jogi oldalak fallbackből is működnek, de az adminból szerkeszthető teljes tartalomhoz az SQL import szükséges.

## Újratesztelés

Deploy után:

```bash
curl -I https://new.localcapital.ro/
curl -I -X OPTIONS https://new.localcapital.ro/
curl -I -A GPTBot https://new.localcapital.ro/
curl -I https://new.localcapital.ro/privacy-policy
curl -I https://new.localcapital.ro/gdpr
curl -I https://new.localcapital.ro/politica-privind-datele-personale
curl -I https://new.localcapital.ro/.well-known/security.txt
```

Elvárt:

- `/` válaszban legyen `Content-Security-Policy`, `Strict-Transport-Security`, `X-Frame-Options`, `X-Content-Type-Options`, `Permissions-Policy`.
- `OPTIONS` ne legyen `200 OK`.
- `GPTBot` ne kapjon normál `200 OK` választ a főoldalra.
- `/privacy-policy` adjon `200 OK` választ.

Az ImmuniWeb riportot csak ezután érdemes frissíteni.

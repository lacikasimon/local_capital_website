# ImmuniWeb A minosites cPanel checklist

Utolsó frissítés: 2026-05-11

## Kódban javított pontok

- Kötelező biztonsági HTTP fejlécek PHP-ból és `public/.htaccess` szinten:
  - `Content-Security-Policy`
  - `Strict-Transport-Security`
  - `X-Frame-Options`
  - `X-Content-Type-Options`
  - `Referrer-Policy`
  - `Permissions-Policy`
  - `Cross-Origin-Opener-Policy`
  - `Cross-Origin-Resource-Policy`
- Alap HTTP method hardening: csak `GET`, `HEAD`, `POST` engedélyezett.
- Alap URL-szintű támadásblokkolás tipikus XSS, path traversal, SQL injection és Log4Shell payloadokra.
- Privacy Policy link felismerhetőbb szöveggel és `rel="privacy-policy"` attribútummal.
- Fallback jogi oldalak, hogy `GDPR`, `Privacy Policy`, `Terms` és `Accessibility` ne legyen 404 akkor sem, ha a DB import hiányos.
- `/.well-known/security.txt` publikálva.

## cPanel / tárhely oldali kötelező lépések

1. Engedélyezd a ModSecurity / WAF védelmet a domainre.
   - cPanel: `Security > ModSecurity`
   - Kapcsold be a `new.localcapital.ro` domainre.
   - Ha van OWASP CRS ruleset opció, azt is aktiváld.

2. Kapcsold be a DNSSEC-et a DNS zónán.
   - cPanel vagy domain/DNS szolgáltató felületén.
   - Ha a DNS-t Cloudflare kezeli, ott a `DNS > Settings > DNSSEC` résznél.

3. Rejtsd el a szerver verzióját, amennyire a tárhely engedi.
   - LiteSpeed/cPanel esetén ez sokszor szolgáltatói beállítás.
   - Kérhető a hostingtól: `ServerTokens ProductOnly`, `ServerSignature Off`, OpenResty/LiteSpeed verzió elrejtése.

4. Whitelist ImmuniWeb scanner IP tartományok, ha a riport továbbra is fingerprinting blokkolást jelez:
   - `192.175.111.224/27`
   - `64.15.129.96/27`
   - `70.38.27.240/28`
   - `72.55.136.144/28`

5. Importáld a friss adatbázis tartalmat vagy futtasd az install scriptet deploy után.
   - A jogi oldalak fallbackből is működnek, de az adminból szerkeszthető teljes tartalomhoz az SQL import szükséges.

## Újratesztelés

Deploy után:

```bash
curl -I https://new.localcapital.ro/
curl -I https://new.localcapital.ro/gdpr
curl -I https://new.localcapital.ro/politica-privind-datele-personale
curl -I https://new.localcapital.ro/.well-known/security.txt
```

Az ImmuniWeb riportot csak ezután érdemes frissíteni.

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Final multilingual cleanup layer. It removes Romanian legal bodies from
-- English/Hungarian pages and normalizes stored paths for localized routes.

UPDATE settings SET setting_value = 'Monday-Friday: 9:00 - 17:00' WHERE language_code = 'en' AND setting_key = 'workingHours';
UPDATE settings SET setting_value = 'Saturday-Sunday: Closed' WHERE language_code = 'en' AND setting_key = 'closedHours';
UPDATE settings SET setting_value = 'Hétfő-péntek: 9:00 - 17:00' WHERE language_code = 'hu' AND setting_key = 'workingHours';
UPDATE settings SET setting_value = 'Szombat-vasárnap: zárva' WHERE language_code = 'hu' AND setting_key = 'closedHours';
UPDATE settings SET setting_value = 'Str. Vasile Lucaciu nr. 3, Satu Mare, Románia' WHERE language_code = 'hu' AND setting_key = 'address';

UPDATE navigation SET label = 'About us', path = '/about-us' WHERE language_code = 'en' AND nav_key = 'about';
UPDATE navigation SET label = 'Loan types', path = '/loan-types' WHERE language_code = 'en' AND nav_key = 'contract';
UPDATE navigation SET label = 'Client guide', path = '/client-guide' WHERE language_code = 'en' AND nav_key = 'guide';
UPDATE navigation SET label = 'News', path = '/news' WHERE language_code = 'en' AND nav_key = 'blog';
UPDATE navigation SET label = 'Contact', path = '/contact' WHERE language_code = 'en' AND nav_key = 'contact';

UPDATE navigation SET label = 'Rólunk', path = '/rolunk' WHERE language_code = 'hu' AND nav_key = 'about';
UPDATE navigation SET label = 'Hitelek', path = '/hitelek' WHERE language_code = 'hu' AND nav_key = 'contract';
UPDATE navigation SET label = 'Ügyfél-tájékoztató', path = '/ugyfel-tajekoztato' WHERE language_code = 'hu' AND nav_key = 'guide';
UPDATE navigation SET label = 'Hírek', path = '/hirek' WHERE language_code = 'hu' AND nav_key = 'blog';
UPDATE navigation SET label = 'Kapcsolat', path = '/kapcsolat' WHERE language_code = 'hu' AND nav_key = 'contact';

UPDATE pages SET path = '/about-us' WHERE language_code = 'en' AND page_key = 'about';
UPDATE pages SET path = '/loan-types' WHERE language_code = 'en' AND page_key = 'contract';
UPDATE pages SET path = '/client-guide' WHERE language_code = 'en' AND page_key = 'guide';
UPDATE pages SET path = '/contact' WHERE language_code = 'en' AND page_key = 'contact';
UPDATE pages SET path = '/accessibility' WHERE language_code = 'en' AND page_key = 'accessibility';
UPDATE pages SET path = '/gdpr' WHERE language_code = 'en' AND page_key = 'gdpr';
UPDATE pages SET path = '/personal-data-policy' WHERE language_code = 'en' AND page_key = 'privacy';
UPDATE pages SET path = '/terms-and-conditions' WHERE language_code = 'en' AND page_key = 'terms';

UPDATE pages SET path = '/rolunk' WHERE language_code = 'hu' AND page_key = 'about';
UPDATE pages SET path = '/hitelek' WHERE language_code = 'hu' AND page_key = 'contract';
UPDATE pages SET path = '/ugyfel-tajekoztato' WHERE language_code = 'hu' AND page_key = 'guide';
UPDATE pages SET path = '/kapcsolat' WHERE language_code = 'hu' AND page_key = 'contact';
UPDATE pages SET path = '/akadalymentesites' WHERE language_code = 'hu' AND page_key = 'accessibility';
UPDATE pages SET path = '/gdpr' WHERE language_code = 'hu' AND page_key = 'gdpr';
UPDATE pages SET path = '/szemelyes-adatok-kezelese' WHERE language_code = 'hu' AND page_key = 'privacy';
UPDATE pages SET path = '/altalanos-szerzodesi-feltetelek' WHERE language_code = 'hu' AND page_key = 'terms';

UPDATE pages
SET
  title = 'GDPR',
  summary = 'Information about personal data protection and data subject rights.',
  body = '## Personal data protection

LOCAL CAPITAL IFN S.A. processes personal data according to Regulation (EU) 2016/679 (GDPR), applicable Romanian law and internal data security rules.

## Controller and contact

The data controller is LOCAL CAPITAL IFN S.A. For questions about personal data, contact info@localcapital.ro or protectiadatelor@localcapital.ro.

## Data we may process

Depending on the request, we may process identification data, contact details, information included in a credit request, communication history, documents required by law and technical data needed for website security.

## Purposes and legal bases

Data may be processed to respond to requests, assess eligibility, prepare and manage contracts, comply with legal obligations, prevent fraud, manage complaints and protect IT systems. The legal bases may include pre-contractual steps, contract performance, legal obligations, legitimate interest and consent where required.

## Retention and recipients

Personal data is kept only for the period required by the stated purpose, legal obligations or legitimate defense of rights. Data may be shared with public authorities, professional advisers and service providers when this is necessary and lawful.

## Data subject rights

You may request access, rectification, erasure, restriction, portability and objection, and you may withdraw consent where processing is based on consent. Requests can be sent to info@localcapital.ro or protectiadatelor@localcapital.ro.

## Supervisory authority

If you believe your rights were not respected, you may contact the Romanian National Supervisory Authority for Personal Data Processing.',
  extra_json = '{"aiSummary":"Local Capital explains GDPR processing, controller contact details, data subject rights, retention and lawful purposes for personal data processing.","faq":[{"question":"Who is the data controller?","answer":"The controller is LOCAL CAPITAL IFN S.A."},{"question":"Where can I send a data protection request?","answer":"Send the request to info@localcapital.ro or protectiadatelor@localcapital.ro."},{"question":"What rights do data subjects have?","answer":"Data subjects may request access, rectification, erasure, restriction, portability, objection and withdrawal of consent where applicable."}]}'
WHERE language_code = 'en' AND page_key = 'gdpr';

UPDATE pages
SET
  title = 'GDPR',
  summary = 'Tájékoztatás a személyes adatok védelméről és az érintetti jogokról.',
  body = '## Személyes adatok védelme

A LOCAL CAPITAL IFN S.A. a személyes adatokat az (EU) 2016/679 rendelet (GDPR), a román jogszabályok és a belső adatbiztonsági szabályok szerint kezeli.

## Adatkezelő és kapcsolat

Az adatkezelő a LOCAL CAPITAL IFN S.A. Személyes adatokkal kapcsolatos kérdés esetén az info@localcapital.ro vagy a protectiadatelor@localcapital.ro címen lehet kapcsolatba lépni.

## Kezelt adatok

A megkereséstől függően kezelhetők azonosító adatok, elérhetőségi adatok, hiteligénylésben szereplő információk, kommunikációs előzmények, jogszabály alapján szükséges dokumentumok és a weboldal biztonságához szükséges technikai adatok.

## Célok és jogalapok

Az adatkezelés célja lehet megkeresések megválaszolása, jogosultság vizsgálata, szerződések előkészítése és kezelése, jogi kötelezettségek teljesítése, csalásmegelőzés, panaszkezelés és informatikai rendszerek védelme. A jogalap lehet szerződéskötést megelőző lépés, szerződés teljesítése, jogi kötelezettség, jogos érdek vagy szükség esetén hozzájárulás.

## Megőrzés és címzettek

A személyes adatokat csak a célhoz, jogi kötelezettségekhez vagy jogok védelméhez szükséges ideig őrizzük meg. Az adatok jogszerűen továbbíthatók hatóságoknak, szakmai tanácsadóknak és szolgáltatóknak, ha ez szükséges.

## Érintetti jogok

Kérhető hozzáférés, helyesbítés, törlés, korlátozás, adathordozhatóság és tiltakozás, valamint visszavonható a hozzájárulás, ha az adatkezelés hozzájáruláson alapul. A kérelmek az info@localcapital.ro vagy protectiadatelor@localcapital.ro címre küldhetők.

## Felügyeleti hatóság

Ha úgy gondolod, hogy jogaid nem érvényesültek, a román adatvédelmi felügyeleti hatósághoz fordulhatsz.',
  extra_json = '{"aiSummary":"A Local Capital bemutatja a GDPR szerinti adatkezelést, az adatkezelő elérhetőségeit, az érintetti jogokat, a megőrzést és az adatkezelési célokat.","faq":[{"question":"Ki az adatkezelő?","answer":"Az adatkezelő a LOCAL CAPITAL IFN S.A."},{"question":"Hová küldhetek adatvédelmi kérelmet?","answer":"A kérelmet az info@localcapital.ro vagy protectiadatelor@localcapital.ro címre lehet küldeni."},{"question":"Milyen jogai vannak az érintetteknek?","answer":"Kérhető hozzáférés, helyesbítés, törlés, korlátozás, adathordozhatóság, tiltakozás és adott esetben a hozzájárulás visszavonása."}]}'
WHERE language_code = 'hu' AND page_key = 'gdpr';

UPDATE pages
SET
  title = 'Personal data policy',
  summary = 'Details about how Local Capital handles website, form and cookie data.',
  body = '## Website privacy notice

This notice explains how LOCAL CAPITAL IFN S.A. handles personal data sent through the website, including contact form data and technical data needed for safe operation.

## Contact form data

When you send a message, we may process your name, email address, phone number, subject, message content, consent confirmation, IP security metadata and the language of the page used.

## Cookies and preferences

The website uses necessary cookies for operation, security, language preferences and consent storage. Optional cookies are used only after consent, when such features are enabled.

## Why we process data

Data is used to answer requests, provide information about credit steps, protect the website, prevent abuse, maintain records required by law and manage potential complaints.

## Security

We use technical and organizational measures such as secure sessions, CSRF protection, input validation, rate limiting and access controls for the administration area.

## Contact

For privacy questions, write to info@localcapital.ro or protectiadatelor@localcapital.ro.',
  extra_json = '{"aiSummary":"Local Capital describes website privacy handling for contact forms, cookies, security data, lawful purposes and privacy contact channels.","faq":[{"question":"What data is sent through the contact form?","answer":"The form can send name, email, phone, subject, message, consent confirmation and security metadata."},{"question":"Are optional cookies used before consent?","answer":"No. Optional cookies are used only after consent if optional features are enabled."},{"question":"How can I ask a privacy question?","answer":"Write to info@localcapital.ro or protectiadatelor@localcapital.ro."}]}'
WHERE language_code = 'en' AND page_key = 'privacy';

UPDATE pages
SET
  title = 'Személyes adatok kezelése',
  summary = 'Részletek arról, hogyan kezeli a Local Capital a weboldal, űrlap és cookie adatokat.',
  body = '## Weboldali adatvédelmi tájékoztató

Ez a tájékoztató bemutatja, hogyan kezeli a LOCAL CAPITAL IFN S.A. a weboldalon keresztül küldött személyes adatokat, beleértve a kapcsolatfelvételi űrlap adatait és a biztonságos működéshez szükséges technikai adatokat.

## Kapcsolatfelvételi űrlap adatai

Üzenet küldésekor kezelhető a név, e-mail cím, telefonszám, tárgy, üzenet tartalma, hozzájárulási jelzés, IP biztonsági metaadat és a használt oldal nyelve.

## Sütik és beállítások

A weboldal a működéshez, biztonsághoz, nyelvi beállításokhoz és hozzájárulás tárolásához szükséges sütiket használ. Opcionális sütik csak hozzájárulás után használhatók, ha ilyen funkció aktív.

## Miért kezelünk adatokat

Az adatokat megkeresések megválaszolására, hiteligénylési lépések ismertetésére, a weboldal védelmére, visszaélések megelőzésére, jogszabályban előírt nyilvántartások vezetésére és esetleges panaszok kezelésére használjuk.

## Biztonság

Technikai és szervezési intézkedéseket alkalmazunk, például biztonságos munkameneteket, CSRF védelmet, bemeneti adatok ellenőrzését, forgalomkorlátozást és adminisztrációs hozzáférés-szabályozást.

## Kapcsolat

Adatvédelmi kérdés esetén írj az info@localcapital.ro vagy protectiadatelor@localcapital.ro címre.',
  extra_json = '{"aiSummary":"A Local Capital bemutatja a weboldali adatkezelést a kapcsolatfelvételi űrlapra, sütikre, biztonsági adatokra, célokra és adatvédelmi kapcsolatra vonatkozóan.","faq":[{"question":"Milyen adatot küld a kapcsolatfelvételi űrlap?","answer":"Az űrlap nevet, e-mail címet, telefonszámot, tárgyat, üzenetet, hozzájárulási jelzést és biztonsági metaadatot küldhet."},{"question":"Használ az oldal opcionális sütiket hozzájárulás előtt?","answer":"Nem. Opcionális sütik csak hozzájárulás után használhatók, ha ilyen funkció aktív."},{"question":"Hogyan kérdezhetek adatvédelemről?","answer":"Írj az info@localcapital.ro vagy protectiadatelor@localcapital.ro címre."}]}'
WHERE language_code = 'hu' AND page_key = 'privacy';

UPDATE pages
SET
  title = 'Terms and conditions',
  summary = 'General terms for using the Local Capital website and information pages.',
  body = '## Terms and conditions

The information published on this website is for general information. It does not represent guaranteed credit approval, a personalized offer, financial advice or a contractual commitment by itself.

## Website use

Users must use the website lawfully and must not send abusive, automated, misleading or security-impacting content. Attempts to access the administration area without authorization are prohibited.

## Credit information

Any credit decision is based on the request analysis, eligibility checks and documents communicated before contract signing. The client should review the amount, duration, APR, total cost, fees, repayment obligations and consequences of late payment before signing.

## Accuracy and updates

LOCAL CAPITAL IFN S.A. aims to keep public information clear and current, but details may change. The personalized offer and signed contractual documents prevail over general website information.

## Intellectual property

Texts, structure, logos and visual elements on the website are protected and may not be reused in a misleading or unauthorized way.

## Security

The website may block malicious requests, automated abuse, suspicious login attempts and traffic that affects the integrity of the service.',
  extra_json = '{"aiSummary":"Local Capital terms explain lawful website use, credit information limits, security rules, updates, intellectual property and the need to review the personalized offer before signing.","faq":[{"question":"Does the website guarantee credit approval?","answer":"No. Website information is general and does not guarantee approval or replace a personalized offer."},{"question":"What should I check before signing?","answer":"Check the amount, duration, APR, total cost, fees, repayment obligations and late payment consequences."},{"question":"Can automated abusive traffic be blocked?","answer":"Yes. Malicious requests and abusive automated traffic may be blocked for security."}]}'
WHERE language_code = 'en' AND page_key = 'terms';

UPDATE pages
SET
  title = 'Általános szerződési feltételek',
  summary = 'A Local Capital weboldal és információs oldalak használatának általános feltételei.',
  body = '## Általános szerződési feltételek

A weboldalon közzétett információk általános tájékoztatásra szolgálnak. Önmagukban nem jelentenek garantált hiteljóváhagyást, személyre szabott ajánlatot, pénzügyi tanácsadást vagy szerződéses kötelezettségvállalást.

## Weboldal használata

A felhasználók kötelesek a weboldalt jogszerűen használni, és nem küldhetnek visszaélésszerű, automatizált, félrevezető vagy a biztonságot veszélyeztető tartalmat. Az adminisztrációs felület jogosulatlan elérése tilos.

## Hitelinformációk

Bármely hiteldöntés a kérelem elemzésén, jogosultsági ellenőrzéseken és az aláírás előtt közölt dokumentumokon alapul. Az ügyfélnek aláírás előtt ellenőriznie kell az összeget, futamidőt, DAE/THM jellegű mutatót, teljes költséget, díjakat, törlesztési kötelezettségeket és késedelmes fizetés következményeit.

## Pontosság és frissítések

A LOCAL CAPITAL IFN S.A. törekszik arra, hogy a nyilvános információk világosak és aktuálisak legyenek, de a részletek változhatnak. A személyre szabott ajánlat és az aláírt szerződéses dokumentumok elsőbbséget élveznek az általános weboldali információkkal szemben.

## Szellemi tulajdon

A weboldalon található szövegek, szerkezet, logók és vizuális elemek védelem alatt állnak, és nem használhatók fel félrevezető vagy jogosulatlan módon.

## Biztonság

A weboldal blokkolhatja a rosszindulatú kéréseket, automatizált visszaéléseket, gyanús bejelentkezési próbálkozásokat és a szolgáltatás épségét veszélyeztető forgalmat.',
  extra_json = '{"aiSummary":"A Local Capital feltételei ismertetik a jogszerű weboldalhasználatot, a hitelinformációk korlátait, biztonsági szabályokat, frissítéseket, szellemi tulajdont és a személyre szabott ajánlat ellenőrzésének szükségességét.","faq":[{"question":"Garantál a weboldal hiteljóváhagyást?","answer":"Nem. A weboldali információ általános tájékoztatás, és nem garantál jóváhagyást vagy személyre szabott ajánlatot."},{"question":"Mit ellenőrizzek aláírás előtt?","answer":"Ellenőrizd az összeget, futamidőt, DAE/THM jellegű mutatót, teljes költséget, díjakat, törlesztési kötelezettségeket és késedelmi következményeket."},{"question":"Blokkolható az automatizált visszaélés?","answer":"Igen. A rosszindulatú kérések és automatizált visszaélések biztonsági okból blokkolhatók."}]}'
WHERE language_code = 'hu' AND page_key = 'terms';

UPDATE posts SET path = '/articles/fast-credit-with-identity-card' WHERE language_code = 'en' AND source_type = 'post' AND slug = 'credit-rapid-cu-buletinul';
UPDATE posts SET path = '/cikkek/gyors-hitel-szemelyi-igazolvannyal' WHERE language_code = 'hu' AND source_type = 'post' AND slug = 'credit-rapid-cu-buletinul';

UPDATE posts SET path = '/services/new-car-loan' WHERE language_code = 'en' AND source_type = 'service' AND slug = 'auto-car-loan';
UPDATE posts SET path = '/services/projects-and-investments-loan' WHERE language_code = 'en' AND source_type = 'service' AND slug = 'business-loan';
UPDATE posts SET path = '/services/education-loan' WHERE language_code = 'en' AND source_type = 'service' AND slug = 'education-loan';
UPDATE posts SET path = '/services/personal-loan' WHERE language_code = 'en' AND source_type = 'service' AND slug = 'personal-loan';
UPDATE posts SET path = '/services/home-improvement-loan' WHERE language_code = 'en' AND source_type = 'service' AND slug = 'property-loan';
UPDATE posts SET path = '/services/family-events-loan' WHERE language_code = 'en' AND source_type = 'service' AND slug = 'wedding-loan';

UPDATE posts SET path = '/szolgaltatasok/uj-auto-hitel' WHERE language_code = 'hu' AND source_type = 'service' AND slug = 'auto-car-loan';
UPDATE posts SET path = '/szolgaltatasok/projektek-es-befektetesek-hitele' WHERE language_code = 'hu' AND source_type = 'service' AND slug = 'business-loan';
UPDATE posts SET path = '/szolgaltatasok/oktatasi-hitel' WHERE language_code = 'hu' AND source_type = 'service' AND slug = 'education-loan';
UPDATE posts SET path = '/szolgaltatasok/szemelyi-kolcson' WHERE language_code = 'hu' AND source_type = 'service' AND slug = 'personal-loan';
UPDATE posts SET path = '/szolgaltatasok/lakasfelujitasi-hitel' WHERE language_code = 'hu' AND source_type = 'service' AND slug = 'property-loan';
UPDATE posts SET path = '/szolgaltatasok/csaladi-esemenyek-hitele' WHERE language_code = 'hu' AND source_type = 'service' AND slug = 'wedding-loan';

UPDATE posts
SET path = '/case-studies/business-planning',
    title = 'Business planning',
    excerpt = 'Archive page imported from the old Local Capital website and cleaned of WordPress navigation elements.',
    body = '## Business planning

This page is kept as part of the old Local Capital website archive. Navigation, sidebars, comments and WordPress forms were removed so the page remains readable in the new CMS.

For current information about Local Capital credit solutions, contact our team.'
WHERE language_code = 'en' AND source_type = 'case_study' AND slug = 'business-planning';

UPDATE posts
SET path = '/case-studies/business-partnerships',
    title = 'Business partnerships',
    excerpt = 'Archive page imported from the old Local Capital website and cleaned of WordPress navigation elements.',
    body = '## Business partnerships

This page is kept as part of the old Local Capital website archive. Navigation, sidebars, comments and WordPress forms were removed so the page remains readable in the new CMS.

For current information about Local Capital credit solutions, contact our team.'
WHERE language_code = 'en' AND source_type = 'case_study' AND slug = 'business-tie-ups';

UPDATE posts
SET path = '/case-studies/mergers-and-acquisitions',
    title = 'Mergers and acquisitions',
    excerpt = 'Archive page imported from the old Local Capital website and cleaned of WordPress navigation elements.',
    body = '## Mergers and acquisitions

This page is kept as part of the old Local Capital website archive. Navigation, sidebars, comments and WordPress forms were removed so the page remains readable in the new CMS.

For current information about Local Capital credit solutions, contact our team.'
WHERE language_code = 'en' AND source_type = 'case_study' AND slug = 'meger-acquistion';

UPDATE posts
SET path = '/case-studies/personal-banking',
    title = 'Personal banking',
    excerpt = 'Archive page imported from the old Local Capital website and cleaned of WordPress navigation elements.',
    body = '## Personal banking

This page is kept as part of the old Local Capital website archive. Navigation, sidebars, comments and WordPress forms were removed so the page remains readable in the new CMS.

For current information about Local Capital credit solutions, contact our team.'
WHERE language_code = 'en' AND source_type = 'case_study' AND slug = 'personal-banking';

UPDATE posts
SET path = '/esettanulmanyok/uzleti-tervezes',
    title = 'Üzleti tervezés',
    excerpt = 'A régi Local Capital weboldalról importált archív oldal, WordPress navigációs elemek nélkül.',
    body = '## Üzleti tervezés

Ez az oldal a régi Local Capital weboldal archívumának részeként maradt meg. A navigációt, oldalsávokat, hozzászólásokat és WordPress űrlapokat eltávolítottuk, hogy az oldal az új CMS-ben is olvasható legyen.

A Local Capital aktuális hitelezési megoldásairól kérj tájékoztatást csapatunktól.'
WHERE language_code = 'hu' AND source_type = 'case_study' AND slug = 'business-planning';

UPDATE posts
SET path = '/esettanulmanyok/uzleti-egyuttmukodesek',
    title = 'Üzleti együttműködések',
    excerpt = 'A régi Local Capital weboldalról importált archív oldal, WordPress navigációs elemek nélkül.',
    body = '## Üzleti együttműködések

Ez az oldal a régi Local Capital weboldal archívumának részeként maradt meg. A navigációt, oldalsávokat, hozzászólásokat és WordPress űrlapokat eltávolítottuk, hogy az oldal az új CMS-ben is olvasható legyen.

A Local Capital aktuális hitelezési megoldásairól kérj tájékoztatást csapatunktól.'
WHERE language_code = 'hu' AND source_type = 'case_study' AND slug = 'business-tie-ups';

UPDATE posts
SET path = '/esettanulmanyok/fuzio-es-felvasarlas',
    title = 'Fúziók és felvásárlások',
    excerpt = 'A régi Local Capital weboldalról importált archív oldal, WordPress navigációs elemek nélkül.',
    body = '## Fúziók és felvásárlások

Ez az oldal a régi Local Capital weboldal archívumának részeként maradt meg. A navigációt, oldalsávokat, hozzászólásokat és WordPress űrlapokat eltávolítottuk, hogy az oldal az új CMS-ben is olvasható legyen.

A Local Capital aktuális hitelezési megoldásairól kérj tájékoztatást csapatunktól.'
WHERE language_code = 'hu' AND source_type = 'case_study' AND slug = 'meger-acquistion';

UPDATE posts
SET path = '/esettanulmanyok/szemelyes-penzugyek',
    title = 'Személyes pénzügyek',
    excerpt = 'A régi Local Capital weboldalról importált archív oldal, WordPress navigációs elemek nélkül.',
    body = '## Személyes pénzügyek

Ez az oldal a régi Local Capital weboldal archívumának részeként maradt meg. A navigációt, oldalsávokat, hozzászólásokat és WordPress űrlapokat eltávolítottuk, hogy az oldal az új CMS-ben is olvasható legyen.

A Local Capital aktuális hitelezési megoldásairól kérj tájékoztatást csapatunktól.'
WHERE language_code = 'hu' AND source_type = 'case_study' AND slug = 'personal-banking';

UPDATE site_links SET label = 'Apply now' WHERE language_code = 'en' AND label = 'Aplica acum';
UPDATE site_links SET label = 'Download here' WHERE language_code = 'en' AND label = 'Descarcă aici';
UPDATE site_links SET label = 'About us' WHERE language_code = 'en' AND label = 'Despre noi';
UPDATE site_links SET label = 'Loan types' WHERE language_code = 'en' AND label = 'Contract';
UPDATE site_links SET label = 'Personal data policy' WHERE language_code = 'en' AND label = 'Politica privind datele personale';
UPDATE site_links SET label = 'Terms and conditions' WHERE language_code = 'en' AND label = 'Termene și condiții';
UPDATE site_links SET label = 'Consumer protection - ANPC' WHERE language_code = 'en' AND label IN ('Protectia consumatorilor – ANPC', 'Protecția consumatorilor – ANPC');
UPDATE site_links SET label = 'Data retention policy' WHERE language_code = 'en' AND label = 'Politica de retentie a datelor cu caracter personal.';

UPDATE site_links SET label = 'Kezdőlap' WHERE language_code = 'hu' AND label IN ('Acasă', 'Acasa');
UPDATE site_links SET label = 'Igénylés most' WHERE language_code = 'hu' AND label = 'Aplica acum';
UPDATE site_links SET label = 'Letöltés' WHERE language_code = 'hu' AND label = 'Descarcă aici';
UPDATE site_links SET label = 'Rólunk' WHERE language_code = 'hu' AND label = 'Despre noi';
UPDATE site_links SET label = 'Hitelek' WHERE language_code = 'hu' AND label = 'Contract';
UPDATE site_links SET label = 'Kapcsolat' WHERE language_code = 'hu' AND label = 'Contact';
UPDATE site_links SET label = 'Személyes adatok kezelése' WHERE language_code = 'hu' AND label = 'Politica privind datele personale';
UPDATE site_links SET label = 'Általános szerződési feltételek' WHERE language_code = 'hu' AND label = 'Termene și condiții';
UPDATE site_links SET label = 'Fogyasztóvédelem - ANPC' WHERE language_code = 'hu' AND label IN ('Protectia consumatorilor – ANPC', 'Protecția consumatorilor – ANPC');
UPDATE site_links SET label = 'Előző' WHERE language_code = 'hu' AND label = 'Previous';
UPDATE site_links SET label = 'Következő' WHERE language_code = 'hu' AND label = 'Next';
UPDATE site_links SET label = 'Személyi kölcsön' WHERE language_code = 'hu' AND label = 'Personal Loan';
UPDATE site_links SET label = 'Oktatási hitel' WHERE language_code = 'hu' AND label = 'Education Loan';
UPDATE site_links SET label = 'Hitel új autóra' WHERE language_code = 'hu' AND label = 'Auto Car Loan';
UPDATE site_links SET label = 'Hitel projektekhez és befektetésekhez' WHERE language_code = 'hu' AND label = 'Business Loan';
UPDATE site_links SET label = 'Lakásfelújítási hitel' WHERE language_code = 'hu' AND label = 'Home Loan';
UPDATE site_links SET label = 'Hitel családi eseményekre' WHERE language_code = 'hu' AND label = 'Wedding Loan';
UPDATE site_links SET label = 'Adatmegőrzési szabályzat' WHERE language_code = 'hu' AND label = 'Politica de retentie a datelor cu caracter personal.';

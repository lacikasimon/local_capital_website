SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

INSERT INTO settings (language_code, setting_key, setting_value) VALUES
('ro', 'tagline', 'Credit simplu și rapid'),
('ro', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, România'),
('ro', 'closedHours', 'S-D: Închis'),
('ro', 'footerText', 'Creditul simplu și rapid este principala caracteristică a Local Capital, oferind clienților acces la resurse financiare fără birocrație inutilă.'),
('ro', 'anpcLabel', 'Protecția consumatorilor - ANPC')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

UPDATE navigation
SET label = 'Acasă'
WHERE language_code = 'ro'
  AND nav_key = 'home';

UPDATE navigation
SET label = 'Noutăți'
WHERE language_code = 'ro'
  AND nav_key = 'blog';

UPDATE pages
SET
  title = 'Credit rapid și simplu pentru independență financiară',
  summary = 'Credite pentru nevoi personale, cu proces simplu, rambursare flexibilă și bani transferați direct pe card.',
  body = '## Visul tău devine realitate cu Local Capital

Ai visuri mari și obiective financiare clare? Cu Local Capital, obții rapid credite și soluții personalizate pentru educație, achiziția unei locuințe, investiții inteligente sau consolidarea datoriilor existente.

Decizia ta de azi definește ziua de mâine. Contactează-ne astăzi pentru a ne ajuta să-ți îndeplinim visurile.',
  cta_label = 'Aplică acum',
  cta_href = '/contact',
  secondary_cta_label = 'Vezi creditele',
  secondary_cta_href = '/contract',
  extra_json = '{"featuresTitle":"Avantaje Local Capital","features":[{"title":"Rambursare simplă","text":"Ai libertatea de a alege metoda de plată a creditului tău, inclusiv prin serviciul PayPoint. Local Capital îți oferă soluții rapide și simple pentru rambursare."},{"title":"Flexibilitate","text":"Cu grijă și atenție la situația ta financiară, Local Capital îți pune la dispoziție opțiuni de rambursare flexibile și adaptate stilului tău de viață."},{"title":"Direct pe card","text":"Primești banii rapid, direct în contul tău bancar. Local Capital îți transferă fondurile în siguranță și eficiență, astfel încât să le ai la îndemână într-o clipă."},{"title":"Credite rapide","text":"La Local Capital efectuăm evaluări rapide și eficiente, oferindu-ți posibilitatea să obții creditul de care ai nevoie în cel mai scurt timp posibil."}],"servicesTitle":"Oferim cele mai bune servicii de creditare","servicesIntro":"Indiferent de natura proiectului sau visului dumneavoastră, Local Capital este aici pentru a vă oferi sprijinul financiar de care aveți nevoie pentru a vă transforma aspirațiile în realitate.","services":[{"title":"Credite rapide pentru sănătate și familie","text":"Ai nevoie de bani pentru sănătate sau familie? Local Capital îți oferă soluții rapide și flexibile pentru cheltuieli medicale și nevoi urgente.","image":"/assets/service-health.jpg"},{"title":"Credite pentru evenimente de familie","text":"Fie că vrei o vacanță, un cadou pentru cei dragi sau să acoperi cheltuieli neprevăzute, Local Capital îți oferă credit simplu și rapid pentru familie.","image":"/assets/service-home.jpg"},{"title":"Credite pentru locuință","text":"Transformă-ți locuința așa cum îți dorești. Cu Local Capital poți obține rapid un credit pentru renovare, reamenajare sau îmbunătățiri în casa ta.","image":"/assets/service-home.jpg"},{"title":"Credite rapide pentru frumusețe și îngrijire personală","text":"Vrei să-ți îmbunătățești rapid aspectul fizic și nu ai fondurile necesare? Cu Local Capital obții credite rapide pentru a investi în frumusețea și încrederea ta.","image":"/assets/service-health.jpg"},{"title":"Credite pentru cadouri și surprize","text":"Surprinde-ți familia și prietenii cu cadouri speciale, fără grija banilor. Local Capital îți oferă soluții rapide pentru a face bucurii celor dragi.","image":"/assets/service-home.jpg"},{"title":"Credit pentru mașină nouă","text":"Transformă-ți visul de a conduce o mașină nouă în realitate. Cu Local Capital obții finanțare rapidă și avantajoasă pentru achiziția autoturismului dorit.","image":"/assets/service-car.jpg"}],"requirementsTitle":"Asta este tot ce ai nevoie pentru a avea totul","requirements":[{"title":"Credit rapid doar cu buletinul","text":"Pentru a accesa creditul rapid, ai nevoie doar de un act de identitate. Fără complicații, fără documente suplimentare, simplu și eficient."},{"title":"Credit simplu cu factură curentă","text":"Aplică pentru credit ușor și sigur. Este suficient să furnizezi o factură curentă pentru verificare, iar noi îți evaluăm eligibilitatea imediat."},{"title":"Bani în doar 2 ore","text":"Cu Local Capital, primești banii direct în contul tău bancar în maximum 2 ore de la solicitare. Rapid, sigur și fără complicații."}],"sourceUrl":"https://localcapital.ro/","sourceModified":"2026-03-24T14:41:46"}'
WHERE page_key = 'home'
  AND language_code = 'ro';

UPDATE pages
SET
  title = 'Despre Local Capital',
  summary = 'Local Capital este o instituție financiară nebancară din România, specializată în credite rapide și flexibile pentru persoane fizice.',
  body = '## Compania noastră

Local Capital este o instituție financiară nebancară din România, specializată în credite rapide și flexibile pentru persoane fizice. Oferim soluții simple și personalizate pentru nevoi urgente, cu aprobare rapidă și proces transparent.

Suntem o companie IFN ce oferă servicii financiare flexibile, adaptate nevoilor dumneavoastră. Procesul este gândit să fie simplu și accesibil, iar echipa noastră te ajută să înțelegi opțiunile disponibile.

## Ce oferim

- Sume flexibile, adaptate nevoii tale.
- Proces rapid, cu pași clari.
- Aprobare simplă și comunicare transparentă.
- Opțiuni de rambursare flexibile.

## Misiunea noastră

Misiunea Local Capital este să ofere sprijin financiar în momente cheie și să fie partenerul care face diferența atunci când apare o provocare. Activitatea noastră este ghidată de integritate, responsabilitate și claritate.',
  extra_json = '{"valuesTitle":"Valorile noastre","values":[{"title":"Integritate","text":"Acționăm corect și transparent în relația cu fiecare client."},{"title":"Responsabilitate","text":"Analizăm fiecare solicitare cu atenție și respect față de situația financiară a clientului."},{"title":"Claritate","text":"Explicăm condițiile și pașii procesului într-un limbaj ușor de înțeles."}]}'
WHERE page_key = 'about'
  AND language_code = 'ro';

UPDATE pages
SET
  title = 'Tipuri de credite oferite',
  summary = 'Produse de credit create pentru obiective financiare diferite, cu opțiuni de rambursare clare.',
  body = '## Pachete de finanțare

Cu devotamentul nostru de a face finanțarea mai accesibilă, prezentăm produse de credit create pentru cerințe și preferințe diferite. Scopul nostru este să te ajutăm să alegi varianta potrivită pentru planurile tale.

Indiferent de pachetul ales, îți oferim opțiuni flexibile, adaptate pentru a-ți atinge obiectivele financiare cu ușurință și încredere.

## Control asupra rambursării

Pachetul de finanțare este conceput pentru a-ți oferi control asupra gestionării împrumutului. Poți discuta opțiuni de rambursare și perioade adaptate bugetului tău.

Echipa Local Capital îți oferă asistență în fiecare etapă a procesului, astfel încât alegerea finală să fie clară și potrivită situației tale.',
  extra_json = '{"products":[{"title":"Credit Flex Basic","text":"O opțiune pentru cei care vor să se concentreze pe rambursarea mai rapidă a împrumutului."},{"title":"Credit Flex Basic PMT","text":"O variantă pentru cei care preferă rate constante pe durata perioadei de rambursare."},{"title":"Credit Flex Juridic","text":"O soluție gândită pentru nevoi specifice, cu discuție personalizată înainte de ofertare."}],"featuresTitle":"Avantaje","features":[{"title":"Rate lunare personalizate","text":"Putem discuta rate adaptate bugetului tău, pentru plăți mai ușor de gestionat."},{"title":"Perioade de rambursare variabile","text":"Durata împrumutului poate fi aleasă în funcție de planurile tale financiare."}]}'
WHERE page_key = 'contract'
  AND language_code = 'ro';

UPDATE pages
SET
  title = 'Contactează-ne',
  summary = 'Suntem aici pentru tine. Scrie-ne sau sună-ne pentru informații despre credite rapide și soluții de finanțare.',
  body = '## Ai întrebări?

Pentru informații despre eligibilitate, produse de creditare sau pașii următori, ne poți contacta telefonic, prin email sau la punctul nostru de lucru.

Consultanții Local Capital vor răspunde în cel mai scurt timp la întrebările tale, în timpul programului de lucru.',
  extra_json = '{"formTitle":"Trimite un mesaj","privacyNote":"Prin trimiterea unui email, confirmi că ai citit informarea privind prelucrarea datelor personale."}'
WHERE page_key = 'contact'
  AND language_code = 'ro';

UPDATE pages
SET summary = 'Politica GDPR și informații despre prelucrarea datelor cu caracter personal în cadrul Local Capital IFN S.A.'
WHERE page_key = 'gdpr'
  AND language_code = 'ro';

UPDATE pages
SET summary = 'Informare privind drepturile persoanelor vizate și modul în care Local Capital IFN S.A. prelucrează datele personale.'
WHERE page_key = 'privacy'
  AND language_code = 'ro';

UPDATE pages
SET summary = 'Termene, condiții și informații privind utilizarea website-ului Local Capital.'
WHERE page_key = 'terms'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit rapid doar cu buletinul',
  excerpt = 'Ce înseamnă o solicitare simplă și ce documente sunt utile pentru o evaluare rapidă.',
  body = '## Credit rapid doar cu buletinul

Un credit rapid începe cu o solicitare clară și cu date de identificare corecte. În etapa inițială, actul de identitate este documentul principal necesar pentru verificarea informațiilor.

Echipa Local Capital îți explică pașii, condițiile și opțiunile disponibile, astfel încât decizia să fie luată informat.',
  published = 1
WHERE source_type = 'post'
  AND slug = 'credit-rapid-cu-buletinul'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit pentru nevoi personale',
  excerpt = 'Credit simplu și rapid pentru cheltuieli urgente, familie, sănătate sau alte nevoi personale.',
  body = '## Credit pentru nevoi personale

Ai nevoie de sprijin rapid pentru familie, sănătate, cadouri, vacanțe sau cheltuieli neprevăzute? Local Capital îți oferă un proces simplu, cu pași clari și răspuns rapid.

## Cum funcționează

Pentru solicitarea inițială ai nevoie de un act de identitate valid, date de contact corecte și, atunci când este necesar, o factură curentă pentru verificare. După aprobare, banii pot fi transferați direct pe card.',
  published = 1
WHERE source_type = 'service'
  AND slug = 'personal-loan'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit pentru educație',
  excerpt = 'Finanțare pentru cursuri, specializări, taxe sau alte cheltuieli legate de dezvoltarea personală.',
  body = '## Credit pentru educație

Educația este o investiție importantă. Local Capital te poate ajuta să acoperi costuri precum cursuri, specializări, taxe, materiale sau alte cheltuieli necesare pentru dezvoltarea ta.

## Sprijin flexibil

Discutăm nevoia ta, verificăm eligibilitatea și îți prezentăm o variantă de rambursare clară, adaptată bugetului disponibil.',
  published = 1
WHERE source_type = 'service'
  AND slug = 'education-loan'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit pentru proiecte și investiții',
  excerpt = 'Soluții de finanțare pentru obiective importante, investiții personale sau consolidarea datoriilor existente.',
  body = '## Credit pentru proiecte și investiții

Cu Local Capital poți obține sprijin pentru planuri importante: investiții personale, consolidarea unor datorii existente sau proiecte care au nevoie de finanțare rapidă.

## Decizie clară

Îți explicăm condițiile, verificăm datele necesare și îți oferim o imagine transparentă asupra costurilor și perioadei de rambursare.',
  published = 1
WHERE source_type = 'service'
  AND slug = 'business-loan'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit pentru locuință',
  excerpt = 'Finanțare rapidă pentru renovare, reamenajare sau îmbunătățiri în casa ta.',
  body = '## Credit pentru locuință

Transformă-ți locuința așa cum îți dorești. Local Capital îți poate oferi sprijin pentru renovare, reamenajare, reparații sau îmbunătățiri necesare în casă.

## Proces simplu

Spune-ne ce plan ai, verificăm eligibilitatea și stabilim împreună pașii următori pentru o finanțare clară și ușor de gestionat.',
  published = 1
WHERE source_type = 'service'
  AND slug = 'property-loan'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit pentru evenimente de familie',
  excerpt = 'Soluții rapide pentru vacanțe, cadouri, evenimente sau cheltuieli neprevăzute.',
  body = '## Credit pentru evenimente de familie

Fie că vrei o vacanță, un cadou pentru cei dragi sau să acoperi cheltuieli neprevăzute, Local Capital îți oferă credit simplu și rapid pentru familie.

## Pentru momente importante

Obții o evaluare rapidă, iar după aprobare fondurile pot ajunge direct pe card, astfel încât să te poți concentra pe planul tău.',
  published = 1
WHERE source_type = 'service'
  AND slug = 'wedding-loan'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Credit pentru mașină nouă',
  excerpt = 'Finanțare rapidă pentru achiziția autoturismului dorit.',
  body = '## Credit pentru mașină nouă

Transformă-ți visul de a conduce o mașină nouă în realitate. Local Capital îți oferă finanțare rapidă pentru achiziția autoturismului dorit.

## Bani direct pe card

După verificarea solicitării și aprobarea creditului, fondurile pot fi transferate direct în contul tău bancar, în siguranță.',
  published = 1
WHERE source_type = 'service'
  AND slug = 'auto-car-loan'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Personal Banking',
  excerpt = 'Pagină de arhivă importată din vechiul site Local Capital, curățată de elementele de navigație WordPress.',
  body = '## Personal Banking

Această pagină este păstrată ca parte a arhivei vechiului site Local Capital. Conținutul a fost curățat de meniuri, sidebar, comentarii și formulare WordPress, pentru ca pagina să rămână lizibilă în noul CMS.

Pentru informații actualizate despre soluțiile de creditare Local Capital, contactează echipa noastră.',
  published = 1
WHERE source_type = 'case_study'
  AND slug = 'personal-banking'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Business Planning',
  excerpt = 'Pagină de arhivă importată din vechiul site Local Capital, curățată de elementele de navigație WordPress.',
  body = '## Business Planning

Această pagină este păstrată ca parte a arhivei vechiului site Local Capital. Conținutul a fost curățat de meniuri, sidebar, comentarii și formulare WordPress, pentru ca pagina să rămână lizibilă în noul CMS.

Pentru informații actualizate despre soluțiile de creditare Local Capital, contactează echipa noastră.',
  published = 1
WHERE source_type = 'case_study'
  AND slug = 'business-planning'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Mergers & Acquisitions',
  excerpt = 'Pagină de arhivă importată din vechiul site Local Capital, curățată de elementele de navigație WordPress.',
  body = '## Mergers & Acquisitions

Această pagină este păstrată ca parte a arhivei vechiului site Local Capital. Conținutul a fost curățat de meniuri, sidebar, comentarii și formulare WordPress, pentru ca pagina să rămână lizibilă în noul CMS.

Pentru informații actualizate despre soluțiile de creditare Local Capital, contactează echipa noastră.',
  published = 1
WHERE source_type = 'case_study'
  AND slug = 'meger-acquistion'
  AND language_code = 'ro';

UPDATE posts
SET
  title = 'Business Tie-ups',
  excerpt = 'Pagină de arhivă importată din vechiul site Local Capital, curățată de elementele de navigație WordPress.',
  body = '## Business Tie-ups

Această pagină este păstrată ca parte a arhivei vechiului site Local Capital. Conținutul a fost curățat de meniuri, sidebar, comentarii și formulare WordPress, pentru ca pagina să rămână lizibilă în noul CMS.

Pentru informații actualizate despre soluțiile de creditare Local Capital, contactează echipa noastră.',
  published = 1
WHERE source_type = 'case_study'
  AND slug = 'business-tie-ups'
  AND language_code = 'ro';

-- Language-specific public content overrides.
INSERT INTO settings (language_code, setting_key, setting_value) VALUES
('ro', 'tagline', 'Credit simplu și rapid'),
('ro', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, România'),
('ro', 'closedHours', 'S-D: Închis'),
('ro', 'footerText', 'Creditul simplu și rapid este principala caracteristică a Local Capital, oferind clienților acces la resurse financiare fără birocrație inutilă.'),
('ro', 'anpcLabel', 'Protecția consumatorilor - ANPC'),
('en', 'tagline', 'Simple and fast credit'),
('en', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, Romania'),
('en', 'workingHours', 'Mon-Fri: 9:00 - 17:00'),
('en', 'closedHours', 'Sat-Sun: Closed'),
('en', 'footerText', 'Simple and fast credit is the main feature of Local Capital, giving clients access to financing without unnecessary bureaucracy.'),
('en', 'anpcLabel', 'Consumer protection - ANPC'),
('hu', 'tagline', 'Egyszerű és gyors hitel'),
('hu', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, Románia'),
('hu', 'workingHours', 'H-P: 9:00 - 17:00'),
('hu', 'closedHours', 'Szo-V: Zárva'),
('hu', 'footerText', 'Az egyszerű és gyors hitel a Local Capital fő jellemzője: felesleges bürokrácia nélkül ad hozzáférést pénzügyi forrásokhoz.'),
('hu', 'anpcLabel', 'Fogyasztóvédelem - ANPC')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO navigation (language_code, nav_key, label, path, sort_order, visible) VALUES
('ro', 'home', 'Acasă', '/', 10, 1),
('ro', 'about', 'Despre noi', '/despre-noi', 20, 1),
('ro', 'contract', 'Contract', '/contract', 30, 1),
('ro', 'blog', 'Noutăți', '/blog', 40, 1),
('ro', 'contact', 'Contact', '/contact', 50, 1),
('en', 'home', 'Home', '/', 10, 1),
('en', 'about', 'About us', '/despre-noi', 20, 1),
('en', 'contract', 'Loan types', '/contract', 30, 1),
('en', 'blog', 'News', '/blog', 40, 1),
('en', 'contact', 'Contact', '/contact', 50, 1),
('hu', 'home', 'Kezdőlap', '/', 10, 1),
('hu', 'about', 'Rólunk', '/despre-noi', 20, 1),
('hu', 'contract', 'Hitelek', '/contract', 30, 1),
('hu', 'blog', 'Hírek', '/blog', 40, 1),
('hu', 'contact', 'Kapcsolat', '/contact', 50, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), path = VALUES(path), sort_order = VALUES(sort_order), visible = VALUES(visible);

UPDATE pages
SET
  title = 'Fast and simple credit for financial independence',
  summary = 'Local Capital offers flexible credit solutions for personal needs, with a clear process and a fast answer.',
  body = '## Your plan becomes reality with Local Capital

Do you have clear financial goals and need quick support? Local Capital helps you access solutions for education, home, health, family, or consolidating existing debts.

Our process is designed to be simple: we discuss the need, check eligibility, and present a transparent repayment option.',
  cta_label = 'Apply now',
  cta_href = '/contact',
  secondary_cta_label = 'View loans',
  secondary_cta_href = '/contract',
  extra_json = '{"featuresTitle":"What you get","features":[{"title":"Simple repayment","text":"Choose the payment method that suits you, including accessible payment services."},{"title":"Flexibility","text":"Repayment options are adapted to your financial situation and lifestyle."},{"title":"Direct to card","text":"Approved funds can reach your bank account quickly and securely."},{"title":"Fast assessment","text":"We review the request efficiently so you can receive an answer as soon as possible."}],"servicesTitle":"Credit services for real needs","servicesIntro":"Whatever your project, Local Capital offers financial support when you need more freedom.","services":[{"title":"Health and family","text":"Quick solutions for medical expenses, urgent needs, and family support.","image":"/assets/service-health.jpg"},{"title":"Home","text":"Credit for renovation, rearrangement, or improvements in your home.","image":"/assets/service-home.jpg"},{"title":"New car","text":"Convenient financing for the car you want to buy.","image":"/assets/service-car.jpg"}],"requirementsTitle":"What you need","requirements":[{"title":"Identity document","text":"For the initial request, you need a valid identity document."},{"title":"Current bill","text":"A current bill can help us verify the data quickly."},{"title":"Fast answer","text":"In many cases, the assessment can be completed in a short time."}]}'
WHERE page_key = 'home' AND language_code = 'en';

UPDATE pages
SET
  title = 'Gyors és egyszerű hitel a pénzügyi önállósághoz',
  summary = 'A Local Capital rugalmas hitelmegoldásokat kínál személyes igényekre, átlátható folyamattal és gyors válasszal.',
  body = '## Terveid valóra válhatnak a Local Capitallal

Világos pénzügyi céljaid vannak, és gyors támogatásra van szükséged? A Local Capital segít elérni a megfelelő megoldást oktatásra, otthonra, egészségre, családra vagy meglévő tartozások rendezésére.

A folyamatunk egyszerű: megbeszéljük az igényt, ellenőrizzük a jogosultságot, és átlátható törlesztési lehetőséget mutatunk be.',
  cta_label = 'Igénylés',
  cta_href = '/contact',
  secondary_cta_label = 'Hitelek megtekintése',
  secondary_cta_href = '/contract',
  extra_json = '{"featuresTitle":"Amit kapsz","features":[{"title":"Egyszerű törlesztés","text":"Kiválaszthatod a számodra megfelelő fizetési módot, elérhető fizetési szolgáltatásokkal."},{"title":"Rugalmasság","text":"A törlesztési lehetőségek a pénzügyi helyzetedhez és életviteledhez igazodnak."},{"title":"Közvetlenül kártyára","text":"A jóváhagyott összeg gyorsan és biztonságosan megérkezhet a bankszámládra."},{"title":"Gyors elbírálás","text":"Hatékonyan elemezzük a kérelmet, hogy mielőbb választ kapj."}],"servicesTitle":"Hitelezési szolgáltatások valós igényekre","servicesIntro":"Bármilyen projektről legyen szó, a Local Capital pénzügyi támogatást kínál azokban a helyzetekben, amikor nagyobb szabadságra van szükséged.","services":[{"title":"Egészség és család","text":"Gyors megoldások egészségügyi kiadásokra, sürgős igényekre és családi támogatásra.","image":"/assets/service-health.jpg"},{"title":"Otthon","text":"Hitel felújításra, átalakításra vagy otthoni fejlesztésekre.","image":"/assets/service-home.jpg"},{"title":"Új autó","text":"Kedvező finanszírozás a kívánt autó megvásárlásához.","image":"/assets/service-car.jpg"}],"requirementsTitle":"Amire szükséged van","requirements":[{"title":"Személyazonosító okmány","text":"Az első igényléshez érvényes személyazonosító okmányra van szükség."},{"title":"Aktuális számla","text":"Egy friss számla segíthet az adatok gyors ellenőrzésében."},{"title":"Gyors válasz","text":"Sok esetben az elbírálás rövid idő alatt elvégezhető."}]}'
WHERE page_key = 'home' AND language_code = 'hu';

UPDATE pages
SET
  title = 'About Local Capital',
  summary = 'We are a Romanian non-bank financial institution focused on fast and flexible credit for individuals.',
  body = '## Our company

Local Capital is a Romanian non-bank financial institution focused on fast and flexible credit for individuals. We offer simple and personalized solutions for urgent needs, with a transparent process and clear communication.

We want to make the path toward financial solutions easier. The procedure is designed to be simple and accessible, and our team helps you understand the available options.

## Our mission

The mission of Local Capital is to provide financial support at key moments and to be the partner that makes a difference when a challenge appears. Our work is guided by integrity, responsibility, and clarity.',
  extra_json = '{"valuesTitle":"Our values","values":[{"title":"Integrity","text":"We act correctly and transparently in every client relationship."},{"title":"Responsibility","text":"We review each request carefully and with respect for the client financial situation."},{"title":"Clarity","text":"We explain the conditions and process steps in clear language."}]}'
WHERE page_key = 'about' AND language_code = 'en';

UPDATE pages
SET
  title = 'A Local Capitalról',
  summary = 'Romániai nem banki pénzügyi intézmény vagyunk, gyors és rugalmas személyi hitelekre szakosodva.',
  body = '## Cégünk

A Local Capital romániai nem banki pénzügyi intézmény, amely gyors és rugalmas személyi hitelekre szakosodott. Egyszerű és személyre szabott megoldásokat kínálunk sürgős igényekre, átlátható folyamattal és világos kommunikációval.

Célunk, hogy könnyebbé tegyük az utat a pénzügyi megoldások felé. Az eljárást egyszerűre és elérhetőre terveztük, csapatunk pedig segít megérteni a rendelkezésre álló lehetőségeket.

## Küldetésünk

A Local Capital küldetése, hogy kulcsfontosságú pillanatokban pénzügyi támogatást nyújtson, és olyan partner legyen, amely valódi segítséget ad, amikor kihívás jelenik meg. Munkánkat tisztesség, felelősség és átláthatóság vezeti.',
  extra_json = '{"valuesTitle":"Értékeink","values":[{"title":"Tisztesség","text":"Korrektül és átláthatóan járunk el minden ügyfélkapcsolatban."},{"title":"Felelősség","text":"Minden kérelmet figyelmesen és az ügyfél pénzügyi helyzetét tiszteletben tartva elemzünk."},{"title":"Átláthatóság","text":"A feltételeket és a folyamat lépéseit érthető nyelven magyarázzuk el."}]}'
WHERE page_key = 'about' AND language_code = 'hu';

UPDATE pages
SET
  title = 'Loan types offered',
  summary = 'Credit products created for different financial goals, with clear repayment options.',
  body = '## Financing packages

With our commitment to making financing more accessible, we present credit products created for different requirements and preferences. Our goal is to help you choose a suitable option for your plans.

The financing package is designed to give you control over managing the loan. You can discuss repayment options and periods adapted to your budget.',
  extra_json = '{"products":[{"title":"Credit Flex Basic","text":"An option for clients who want to focus on faster repayment of the loan."},{"title":"Credit Flex Basic PMT","text":"A version for clients who prefer constant installments throughout the repayment period."},{"title":"Credit Flex Legal","text":"A solution designed for specific needs, with a personalized discussion before the offer."}],"featuresTitle":"Advantages","features":[{"title":"Personalized monthly installments","text":"We can discuss installments adapted to your budget, for payments that are easier to manage."},{"title":"Variable repayment periods","text":"The duration of the loan can be chosen according to your financial plans."}]}'
WHERE page_key = 'contract' AND language_code = 'en';

UPDATE pages
SET
  title = 'Elérhető hiteltípusok',
  summary = 'Különböző pénzügyi célokra kialakított hiteltermékek, világos törlesztési lehetőségekkel.',
  body = '## Finanszírozási csomagok

Elkötelezettek vagyunk amellett, hogy a finanszírozás elérhetőbb legyen, ezért különböző igényekre és preferenciákra kialakított hiteltermékeket kínálunk. Célunk, hogy segítsünk a terveidhez illő megoldás kiválasztásában.

A finanszírozási csomag célja, hogy kontrollt adjon a hitel kezelésében. A törlesztési lehetőségek és időszakok a költségvetésedhez igazítva beszélhetők át.',
  extra_json = '{"products":[{"title":"Credit Flex Basic","text":"Lehetőség azoknak, akik a hitel gyorsabb törlesztésére szeretnének koncentrálni."},{"title":"Credit Flex Basic PMT","text":"Változat azoknak, akik állandó részleteket szeretnének a törlesztési időszak alatt."},{"title":"Credit Flex Juridic","text":"Speciális igényekre kialakított megoldás, személyre szabott egyeztetéssel az ajánlat előtt."}],"featuresTitle":"Előnyök","features":[{"title":"Személyre szabott havi részletek","text":"A költségvetésedhez igazított részleteket beszélhetünk át, hogy a fizetés könnyebben kezelhető legyen."},{"title":"Változó törlesztési időszakok","text":"A hitel futamideje pénzügyi terveid szerint választható ki."}]}'
WHERE page_key = 'contract' AND language_code = 'hu';

UPDATE pages
SET
  title = 'Contact us',
  summary = 'We are here for you. Write or call us for information about fast credit and financing solutions.',
  body = '## Feel free to contact us

For questions about eligibility, credit products, or the next steps, use the contact details below. The Local Capital team will reply during business hours.',
  extra_json = '{"formTitle":"Send a message","privacyNote":"By sending this form, you confirm that you have read the personal data processing notice."}'
WHERE page_key = 'contact' AND language_code = 'en';

UPDATE pages
SET
  title = 'Kapcsolatfelvétel',
  summary = 'Itt vagyunk, hogy segítsünk. Írj vagy hívj minket gyors hitelekkel és finanszírozási megoldásokkal kapcsolatban.',
  body = '## Fordulj hozzánk bizalommal

Jogosultsággal, hiteltermékekkel vagy a következő lépésekkel kapcsolatos kérdések esetén használd az alábbi elérhetőségeket. A Local Capital csapata munkaidőben válaszol.',
  extra_json = '{"formTitle":"Üzenet küldése","privacyNote":"Az űrlap elküldésével megerősíted, hogy elolvastad a személyes adatok kezeléséről szóló tájékoztatót."}'
WHERE page_key = 'contact' AND language_code = 'hu';

UPDATE pages
SET
  title = 'GDPR',
  summary = 'Information about personal data protection and the rights of data subjects.',
  body = CASE WHEN body LIKE '## Legal information%' THEN body ELSE CONCAT('## Legal information

The official legal text below is maintained in Romanian. For an authorized translation, contact Local Capital.

', body) END
WHERE page_key = 'gdpr' AND language_code = 'en';

UPDATE pages
SET
  title = 'GDPR',
  summary = 'Tájékoztatás a személyes adatok védelméről és az érintettek jogairól.',
  body = CASE WHEN body LIKE '## Jogi tájékoztatás%' THEN body ELSE CONCAT('## Jogi tájékoztatás

Az alábbi hivatalos jogi szöveg román nyelven van karbantartva. Hiteles fordításért lépj kapcsolatba a Local Capital csapatával.

', body) END
WHERE page_key = 'gdpr' AND language_code = 'hu';

UPDATE pages
SET
  title = 'Personal data policy',
  summary = 'Details about how Local Capital processes and protects personal data.',
  body = CASE WHEN body LIKE '## Legal information%' THEN body ELSE CONCAT('## Legal information

The official legal text below is maintained in Romanian. For an authorized translation, contact Local Capital.

', body) END
WHERE page_key = 'privacy' AND language_code = 'en';

UPDATE pages
SET
  title = 'Személyes adatok kezelése',
  summary = 'Részletek arról, hogyan kezeli és védi a Local Capital a személyes adatokat.',
  body = CASE WHEN body LIKE '## Jogi tájékoztatás%' THEN body ELSE CONCAT('## Jogi tájékoztatás

Az alábbi hivatalos jogi szöveg román nyelven van karbantartva. Hiteles fordításért lépj kapcsolatba a Local Capital csapatával.

', body) END
WHERE page_key = 'privacy' AND language_code = 'hu';

UPDATE pages
SET
  title = 'Terms and conditions',
  summary = 'General terms for using the website and Local Capital information pages.',
  body = CASE WHEN body LIKE '## Legal information%' THEN body ELSE CONCAT('## Legal information

The official legal text below is maintained in Romanian. For an authorized translation, contact Local Capital.

', body) END
WHERE page_key = 'terms' AND language_code = 'en';

UPDATE pages
SET
  title = 'Általános szerződési feltételek',
  summary = 'A weboldal és a Local Capital információs oldalainak általános használati feltételei.',
  body = CASE WHEN body LIKE '## Jogi tájékoztatás%' THEN body ELSE CONCAT('## Jogi tájékoztatás

Az alábbi hivatalos jogi szöveg román nyelven van karbantartva. Hiteles fordításért lépj kapcsolatba a Local Capital csapatával.

', body) END
WHERE page_key = 'terms' AND language_code = 'hu';

INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published)
SELECT 'en', source_type, slug, path, source_url, title, post_date, excerpt, body, published
FROM posts
WHERE language_code = 'ro' AND source_type IN ('service', 'case_study')
ON DUPLICATE KEY UPDATE slug = VALUES(slug);

INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published)
SELECT 'hu', source_type, slug, path, source_url, title, post_date, excerpt, body, published
FROM posts
WHERE language_code = 'ro' AND source_type IN ('service', 'case_study')
ON DUPLICATE KEY UPDATE slug = VALUES(slug);

INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published) VALUES
('en', 'post', 'credit-rapid-cu-buletinul', '/blog/credit-rapid-cu-buletinul', NULL, 'Fast credit with identity card', '2026-04-29', 'What a simple request means and which documents are useful for a fast assessment.', '## Fast credit with identity card

A fast credit request starts with clear information and correct identification data. In the initial stage, the identity document is the main document required for verification.

The Local Capital team explains the steps, conditions, and available options, so the decision can be made with confidence.', 1),
('hu', 'post', 'credit-rapid-cu-buletinul', '/blog/credit-rapid-cu-buletinul', NULL, 'Gyors hitel személyi igazolvánnyal', '2026-04-29', 'Mit jelent az egyszerű igénylés, és mely dokumentumok segítenek a gyors elbírálásban.', '## Gyors hitel személyi igazolvánnyal

A gyors hiteligénylés világos adatokkal és pontos azonosítással kezdődik. Az első szakaszban a személyazonosító okmány a fő dokumentum az adatok ellenőrzéséhez.

A Local Capital csapata elmagyarázza a lépéseket, a feltételeket és az elérhető lehetőségeket, hogy magabiztosan hozhass döntést.', 1)
ON DUPLICATE KEY UPDATE
path = VALUES(path),
source_url = VALUES(source_url),
title = VALUES(title),
post_date = VALUES(post_date),
excerpt = VALUES(excerpt),
body = VALUES(body),
published = VALUES(published);

UPDATE posts
SET
  title = 'Personal loan',
  excerpt = 'A flexible loan for personal needs, without unnecessary bureaucracy.',
  body = '## Personal loan

A personal loan from Local Capital can help cover urgent expenses, planned purchases, family needs, or consolidating existing debts.

The process is simple and clear: submit the request, discuss the amount and repayment period, and receive an answer as quickly as possible.',
  published = 1
WHERE source_type = 'service' AND slug = 'personal-loan' AND language_code = 'en';

UPDATE posts
SET
  title = 'Személyi kölcsön',
  excerpt = 'Rugalmas hitel személyes igényekre, felesleges bürokrácia nélkül.',
  body = '## Személyi kölcsön

A Local Capital személyi kölcsöne segíthet sürgős kiadások, tervezett vásárlások, családi igények vagy meglévő tartozások rendezése esetén.

A folyamat egyszerű és világos: elküldöd az igénylést, átbeszéljük az összeget és a futamidőt, majd a lehető legrövidebb időn belül választ kapsz.',
  published = 1
WHERE source_type = 'service' AND slug = 'personal-loan' AND language_code = 'hu';

UPDATE posts
SET
  title = 'Education loan',
  excerpt = 'Support for courses, school fees, materials, or professional development.',
  body = '## Education loan

Investing in education can open new opportunities. Local Capital offers financing for courses, school fees, study materials, or professional development programs.

We help you find a repayment option that fits your budget, so you can focus on learning and progress.',
  published = 1
WHERE source_type = 'service' AND slug = 'education-loan' AND language_code = 'en';

UPDATE posts
SET
  title = 'Oktatási hitel',
  excerpt = 'Támogatás tanfolyamokra, tandíjra, tananyagokra vagy szakmai fejlődésre.',
  body = '## Oktatási hitel

Az oktatásba való befektetés új lehetőségeket nyithat meg. A Local Capital finanszírozást kínál tanfolyamokra, tandíjra, tananyagokra vagy szakmai továbbképzésre.

Segítünk olyan törlesztési megoldást találni, amely illeszkedik a költségvetésedhez, így a tanulásra és a fejlődésre koncentrálhatsz.',
  published = 1
WHERE source_type = 'service' AND slug = 'education-loan' AND language_code = 'hu';

UPDATE posts
SET
  title = 'Loan for projects and investments',
  excerpt = 'Financing for plans that need quick access to capital.',
  body = '## Loan for projects and investments

When an opportunity appears, timing matters. Local Capital offers flexible credit solutions for projects, investments, or expenses that need quick financing.

We review the request efficiently and explain the repayment conditions before you make a decision.',
  published = 1
WHERE source_type = 'service' AND slug = 'business-loan' AND language_code = 'en';

UPDATE posts
SET
  title = 'Hitel projektekhez és befektetésekhez',
  excerpt = 'Finanszírozás olyan tervekhez, amelyek gyors tőkehozzáférést igényelnek.',
  body = '## Hitel projektekhez és befektetésekhez

Amikor lehetőség adódik, az időzítés sokat számít. A Local Capital rugalmas hitelmegoldásokat kínál projektekhez, befektetésekhez vagy gyors finanszírozást igénylő kiadásokhoz.

Hatékonyan elemezzük a kérelmet, és a döntés előtt világosan elmagyarázzuk a törlesztési feltételeket.',
  published = 1
WHERE source_type = 'service' AND slug = 'business-loan' AND language_code = 'hu';

UPDATE posts
SET
  title = 'Home improvement loan',
  excerpt = 'Credit for renovation, repairs, furniture, or improvements in your home.',
  body = '## Home improvement loan

Your home sometimes needs quick investment. Local Capital can support renovation, repairs, furniture purchases, or other improvements that make your home more comfortable.

After approval, the funds can be transferred directly to your card, securely and quickly.',
  published = 1
WHERE source_type = 'service' AND slug = 'property-loan' AND language_code = 'en';

UPDATE posts
SET
  title = 'Lakásfelújítási hitel',
  excerpt = 'Hitel felújításra, javításokra, bútorra vagy otthoni fejlesztésekre.',
  body = '## Lakásfelújítási hitel

Az otthon néha gyors befektetést igényel. A Local Capital támogatást nyújthat felújításra, javításokra, bútorvásárlásra vagy más fejlesztésekre, amelyek kényelmesebbé teszik az otthonodat.

Jóváhagyás után az összeg biztonságosan és gyorsan a kártyádra érkezhet.',
  published = 1
WHERE source_type = 'service' AND slug = 'property-loan' AND language_code = 'hu';

UPDATE posts
SET
  title = 'Loan for family events',
  excerpt = 'Financial support for weddings, baptisms, anniversaries, or other important moments.',
  body = '## Loan for family events

Important family moments deserve attention, not financial pressure. Local Capital can help with financing for weddings, baptisms, anniversaries, or other meaningful events.

You receive a fast assessment, and after approval the funds can be transferred directly to your card.',
  published = 1
WHERE source_type = 'service' AND slug = 'wedding-loan' AND language_code = 'en';

UPDATE posts
SET
  title = 'Hitel családi eseményekre',
  excerpt = 'Pénzügyi támogatás esküvőre, keresztelőre, évfordulóra vagy más fontos pillanatra.',
  body = '## Hitel családi eseményekre

A fontos családi pillanatok figyelmet érdemelnek, nem pénzügyi nyomást. A Local Capital finanszírozással segíthet esküvő, keresztelő, évforduló vagy más jelentős esemény esetén.

Gyors elbírálást kapsz, és jóváhagyás után az összeg közvetlenül a kártyádra kerülhet.',
  published = 1
WHERE source_type = 'service' AND slug = 'wedding-loan' AND language_code = 'hu';

UPDATE posts
SET
  title = 'New car loan',
  excerpt = 'Fast financing for buying the car you want.',
  body = '## New car loan

Turn the plan of driving a new car into reality. Local Capital offers fast financing for buying the car you want.

After checking the request and approving the credit, the funds can be transferred directly to your bank account, securely.',
  published = 1
WHERE source_type = 'service' AND slug = 'auto-car-loan' AND language_code = 'en';

UPDATE posts
SET
  title = 'Hitel új autóra',
  excerpt = 'Gyors finanszírozás a kívánt autó megvásárlásához.',
  body = '## Hitel új autóra

Váltsd valóra az új autó vezetéséről szóló tervet. A Local Capital gyors finanszírozást kínál a kívánt autó megvásárlásához.

A kérelem ellenőrzése és a hitel jóváhagyása után az összeg biztonságosan közvetlenül a bankszámládra utalható.',
  published = 1
WHERE source_type = 'service' AND slug = 'auto-car-loan' AND language_code = 'hu';

UPDATE posts
SET
  excerpt = 'Archive page imported from the old Local Capital website and cleaned of WordPress navigation elements.',
  body = CONCAT('## ', title, '

This page is kept as part of the old Local Capital website archive. Navigation, sidebars, comments, and WordPress forms were removed so the page remains readable in the new CMS.

For current information about Local Capital credit solutions, contact our team.'),
  published = 1
WHERE source_type = 'case_study' AND language_code = 'en';

UPDATE posts
SET
  excerpt = 'A régi Local Capital weboldalról importált archív oldal, WordPress navigációs elemek nélkül.',
  body = CONCAT('## ', title, '

Ez az oldal a régi Local Capital weboldal archívumának részeként maradt meg. A navigációt, oldalsávokat, hozzászólásokat és WordPress űrlapokat eltávolítottuk, hogy az oldal az új CMS-ben is olvasható legyen.

A Local Capital aktuális hitelezési megoldásairól kérj tájékoztatást csapatunktól.'),
  published = 1
WHERE source_type = 'case_study' AND language_code = 'hu';

-- AI SEO: concise summaries and FAQ content for answer engines.
UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Local Capital oferă credite rapide și simple pentru persoane fizice din România, cu evaluare eficientă, rambursare flexibilă și fonduri transferate direct pe card după aprobare.","faq":[{"question":"Ce documente sunt necesare pentru solicitarea inițială?","answer":"Pentru solicitarea inițială ai nevoie de un act de identitate valid. În anumite situații poate fi utilă și o factură curentă pentru verificarea rapidă a datelor."},{"question":"Cât de repede pot primi un răspuns?","answer":"Solicitarea este analizată eficient, iar în multe cazuri poți primi un răspuns într-un timp scurt, în funcție de informațiile furnizate."},{"question":"Banii pot fi transferați direct pe card?","answer":"Da. După aprobarea creditului, fondurile pot fi transferate direct în contul tău bancar, în siguranță."}]}')
WHERE page_key = 'home' AND language_code = 'ro';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Local Capital offers simple and fast credit solutions for individuals in Romania, with efficient assessment, flexible repayment, and funds transferred directly to card after approval.","faq":[{"question":"Which documents are needed for the initial request?","answer":"For the initial request, you need a valid identity document. In some cases, a current bill can also help with quick data verification."},{"question":"How quickly can I receive an answer?","answer":"The request is reviewed efficiently, and in many cases you can receive an answer in a short time, depending on the information provided."},{"question":"Can the money be transferred directly to my card?","answer":"Yes. After credit approval, the funds can be transferred directly to your bank account securely."}]}')
WHERE page_key = 'home' AND language_code = 'en';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"A Local Capital egyszerű és gyors hitelmegoldásokat kínál romániai magánszemélyeknek, hatékony elbírálással, rugalmas törlesztéssel és jóváhagyás után közvetlen kártyára utalással.","faq":[{"question":"Milyen dokumentum szükséges az első igényléshez?","answer":"Az első igényléshez érvényes személyazonosító okmányra van szükség. Bizonyos esetekben egy friss számla is segíthet az adatok gyors ellenőrzésében."},{"question":"Milyen gyorsan kaphatok választ?","answer":"A kérelmet hatékonyan elemezzük, és sok esetben rövid időn belül választ kaphatsz, a megadott információktól függően."},{"question":"Az összeg közvetlenül a kártyámra érkezhet?","answer":"Igen. A hitel jóváhagyása után az összeg biztonságosan közvetlenül a bankszámládra utalható."}]}')
WHERE page_key = 'home' AND language_code = 'hu';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Local Capital este o instituție financiară nebancară din România specializată în credite rapide și flexibile pentru persoane fizice, cu comunicare clară și proces transparent.","faq":[{"question":"Ce este Local Capital?","answer":"Local Capital este o instituție financiară nebancară din România, specializată în soluții de creditare rapide și flexibile pentru persoane fizice."},{"question":"Care sunt valorile companiei?","answer":"Activitatea Local Capital este ghidată de integritate, responsabilitate și claritate în relația cu fiecare client."}]}')
WHERE page_key = 'about' AND language_code = 'ro';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Local Capital is a Romanian non-bank financial institution specializing in fast and flexible credit for individuals, with clear communication and a transparent process.","faq":[{"question":"What is Local Capital?","answer":"Local Capital is a Romanian non-bank financial institution focused on fast and flexible credit solutions for individuals."},{"question":"What values guide the company?","answer":"Local Capital is guided by integrity, responsibility, and clarity in every client relationship."}]}')
WHERE page_key = 'about' AND language_code = 'en';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"A Local Capital romániai nem banki pénzügyi intézmény, amely gyors és rugalmas személyi hitelmegoldásokra szakosodott, világos kommunikációval és átlátható folyamattal.","faq":[{"question":"Mi a Local Capital?","answer":"A Local Capital romániai nem banki pénzügyi intézmény, amely gyors és rugalmas hitelmegoldásokat kínál magánszemélyeknek."},{"question":"Milyen értékek vezetik a céget?","answer":"A Local Capital munkáját tisztesség, felelősség és átláthatóság vezeti minden ügyfélkapcsolatban."}]}')
WHERE page_key = 'about' AND language_code = 'hu';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Local Capital oferă produse de credit precum Credit Flex Basic, Credit Flex Basic PMT și Credit Flex Juridic, cu opțiuni de rambursare adaptate bugetului clientului.","faq":[{"question":"Ce tipuri de credit sunt disponibile?","answer":"Local Capital oferă produse precum Credit Flex Basic, Credit Flex Basic PMT și Credit Flex Juridic, în funcție de nevoile clientului."},{"question":"Pot discuta o rată lunară adaptată bugetului meu?","answer":"Da. Opțiunile de rambursare pot fi discutate în funcție de situația financiară și planurile clientului."},{"question":"Perioada de rambursare este fixă?","answer":"Perioada poate varia în funcție de produs, eligibilitate și discuția personalizată cu echipa Local Capital."}]}')
WHERE page_key = 'contract' AND language_code = 'ro';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Local Capital offers credit products such as Credit Flex Basic, Credit Flex Basic PMT, and Credit Flex Legal, with repayment options adapted to the client budget.","faq":[{"question":"Which loan types are available?","answer":"Local Capital offers products such as Credit Flex Basic, Credit Flex Basic PMT, and Credit Flex Legal, depending on the client needs."},{"question":"Can I discuss a monthly installment adapted to my budget?","answer":"Yes. Repayment options can be discussed based on the financial situation and plans of the client."},{"question":"Is the repayment period fixed?","answer":"The period can vary depending on the product, eligibility, and the personalized discussion with the Local Capital team."}]}')
WHERE page_key = 'contract' AND language_code = 'en';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"A Local Capital Credit Flex Basic, Credit Flex Basic PMT és Credit Flex Juridic típusú hiteltermékeket kínál, az ügyfél költségvetéséhez igazítható törlesztési lehetőségekkel.","faq":[{"question":"Milyen hiteltípusok érhetők el?","answer":"A Local Capital Credit Flex Basic, Credit Flex Basic PMT és Credit Flex Juridic termékeket kínál az ügyfél igényeitől függően."},{"question":"Megbeszélhető a költségvetésemhez igazított havi részlet?","answer":"Igen. A törlesztési lehetőségek az ügyfél pénzügyi helyzete és tervei alapján beszélhetők át."},{"question":"Fix a törlesztési időszak?","answer":"A futamidő a terméktől, a jogosultságtól és a Local Capital csapatával folytatott személyre szabott egyeztetéstől függően változhat."}]}')
WHERE page_key = 'contract' AND language_code = 'hu';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"Pentru informații despre credite rapide Local Capital, clienții pot suna la 0318 110 001, scrie la info@localcapital.ro sau folosi formularul de contact securizat.","faq":[{"question":"Cum pot contacta Local Capital?","answer":"Poți suna la 0318 110 001, poți scrie la info@localcapital.ro sau poți trimite mesaj prin formularul de contact."},{"question":"Care este programul de lucru?","answer":"Programul este de luni până vineri, între 9:00 și 17:00."},{"question":"Unde se află Local Capital?","answer":"Adresa publică este Str. Vasile Lucaciu nr. 3, Satu Mare, România."}]}')
WHERE page_key = 'contact' AND language_code = 'ro';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"For information about Local Capital fast credit, clients can call 0318 110 001, write to info@localcapital.ro, or use the secure contact form.","faq":[{"question":"How can I contact Local Capital?","answer":"You can call 0318 110 001, write to info@localcapital.ro, or send a message through the contact form."},{"question":"What are the business hours?","answer":"Business hours are Monday to Friday, from 9:00 to 17:00."},{"question":"Where is Local Capital located?","answer":"The public address is Str. Vasile Lucaciu nr. 3, Satu Mare, Romania."}]}')
WHERE page_key = 'contact' AND language_code = 'en';

UPDATE pages
SET extra_json = JSON_MERGE_PATCH(COALESCE(NULLIF(extra_json, ''), '{}'), '{"aiSummary":"A Local Capital gyors hiteleiről a 0318 110 001 telefonszámon, az info@localcapital.ro címen vagy a biztonságos kapcsolatfelvételi űrlapon kérhető tájékoztatás.","faq":[{"question":"Hogyan léphetek kapcsolatba a Local Capitallal?","answer":"Hívhatod a 0318 110 001 számot, írhatsz az info@localcapital.ro címre, vagy üzenetet küldhetsz a kapcsolatfelvételi űrlapon."},{"question":"Mi a nyitvatartás?","answer":"A nyitvatartás hétfőtől péntekig 9:00 és 17:00 között van."},{"question":"Hol található a Local Capital?","answer":"A nyilvános cím: Str. Vasile Lucaciu nr. 3, Satu Mare, Románia."}]}')
WHERE page_key = 'contact' AND language_code = 'hu';

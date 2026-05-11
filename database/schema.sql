SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code CHAR(2) NOT NULL DEFAULT 'ro',
  setting_key VARCHAR(120) NOT NULL,
  setting_value TEXT NOT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_settings_language_key (language_code, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS navigation (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code CHAR(2) NOT NULL DEFAULT 'ro',
  nav_key VARCHAR(80) NOT NULL,
  label VARCHAR(160) NOT NULL,
  path VARCHAR(180) NOT NULL,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  visible TINYINT(1) NOT NULL DEFAULT 1,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_navigation_language_key (language_code, nav_key),
  KEY idx_navigation_language_path (language_code, path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code CHAR(2) NOT NULL DEFAULT 'ro',
  page_key VARCHAR(80) NOT NULL,
  path VARCHAR(180) NOT NULL,
  title VARCHAR(220) NOT NULL,
  summary TEXT NOT NULL,
  body LONGTEXT NOT NULL,
  cta_label VARCHAR(120) NULL,
  cta_href VARCHAR(180) NULL,
  secondary_cta_label VARCHAR(120) NULL,
  secondary_cta_href VARCHAR(180) NULL,
  extra_json LONGTEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_pages_language_key (language_code, page_key),
  KEY idx_pages_language_path (language_code, path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code CHAR(2) NOT NULL DEFAULT 'ro',
  source_type VARCHAR(40) NOT NULL DEFAULT 'post',
  slug VARCHAR(180) NOT NULL,
  path VARCHAR(220) NOT NULL,
  source_url VARCHAR(255) NULL,
  title VARCHAR(220) NOT NULL,
  post_date DATE NOT NULL,
  excerpt TEXT NOT NULL,
  body LONGTEXT NOT NULL,
  published TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_posts_language_type_slug (language_code, source_type, slug),
  KEY idx_posts_language_published_date (language_code, published, post_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS site_links (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code CHAR(2) NOT NULL DEFAULT 'ro',
  source_url VARCHAR(255) NOT NULL,
  href VARCHAR(500) NOT NULL,
  label VARCHAR(255) NOT NULL DEFAULT '',
  is_internal TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_site_link (language_code, source_url, href),
  KEY idx_site_links_language_internal (language_code, is_internal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  language_code CHAR(2) NOT NULL DEFAULT 'ro',
  name VARCHAR(160) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(60) NULL,
  subject VARCHAR(220) NOT NULL,
  message TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'new',
  ip_hash CHAR(64) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_status_created (status, created_at),
  KEY idx_contact_language_created (language_code, created_at),
  KEY idx_contact_ip_created (ip_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(120) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_admin_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_login_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  attempt_key CHAR(64) NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  username_hash CHAR(64) NOT NULL,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin_login_attempt_key_time (attempt_key, attempted_at),
  KEY idx_admin_login_ip_time (ip_hash, attempted_at),
  KEY idx_admin_login_attempt_time (attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS admin_login_bans (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_hash CHAR(64) NOT NULL,
  banned_until DATETIME NOT NULL,
  reason VARCHAR(120) NOT NULL DEFAULT 'admin_login',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_admin_login_ban_ip (ip_hash),
  KEY idx_admin_login_banned_until (banned_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anaf_consents (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  status VARCHAR(20) NOT NULL DEFAULT 'draft',
  public_token_hash CHAR(64) NULL,
  public_token_enc TEXT NULL,
  token_expires_at DATETIME NULL,
  request_reference_enc TEXT NOT NULL,
  first_name_enc TEXT NOT NULL,
  last_name_enc TEXT NOT NULL,
  cnp_enc TEXT NOT NULL,
  id_series_enc TEXT NOT NULL,
  id_number_enc TEXT NOT NULL,
  id_issued_by_enc TEXT NOT NULL,
  id_issued_at_enc TEXT NOT NULL,
  email_enc TEXT NOT NULL,
  phone_enc TEXT NOT NULL,
  address_enc TEXT NOT NULL,
  consent_anaf TINYINT(1) NOT NULL DEFAULT 0,
  consent_text_version VARCHAR(60) NOT NULL DEFAULT '',
  consent_text TEXT NULL,
  ip_address_enc TEXT NULL,
  ip_hash CHAR(64) NULL,
  user_agent_hash CHAR(64) NULL,
  created_by_admin_id INT UNSIGNED NULL,
  submitted_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uniq_anaf_public_token_hash (public_token_hash),
  KEY idx_anaf_status_created (status, created_at),
  KEY idx_anaf_submitted_at (submitted_at),
  KEY idx_anaf_token_expires (token_expires_at),
  CONSTRAINT fk_anaf_created_by_admin FOREIGN KEY (created_by_admin_id) REFERENCES admin_users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS anaf_consent_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  ip_hash CHAR(64) NOT NULL,
  public_token_hash CHAR(64) NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_anaf_attempt_ip_time (ip_hash, attempted_at),
  KEY idx_anaf_attempt_token_time (public_token_hash, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO settings (language_code, setting_key, setting_value) VALUES
('ro', 'brandName', 'Local Capital'),
('ro', 'legalName', 'LOCAL CAPITAL IFN S.A.'),
('ro', 'tagline', 'Credit simplu si rapid'),
('ro', 'phone', '0318 110 001'),
('ro', 'email', 'info@localcapital.ro'),
('ro', 'dataProtectionEmail', 'protectiadatelor@localcapital.ro'),
('ro', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, Romania'),
('ro', 'workingHours', 'L-V: 9:00 - 17:00'),
('ro', 'closedHours', 'S-D: Inchis'),
('ro', 'footerText', 'Creditul simplu si rapid este principala caracteristica a Local Capital, oferind clientilor acces la resurse financiare fara birocratie inutila.'),
('ro', 'anpcLabel', 'Protectia consumatorilor - ANPC'),
('ro', 'anpcUrl', 'https://anpc.ro/'),
('en', 'brandName', 'Local Capital'),
('en', 'legalName', 'LOCAL CAPITAL IFN S.A.'),
('en', 'tagline', 'Simple and fast credit'),
('en', 'phone', '0318 110 001'),
('en', 'email', 'info@localcapital.ro'),
('en', 'dataProtectionEmail', 'protectiadatelor@localcapital.ro'),
('en', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, Romania'),
('en', 'workingHours', 'Mon-Fri: 9:00 - 17:00'),
('en', 'closedHours', 'Sat-Sun: Closed'),
('en', 'footerText', 'Simple and fast credit is the main feature of Local Capital, giving clients access to financing without unnecessary bureaucracy.'),
('en', 'anpcLabel', 'Consumer protection - ANPC'),
('en', 'anpcUrl', 'https://anpc.ro/'),
('hu', 'brandName', 'Local Capital'),
('hu', 'legalName', 'LOCAL CAPITAL IFN S.A.'),
('hu', 'tagline', 'Egyszeru es gyors hitel'),
('hu', 'phone', '0318 110 001'),
('hu', 'email', 'info@localcapital.ro'),
('hu', 'dataProtectionEmail', 'protectiadatelor@localcapital.ro'),
('hu', 'address', 'Str. Vasile Lucaciu nr. 3, Satu Mare, Romania'),
('hu', 'workingHours', 'H-P: 9:00 - 17:00'),
('hu', 'closedHours', 'Szo-V: Zarva'),
('hu', 'footerText', 'Az egyszeru es gyors hitel a Local Capital fo jellemzoje, amely felesleges burokracia nelkul ad hozzaferest penzugyi forrasokhoz.'),
('hu', 'anpcLabel', 'Fogyasztovedelem - ANPC'),
('hu', 'anpcUrl', 'https://anpc.ro/')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO navigation (language_code, nav_key, label, path, sort_order, visible) VALUES
('ro', 'home', 'Acasa', '/', 10, 1),
('ro', 'about', 'Despre noi', '/despre-noi', 20, 1),
('ro', 'contract', 'Contract', '/contract', 30, 1),
('ro', 'blog', 'Noutati', '/blog', 40, 1),
('ro', 'contact', 'Contact', '/contact', 50, 1),
('en', 'home', 'Home', '/', 10, 1),
('en', 'about', 'About us', '/despre-noi', 20, 1),
('en', 'contract', 'Contract', '/contract', 30, 1),
('en', 'blog', 'News', '/blog', 40, 1),
('en', 'contact', 'Contact', '/contact', 50, 1),
('hu', 'home', 'Kezdolap', '/', 10, 1),
('hu', 'about', 'Rolunk', '/despre-noi', 20, 1),
('hu', 'contract', 'Szerzodes', '/contract', 30, 1),
('hu', 'blog', 'Hirek', '/blog', 40, 1),
('hu', 'contact', 'Kapcsolat', '/contact', 50, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), path = VALUES(path), sort_order = VALUES(sort_order), visible = VALUES(visible);

INSERT INTO pages
(language_code, page_key, path, title, summary, body, cta_label, cta_href, secondary_cta_label, secondary_cta_href, extra_json)
VALUES
(
  'ro',
  'home',
  '/',
  'Credit rapid si simplu pentru independenta financiara',
  'Local Capital ofera solutii de creditare flexibile pentru nevoi personale, cu proces clar si raspuns rapid.',
  '## Visul tau devine realitate cu Local Capital

Ai obiective financiare clare si ai nevoie de sprijin rapid? Local Capital te ajuta sa accesezi solutii adaptate pentru educatie, locuinta, sanatate, familie sau consolidarea unor datorii existente.

Procesul nostru este gandit sa fie simplu: discutam nevoia, verificam eligibilitatea si iti prezentam o optiune transparenta de rambursare.',
  'Aplica acum',
  '/contact',
  'Vezi creditele',
  '/contract',
  '{"featuresTitle":"Ce primesti","features":[{"title":"Rambursare simpla","text":"Ai libertatea de a alege metoda de plata potrivita, inclusiv prin servicii de plata accesibile."},{"title":"Flexibilitate","text":"Optiunile de rambursare sunt adaptate situatiei tale financiare si stilului tau de viata."},{"title":"Direct pe card","text":"Fondurile aprobate pot ajunge rapid in contul tau bancar, in siguranta."},{"title":"Evaluare rapida","text":"Analizam solicitarea eficient, astfel incat sa primesti raspunsul de care ai nevoie cat mai repede."}],"servicesTitle":"Oferim servicii de creditare pentru nevoi reale","servicesIntro":"Indiferent de proiectul tau, Local Capital ofera sprijin financiar pentru momentele in care ai nevoie de mai multa libertate.","services":[{"title":"Sanatate si familie","text":"Solutii rapide pentru cheltuieli medicale, nevoi urgente si sprijin pentru familie.","image":"/assets/service-health.jpg"},{"title":"Locuinta","text":"Credit pentru renovare, reamenajare sau imbunatatiri in casa ta.","image":"/assets/service-home.jpg"},{"title":"Masina noua","text":"Finantare avantajoasa pentru achizitia autoturismului dorit.","image":"/assets/service-car.jpg"}],"requirementsTitle":"De ce ai nevoie","requirements":[{"title":"Act de identitate","text":"Pentru solicitarea initiala ai nevoie de un act de identitate valid."},{"title":"Factura curenta","text":"O factura curenta poate ajuta verificarea rapida a datelor."},{"title":"Raspuns rapid","text":"In multe cazuri, evaluarea poate fi realizata intr-un timp scurt."}]}'
),
(
  'ro',
  'about',
  '/despre-noi',
  'Despre Local Capital',
  'Suntem o institutie financiara nebancara din Romania, specializata in credite rapide si flexibile pentru persoane fizice.',
  '## Compania noastra

Local Capital este o institutie financiara nebancara din Romania, specializata in credite rapide si flexibile pentru persoane fizice. Oferim solutii simple si personalizate pentru nevoi urgente, cu proces transparent si comunicare clara.

Ne dorim sa usuram drumul catre solutii financiare. Procedura este conceputa sa fie simpla si accesibila, iar echipa noastra te ajuta sa intelegi optiunile disponibile.

## Misiunea noastra

Misiunea Local Capital este sa ofere sprijin financiar in momente cheie si sa fie partenerul care face diferenta atunci cand apare o provocare. Activitatea noastra este ghidata de integritate, responsabilitate si claritate.',
  NULL,
  NULL,
  NULL,
  NULL,
  '{"valuesTitle":"Valorile noastre","values":[{"title":"Integritate","text":"Actionam corect si transparent in relatia cu fiecare client."},{"title":"Responsabilitate","text":"Analizam fiecare solicitare cu atentie si respect fata de situatia financiara a clientului."},{"title":"Claritate","text":"Explicam conditiile si pasii procesului intr-un limbaj usor de inteles."}]}'
),
(
  'ro',
  'contract',
  '/contract',
  'Tipuri de credite oferite',
  'Produse de credit create pentru obiective financiare diferite, cu optiuni de rambursare clare.',
  '## Pachete de finantare

Cu devotamentul nostru de a face finantarea mai accesibila, iti prezentam produse de credit create pentru cerinte si preferinte diferite. Scopul nostru este sa te ajutam sa alegi o varianta potrivita pentru planurile tale.

Pachetul de finantare este conceput pentru a-ti oferi control asupra gestionarii imprumutului. Poti discuta optiuni de rambursare si perioade adaptate bugetului tau.',
  NULL,
  NULL,
  NULL,
  NULL,
  '{"products":[{"title":"Credit Flex Basic","text":"O optiune pentru cei care vor sa se concentreze pe rambursarea mai rapida a imprumutului."},{"title":"Credit Flex Basic PMT","text":"O varianta pentru cei care prefera rate constante pe durata perioadei de rambursare."},{"title":"Credit Flex Juridic","text":"O solutie gandita pentru nevoi specifice, cu discutie personalizata inainte de ofertare."}],"featuresTitle":"Avantaje","features":[{"title":"Rate lunare personalizate","text":"Putem discuta rate adaptate bugetului tau, pentru plati mai usor de gestionat."},{"title":"Perioade de rambursare variabile","text":"Durata imprumutului poate fi aleasa in functie de planurile tale financiare."}]}'
),
(
  'ro',
  'contact',
  '/contact',
  'Contacteaza-ne',
  'Suntem aici pentru tine. Scrie-ne sau suna-ne pentru informatii despre credite rapide si solutii de finantare.',
  '## Nu ezita sa ne contactezi

Pentru intrebari despre eligibilitate, produse de creditare sau pasii urmatori, foloseste datele de contact de mai jos. Echipa Local Capital iti va raspunde in timpul programului de lucru.',
  NULL,
  NULL,
  NULL,
  NULL,
  '{"formTitle":"Trimite un mesaj","privacyNote":"Prin trimiterea unui email, confirmi ca ai citit informarea privind prelucrarea datelor personale."}'
)
ON DUPLICATE KEY UPDATE
path = VALUES(path),
title = VALUES(title),
summary = VALUES(summary),
body = VALUES(body),
cta_label = VALUES(cta_label),
cta_href = VALUES(cta_href),
secondary_cta_label = VALUES(secondary_cta_label),
secondary_cta_href = VALUES(secondary_cta_href),
extra_json = VALUES(extra_json);

INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published) VALUES
(
  'ro',
  'post',
  'credit-rapid-cu-buletinul',
  '/blog/credit-rapid-cu-buletinul',
  NULL,
  'Credit rapid doar cu buletinul',
  '2026-04-29',
  'Ce inseamna o solicitare simpla si ce documente sunt utile pentru o evaluare rapida.',
  'Un credit rapid incepe cu o solicitare clara si cu date de identificare corecte. In etapa initiala, actul de identitate este documentul principal necesar pentru verificarea informatiilor.

Echipa Local Capital iti explica pasii, conditiile si optiunile disponibile, astfel incat decizia sa fie luata informat.',
  1
)
ON DUPLICATE KEY UPDATE
path = VALUES(path),
source_url = VALUES(source_url),
title = VALUES(title),
post_date = VALUES(post_date),
excerpt = VALUES(excerpt),
body = VALUES(body),
published = VALUES(published);

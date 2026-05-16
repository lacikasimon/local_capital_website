# Descriere tehnică a metodei de validare a acordului ANAF în format electronic

**Document tehnic pentru prezentare către ANAF**  
**Entitate:** LOCAL CAPITAL IFN S.A.  
**Sistem:** Formular public de acord ANAF și generator PDF de acord completat  
**Versiune document:** 1.0  
**Data:** 15.05.2026

## 1. Scopul documentului

Prezentul document descrie metoda tehnică prin care LOCAL CAPITAL IFN S.A. obține, validează, arhivează și poate pune la dispoziție acordul electronic pentru consultarea, transmiterea și prelucrarea informațiilor din bazele de date MF/ANAF.

Documentul are caracter tehnic. Textul juridic al acordului este furnizat separat de departamentul juridic și nu este modificat prin prezenta descriere.

Metoda implementată este concepută pentru scenariul de semnătură electronică simplă / acceptare expresă și neechivocă a acordului, cu generarea acordului în format PDF și includerea în PDF a elementelor tehnice de identificare și audit: nume și prenume în clar, data și ora acceptării, IP-ul stației de unde s-a dat acordul, imaginea semnăturii desenate electronic și identificatori criptografici de integritate.

## 2. Baza tehnică avută în vedere

Implementarea urmărește cerințele tehnice relevante din cadrul OPANAF nr. 146/2022 privind schimbul de informații între ANAF și persoanele juridice de drept privat semnatare ale protocolului. În special, pentru semnătura electronică simplă / acceptarea expresă, acordul trebuie generat în format PDF, iar semnătura trebuie să cuprindă numele și prenumele în clar ale contribuabilului/reprezentantului legal, data și ora acceptării exprese, precum și IP-ul stației de unde s-a dat acordul.

Sistemul este proiectat ca mecanism de acceptare electronică simplă și probă tehnică de acceptare. Sistemul nu pretinde că aplică o semnătură electronică calificată și nu inserează în mod fals o semnătură digitală calificată de tip PAdES. Dacă, pentru un anumit flux, se solicită semnătură calificată, aceasta trebuie furnizată separat printr-un prestator de servicii de încredere calificat.

## 3. Descrierea fluxului de acceptare

1. Clientul accesează formularul ANAF printr-o pagină publică neindexabilă sau printr-un link unic generat din panoul de administrare.
2. Formularul solicită datele necesare pentru completarea acordului: nume, prenume, CNP, date CI, adresă CI, email, telefon și referința cererii/dosarului, după caz.
3. Clientul citește textul acordului și confirmă prin bifarea explicită a casetei de acord.
4. Clientul aplică semnătura desenată electronic într-un chenar dedicat, utilizând mouse-ul sau, pe dispozitive mobile, degetul/stylusul.
5. La trimiterea formularului, serverul validează datele, semnătura și măsurile anti-abuz.
6. Serverul înregistrează momentul acceptării, IP-ul stației de unde s-a transmis formularul, hash-ul user-agentului, hash-ul semnăturii și hash-ul probatoriu al întregului pachet de acceptare.
7. Sistemul generează un PDF completat pe baza șablonului oficial de acord, care include datele introduse, semnătura desenată și zona de audit tehnic.
8. Clientul primește după trimitere un link semnat, valabil 7 zile, pentru descărcarea PDF-ului completat.
9. Administratorii autorizați pot descărca același PDF din panoul intern, pentru arhivare sau transmitere către ANAF.

## 4. Date tehnice înregistrate la acceptare

| Element | Modul de colectare / generare | Utilizare în probă |
|---|---|---|
| Nume și prenume | Câmpuri completate de client sau precompletate în linkul unic | Apar în câmpurile PDF și în zona de audit ca semnatar în clar |
| CNP și date CI | Câmpuri de formular validate server-side | Completează câmpurile PDF ale acordului |
| Data și ora acceptării | Timestamp server-side la momentul acceptării | Apare în PDF ca moment al acceptării |
| IP-ul stației | Valoarea IP primită de aplicație la cererea HTTP de submit | Apare în PDF ca IP de trimitere |
| Imaginea semnăturii | Canvas HTML, exportat ca imagine PNG și validat server-side | Este inserată vizual în PDF |
| Hash semnătură | SHA-256 calculat din imaginea semnăturii | Permite verificarea integrității imaginii stocate |
| Hash IP | HMAC-SHA-256 calculat din IP | Permite audit fără expunerea IP-ului în clar în baza de date |
| Hash user-agent | HMAC-SHA-256 calculat din user-agent | Completează amprenta tehnică a sesiunii |
| Audit hash | SHA-256 peste pachetul de acceptare | Demonstrează integritatea ansamblului de date acceptate |
| Audit seal | HMAC-SHA-256 peste audit hash, cu secretul aplicației | Detectează modificarea ulterioară a pachetului de audit |
| Hash PDF | SHA-256 calculat la descărcarea PDF-ului | Este transmis ca header tehnic `X-Document-SHA256` |

## 5. Generarea PDF-ului de acord

PDF-ul este generat pe baza șablonului intern `resources/acord-anaf-template.pdf`, derivat din formularul de acord ANAF utilizat de societate. Generatorul completează câmpurile formularului PDF și adaugă aparențe vizuale pentru ca datele să fie vizibile în cititoare PDF standard.

PDF-ul generat conține:

- data acordului;
- numele și prenumele în clar;
- datele CI și CNP-ul;
- bifa de acceptare;
- imaginea semnăturii desenate electronic;
- numele complet al semnatarului în zona de audit;
- data și ora acceptării exprese;
- IP-ul stației de unde s-a transmis acordul;
- hash-ul semnăturii;
- audit hash și audit seal.

Zona de semnătură este tratată ca semnătură electronică simplă / acceptare expresă auditabilă, nu ca semnătură electronică calificată. Din acest motiv, sistemul nu inserează un câmp PDF criptografic de tip semnătură calificată, ci o reprezentare vizuală și un audit trail tehnic verificabil.

## 6. Măsuri de securitate și control

Sistemul aplică următoarele măsuri tehnice:

- token unic pentru linkurile precompletate, stocat doar ca hash;
- expirarea linkurilor de completare și dezactivarea tokenului după trimitere;
- protecție CSRF pentru formular;
- câmp honeypot anti-bot;
- limitare de trafic pe IP și pe token;
- suport reCAPTCHA v3 configurabil pentru formularul ANAF;
- validarea server-side a CNP-ului, datei CI, câmpurilor obligatorii și imaginii de semnătură;
- criptarea datelor sensibile în baza de date cu AES-256-GCM;
- hash-uri HMAC pentru IP, user-agent și tokenuri;
- stocarea semnăturii desenate ca imagine criptată;
- acces administrativ doar pentru utilizatori autentificați;
- headere de securitate pentru răspunsurile HTTP;
- PDF-urile de descărcare sunt livrate cu `Cache-Control: no-store, no-cache, must-revalidate`.

## 7. Arhivare și acces ulterior

După acceptare, datele formularului, semnătura și metadatele de audit sunt păstrate în baza de date criptată a aplicației. Linkul public inițial nu mai poate fi reutilizat. PDF-ul poate fi regenerat ulterior din datele salvate și poate fi pus la dispoziția clientului sau a reprezentanților autorizați.

Pentru client, se generează un link de descărcare semnat, cu valabilitate de 7 zile. Linkul conține o semnătură HMAC și nu este valid dacă este modificat sau expirat.

Pentru administratori, PDF-ul poate fi descărcat din panoul intern. Fiecare PDF generat conține aceleași elemente de identificare și audit.

## 8. Verificarea integrității

Integritatea acceptării poate fi verificată astfel:

1. Se recalculează hash-ul semnăturii din imaginea semnăturii stocate.
2. Se recalculează audit hash din datele acceptate, versiunea textului acordului, hash-ul semnăturii, hash-ul IP, hash-ul user-agent și timestamp.
3. Se recalculează audit seal cu secretul aplicației.
4. Dacă valorile recalculate coincid cu valorile stocate, pachetul de acceptare nu a fost modificat față de momentul înregistrării.
5. Pentru PDF-ul livrat, se poate calcula SHA-256 al fișierului și compara cu headerul `X-Document-SHA256` transmis la descărcare.

## 9. Condiții operaționale necesare

Pentru ca metoda să rămână corectă tehnic, trebuie respectate următoarele condiții operaționale:

- aplicația trebuie servită exclusiv prin HTTPS în producție;
- serverul trebuie sincronizat prin NTP sau mecanism echivalent, pentru acuratețea timestampului;
- dacă aplicația rulează în spatele unui reverse proxy, load balancer sau CDN, infrastructura trebuie configurată astfel încât aplicația să primească IP-ul real al clientului în câmpul utilizat pentru `REMOTE_ADDR`;
- secretul aplicației folosit pentru HMAC și criptare trebuie păstrat confidențial și rotit conform politicii interne;
- backupurile bazei de date trebuie protejate cel puțin la același nivel ca baza de date principală;
- accesul administrativ trebuie limitat la personal autorizat și auditat procedural;
- perioada de retenție trebuie aliniată politicii interne și cerințelor protocolului aplicabil cu ANAF.

## 10. Mapare cerință - implementare

| Cerință tehnică observată | Implementare în sistem |
|---|---|
| Acordul se generează în format PDF | PDF generat din șablonul `acord-anaf-template.pdf` |
| Numele și prenumele apar în clar | Câmpurile `Nume`, `Prenume` și zona `Semnatar` din PDF |
| Data și ora acceptării apar în PDF | Timestamp server-side în zona de audit |
| IP-ul stației apare în PDF | IP-ul cererii de submit este inclus ca `IP trimitere` |
| Semnătura electronică simplă este asociată cu acordul | Semnătura desenată este inserată ca imagine în zona de semnătură |
| Acceptarea este expresă și neechivocă | Bifă obligatorie de acord plus semnătură desenată și submit explicit |
| Acordul poate fi arhivat și pus la dispoziție | Date criptate în DB, PDF regenerabil pentru admin și client |
| Modificările ulterioare sunt detectabile | Hash semnătură, audit hash și audit seal HMAC |

## 11. Limitări declarate ale metodei

Metoda descrisă nu este o semnătură electronică calificată și nu substituie un certificat calificat emis de un prestator de servicii de încredere calificat. Ea reprezintă o metodă de acceptare expresă în format electronic, cu semnătură electronică simplă și audit trail tehnic.

În cazul în care ANAF sau protocolul aplicabil solicită pentru un anumit flux semnătură electronică calificată, fluxul trebuie completat cu un mecanism de semnare calificată separat.

## 12. Surse normative și tehnice consultate

- OPANAF nr. 146/2022 - Portal Legislativ: https://legislatie.just.ro/Public/DetaliiDocument/251680
- Legea nr. 214/2024 privind utilizarea semnăturii electronice, a mărcii temporale și prestarea serviciilor de încredere bazate pe acestea - Portal Legislativ: https://legislatie.just.ro/Public/DetaliiDocumentAfis/285178
- Regulamentul (UE) nr. 910/2014 (eIDAS) - Publications Office of the European Union: https://op.europa.eu/en/publication-detail/-/publication/23b61856-2e82-11e4-8c3c-01aa75ed71a1/language-en

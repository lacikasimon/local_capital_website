SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- Trust-focused IFN content layer. These values avoid unverified claims and
-- keep legal identifiers empty until the company validates the exact data.
INSERT INTO settings (language_code, setting_key, setting_value) VALUES
('ro', 'tagline', 'Creditare responsabilă, cu pași clari'),
('ro', 'footerText', 'LOCAL CAPITAL IFN S.A. oferă soluții de creditare pentru persoane fizice, cu informații clare despre costuri, rambursare și pașii de aplicare.'),
('ro', 'complaintsEmail', 'info@localcapital.ro'),
('ro', 'cui', ''),
('ro', 'registrationNumber', ''),
('ro', 'bnrRegistryType', ''),
('ro', 'bnrRegistryNumber', ''),
('ro', 'shareCapital', ''),
('en', 'tagline', 'Responsible lending with clear steps'),
('en', 'footerText', 'LOCAL CAPITAL IFN S.A. offers credit solutions for individuals, with clear information about costs, repayment, and application steps.'),
('en', 'complaintsEmail', 'info@localcapital.ro'),
('en', 'cui', ''),
('en', 'registrationNumber', ''),
('en', 'bnrRegistryType', ''),
('en', 'bnrRegistryNumber', ''),
('en', 'shareCapital', ''),
('hu', 'tagline', 'Felelős hitelezés, világos lépésekkel'),
('hu', 'footerText', 'A LOCAL CAPITAL IFN S.A. magánszemélyeknek kínál hitelmegoldásokat, világos információkkal a költségekről, törlesztésről és igénylési lépésekről.'),
('hu', 'complaintsEmail', 'info@localcapital.ro'),
('hu', 'cui', ''),
('hu', 'registrationNumber', ''),
('hu', 'bnrRegistryType', ''),
('hu', 'bnrRegistryNumber', ''),
('hu', 'shareCapital', '')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

INSERT INTO navigation (language_code, nav_key, label, path, sort_order, visible) VALUES
('ro', 'guide', 'Ghid client', '/ghid-client', 35, 1),
('en', 'guide', 'Client guide', '/client-guide', 35, 1),
('hu', 'guide', 'Ügyfél-tájékoztató', '/ugyfel-tajekoztato', 35, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label), path = VALUES(path), sort_order = VALUES(sort_order), visible = VALUES(visible);

UPDATE navigation SET sort_order = 40 WHERE nav_key = 'blog';
UPDATE navigation SET sort_order = 50 WHERE nav_key = 'contact';

UPDATE pages
SET
  title = 'Credit pentru nevoi personale, cu pași clari și costuri explicate',
  summary = 'Local Capital oferă soluții de creditare pentru persoane fizice, cu evaluare responsabilă și informații transparente înainte de semnare.',
  body = '## Decizia de credit trebuie luată informat

Local Capital te ajută să înțelegi pașii unei solicitări de credit: discutăm nevoia, verificăm informațiile necesare și îți explicăm costurile, DAE, durata și opțiunile de rambursare înainte de semnarea contractului.

Nu promitem aprobare garantată și nu recomandăm un împrumut fără analiza bugetului personal. Scopul nostru este ca fiecare client să poată compara oferta, să înțeleagă obligația financiară și să aleagă responsabil.',
  cta_label = 'Verifică pașii',
  cta_href = '/ghid-client',
  secondary_cta_label = 'Discută cu un consultant',
  secondary_cta_href = '/contact',
  extra_json = '{"featuresTitle":"Ce primești înainte de decizie","features":[{"title":"Costuri explicate","text":"Primești informații despre DAE, totalul de plată, durată și condiții înainte de semnarea contractului."},{"title":"Evaluare responsabilă","text":"Solicitarea este analizată în funcție de datele furnizate și de capacitatea de rambursare."},{"title":"Proces pas cu pas","text":"Știi ce documente sunt necesare, cum se verifică eligibilitatea și când primești oferta."}],"servicesTitle":"Soluții de creditare pentru nevoi reale","servicesIntro":"Produsele Local Capital sunt prezentate cu accent pe claritate, rambursare și utilizare responsabilă.","services":[{"title":"Nevoi personale","text":"Sprijin financiar pentru cheltuieli planificate sau neprevăzute, cu ofertă discutată înainte de semnare.","image":"/assets/service-health.jpg"},{"title":"Locuință și familie","text":"Finanțare pentru proiecte personale, reparații, familie sau alte obiective importante.","image":"/assets/service-home.jpg"},{"title":"Planuri și mobilitate","text":"Soluții pentru proiecte personale, achiziții sau consolidarea unor obligații existente, în funcție de eligibilitate.","image":"/assets/service-car.jpg"}],"requirementsTitle":"Pașii unei solicitări","requirements":[{"title":"1. Date inițiale","text":"Pentru solicitarea inițială este necesar un act de identitate valid și date de contact corecte."},{"title":"2. Analiză","text":"În funcție de solicitare, pot fi necesare informații suplimentare pentru verificarea eligibilității."},{"title":"3. Ofertă","text":"Înainte de semnare se comunică durata, costul total, DAE și obligațiile de rambursare."}],"responsibleNotice":"Creditul reprezintă o obligație financiară. Analizează costul total, DAE, durata, comisioanele și capacitatea ta de rambursare înainte de semnarea contractului.","loanExample":{"title":"Ce verifici în oferta de credit","intro":"Valorile exacte sunt comunicate în oferta personalizată, înainte de semnare. Nu folosi un credit fără să înțelegi aceste elemente.","items":[{"label":"Valoarea creditului","value":"suma solicitată și aprobată"},{"label":"Durata contractului","value":"perioada de rambursare stabilită în ofertă"},{"label":"DAE","value":"indicatorul care arată costul total anualizat al creditului"},{"label":"Total de plată","value":"suma totală pe care o rambursezi conform contractului"}],"note":"Cifrele concrete trebuie completate doar pe baza grilei de costuri validate juridic și comercial."},"documents":[{"label":"Informare drepturi persoane vizate","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Politica de retenție date","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"aiSummary":"Local Capital IFN oferă credite pentru persoane fizice, cu pași clari, evaluare responsabilă și explicarea costurilor, DAE și rambursării înainte de semnare.","faq":[{"question":"Ce trebuie să verific înainte de a semna un contract de credit?","answer":"Verifică suma împrumutată, durata, DAE, costul total, comisioanele, rata de rambursare și consecințele întârzierii la plată."},{"question":"Aprobarea este garantată?","answer":"Nu. O solicitare de credit este analizată pe baza datelor furnizate și a criteriilor de eligibilitate."},{"question":"De ce este importantă DAE?","answer":"DAE ajută la compararea costului total anualizat al creditului, incluzând dobânda și costurile relevante."}]}'
WHERE page_key = 'home' AND language_code = 'ro';

UPDATE pages
SET
  title = 'Credit for personal needs, with clear steps and explained costs',
  summary = 'Local Capital offers credit solutions for individuals, with responsible assessment and transparent information before signing.',
  body = '## A credit decision should be informed

Local Capital helps you understand the steps of a credit request: we discuss the need, check the required information, and explain the costs, APR, duration, and repayment options before the contract is signed.

We do not promise guaranteed approval and we do not recommend borrowing without reviewing the personal budget. The goal is for every client to compare the offer, understand the financial obligation, and choose responsibly.',
  cta_label = 'Check the steps',
  cta_href = '/client-guide',
  secondary_cta_label = 'Talk to a consultant',
  secondary_cta_href = '/contact',
  extra_json = '{"featuresTitle":"What you receive before deciding","features":[{"title":"Explained costs","text":"You receive information about APR, total repayment, duration, and conditions before signing."},{"title":"Responsible assessment","text":"The request is reviewed based on the information provided and repayment capacity."},{"title":"Step-by-step process","text":"You know which documents are needed, how eligibility is checked, and when the offer is provided."}],"servicesTitle":"Credit solutions for real needs","servicesIntro":"Local Capital products are presented with a focus on clarity, repayment, and responsible use.","services":[{"title":"Personal needs","text":"Financial support for planned or unexpected expenses, with an offer discussed before signing.","image":"/assets/service-health.jpg"},{"title":"Home and family","text":"Financing for personal projects, repairs, family needs, or other important objectives.","image":"/assets/service-home.jpg"},{"title":"Plans and mobility","text":"Solutions for personal projects, purchases, or consolidation of existing obligations, depending on eligibility.","image":"/assets/service-car.jpg"}],"requirementsTitle":"Request steps","requirements":[{"title":"1. Initial data","text":"For the initial request, a valid identity document and correct contact details are needed."},{"title":"2. Review","text":"Depending on the request, additional information may be needed for eligibility checks."},{"title":"3. Offer","text":"Before signing, the duration, total cost, APR, and repayment obligations are communicated."}],"responsibleNotice":"Credit is a financial obligation. Review the total cost, APR, duration, fees, and your repayment capacity before signing the contract.","loanExample":{"title":"What to check in the credit offer","intro":"Exact values are communicated in the personalized offer before signing. Do not use credit without understanding these elements.","items":[{"label":"Credit amount","value":"the requested and approved amount"},{"label":"Contract duration","value":"the repayment period set in the offer"},{"label":"APR","value":"the indicator showing the annualized total cost of credit"},{"label":"Total repayment","value":"the total amount repaid under the contract"}],"note":"Concrete figures must be filled only based on the legally and commercially validated cost grid."},"documents":[{"label":"Data subject rights notice","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Data retention policy","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"aiSummary":"Local Capital IFN offers credit for individuals, with clear steps, responsible assessment, and cost, APR, and repayment explanations before signing.","faq":[{"question":"What should I check before signing a credit contract?","answer":"Check the borrowed amount, duration, APR, total cost, fees, repayment installment, and consequences of late payment."},{"question":"Is approval guaranteed?","answer":"No. A credit request is reviewed based on the information provided and eligibility criteria."},{"question":"Why is APR important?","answer":"APR helps compare the annualized total cost of credit, including interest and relevant costs."}]}'
WHERE page_key = 'home' AND language_code = 'en';

UPDATE pages
SET
  title = 'Személyi hitel világos lépésekkel és ismertetett költségekkel',
  summary = 'A Local Capital magánszemélyeknek kínál hitelmegoldásokat felelős elbírálással és átlátható tájékoztatással aláírás előtt.',
  body = '## A hiteldöntés legyen tájékozott döntés

A Local Capital segít megérteni a hiteligénylés lépéseit: megbeszéljük az igényt, ellenőrizzük a szükséges információkat, és aláírás előtt ismertetjük a költségeket, a DAE/THM jellegű mutatót, a futamidőt és a törlesztési lehetőségeket.

Nem ígérünk garantált jóváhagyást, és nem javaslunk hitelt a személyes költségvetés átgondolása nélkül. Célunk, hogy minden ügyfél össze tudja hasonlítani az ajánlatot, értse a pénzügyi kötelezettséget, és felelősen döntsön.',
  cta_label = 'Lépések megtekintése',
  cta_href = '/ugyfel-tajekoztato',
  secondary_cta_label = 'Beszélj tanácsadóval',
  secondary_cta_href = '/kapcsolat',
  extra_json = '{"featuresTitle":"Amit döntés előtt megkapsz","features":[{"title":"Ismertetett költségek","text":"Aláírás előtt információt kapsz a DAE/THM jellegű mutatóról, a teljes törlesztésről, futamidőről és feltételekről."},{"title":"Felelős elbírálás","text":"A kérelmet a megadott adatok és a törlesztési képesség figyelembevételével vizsgáljuk."},{"title":"Lépésről lépésre folyamat","text":"Tudod, milyen dokumentum kell, hogyan történik a jogosultság ellenőrzése, és mikor érkezik az ajánlat."}],"servicesTitle":"Hitelmegoldások valós igényekre","servicesIntro":"A Local Capital termékei az átláthatóságra, törlesztésre és felelős használatra épülnek.","services":[{"title":"Személyes igények","text":"Pénzügyi támogatás tervezett vagy váratlan kiadásokra, aláírás előtt megbeszélt ajánlattal.","image":"/assets/service-health.jpg"},{"title":"Otthon és család","text":"Finanszírozás személyes projektekre, javításokra, családi igényekre vagy fontos célokra.","image":"/assets/service-home.jpg"},{"title":"Tervek és mobilitás","text":"Megoldások személyes projektekre, vásárlásokra vagy meglévő kötelezettségek rendezésére, jogosultságtól függően.","image":"/assets/service-car.jpg"}],"requirementsTitle":"Az igénylés lépései","requirements":[{"title":"1. Kezdeti adatok","text":"Az első igényléshez érvényes személyazonosító okmány és pontos elérhetőségek szükségesek."},{"title":"2. Elbírálás","text":"Az igénytől függően további információkra lehet szükség a jogosultság ellenőrzéséhez."},{"title":"3. Ajánlat","text":"Aláírás előtt közöljük a futamidőt, teljes költséget, DAE/THM jellegű mutatót és törlesztési kötelezettséget."}],"responsibleNotice":"A hitel pénzügyi kötelezettség. Aláírás előtt vizsgáld meg a teljes költséget, a DAE/THM jellegű mutatót, a futamidőt, díjakat és a törlesztési képességedet.","loanExample":{"title":"Mit ellenőrizz a hitelajánlatban","intro":"A pontos értékek a személyre szabott ajánlatban, aláírás előtt jelennek meg. Ne vegyél fel hitelt ezek megértése nélkül.","items":[{"label":"Hitelösszeg","value":"az igényelt és jóváhagyott összeg"},{"label":"Szerződés futamideje","value":"az ajánlatban szereplő törlesztési időszak"},{"label":"DAE/THM jellegű mutató","value":"a hitel évesített teljes költségét jelző mutató"},{"label":"Teljes visszafizetés","value":"a szerződés szerint visszafizetendő teljes összeg"}],"note":"Konkrét számok csak jogilag és üzletileg validált költségtábla alapján tölthetők ki."},"documents":[{"label":"Érintetti jogokról szóló tájékoztató","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Adatmegőrzési szabályzat","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"aiSummary":"A Local Capital IFN magánszemélyeknek kínál hitelt világos lépésekkel, felelős elbírálással és a költségek, DAE/THM és törlesztés aláírás előtti ismertetésével.","faq":[{"question":"Mit kell ellenőriznem hitelszerződés aláírása előtt?","answer":"Ellenőrizd a felvett összeget, futamidőt, DAE/THM jellegű mutatót, teljes költséget, díjakat, törlesztőrészletet és a késedelmes fizetés következményeit."},{"question":"Garantált a jóváhagyás?","answer":"Nem. A hitelkérelmet a megadott adatok és jogosultsági feltételek alapján vizsgáljuk."},{"question":"Miért fontos a DAE/THM jellegű mutató?","answer":"Segít összehasonlítani a hitel évesített teljes költségét, beleértve a kamatot és releváns költségeket."}]}'
WHERE page_key = 'home' AND language_code = 'hu';

UPDATE pages
SET
  title = 'Despre Local Capital IFN',
  summary = 'Local Capital este o instituție financiară nebancară din România, orientată spre creditare responsabilă, transparență și comunicare clară.',
  body = '## Cine suntem

LOCAL CAPITAL IFN S.A. este operatorul serviciilor Local Capital. Activitatea noastră este orientată către soluții de creditare pentru persoane fizice, cu accent pe transparență, evaluare responsabilă și explicarea costurilor înainte de semnarea contractului.

## Principiile noastre

Un credit trebuie înțeles înainte de a fi semnat. De aceea, punem accent pe informații clare despre DAE, costul total, durata contractului, rambursare și consecințele întârzierii la plată.

## Relația cu clientul

Fiecare solicitare este analizată individual. Înainte de semnare, clientul trebuie să primească informațiile necesare pentru a decide dacă produsul este potrivit bugetului și nevoii sale.',
  extra_json = '{"valuesTitle":"Principii de lucru","values":[{"title":"Transparență","text":"Costurile, DAE și obligațiile de rambursare trebuie explicate înainte de semnare."},{"title":"Responsabilitate","text":"Analizăm solicitarea cu atenție și nu prezentăm creditul ca pe o soluție garantată."},{"title":"Claritate","text":"Folosim un limbaj simplu pentru pașii, documentele și condițiile importante."}],"responsibleNotice":"Verifică întotdeauna costul total al creditului și capacitatea ta de rambursare înainte de asumarea unei obligații financiare.","documents":[{"label":"Informare drepturi persoane vizate","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Politica de retenție date","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"faq":[{"question":"Ce este Local Capital?","answer":"Local Capital este marca prin care LOCAL CAPITAL IFN S.A. comunică soluții de creditare pentru persoane fizice."},{"question":"De ce sunt importante datele legale?","answer":"Datele legale ajută clientul să identifice operatorul, să verifice informațiile și să știe unde poate trimite solicitări sau reclamații."},{"question":"Cum pot trimite o solicitare privind datele personale?","answer":"Poți scrie la adresa de protecție a datelor afișată pe site."}]}'
WHERE page_key = 'about' AND language_code = 'ro';

UPDATE pages
SET
  title = 'About Local Capital IFN',
  summary = 'Local Capital is a Romanian non-bank financial institution focused on responsible lending, transparency, and clear communication.',
  body = '## Who we are

LOCAL CAPITAL IFN S.A. is the operator of the Local Capital services. Our activity focuses on credit solutions for individuals, with an emphasis on transparency, responsible assessment, and cost explanations before contract signing.

## Our principles

Credit must be understood before it is signed. That is why we emphasize clear information about APR, total cost, contract duration, repayment, and consequences of late payment.

## Client relationship

Every request is reviewed individually. Before signing, the client should receive the information needed to decide whether the product fits their budget and need.',
  extra_json = '{"valuesTitle":"Working principles","values":[{"title":"Transparency","text":"Costs, APR, and repayment obligations should be explained before signing."},{"title":"Responsibility","text":"We review the request carefully and do not present credit as a guaranteed solution."},{"title":"Clarity","text":"We use simple language for important steps, documents, and conditions."}],"responsibleNotice":"Always check the total cost of credit and your repayment capacity before taking on a financial obligation.","documents":[{"label":"Data subject rights notice","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Data retention policy","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"faq":[{"question":"What is Local Capital?","answer":"Local Capital is the brand through which LOCAL CAPITAL IFN S.A. communicates credit solutions for individuals."},{"question":"Why are legal details important?","answer":"Legal details help the client identify the operator, verify information, and know where to send requests or complaints."},{"question":"How can I send a personal data request?","answer":"You can write to the data protection address displayed on the website."}]}'
WHERE page_key = 'about' AND language_code = 'en';

UPDATE pages
SET
  title = 'A Local Capital IFN-ről',
  summary = 'A Local Capital romániai nem banki pénzügyi intézmény, amely felelős hitelezésre, átláthatóságra és világos kommunikációra épít.',
  body = '## Kik vagyunk

A LOCAL CAPITAL IFN S.A. a Local Capital szolgáltatások üzemeltetője. Tevékenységünk magánszemélyeknek szóló hitelmegoldásokra fókuszál, átláthatósággal, felelős elbírálással és a költségek aláírás előtti ismertetésével.

## Alapelveink

A hitelt meg kell érteni, mielőtt aláírják. Ezért hangsúlyt helyezünk a DAE/THM jellegű mutatóra, a teljes költségre, a futamidőre, törlesztésre és a késedelmes fizetés következményeire.

## Ügyfélkapcsolat

Minden kérelmet egyedileg vizsgálunk. Aláírás előtt az ügyfélnek meg kell kapnia azokat az információkat, amelyek alapján eldöntheti, hogy a termék illik-e a költségvetéséhez és igényéhez.',
  extra_json = '{"valuesTitle":"Működési elvek","values":[{"title":"Átláthatóság","text":"A költségeket, DAE/THM jellegű mutatót és törlesztési kötelezettségeket aláírás előtt ismertetni kell."},{"title":"Felelősség","text":"A kérelmet körültekintően vizsgáljuk, és a hitelt nem mutatjuk be garantált megoldásként."},{"title":"Érthetőség","text":"Egyszerű nyelvet használunk a fontos lépésekhez, dokumentumokhoz és feltételekhez."}],"responsibleNotice":"Pénzügyi kötelezettség vállalása előtt mindig ellenőrizd a hitel teljes költségét és a törlesztési képességedet.","documents":[{"label":"Érintetti jogokról szóló tájékoztató","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Adatmegőrzési szabályzat","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"faq":[{"question":"Mi a Local Capital?","answer":"A Local Capital az a márka, amelyen keresztül a LOCAL CAPITAL IFN S.A. magánszemélyeknek szóló hitelmegoldásokat kommunikál."},{"question":"Miért fontosak a jogi adatok?","answer":"A jogi adatok segítenek az üzemeltető azonosításában, az információk ellenőrzésében, valamint kérelmek és panaszok benyújtásában."},{"question":"Hogyan küldhetek személyes adatokkal kapcsolatos kérelmet?","answer":"Írhatsz a weboldalon megadott adatvédelmi címre."}]}'
WHERE page_key = 'about' AND language_code = 'hu';

UPDATE pages
SET
  title = 'Credite și costuri explicate înainte de semnare',
  summary = 'Produse de credit prezentate cu pași clari, informații despre DAE, cost total și rambursare responsabilă.',
  body = '## Alege doar după ce înțelegi oferta

Produsele Local Capital sunt discutate în funcție de nevoia clientului, eligibilitate și capacitatea de rambursare. Înainte de semnare, clientul trebuie să înțeleagă suma împrumutată, durata, DAE, costul total, ratele și consecințele neplății.

## Produse disponibile

Produsele pot fi adaptate în funcție de analiza solicitării. Denumirile comerciale nu înlocuiesc oferta personalizată și documentele contractuale.',
  extra_json = '{"productsTitle":"Produse de credit","products":[{"title":"Credit Flex Basic","text":"Variantă pentru nevoi personale, cu rambursare discutată în funcție de buget și eligibilitate."},{"title":"Credit Flex Basic PMT","text":"Variantă cu rate lunare planificate, comunicată prin oferta personalizată înainte de semnare."},{"title":"Credit Flex Juridic","text":"Soluție discutată individual, în funcție de situație, documente și analiza solicitării."}],"processTitle":"Proces de aplicare","process":[{"title":"1. Solicitare","text":"Transmiți datele inițiale și nevoia de finanțare."},{"title":"2. Verificare","text":"Analizăm eligibilitatea și putem solicita informații suplimentare."},{"title":"3. Ofertă","text":"Primești informații despre cost total, DAE, durată și rambursare."},{"title":"4. Decizie","text":"Semnezi doar dacă înțelegi și accepți condițiile."}],"responsibleNotice":"Nu lua o decizie doar pe baza vitezei. Compară DAE, costul total și obligațiile de plată.","loanExample":{"title":"Elemente obligatorii în oferta personalizată","items":[{"label":"Suma creditului","value":"valoarea aprobată pentru client"},{"label":"Durata","value":"numărul de luni sau perioada contractuală"},{"label":"DAE","value":"costul total anualizat al creditului"},{"label":"Cost total","value":"valoarea totală plătibilă conform contractului"},{"label":"Întârziere la plată","value":"consecințele și costurile comunicate contractual"}],"note":"Completează valorile numerice după validarea grilei de costuri și a documentelor juridice."},"faq":[{"question":"Ce este DAE?","answer":"DAE este dobânda anuală efectivă și ajută la înțelegerea costului total anualizat al creditului."},{"question":"Pot rambursa anticipat?","answer":"Condițiile de rambursare anticipată trebuie verificate în oferta și contractul comunicat clientului."},{"question":"Ce se întâmplă dacă întârzii plata?","answer":"Întârzierea poate genera costuri suplimentare și alte consecințe contractuale. Acestea trebuie citite înainte de semnare."}]}'
WHERE page_key = 'contract' AND language_code = 'ro';

UPDATE pages
SET
  title = 'Loans and costs explained before signing',
  summary = 'Credit products presented with clear steps, APR, total cost, and responsible repayment information.',
  body = '## Choose only after understanding the offer

Local Capital products are discussed based on the client need, eligibility, and repayment capacity. Before signing, the client should understand the borrowed amount, duration, APR, total cost, installments, and consequences of non-payment.

## Available products

Products may be adapted depending on the request assessment. Commercial names do not replace the personalized offer and contractual documents.',
  extra_json = '{"productsTitle":"Credit products","products":[{"title":"Credit Flex Basic","text":"Option for personal needs, with repayment discussed according to budget and eligibility."},{"title":"Credit Flex Basic PMT","text":"Option with planned monthly installments, communicated through the personalized offer before signing."},{"title":"Credit Flex Legal","text":"Individually discussed solution depending on the situation, documents, and request assessment."}],"processTitle":"Application process","process":[{"title":"1. Request","text":"You send the initial data and financing need."},{"title":"2. Check","text":"We review eligibility and may request additional information."},{"title":"3. Offer","text":"You receive information about total cost, APR, duration, and repayment."},{"title":"4. Decision","text":"You sign only if you understand and accept the conditions."}],"responsibleNotice":"Do not decide based only on speed. Compare APR, total cost, and payment obligations.","loanExample":{"title":"Mandatory elements in the personalized offer","items":[{"label":"Credit amount","value":"the amount approved for the client"},{"label":"Duration","value":"number of months or contractual period"},{"label":"APR","value":"annualized total cost of credit"},{"label":"Total cost","value":"total amount payable under the contract"},{"label":"Late payment","value":"contractual consequences and costs"}],"note":"Fill numerical values after validating the cost grid and legal documents."},"faq":[{"question":"What is APR?","answer":"APR is the annual percentage rate and helps understand the annualized total cost of credit."},{"question":"Can I repay early?","answer":"Early repayment conditions should be checked in the offer and contract communicated to the client."},{"question":"What happens if I pay late?","answer":"Late payment may generate additional costs and other contractual consequences. These must be read before signing."}]}'
WHERE page_key = 'contract' AND language_code = 'en';

UPDATE pages
SET
  title = 'Hitelek és költségek aláírás előtti ismertetése',
  summary = 'Hiteltermékek világos lépésekkel, DAE/THM jellegű mutatóval, teljes költséggel és felelős törlesztési információkkal.',
  body = '## Csak akkor dönts, ha érted az ajánlatot

A Local Capital termékei az ügyfél igénye, jogosultsága és törlesztési képessége alapján kerülnek megbeszélésre. Aláírás előtt az ügyfélnek értenie kell a hitelösszeget, futamidőt, DAE/THM jellegű mutatót, teljes költséget, részleteket és a nemfizetés következményeit.

## Elérhető termékek

A termékek az elbírálástól függően igazíthatók. A kereskedelmi elnevezések nem helyettesítik a személyre szabott ajánlatot és szerződéses dokumentumokat.',
  extra_json = '{"productsTitle":"Hiteltermékek","products":[{"title":"Credit Flex Basic","text":"Személyes igényekre szóló opció, a költségvetés és jogosultság alapján megbeszélt törlesztéssel."},{"title":"Credit Flex Basic PMT","text":"Tervezett havi részletekkel működő opció, amelyet aláírás előtt személyre szabott ajánlat mutat be."},{"title":"Credit Flex Juridic","text":"Egyedileg megbeszélt megoldás a helyzettől, dokumentumoktól és elbírálástól függően."}],"processTitle":"Igénylési folyamat","process":[{"title":"1. Kérelem","text":"Megadod a kezdeti adatokat és a finanszírozási igényt."},{"title":"2. Ellenőrzés","text":"Megvizsgáljuk a jogosultságot, és további információt kérhetünk."},{"title":"3. Ajánlat","text":"Információt kapsz a teljes költségről, DAE/THM jellegű mutatóról, futamidőről és törlesztésről."},{"title":"4. Döntés","text":"Csak akkor írsz alá, ha érted és elfogadod a feltételeket."}],"responsibleNotice":"Ne csak a gyorsaság alapján dönts. Hasonlítsd össze a DAE/THM jellegű mutatót, a teljes költséget és a fizetési kötelezettségeket.","loanExample":{"title":"Kötelező elemek a személyre szabott ajánlatban","items":[{"label":"Hitelösszeg","value":"az ügyfél számára jóváhagyott összeg"},{"label":"Futamidő","value":"hónapok száma vagy szerződéses időszak"},{"label":"DAE/THM jellegű mutató","value":"a hitel évesített teljes költsége"},{"label":"Teljes költség","value":"a szerződés szerint fizetendő teljes összeg"},{"label":"Késedelmes fizetés","value":"szerződéses következmények és költségek"}],"note":"A konkrét számokat a költségtábla és jogi dokumentumok validálása után kell kitölteni."},"faq":[{"question":"Mi a DAE/THM jellegű mutató?","answer":"A hitel évesített teljes költségét mutató érték, amely segít a költségek megértésében."},{"question":"Lehet előtörleszteni?","answer":"Az előtörlesztés feltételeit az ügyfélnek közölt ajánlatban és szerződésben kell ellenőrizni."},{"question":"Mi történik késedelmes fizetésnél?","answer":"A késedelem további költségeket és szerződéses következményeket eredményezhet. Ezeket aláírás előtt el kell olvasni."}]}'
WHERE page_key = 'contract' AND language_code = 'hu';

INSERT INTO pages (language_code, page_key, path, title, summary, body, cta_label, cta_href, secondary_cta_label, secondary_cta_href, extra_json) VALUES
('ro', 'guide', '/ghid-client', 'Ghid client: costuri, DAE și rambursare', 'Informații utile pentru a înțelege un credit înainte de semnare.', '## Înainte să aplici

Un credit trebuie evaluat în funcție de costul total, DAE, durata contractului, rata de rambursare și consecințele întârzierii la plată.

## Ce este DAE

DAE este un indicator care ajută la compararea costului total anualizat al creditului. Nu analiza doar rata lunară: verifică și comisioanele, costurile suplimentare și suma totală de rambursat.

## Când să ceri clarificări

Cere explicații dacă nu înțelegi o condiție, un cost sau o consecință contractuală. Semnează doar după ce toate informațiile importante sunt clare.', NULL, NULL, NULL, NULL, '{"responsibleNotice":"Acest ghid este informativ și nu înlocuiește oferta personalizată, documentele contractuale sau consultanța juridică/financiară.","processTitle":"Checklist înainte de semnare","process":[{"title":"Cost total","text":"Verifică suma totală pe care o vei rambursa."},{"title":"DAE","text":"Compară indicatorul DAE cu alte oferte disponibile."},{"title":"Durată","text":"Asigură-te că perioada de rambursare se potrivește bugetului."},{"title":"Întârziere","text":"Citește costurile și consecințele întârzierii la plată."}],"faq":[{"question":"DAE este același lucru cu dobânda?","answer":"Nu. DAE include dobânda și alte costuri relevante, fiind un indicator al costului total anualizat."},{"question":"Pot refuza oferta după ce primesc informațiile?","answer":"Da. Solicitarea de informații nu trebuie tratată ca obligație de semnare."},{"question":"Unde trimit o reclamație?","answer":"Poți folosi datele de contact afișate pe site și canalele ANPC/SAL/SOL din footer."}]}'),
('en', 'guide', '/client-guide', 'Client guide: costs, APR, and repayment', 'Useful information for understanding credit before signing.', '## Before applying

Credit should be assessed based on total cost, APR, contract duration, repayment installment, and consequences of late payment.

## What APR means

APR is an indicator that helps compare the annualized total cost of credit. Do not look only at the monthly installment: also check fees, additional costs, and the total amount to repay.

## When to ask for clarification

Ask for explanations if you do not understand a condition, cost, or contractual consequence. Sign only after the important information is clear.', NULL, NULL, NULL, NULL, '{"responsibleNotice":"This guide is informative and does not replace the personalized offer, contractual documents, or legal/financial advice.","processTitle":"Checklist before signing","process":[{"title":"Total cost","text":"Check the total amount you will repay."},{"title":"APR","text":"Compare the APR indicator with other available offers."},{"title":"Duration","text":"Make sure the repayment period fits your budget."},{"title":"Late payment","text":"Read the costs and consequences of late payment."}],"faq":[{"question":"Is APR the same as interest?","answer":"No. APR includes interest and other relevant costs and indicates the annualized total cost."},{"question":"Can I refuse the offer after receiving information?","answer":"Yes. Requesting information should not be treated as an obligation to sign."},{"question":"Where can I send a complaint?","answer":"You can use the contact details displayed on the site and the ANPC/SAL/SOL channels in the footer."}]}'),
('hu', 'guide', '/ugyfel-tajekoztato', 'Ügyfél-tájékoztató: költségek, DAE/THM és törlesztés', 'Hasznos információk a hitel aláírás előtti megértéséhez.', '## Igénylés előtt

A hitelt a teljes költség, DAE/THM jellegű mutató, futamidő, törlesztőrészlet és késedelmes fizetés következményei alapján érdemes értékelni.

## Mit jelent a DAE/THM jellegű mutató

Ez a mutató segít összehasonlítani a hitel évesített teljes költségét. Ne csak a havi részletet nézd: ellenőrizd a díjakat, további költségeket és a teljes visszafizetendő összeget is.

## Mikor kérj magyarázatot

Kérj magyarázatot, ha nem értesz egy feltételt, költséget vagy szerződéses következményt. Csak akkor írj alá, ha minden fontos információ világos.', NULL, NULL, NULL, NULL, '{"responsibleNotice":"Ez az útmutató tájékoztató jellegű, és nem helyettesíti a személyre szabott ajánlatot, szerződéses dokumentumokat vagy jogi/pénzügyi tanácsadást.","processTitle":"Ellenőrzőlista aláírás előtt","process":[{"title":"Teljes költség","text":"Ellenőrizd a teljes visszafizetendő összeget."},{"title":"DAE/THM","text":"Hasonlítsd össze a mutatót más elérhető ajánlatokkal."},{"title":"Futamidő","text":"Győződj meg róla, hogy a törlesztési időszak illik a költségvetésedhez."},{"title":"Késedelem","text":"Olvasd el a késedelmes fizetés költségeit és következményeit."}],"faq":[{"question":"A DAE/THM ugyanaz, mint a kamat?","answer":"Nem. A DAE/THM jellegű mutató a kamaton túl más releváns költségeket is figyelembe vehet, és az évesített teljes költséget jelzi."},{"question":"Visszautasíthatom az ajánlatot tájékoztatás után?","answer":"Igen. Az információkérést nem kell aláírási kötelezettségként kezelni."},{"question":"Hová küldhetek panaszt?","answer":"Használhatod a weboldalon megadott elérhetőségeket és a footerben szereplő ANPC/SAL/SOL csatornákat."}]}')
ON DUPLICATE KEY UPDATE
path = VALUES(path),
title = VALUES(title),
summary = VALUES(summary),
body = VALUES(body),
extra_json = VALUES(extra_json);

UPDATE pages
SET
  title = 'Contact Local Capital',
  summary = 'Contactează echipa Local Capital pentru informații despre eligibilitate, costuri, documente și pașii de aplicare.',
  body = '## Cere informații înainte de decizie

Pentru întrebări despre eligibilitate, costuri, DAE, rambursare, documente sau pașii de aplicare, folosește datele de contact de mai jos.

Nu trimite prin formular documente sensibile sau copii de acte. Echipa noastră îți va indica pașii potriviți în timpul programului de lucru.',
  extra_json = '{"formTitle":"Trimite o solicitare","privacyNote":"Prin trimiterea formularului confirmi că ai citit informarea privind prelucrarea datelor personale. Nu include copii de acte sau date sensibile în mesaj.","documents":[{"label":"Informare drepturi persoane vizate","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Politica de retenție date","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"faq":[{"question":"Pot primi informații înainte să aplic?","answer":"Da. Poți solicita explicații despre pași, documente, costuri și rambursare înainte de a decide."},{"question":"Pot trimite documente prin formular?","answer":"Nu recomandăm trimiterea documentelor sensibile prin formularul general de contact."},{"question":"Unde pot trimite o cerere privind datele personale?","answer":"Folosește adresa de protecție a datelor afișată pe site."}]}'
WHERE page_key = 'contact' AND language_code = 'ro';

UPDATE pages
SET
  title = 'Contact Local Capital',
  summary = 'Contact the Local Capital team for information about eligibility, costs, documents, and application steps.',
  body = '## Ask for information before deciding

For questions about eligibility, costs, APR, repayment, documents, or application steps, use the contact details below.

Do not send sensitive documents or identity copies through the form. Our team will indicate the suitable next steps during business hours.',
  extra_json = '{"formTitle":"Send a request","privacyNote":"By submitting the form, you confirm that you have read the personal data processing notice. Do not include identity copies or sensitive data in the message.","documents":[{"label":"Data subject rights notice","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Data retention policy","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"faq":[{"question":"Can I receive information before applying?","answer":"Yes. You can request explanations about steps, documents, costs, and repayment before deciding."},{"question":"Can I send documents through the form?","answer":"We do not recommend sending sensitive documents through the general contact form."},{"question":"Where can I send a personal data request?","answer":"Use the data protection address displayed on the website."}]}'
WHERE page_key = 'contact' AND language_code = 'en';

UPDATE pages
SET
  title = 'Kapcsolat Local Capital',
  summary = 'Lépj kapcsolatba a Local Capital csapatával jogosultsággal, költségekkel, dokumentumokkal és igénylési lépésekkel kapcsolatban.',
  body = '## Kérj információt döntés előtt

Jogosultsággal, költségekkel, DAE/THM jellegű mutatóval, törlesztéssel, dokumentumokkal vagy igénylési lépésekkel kapcsolatos kérdésekhez használd az alábbi elérhetőségeket.

Ne küldj érzékeny dokumentumokat vagy okmánymásolatot az általános űrlapon keresztül. Csapatunk munkaidőben jelzi a megfelelő következő lépéseket.',
  extra_json = '{"formTitle":"Kérelem küldése","privacyNote":"Az űrlap elküldésével megerősíted, hogy elolvastad a személyes adatok kezeléséről szóló tájékoztatót. Ne írj be okmánymásolatot vagy érzékeny adatot az üzenetbe.","documents":[{"label":"Érintetti jogokról szóló tájékoztató","href":"/downloads/informare-privind-drepturile-persoanelor-vizate.pdf"},{"label":"Adatmegőrzési szabályzat","href":"/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf"}],"faq":[{"question":"Kaphatok információt igénylés előtt?","answer":"Igen. Kérhetsz magyarázatot a lépésekről, dokumentumokról, költségekről és törlesztésről döntés előtt."},{"question":"Küldhetek dokumentumot az űrlapon?","answer":"Nem javasoljuk érzékeny dokumentumok küldését az általános kapcsolatfelvételi űrlapon keresztül."},{"question":"Hová küldhetek személyes adattal kapcsolatos kérelmet?","answer":"Használd a weboldalon megadott adatvédelmi címet."}]}'
WHERE page_key = 'contact' AND language_code = 'hu';

UPDATE posts
SET published = 0
WHERE source_type = 'case_study';

UPDATE posts
SET
  title = 'Credit rapid: ce verifici înainte de solicitare',
  excerpt = 'Documente, costuri și pași pe care merită să îi verifici înainte de a solicita un credit.',
  body = '## Credit rapid: ce verifici înainte de solicitare

Un credit rapid nu trebuie ales doar pentru viteză. Verifică suma de care ai nevoie, capacitatea de rambursare, costul total, DAE și consecințele întârzierii la plată.

Pentru solicitarea inițială este necesar un act de identitate valid. În funcție de analiza solicitării, pot fi necesare informații suplimentare.'
WHERE source_type = 'post' AND slug = 'credit-rapid-cu-buletinul' AND language_code = 'ro';

UPDATE posts
SET
  title = 'Fast credit: what to check before requesting it',
  excerpt = 'Documents, costs, and steps worth checking before requesting credit.',
  body = '## Fast credit: what to check before requesting it

Fast credit should not be chosen only because it is fast. Check the amount you need, repayment capacity, total cost, APR, and consequences of late payment.

For the initial request, a valid identity document is needed. Depending on the assessment, additional information may be required.'
WHERE source_type = 'post' AND slug = 'credit-rapid-cu-buletinul' AND language_code = 'en';

UPDATE posts
SET
  title = 'Gyors hitel: mit ellenőrizz igénylés előtt',
  excerpt = 'Dokumentumok, költségek és lépések, amelyeket érdemes ellenőrizni hiteligénylés előtt.',
  body = '## Gyors hitel: mit ellenőrizz igénylés előtt

A gyors hitelt nem érdemes kizárólag a gyorsaság miatt választani. Ellenőrizd a szükséges összeget, törlesztési képességet, teljes költséget, DAE/THM jellegű mutatót és a késedelmes fizetés következményeit.

Az első igényléshez érvényes személyazonosító okmány szükséges. Az elbírálástól függően további információra lehet szükség.'
WHERE source_type = 'post' AND slug = 'credit-rapid-cu-buletinul' AND language_code = 'hu';

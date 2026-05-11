# ANAF acord - jogi forrásjegyzet

Ez munkaanyag, nem ügyvédi állásfoglalás. Az éles használat előtt a végleges szöveget és az üzleti folyamatot román adatvédelmi/jogi szakértővel kell jóváhagyatni.

## Ellenőrzött fő források

- Regulamentul (UE) 2016/679, különösen a jogalapokra, hozzájárulásra és érintetti jogokra vonatkozó részek: https://eur-lex.europa.eu/eli/reg/2016/679/oj
- EDPB Guidelines 05/2020 on consent under Regulation 2016/679: https://www.edpb.europa.eu/our-work-tools/our-documents/guidelines/guidelines-052020-consent-under-regulation-2016679_en
- Legea nr. 190/2018 privind măsuri de punere în aplicare a Regulamentului (UE) 2016/679: https://legislatie.just.ro/Public/DetaliiDocument/203151
- OPANAF nr. 146/2022, protocol/model acord ANAF pentru persoane juridice de drept privat: https://static.anaf.ro/static/10/Anaf/legislatie/OPANAF_146_2022.pdf
- OPANAF nr. 3194/2019, modificări relevante pentru accesul instituțiilor financiare nebancare/instituțiilor de credit la date ANAF: https://static.anaf.ro/static/10/Anaf/legislatie/OPANAF_3194_2019.pdf

## Mapare în implementare

- Formularul public este doar în română, la `/acord-anaf`, și este marcat `noindex,nofollow`.
- Textul acceptat începe cu `EXEMPLU`, fiind o primă versiune de lucru.
- Linkurile precompletate sunt tokenizate, expiră și devin inutilizabile după trimitere.
- Datele sensibile sunt criptate în baza de date, iar CNP/IP/nume nu sunt păstrate în clar.
- PDF-ul generat din admin include markerul `EXEMPLU`, numele clientului, data/ora acceptării și IP-ul stației, deoarece modelul ANAF pentru acceptare electronică cere aceste elemente.
- Retragerea acordului este menționată în text; operațional trebuie stabilit canalul intern exact pentru cererile de retragere și verificat dacă adresa DPO publicată este corectă.

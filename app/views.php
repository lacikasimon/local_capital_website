<?php

declare(strict_types=1);

function public_layout(array $site, string $body, array $options = []): string
{
    $settings = $site['settings'];
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $title = ($options['title'] ?? null)
        ? $options['title'] . ' | ' . $settings['brandName']
        : $settings['brandName'] . ' | ' . $settings['tagline'];
    $description = seo_description((string) ($options['description'] ?? $settings['footerText']));
    $canonicalPath = $options['canonicalPath'] ?? current_request_path();
    $canonical = absolute_url(localized_path($canonicalPath, $language));
    $image = absolute_url($options['image'] ?? '/assets/hero-family.png');
    $robots = $options['robots'] ?? 'index,follow';
    $aiSummary = seo_description((string) ($options['aiSummary'] ?? $description));
    $alternateLinks = '';
    foreach (SUPPORTED_LANGUAGES as $code) {
        $alternateLinks .= "\n    " . '<link rel="alternate" hreflang="' . e($code) . '" href="' . e(absolute_url(localized_path($canonicalPath, $code))) . '">';
    }
    $alternateLinks .= "\n    " . '<link rel="alternate" hreflang="x-default" href="' . e(absolute_url(localized_path($canonicalPath, DEFAULT_LANGUAGE))) . '">';
    $structuredData = json_encode(build_structured_data($site, $options + [
        'metaTitle' => $title,
        'schemaTitle' => (string) ($options['title'] ?? $settings['brandName']),
        'description' => $description,
        'aiSummary' => $aiSummary,
        'canonicalPath' => $canonicalPath,
        'canonicalUrl' => $canonical,
        'image' => $image,
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    return '<!doctype html>
<html lang="' . e($language) . '">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($title) . '</title>
    <meta name="description" content="' . e($description) . '">
    <meta name="ai-summary" content="' . e($aiSummary) . '">
    <meta name="robots" content="' . e($robots) . '">
    <link rel="canonical" href="' . e($canonical) . '">' . $alternateLinks . '
    <meta property="og:type" content="website">
    <meta property="og:locale" content="' . e(locale_for_language($language)) . '">
    <meta property="og:site_name" content="' . e($settings['brandName']) . '">
    <meta property="og:title" content="' . e($title) . '">
    <meta property="og:description" content="' . e($description) . '">
    <meta property="og:url" content="' . e($canonical) . '">
    <meta property="og:image" content="' . e($image) . '">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="' . e($title) . '">
    <meta name="twitter:description" content="' . e($description) . '">
    <meta name="twitter:image" content="' . e($image) . '">
    <link rel="alternate" type="text/plain" title="LLMs.txt" href="' . e(absolute_url('/llms.txt')) . '">
    <link rel="icon" href="/assets/logo.png">
    <link rel="stylesheet" href="/styles.css">
    <script type="application/ld+json" nonce="' . e(csp_nonce()) . '">' . str_replace('</', '<\/', $structuredData ?: '{}') . '</script>
  </head>
  <body>
    ' . render_header($site, $options['active'] ?? '', $canonicalPath) . '
    <main>' . $body . '</main>
    ' . render_footer($site) . '
    ' . render_cookie_consent($site) . '
    ' . render_mobile_menu_script() . '
    ' . render_cookie_consent_script() . '
  </body>
</html>';
}

function ui_text(array $site, string $key): string
{
    static $strings = [
        'ro' => [
            'aria_main_nav' => 'Navigație principală',
            'aria_language_nav' => 'Limbă',
            'footer_schedule' => 'Program',
            'footer_contact' => 'Contact',
            'footer_links' => 'Linkuri utile',
            'footer_terms' => 'Termene și condiții',
            'footer_privacy' => 'Politica privind datele personale',
            'footer_data_rights' => 'Informare drepturi persoane vizate',
            'footer_retention' => 'Politica de retenție date',
            'cookie_title' => 'Preferințe cookie',
            'cookie_text' => 'Folosim cookie-uri necesare pentru funcționarea site-ului. Cookie-urile opționale vor fi folosite doar după acordul tău.',
            'cookie_privacy' => 'Vezi politica privind datele personale',
            'cookie_necessary' => 'Doar necesare',
            'cookie_accept_all' => 'Accept toate',
            'menu_toggle' => 'Meniu',
            'trust_aria' => 'Avantaje rapide',
            'trust_time_value' => '2 ore',
            'trust_time_label' => 'transfer rapid după aprobare',
            'trust_direct_value' => 'Direct',
            'trust_direct_label' => 'bani transferați pe card',
            'trust_ifn_value' => 'IFN',
            'trust_ifn_label' => 'soluții flexibile pentru persoane fizice',
            'home_advantages' => 'Avantaje',
            'home_services' => 'Servicii',
            'home_process' => 'Proces simplu',
            'home_cta_title' => 'Ai nevoie de o soluție rapidă?',
            'home_cta_text' => 'Trimite-ne un mesaj și revenim cu pașii potriviți pentru situația ta.',
            'home_cta_button' => 'Contactează-ne',
            'contact_success' => 'Mesajul a fost trimis. Îți vom răspunde în timpul programului de lucru.',
            'contact_error_title' => 'Verifică formularul:',
            'contact_eyebrow' => 'Contact',
            'contact_form_title' => 'Trimite un mesaj',
            'contact_honeypot' => 'Website',
            'contact_name' => 'Nume',
            'contact_email' => 'Email',
            'contact_phone' => 'Telefon',
            'contact_subject' => 'Subiect',
            'contact_message' => 'Mesaj',
            'contact_privacy' => 'Am citit informarea privind prelucrarea datelor personale.',
            'contact_send' => 'Trimite',
            'contact_facts' => 'Date de contact',
            'contact_address' => 'Adresă',
            'blog_title' => 'Conținut importat și informații utile',
            'blog_intro' => 'Articole, servicii și studii de caz curățate din vechiul site.',
            'blog_empty' => 'Nu există articole publicate momentan.',
            'blog_page_title' => 'Conținut importat',
            'blog_description' => 'Conținut importat și informații utile Local Capital',
            'case_eyebrow' => 'Studiu de caz',
            'case_intro' => 'Arhivă de studii de caz importată din vechiul site Local Capital.',
            'case_empty' => 'Nu există studii de caz publicate momentan.',
            'case_description' => 'Arhiva de studii de caz Local Capital',
            'faq_eyebrow' => 'Întrebări frecvente',
            'faq_title' => 'Răspunsuri utile pentru o decizie informată',
        ],
        'en' => [
            'aria_main_nav' => 'Main navigation',
            'aria_language_nav' => 'Language',
            'footer_schedule' => 'Schedule',
            'footer_contact' => 'Contact',
            'footer_links' => 'Useful links',
            'footer_terms' => 'Terms and conditions',
            'footer_privacy' => 'Personal data policy',
            'footer_data_rights' => 'Data subject rights notice',
            'footer_retention' => 'Data retention policy',
            'cookie_title' => 'Cookie preferences',
            'cookie_text' => 'We use necessary cookies to keep the website working. Optional cookies will only be used after your consent.',
            'cookie_privacy' => 'View the personal data policy',
            'cookie_necessary' => 'Necessary only',
            'cookie_accept_all' => 'Accept all',
            'menu_toggle' => 'Menu',
            'trust_aria' => 'Quick advantages',
            'trust_time_value' => '2 hours',
            'trust_time_label' => 'fast transfer after approval',
            'trust_direct_value' => 'Direct',
            'trust_direct_label' => 'money transferred to your card',
            'trust_ifn_value' => 'IFN',
            'trust_ifn_label' => 'flexible solutions for individuals',
            'home_advantages' => 'Advantages',
            'home_services' => 'Services',
            'home_process' => 'Simple process',
            'home_cta_title' => 'Need a fast solution?',
            'home_cta_text' => 'Send us a message and we will get back with the right steps for your situation.',
            'home_cta_button' => 'Contact us',
            'contact_success' => 'Message sent. We will reply during business hours.',
            'contact_error_title' => 'Check the form:',
            'contact_eyebrow' => 'Contact',
            'contact_form_title' => 'Send a message',
            'contact_honeypot' => 'Website',
            'contact_name' => 'Name',
            'contact_email' => 'Email',
            'contact_phone' => 'Phone',
            'contact_subject' => 'Subject',
            'contact_message' => 'Message',
            'contact_privacy' => 'I have read the personal data processing notice.',
            'contact_send' => 'Send',
            'contact_facts' => 'Contact details',
            'contact_address' => 'Address',
            'blog_title' => 'Imported content and useful information',
            'blog_intro' => 'Articles, services and case studies cleaned from the old website.',
            'blog_empty' => 'There are no published articles at the moment.',
            'blog_page_title' => 'Imported content',
            'blog_description' => 'Imported Local Capital content and useful information',
            'case_eyebrow' => 'Case study',
            'case_intro' => 'Case study archive imported from the old Local Capital website.',
            'case_empty' => 'There are no published case studies at the moment.',
            'case_description' => 'Local Capital case study archive',
            'faq_eyebrow' => 'Frequently asked questions',
            'faq_title' => 'Useful answers for an informed decision',
        ],
        'hu' => [
            'aria_main_nav' => 'Fő navigáció',
            'aria_language_nav' => 'Nyelv',
            'footer_schedule' => 'Nyitvatartás',
            'footer_contact' => 'Kapcsolat',
            'footer_links' => 'Hasznos linkek',
            'footer_terms' => 'Általános szerződési feltételek',
            'footer_privacy' => 'Személyes adatok kezelése',
            'footer_data_rights' => 'Érintetti jogokról szóló tájékoztató',
            'footer_retention' => 'Adatmegőrzési szabályzat',
            'cookie_title' => 'Cookie beállítások',
            'cookie_text' => 'Az oldal működéséhez szükséges sütiket használunk. Az opcionális sütiket csak a hozzájárulásod után használjuk.',
            'cookie_privacy' => 'Adatkezelési tájékoztató megnyitása',
            'cookie_necessary' => 'Csak szükséges',
            'cookie_accept_all' => 'Mindet elfogadom',
            'menu_toggle' => 'Menü',
            'trust_aria' => 'Gyors előnyök',
            'trust_time_value' => '2 óra',
            'trust_time_label' => 'gyors utalás jóváhagyás után',
            'trust_direct_value' => 'Közvetlenül',
            'trust_direct_label' => 'pénz a kártyádra utalva',
            'trust_ifn_value' => 'IFN',
            'trust_ifn_label' => 'rugalmas megoldások magánszemélyeknek',
            'home_advantages' => 'Előnyök',
            'home_services' => 'Szolgáltatások',
            'home_process' => 'Egyszerű folyamat',
            'home_cta_title' => 'Gyors megoldásra van szükséged?',
            'home_cta_text' => 'Írj nekünk, és visszajelzünk a helyzetedhez illő lépésekkel.',
            'home_cta_button' => 'Kapcsolatfelvétel',
            'contact_success' => 'Üzenetedet elküldtük. Munkaidőben válaszolunk.',
            'contact_error_title' => 'Ellenőrizd az űrlapot:',
            'contact_eyebrow' => 'Kapcsolat',
            'contact_form_title' => 'Üzenet küldése',
            'contact_honeypot' => 'Weboldal',
            'contact_name' => 'Név',
            'contact_email' => 'E-mail',
            'contact_phone' => 'Telefon',
            'contact_subject' => 'Tárgy',
            'contact_message' => 'Üzenet',
            'contact_privacy' => 'Elolvastam a személyes adatok kezeléséről szóló tájékoztatót.',
            'contact_send' => 'Küldés',
            'contact_facts' => 'Elérhetőségek',
            'contact_address' => 'Cím',
            'blog_title' => 'Importált tartalom és hasznos információk',
            'blog_intro' => 'A régi oldalról megtisztított cikkek, szolgáltatások és esettanulmányok.',
            'blog_empty' => 'Jelenleg nincs közzétett cikk.',
            'blog_page_title' => 'Importált tartalom',
            'blog_description' => 'Importált Local Capital tartalom és hasznos információk',
            'case_eyebrow' => 'Esettanulmány',
            'case_intro' => 'A régi Local Capital weboldalról importált esettanulmány-archívum.',
            'case_empty' => 'Jelenleg nincs közzétett esettanulmány.',
            'case_description' => 'Local Capital esettanulmány-archívum',
            'faq_eyebrow' => 'Gyakori kérdések',
            'faq_title' => 'Hasznos válaszok a megalapozott döntéshez',
        ],
    ];

    $language = normalize_language($site['language'] ?? DEFAULT_LANGUAGE);
    return $strings[$language][$key] ?? $strings[DEFAULT_LANGUAGE][$key] ?? $key;
}

function language_label(string $language): string
{
    return [
        'ro' => 'Romanian',
        'en' => 'English',
        'hu' => 'Hungarian',
    ][normalize_language($language)] ?? 'Romanian';
}

function schema_reference(string $fragment): array
{
    return ['@id' => absolute_url('/#' . ltrim($fragment, '#'))];
}

function normalized_faq_items(array $items): array
{
    $faq = [];
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        $question = trim((string) ($item['question'] ?? $item['title'] ?? ''));
        $answer = trim((string) ($item['answer'] ?? $item['text'] ?? ''));
        if ($question === '' || $answer === '') {
            continue;
        }
        $faq[] = compact('question', 'answer');
    }
    return $faq;
}

function build_breadcrumb_schema(array $site, string $canonicalPath, string $title): array
{
    $language = normalize_language($site['language'] ?? DEFAULT_LANGUAGE);
    $items = [[
        '@type' => 'ListItem',
        'position' => 1,
        'name' => $site['navigation'][0]['label'] ?? $site['settings']['brandName'],
        'item' => absolute_url(localized_path('/', $language)),
    ]];

    if ($canonicalPath !== '/') {
        $items[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => $title,
            'item' => absolute_url(localized_path($canonicalPath, $language)),
        ];
    }

    return [
        '@type' => 'BreadcrumbList',
        '@id' => absolute_url(localized_path($canonicalPath, $language)) . '#breadcrumb',
        'itemListElement' => $items,
    ];
}

function build_structured_data(array $site, array $options): array
{
    $settings = $site['settings'];
    $language = normalize_language($site['language'] ?? DEFAULT_LANGUAGE);
    $canonicalPath = (string) ($options['canonicalPath'] ?? '/');
    $canonicalUrl = (string) ($options['canonicalUrl'] ?? absolute_url(localized_path($canonicalPath, $language)));
    $title = (string) ($options['schemaTitle'] ?? $options['title'] ?? $settings['brandName']);
    $description = (string) ($options['description'] ?? $settings['footerText']);
    $image = (string) ($options['image'] ?? absolute_url('/assets/hero-family.png'));
    $faqItems = normalized_faq_items($options['faqItems'] ?? []);
    $servicePosts = published_posts_by_type($site, 'service');
    $organizationId = schema_reference('organization');
    $websiteId = schema_reference('website');
    $webPageId = $canonicalUrl . '#webpage';
    $pageType = (string) ($options['webPageType'] ?? 'WebPage');

    $organization = [
        '@type' => ['Organization', 'FinancialService'],
        '@id' => $organizationId['@id'],
        'name' => $settings['brandName'],
        'legalName' => $settings['legalName'] ?? $settings['brandName'],
        'url' => app_base_url(),
        'logo' => absolute_url('/assets/logo.png'),
        'image' => $image,
        'telephone' => $settings['phone'] ?? '',
        'email' => $settings['email'] ?? '',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Str. Vasile Lucaciu nr. 3',
            'addressLocality' => 'Satu Mare',
            'addressCountry' => 'RO',
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Romania',
        ],
        'availableLanguage' => array_map('language_label', SUPPORTED_LANGUAGES),
        'openingHoursSpecification' => [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            'opens' => '09:00',
            'closes' => '17:00',
        ]],
        'contactPoint' => [[
            '@type' => 'ContactPoint',
            'telephone' => $settings['phone'] ?? '',
            'email' => $settings['email'] ?? '',
            'contactType' => 'customer support',
            'areaServed' => 'RO',
            'availableLanguage' => array_map('language_label', SUPPORTED_LANGUAGES),
        ]],
    ];

    if ($servicePosts) {
        $organization['hasOfferCatalog'] = [
            '@type' => 'OfferCatalog',
            'name' => $settings['brandName'] . ' credit services',
            'itemListElement' => array_map(fn ($post) => [
                '@type' => 'Offer',
                'itemOffered' => [
                    '@type' => 'Service',
                    'name' => $post['title'],
                    'description' => $post['excerpt'] ?: plain_text($post['body']),
                    'url' => absolute_url(localized_path(localized_post_path($post, $language), $language)),
                    'provider' => $organizationId,
                ],
            ], $servicePosts),
        ];
    }

    $graph = [
        $organization,
        [
            '@type' => 'WebSite',
            '@id' => $websiteId['@id'],
            'url' => app_base_url(),
            'name' => $settings['brandName'],
            'description' => $settings['footerText'] ?? $description,
            'inLanguage' => $language,
            'publisher' => $organizationId,
        ],
        [
            '@type' => $pageType,
            '@id' => $webPageId,
            'url' => $canonicalUrl,
            'name' => $title,
            'headline' => $title,
            'description' => $description,
            'inLanguage' => $language,
            'isPartOf' => $websiteId,
            'about' => $organizationId,
            'publisher' => $organizationId,
            'breadcrumb' => ['@id' => $canonicalUrl . '#breadcrumb'],
            'primaryImageOfPage' => [
                '@type' => 'ImageObject',
                'url' => $image,
            ],
        ],
        build_breadcrumb_schema($site, $canonicalPath, $title),
    ];

    if (($options['schemaType'] ?? '') === 'Article') {
        $graph[] = [
            '@type' => 'Article',
            '@id' => $canonicalUrl . '#article',
            'headline' => $title,
            'description' => $description,
            'datePublished' => $options['datePublished'] ?? null,
            'dateModified' => $options['dateModified'] ?? $options['datePublished'] ?? null,
            'inLanguage' => $language,
            'mainEntityOfPage' => ['@id' => $webPageId],
            'author' => $organizationId,
            'publisher' => $organizationId,
            'image' => $image,
        ];
    }

    if (($options['schemaType'] ?? '') === 'Service') {
        $graph[] = [
            '@type' => 'Service',
            '@id' => $canonicalUrl . '#service',
            'name' => $title,
            'description' => $description,
            'url' => $canonicalUrl,
            'provider' => $organizationId,
            'areaServed' => ['@type' => 'Country', 'name' => 'Romania'],
            'serviceType' => 'Credit service',
        ];
    }

    if ($faqItems) {
        $graph[] = [
            '@type' => 'FAQPage',
            '@id' => $canonicalUrl . '#faq',
            'mainEntity' => array_map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ], $faqItems),
        ];
    }

    return [
        '@context' => 'https://schema.org',
        '@graph' => array_values(array_filter($graph)),
    ];
}

function render_header(array $site, string $active, string $currentPath = '/'): string
{
    $settings = $site['settings'];
    $language = $site['language'] ?? DEFAULT_LANGUAGE;
    $menuId = 'site-menu-toggle';
    $navId = 'site-main-nav';
    $links = '';

    foreach ($site['navigation'] as $item) {
        if (!$item['visible']) {
            continue;
        }
        $class = $active === $item['key'] ? ' class="active"' : '';
        $links .= '<a' . $class . ' href="' . e(localized_path($item['path'], $language)) . '">' . e($item['label']) . '</a>';
    }

    $languageLinks = '';
    foreach (SUPPORTED_LANGUAGES as $code) {
        $class = $language === $code ? ' class="active"' : '';
        $languageLinks .= '<a' . $class . ' href="' . e(localized_path($currentPath, $code)) . '">' . strtoupper(e($code)) . '</a>';
    }

    return '<header class="site-header">
    <a class="brand" href="' . e(localized_path('/', $language)) . '" aria-label="' . e($settings['brandName']) . '">
      <img src="/assets/logo.png" alt="" width="52" height="46">
      <span>
        <strong>' . e($settings['brandName']) . '</strong>
        <small>' . e($settings['tagline']) . '</small>
      </span>
    </a>
    <input class="menu-toggle-input" id="' . e($menuId) . '" type="checkbox" aria-label="' . e(ui_text($site, 'menu_toggle')) . '" aria-controls="' . e($navId) . '" aria-expanded="false">
    <label class="menu-toggle" for="' . e($menuId) . '">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span class="sr-only">' . e(ui_text($site, 'menu_toggle')) . '</span>
    </label>
    <nav class="main-nav" id="' . e($navId) . '" aria-label="' . e(ui_text($site, 'aria_main_nav')) . '">' . $links . '</nav>
    <nav class="language-nav" aria-label="' . e(ui_text($site, 'aria_language_nav')) . '">' . $languageLinks . '</nav>
    <a class="header-contact" href="tel:' . e(preg_replace('/\s+/', '', $settings['phone'])) . '">
      <svg class="phone-icon" aria-hidden="true" viewBox="0 0 24 24" focusable="false">
        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.91.33 1.8.63 2.65a2 2 0 0 1-.45 2.11L8.09 9.69a16 16 0 0 0 6.22 6.22l1.21-1.21a2 2 0 0 1 2.11-.45c.85.3 1.74.51 2.65.63A2 2 0 0 1 22 16.92Z"></path>
      </svg>
      <span>' . e($settings['phone']) . '</span>
    </a>
  </header>';
}

function render_mobile_menu_script(): string
{
    return '<script nonce="' . e(csp_nonce()) . '">
(() => {
  document.querySelectorAll(".menu-toggle-input[aria-controls]").forEach((toggle) => {
    const sync = () => toggle.setAttribute("aria-expanded", toggle.checked ? "true" : "false");
    toggle.addEventListener("change", sync);
    sync();
  });
})();
</script>';
}

function cookie_consent_cookie_name(): string
{
    return 'lc_cookie_consent';
}

function has_cookie_consent(): bool
{
    $choice = (string) ($_COOKIE[cookie_consent_cookie_name()] ?? '');
    return in_array($choice, ['necessary', 'all'], true);
}

function render_cookie_consent(array $site): string
{
    $language = normalize_language($site['language'] ?? DEFAULT_LANGUAGE);
    $hidden = has_cookie_consent() ? ' hidden' : '';
    $privacyPath = localized_path('/politica-privind-datele-personale', $language);

    return '<div class="cookie-consent-backdrop" data-cookie-consent' . $hidden . '>
    <section class="cookie-consent" role="region" aria-labelledby="cookie-consent-title" aria-describedby="cookie-consent-text">
      <div>
        <h2 id="cookie-consent-title">' . e(ui_text($site, 'cookie_title')) . '</h2>
        <p id="cookie-consent-text">' . e(ui_text($site, 'cookie_text')) . '</p>
        <a href="' . e($privacyPath) . '">' . e(ui_text($site, 'cookie_privacy')) . '</a>
      </div>
      <div class="cookie-actions">
        <button class="button button-light" type="button" data-cookie-choice="necessary">' . e(ui_text($site, 'cookie_necessary')) . '</button>
        <button class="button" type="button" data-cookie-choice="all">' . e(ui_text($site, 'cookie_accept_all')) . '</button>
      </div>
    </section>
  </div>';
}

function render_cookie_consent_script(): string
{
    $cookieName = cookie_consent_cookie_name();

    return '<script nonce="' . e(csp_nonce()) . '">
(() => {
  const banner = document.querySelector("[data-cookie-consent]");
  if (!banner) {
    return;
  }

  const cookieName = "' . e($cookieName) . '";
  const hasConsent = document.cookie
    .split("; ")
    .some((cookie) => cookie.startsWith(`${cookieName}=`));

  if (hasConsent) {
    banner.hidden = true;
    return;
  }

  banner.hidden = false;

  const setConsent = (choice) => {
    const maxAge = 60 * 60 * 24 * 180;
    const secure = window.location.protocol === "https:" ? "; Secure" : "";
    document.cookie = `${cookieName}=${encodeURIComponent(choice)}; Max-Age=${maxAge}; Path=/; SameSite=Lax${secure}`;
    banner.hidden = true;
  };

  banner.querySelectorAll("[data-cookie-choice]").forEach((button) => {
    button.addEventListener("click", () => setConsent(button.dataset.cookieChoice || "necessary"));
  });
})();
</script>';
}

function render_footer(array $site): string
{
    $settings = $site['settings'];
    $language = $site['language'] ?? DEFAULT_LANGUAGE;

    return '<footer class="site-footer">
    <div class="footer-grid">
      <section>
        <img src="/assets/logo.png" alt="' . e($settings['brandName']) . '" width="68" height="61">
        <p>' . e($settings['footerText']) . '</p>
      </section>
      <section>
        <h2>' . e(ui_text($site, 'footer_schedule')) . '</h2>
        <p>' . e($settings['workingHours']) . '</p>
        <p>' . e($settings['closedHours']) . '</p>
      </section>
      <section>
        <h2>' . e(ui_text($site, 'footer_contact')) . '</h2>
        <p><a href="mailto:' . e($settings['email']) . '">' . e($settings['email']) . '</a></p>
        <p><a href="tel:' . e(preg_replace('/\s+/', '', $settings['phone'])) . '">' . e($settings['phone']) . '</a></p>
        <p>' . e($settings['address']) . '</p>
      </section>
      <section>
        <h2>' . e(ui_text($site, 'footer_links')) . '</h2>
        <p><a href="' . e($settings['anpcUrl']) . '" target="_blank" rel="nofollow noopener">' . e($settings['anpcLabel']) . '</a></p>
        <p><a href="' . e(localized_path('/gdpr', $language)) . '">GDPR</a></p>
        <p><a href="' . e(localized_path('/termene-si-conditii', $language)) . '">' . e(ui_text($site, 'footer_terms')) . '</a></p>
        <p><a href="' . e(localized_path('/politica-privind-datele-personale', $language)) . '">' . e(ui_text($site, 'footer_privacy')) . '</a></p>
        <p><a href="/downloads/informare-privind-drepturile-persoanelor-vizate.pdf">' . e(ui_text($site, 'footer_data_rights')) . '</a></p>
        <p><a href="/downloads/politica-de-retentie-a-datelor-cu-caracter-personal.pdf">' . e(ui_text($site, 'footer_retention')) . '</a></p>
        <div class="footer-regulatory-logos" aria-label="ANPC, SAL si SOL">
          <a href="' . e($settings['anpcUrl']) . '" target="_blank" rel="nofollow noopener" aria-label="ANPC">
            <img src="/assets/anpc.webp" alt="ANPC" loading="lazy" decoding="async">
          </a>
          <a href="https://anpc.ro/ce-este-sal/" target="_blank" rel="nofollow noopener" aria-label="Solutionarea alternativa a litigiilor">
            <img src="/assets/anpc-sal.svg" alt="Solutionarea alternativa a litigiilor" loading="lazy" decoding="async">
          </a>
          <a href="https://ec.europa.eu/consumers/odr" target="_blank" rel="nofollow noopener" aria-label="Solutionarea online a litigiilor">
            <img src="/assets/anpc-sol.svg" alt="Solutionarea online a litigiilor" loading="lazy" decoding="async">
          </a>
        </div>
      </section>
    </div>
  </footer>';
}

function published_posts_by_type(array $site, string $type): array
{
    return array_values(array_filter(
        $site['posts'],
        fn ($post) => ($post['source_type'] ?? '') === $type && !empty($post['published'])
    ));
}

function render_link_card_grid(array $site, array $posts): string
{
    $html = '';
    foreach ($posts as $post) {
        $html .= '<article class="post-card">
        <p class="eyebrow">' . e(format_date($post['date'])) . '</p>
        <h3><a href="' . e(localized_path(localized_post_path($post, $site['language']), $site['language'])) . '">' . e($post['title']) . '</a></h3>
        <p>' . e($post['excerpt'] ?: plain_text($post['body'])) . '</p>
      </article>';
    }

    return '<div class="card-grid">' . $html . '</div>';
}

function render_text_cards(array $items): string
{
    $html = '';
    foreach ($items as $item) {
        $html .= '<article class="text-card">
        <h3>' . e($item['title'] ?? '') . '</h3>
        <p>' . e($item['text'] ?? '') . '</p>
      </article>';
    }
    return $html;
}

function render_faq_section(array $site, array $items): string
{
    $faqItems = normalized_faq_items($items);
    if (!$faqItems) {
        return '';
    }

    $html = '';
    foreach ($faqItems as $item) {
        $html .= '<details class="faq-item">
        <summary>' . e($item['question']) . '</summary>
        <p>' . e($item['answer']) . '</p>
      </details>';
    }

    return '<section class="content-band faq-band">
    <div class="section-heading">
      <p class="eyebrow">' . e(ui_text($site, 'faq_eyebrow')) . '</p>
      <h2>' . e(ui_text($site, 'faq_title')) . '</h2>
    </div>
    <div class="faq-list">' . $html . '</div>
  </section>';
}

function render_home(array $site): string
{
    $page = $site['pages']['home'];
    $services = '';
    $trustItems = [
        ['value' => ui_text($site, 'trust_time_value'), 'label' => ui_text($site, 'trust_time_label')],
        ['value' => ui_text($site, 'trust_direct_value'), 'label' => ui_text($site, 'trust_direct_label')],
        ['value' => ui_text($site, 'trust_ifn_value'), 'label' => ui_text($site, 'trust_ifn_label')],
    ];
    $trust = '';

    foreach ($page['services'] ?? [] as $service) {
        $services .= '<article class="service-card">
        <img src="' . e($service['image'] ?? '') . '" alt="">
        <div>
          <h3>' . e($service['title'] ?? '') . '</h3>
          <p>' . e($service['text'] ?? '') . '</p>
        </div>
      </article>';
    }

    foreach ($trustItems as $item) {
        $trust .= '<div><strong>' . e($item['value']) . '</strong><span>' . e($item['label']) . '</span></div>';
    }

    $body = '<section class="hero home-hero">
    <div class="hero-copy">
      <p class="eyebrow">' . e($site['settings']['tagline']) . '</p>
      <h1>' . e($page['title']) . '</h1>
      <p>' . e($page['summary']) . '</p>
      <div class="hero-actions">
        <a class="button" href="' . e(localized_path($page['ctaHref'], $site['language'])) . '">' . e($page['ctaLabel']) . '</a>
        <a class="button button-secondary" href="' . e(localized_path($page['secondaryCtaHref'], $site['language'])) . '">' . e($page['secondaryCtaLabel']) . '</a>
      </div>
    </div>
  </section>
  <section class="trust-strip" aria-label="' . e(ui_text($site, 'trust_aria')) . '">
    ' . $trust . '
  </section>
  <section class="content-band">
    <div class="prose">' . render_markdown($page['body']) . '</div>
  </section>
  <section class="content-band muted">
    <div class="section-heading">
      <p class="eyebrow">' . e(ui_text($site, 'home_advantages')) . '</p>
      <h2>' . e($page['featuresTitle'] ?? ui_text($site, 'home_advantages')) . '</h2>
    </div>
    <div class="card-grid">' . render_text_cards($page['features'] ?? []) . '</div>
  </section>
  <section class="content-band">
    <div class="section-heading">
      <p class="eyebrow">' . e(ui_text($site, 'home_services')) . '</p>
      <h2>' . e($page['servicesTitle'] ?? ui_text($site, 'home_services')) . '</h2>
      <p>' . e($page['servicesIntro'] ?? '') . '</p>
    </div>
    <div class="service-grid">' . $services . '</div>
  </section>
  <section class="content-band muted">
    <div class="section-heading">
      <p class="eyebrow">' . e(ui_text($site, 'home_process')) . '</p>
      <h2>' . e($page['requirementsTitle'] ?? 'De ce ai nevoie') . '</h2>
    </div>
    <div class="card-grid">' . render_text_cards($page['requirements'] ?? []) . '</div>
  </section>
  ' . render_faq_section($site, $page['faq'] ?? []) . '
  <section class="cta-band">
    <div>
      <p class="eyebrow">Local Capital</p>
      <h2>' . e(ui_text($site, 'home_cta_title')) . '</h2>
      <p>' . e(ui_text($site, 'home_cta_text')) . '</p>
    </div>
    <a class="button" href="' . e(localized_path('/contact', $site['language'])) . '">' . e(ui_text($site, 'home_cta_button')) . '</a>
  </section>';

    return public_layout($site, $body, [
        'active' => 'home',
        'title' => $page['title'],
        'description' => $page['summary'],
        'aiSummary' => $page['aiSummary'] ?? $page['summary'],
        'faqItems' => $page['faq'] ?? [],
        'canonicalPath' => '/',
    ]);
}

function render_generic_page(array $site, string $key, array $page): string
{
    $extra = '';
    foreach (['values', 'products', 'features'] as $collection) {
        if (!empty($page[$collection]) && is_array($page[$collection])) {
            $extra .= '<div class="card-grid">' . render_text_cards($page[$collection]) . '</div>';
        }
    }

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">' . e($site['settings']['brandName']) . '</p>
      <h1>' . e($page['title']) . '</h1>
      <p>' . e($page['summary']) . '</p>
    </div>
  </section>
  <section class="content-band">
    <div class="prose">' . render_markdown($page['body']) . '</div>
    ' . $extra . '
  </section>
  ' . render_faq_section($site, $page['faq'] ?? []);

    return public_layout($site, $body, [
        'active' => $key,
        'title' => $page['title'],
        'description' => $page['summary'],
        'aiSummary' => $page['aiSummary'] ?? $page['summary'],
        'faqItems' => $page['faq'] ?? [],
        'webPageType' => $key === 'about' ? 'AboutPage' : 'WebPage',
        'canonicalPath' => $page['path'] ?? current_request_path(),
    ]);
}

function render_contact(array $site, array $errors = [], array $old = []): string
{
    $settings = $site['settings'];
    $page = $site['pages']['contact'];
    $sent = ($_GET['sent'] ?? '') === '1';
    $message = '';
    if ($sent) {
        $message = '<p class="form-message success">' . e(ui_text($site, 'contact_success')) . '</p>';
    } elseif ($errors) {
        $items = '';
        foreach ($errors as $error) {
            $items .= '<li>' . e($error) . '</li>';
        }
        $message = '<div class="form-message error"><strong>' . e(ui_text($site, 'contact_error_title')) . '</strong><ul>' . $items . '</ul></div>';
    }

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">' . e(ui_text($site, 'contact_eyebrow')) . '</p>
      <h1>' . e($page['title']) . '</h1>
      <p>' . e($page['summary']) . '</p>
    </div>
  </section>
  <section class="contact-layout">
    <div class="prose">' . render_markdown($page['body']) . '</div>
    <div class="contact-panel">
      <h2>' . e($page['formTitle'] ?? ui_text($site, 'contact_form_title')) . '</h2>
      ' . $message . '
      <form action="' . e(localized_path('/contact', $site['language'])) . '" method="post">
        <input type="hidden" name="contact_token" value="' . e(contact_form_token()) . '">
        <label class="hidden-field">' . e(ui_text($site, 'contact_honeypot')) . ' <input name="website" tabindex="-1" autocomplete="off"></label>
        <label>' . e(ui_text($site, 'contact_name')) . ' <input name="name" autocomplete="name" maxlength="160" value="' . e($old['name'] ?? '') . '" required></label>
        <label>' . e(ui_text($site, 'contact_email')) . ' <input name="email" type="email" autocomplete="email" maxlength="190" value="' . e($old['email'] ?? '') . '" required></label>
        <label>' . e(ui_text($site, 'contact_phone')) . ' <input name="phone" autocomplete="tel" maxlength="60" value="' . e($old['phone'] ?? '') . '"></label>
        <label>' . e(ui_text($site, 'contact_subject')) . ' <input name="subject" maxlength="220" value="' . e($old['subject'] ?? '') . '" required></label>
        <label>' . e(ui_text($site, 'contact_message')) . ' <textarea name="message" rows="6" maxlength="4000" required>' . e($old['message'] ?? '') . '</textarea></label>
        <label class="checkbox privacy-check"><input name="privacy" type="checkbox" value="1" ' . (!empty($old['privacy']) ? 'checked' : '') . ' required> ' . e(ui_text($site, 'contact_privacy')) . '</label>
        <button class="button" type="submit">' . e(ui_text($site, 'contact_send')) . '</button>
      </form>
      <p class="privacy">' . e($page['privacyNote'] ?? '') . '</p>
    </div>
    <aside class="contact-facts">
      <h2>' . e(ui_text($site, 'contact_facts')) . '</h2>
      <p><strong>' . e(ui_text($site, 'contact_phone')) . '</strong><br><a href="tel:' . e(preg_replace('/\s+/', '', $settings['phone'])) . '">' . e($settings['phone']) . '</a></p>
      <p><strong>' . e(ui_text($site, 'contact_email')) . '</strong><br><a href="mailto:' . e($settings['email']) . '">' . e($settings['email']) . '</a></p>
      <p><strong>' . e(ui_text($site, 'contact_address')) . '</strong><br>' . e($settings['address']) . '</p>
      <p><strong>' . e(ui_text($site, 'footer_schedule')) . '</strong><br>' . e($settings['workingHours']) . '<br>' . e($settings['closedHours']) . '</p>
    </aside>
  </section>';

    return public_layout($site, $body, [
        'active' => 'contact',
        'title' => $page['title'],
        'description' => $page['summary'],
        'aiSummary' => $page['aiSummary'] ?? $page['summary'],
        'faqItems' => $page['faq'] ?? [],
        'webPageType' => 'ContactPage',
        'canonicalPath' => route_page_path('contact', $site['language']) ?? '/contact',
    ]);
}

function render_blog(array $site): string
{
    $posts = '';
    foreach ($site['posts'] as $post) {
        if (!$post['published']) {
            continue;
        }
        $posts .= '<article class="post-card">
        <p class="eyebrow">' . e(format_date($post['date'])) . '</p>
        <h2><a href="' . e(localized_path(localized_post_path($post, $site['language']), $site['language'])) . '">' . e($post['title']) . '</a></h2>
        <p>' . e($post['excerpt'] ?: plain_text($post['body'])) . '</p>
      </article>';
    }

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">Local Capital</p>
      <h1>' . e(ui_text($site, 'blog_title')) . '</h1>
      <p>' . e(ui_text($site, 'blog_intro')) . '</p>
    </div>
  </section>
  <section class="content-band">
    <div class="post-list">' . ($posts ?: '<p>' . e(ui_text($site, 'blog_empty')) . '</p>') . '</div>
  </section>';

    return public_layout($site, $body, [
        'active' => 'blog',
        'title' => ui_text($site, 'blog_page_title'),
        'description' => ui_text($site, 'blog_description'),
        'canonicalPath' => blog_index_path($site['language']),
    ]);
}

function render_case_study_archive(array $site, string $category): string
{
    $posts = published_posts_by_type($site, 'case_study');
    $title = label_from_key($category);

    $body = '<section class="page-hero">
    <div>
      <p class="eyebrow">' . e(ui_text($site, 'case_eyebrow')) . '</p>
      <h1>' . e($title) . '</h1>
      <p>' . e(ui_text($site, 'case_intro')) . '</p>
    </div>
  </section>
  <section class="content-band">
    ' . ($posts ? render_link_card_grid($site, $posts) : '<p>' . e(ui_text($site, 'case_empty')) . '</p>') . '
  </section>';

    return public_layout($site, $body, [
        'active' => 'blog',
        'title' => $title,
        'description' => ui_text($site, 'case_description'),
        'canonicalPath' => case_study_archive_path($site['language']),
    ]);
}

function render_post_article(array $site, array $post): string
{
    $body = '<article class="article">
    <p class="eyebrow">' . e(format_date($post['date'])) . '</p>
    <h1>' . e($post['title']) . '</h1>
    <p class="lead">' . e($post['excerpt']) . '</p>
    <div class="prose">' . render_markdown($post['body']) . '</div>
  </article>';

    return public_layout($site, $body, [
        'active' => 'blog',
        'title' => $post['title'],
        'description' => $post['excerpt'] ?: plain_text($post['body']),
        'aiSummary' => $post['excerpt'] ?: plain_text($post['body']),
        'schemaType' => ($post['source_type'] ?? '') === 'service' ? 'Service' : 'Article',
        'webPageType' => ($post['source_type'] ?? '') === 'service' ? 'ItemPage' : 'Article',
        'datePublished' => $post['date'] ?? null,
        'canonicalPath' => localized_post_path($post, $site['language']),
    ]);
}

function render_post(array $site, string $slug): ?string
{
    foreach ($site['posts'] as $post) {
        if ($post['slug'] !== $slug || !$post['published']) {
            continue;
        }

        return render_post_article($site, $post);
    }

    return null;
}

function render_post_by_path(array $site, string $path): ?string
{
    foreach ($site['posts'] as $post) {
        if (!$post['published'] || !post_path_matches($post, $site['language'], $path)) {
            continue;
        }

        return render_post_article($site, $post);
    }

    return null;
}

function error_page_text(?string $language, string $key): string
{
    static $texts = [
        'ro' => [
            'not_found_title' => 'Pagina nu a fost găsită',
            'not_found_message' => 'Adresa cerută nu există.',
            'method_title' => 'Metodă nepermisă',
            'method_message' => 'Această pagină acceptă doar citire.',
            'rejected_title' => 'Cerere respinsă',
            'rejected_message' => 'Tokenul de securitate nu este valid.',
            'server_title' => 'Eroare',
            'server_message' => 'A apărut o eroare. Te rugăm să încerci din nou.',
            'back_home' => 'Înapoi la prima pagină',
        ],
        'en' => [
            'not_found_title' => 'Page not found',
            'not_found_message' => 'The requested address does not exist.',
            'method_title' => 'Method not allowed',
            'method_message' => 'This page only accepts read requests.',
            'rejected_title' => 'Request rejected',
            'rejected_message' => 'The security token is not valid.',
            'server_title' => 'Error',
            'server_message' => 'An error occurred. Please try again.',
            'back_home' => 'Back to the home page',
        ],
        'hu' => [
            'not_found_title' => 'Az oldal nem található',
            'not_found_message' => 'A kért cím nem létezik.',
            'method_title' => 'Nem engedélyezett metódus',
            'method_message' => 'Ez az oldal csak olvasási kéréseket fogad.',
            'rejected_title' => 'A kérés elutasítva',
            'rejected_message' => 'A biztonsági token nem érvényes.',
            'server_title' => 'Hiba',
            'server_message' => 'Hiba történt. Kérjük, próbáld újra.',
            'back_home' => 'Vissza a főoldalra',
        ],
    ];

    $language = normalize_language($language);
    return $texts[$language][$key] ?? $texts[DEFAULT_LANGUAGE][$key] ?? $key;
}

function render_error_page(string $title, string $message, ?string $language = DEFAULT_LANGUAGE): string
{
    $language = normalize_language($language);

    return '<!doctype html>
<html lang="' . e($language) . '">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>' . e($title) . '</title>
    <meta name="robots" content="noindex,nofollow">
    <link rel="stylesheet" href="/styles.css">
  </head>
  <body>
    <main class="error-page">
      <h1>' . e($title) . '</h1>
      <p>' . e($message) . '</p>
      <a class="button" href="' . e(localized_path('/', $language)) . '">' . e(error_page_text($language, 'back_home')) . '</a>
    </main>
  </body>
</html>';
}

function render_robots_txt(): string
{
    $privateRules = "Allow: /\n"
        . "Allow: /llms.txt\n"
        . "Disallow: /admin/\n"
        . "Disallow: /config/\n"
        . "Disallow: /database/\n"
        . "Disallow: /scripts/\n";
    $aiBots = ['GPTBot', 'ChatGPT-User', 'ClaudeBot', 'PerplexityBot', 'Google-Extended'];
    $robots = "User-agent: *\n" . $privateRules . "\n";

    foreach ($aiBots as $bot) {
        $robots .= "User-agent: " . $bot . "\n" . $privateRules . "\n";
    }

    return $robots
        . "# AI discovery: " . absolute_url('/llms.txt') . "\n"
        . "Sitemap: " . absolute_url('/sitemap.xml') . "\n";
}

function render_llms_txt(): string
{
    $lines = [
        '# Local Capital',
        '',
        '> LOCAL CAPITAL IFN S.A. is a Romanian non-bank financial institution offering simple and fast credit solutions for personal needs.',
        '',
        'Canonical site: ' . app_base_url(),
        'Sitemap: ' . absolute_url('/sitemap.xml'),
        'Primary AI-readable file: ' . absolute_url('/llms.txt'),
        'Contact: info@localcapital.ro, 0318 110 001',
        'Languages: Romanian (ro), English (en), Hungarian (hu)',
        '',
        '## AI usage notes',
        '- This file summarizes public website content for search engines and AI answer engines.',
        '- Do not treat website content as a guaranteed credit approval or as personalized financial advice.',
        '- For legal pages, the official maintained legal text is Romanian unless Local Capital publishes an authorized translation.',
        '- Private admin URLs and source folders are excluded from indexing.',
        '',
    ];

    foreach (SUPPORTED_LANGUAGES as $language) {
        $site = load_site($language);
        $home = $site['pages']['home'] ?? [];
        $lines[] = '## ' . language_label($language) . ' content (' . $language . ')';
        if ($home) {
            $lines[] = '- Home: ' . absolute_url(localized_path('/', $language)) . ' - ' . plain_text((string) ($home['summary'] ?? ''), 240);
        }

        foreach (['about', 'contract', 'contact'] as $key) {
            if (empty($site['pages'][$key])) {
                continue;
            }
            $page = $site['pages'][$key];
            $lines[] = '- ' . $page['title'] . ': ' . absolute_url(localized_path($page['path'] ?? '/', $language)) . ' - ' . plain_text((string) ($page['summary'] ?? ''), 240);
        }

        $servicePosts = published_posts_by_type($site, 'service');
        if ($servicePosts) {
            $lines[] = '';
            $lines[] = '### Credit services';
            foreach ($servicePosts as $post) {
                $lines[] = '- ' . $post['title'] . ': ' . absolute_url(localized_path(localized_post_path($post, $language), $language)) . ' - ' . plain_text((string) ($post['excerpt'] ?: $post['body']), 220);
            }
        }

        $articles = array_values(array_filter(
            $site['posts'],
            fn ($post) => ($post['source_type'] ?? '') === 'post' && !empty($post['published'])
        ));
        if ($articles) {
            $lines[] = '';
            $lines[] = '### Articles';
            foreach ($articles as $post) {
                $lines[] = '- ' . $post['title'] . ': ' . absolute_url(localized_path(localized_post_path($post, $language), $language)) . ' - ' . plain_text((string) ($post['excerpt'] ?: $post['body']), 220);
            }
        }

        $legalPages = array_intersect_key($site['pages'], array_flip(['gdpr', 'privacy', 'terms']));
        if ($legalPages) {
            $lines[] = '';
            $lines[] = '### Legal and policy pages';
            foreach ($legalPages as $page) {
                $lines[] = '- ' . $page['title'] . ': ' . absolute_url(localized_path($page['path'] ?? '/', $language));
            }
        }
        $lines[] = '';
    }

    return implode("\n", $lines) . "\n";
}

function sitemap_paths_for_site(array $site): array
{
    $paths = ['/'];

    foreach ($site['pages'] as $page) {
        $paths[] = $page['path'] ?? '/';
    }

    $paths[] = blog_index_path($site['language']);
    $paths[] = case_study_archive_path($site['language']);

    foreach ($site['posts'] as $post) {
        if (!empty($post['published'])) {
            $paths[] = localized_post_path($post, $site['language']);
        }
    }

    $paths = array_values(array_unique(array_filter($paths)));
    sort($paths);
    return $paths;
}

function render_sitemap_xml(): string
{
    $entries = [];
    foreach (SUPPORTED_LANGUAGES as $language) {
        $site = load_site($language);
        foreach (sitemap_paths_for_site($site) as $path) {
            $entries[localized_route_path($path, DEFAULT_LANGUAGE)] = true;
        }
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

    foreach (array_keys($entries) as $path) {
        foreach (SUPPORTED_LANGUAGES as $language) {
            $url = absolute_url(localized_path($path, $language));
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . e($url) . "</loc>\n";
            foreach (SUPPORTED_LANGUAGES as $alternate) {
                $xml .= '    <xhtml:link rel="alternate" hreflang="' . e($alternate) . '" href="' . e(absolute_url(localized_path($path, $alternate))) . "\" />\n";
            }
            $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="' . e(absolute_url(localized_path($path, DEFAULT_LANGUAGE))) . "\" />\n";
            $xml .= "  </url>\n";
        }
    }

    return $xml . "</urlset>\n";
}

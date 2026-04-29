#!/usr/bin/env node

const { execFileSync } = require("node:child_process");
const fs = require("node:fs");
const path = require("node:path");

const ROOT = path.resolve(__dirname, "..");
const BASE_URL = "https://localcapital.ro";
const OUT_SQL = path.join(ROOT, "database", "imported-wordpress.sql");
const REPORT_JSON = path.join(ROOT, "database", "imported-wordpress-report.json");
const LANGUAGES = ["ro", "en", "hu"];

const pageKeyMap = new Map([
  ["home", { key: "home", path: "/" }],
  ["elementor-3758", { key: "about", path: "/despre-noi" }],
  ["contact", { key: "contact", path: "/contact" }],
  ["contract", { key: "contract", path: "/contract" }],
  ["gdpr", { key: "gdpr", path: "/gdpr" }],
  ["termene-si-conditii", { key: "terms", path: "/termene-si-conditii" }],
  ["politica-privind-datele-personale", { key: "privacy", path: "/politica-privind-datele-personale" }]
]);

function fetchText(url) {
  let lastError;
  for (let attempt = 1; attempt <= 3; attempt += 1) {
    try {
      return execFileSync(
        "curl",
        ["-L", "-s", "--compressed", "--max-time", "60", "-A", "LocalCapitalMigration/1.0", url],
        {
          encoding: "utf8",
          maxBuffer: 30 * 1024 * 1024
        }
      );
    } catch (error) {
      lastError = error;
    }
  }
  throw lastError;
}

function fetchJson(url) {
  const body = fetchText(url);
  return JSON.parse(body);
}

function decodeHtml(value) {
  return String(value ?? "")
    .replace(/&#(\d+);/g, (_, code) => String.fromCodePoint(Number(code)))
    .replace(/&#x([a-f0-9]+);/gi, (_, code) => String.fromCodePoint(parseInt(code, 16)))
    .replace(/&nbsp;/g, " ")
    .replace(/&amp;/g, "&")
    .replace(/&quot;/g, '"')
    .replace(/&#039;/g, "'")
    .replace(/&apos;/g, "'")
    .replace(/&hellip;/g, "...")
    .replace(/&ndash;/g, "-")
    .replace(/&mdash;/g, "-")
    .replace(/&rsquo;/g, "'")
    .replace(/&lsquo;/g, "'")
    .replace(/&rdquo;/g, '"')
    .replace(/&ldquo;/g, '"')
    .replace(/&lt;/g, "<")
    .replace(/&gt;/g, ">");
}

function cleanText(html) {
  return decodeHtml(html)
    .replace(/<script[\s\S]*?<\/script>/gi, " ")
    .replace(/<style[\s\S]*?<\/style>/gi, " ")
    .replace(/<noscript[\s\S]*?<\/noscript>/gi, " ")
    .replace(/<br\s*\/?>/gi, "\n")
    .replace(/<\/(p|div|section|article|h[1-6]|li|ul|ol)>/gi, "\n")
    .replace(/<[^>]+>/g, " ")
    .replace(/[ \t]+\n/g, "\n")
    .replace(/\n{3,}/g, "\n\n")
    .replace(/[ \t]{2,}/g, " ")
    .trim();
}

function excerpt(text, length = 260) {
  const normalized = String(text ?? "").replace(/\s+/g, " ").trim();
  if (normalized.length <= length) return normalized;
  return `${normalized.slice(0, length - 3).trim()}...`;
}

function titleFromHtml(html) {
  const match = html.match(/<title[^>]*>([\s\S]*?)<\/title>/i);
  return match ? cleanText(match[1]) : "";
}

function slugFromPath(pathname) {
  const parts = pathname.split("/").filter(Boolean);
  return parts.at(-1) || "home";
}

function normalizeUrl(href, sourceUrl) {
  if (!href) return "";
  const trimmed = decodeHtml(href).trim();
  if (!trimmed || trimmed.startsWith("#") || /^javascript:/i.test(trimmed)) return "";
  try {
    return new URL(trimmed, sourceUrl).toString();
  } catch {
    return trimmed;
  }
}

function pathnameFromUrl(url) {
  try {
    const parsed = new URL(url);
    let pathname = parsed.pathname.replace(/\/+$/, "");
    return pathname || "/";
  } catch {
    return "/";
  }
}

function isInternal(url) {
  try {
    return new URL(url).hostname === "localcapital.ro";
  } catch {
    return false;
  }
}

function extractLinks(html, sourceUrl, language) {
  const links = [];
  const regex = /<a\b[^>]*href=(["'])(.*?)\1[^>]*>([\s\S]*?)<\/a>/gi;
  let match;
  while ((match = regex.exec(html))) {
    const href = normalizeUrl(match[2], sourceUrl);
    if (!href) continue;
    links.push({
      language,
      sourceUrl,
      href,
      label: excerpt(cleanText(match[3]), 250),
      internal: isInternal(href)
    });
  }
  return links;
}

function sql(value) {
  if (value === null || value === undefined) return "NULL";
  return `'${String(value).replace(/\\/g, "\\\\").replace(/'/g, "''")}'`;
}

function valueList(rows, columns) {
  return rows.map((row) => `(${columns.map((column) => sql(row[column])).join(", ")})`).join(",\n");
}

function uniqueBy(items, keyFn) {
  const map = new Map();
  for (const item of items) {
    const key = keyFn(item);
    if (!map.has(key)) map.set(key, item);
  }
  return [...map.values()];
}

function sitemapUrls(xml) {
  return [...xml.matchAll(/<loc>([\s\S]*?)<\/loc>/g)].map((match) => decodeHtml(match[1]).trim());
}

async function main() {
  const pages = [];
  const posts = [];
  const links = [];
  const htmlUrls = new Set([`${BASE_URL}/`, `${BASE_URL}/ro/`, `${BASE_URL}/hu/`]);

  const sitemapIndex = fetchText(`${BASE_URL}/wp-sitemap.xml`);
  const sitemapFiles = sitemapUrls(sitemapIndex);
  for (const sitemap of sitemapFiles) {
    const xml = fetchText(sitemap);
    for (const url of sitemapUrls(xml)) htmlUrls.add(url);
  }

  for (const language of LANGUAGES) {
    const wpPages = fetchJson(`${BASE_URL}/wp-json/wp/v2/pages?per_page=100&lang=${language}`);
    for (const page of wpPages) {
      const mapped = pageKeyMap.get(page.slug) ?? {
        key: page.slug,
        path: pathnameFromUrl(page.link)
      };
      const body = cleanText(page.content?.rendered ?? page.excerpt?.rendered ?? "");
      pages.push({
        language_code: language,
        page_key: mapped.key,
        path: mapped.path,
        title: cleanText(page.title?.rendered ?? mapped.key),
        summary: excerpt(cleanText(page.excerpt?.rendered ?? body)),
        body,
        cta_label: mapped.key === "home" ? "Aplica acum" : null,
        cta_href: mapped.key === "home" ? "/contact" : null,
        secondary_cta_label: mapped.key === "home" ? "Vezi creditele" : null,
        secondary_cta_href: mapped.key === "home" ? "/contract" : null,
        extra_json: "{}"
      });
      htmlUrls.add(page.link);
    }
  }

  const wpServices = fetchJson(`${BASE_URL}/wp-json/wp/v2/finlon_service?per_page=100`);
  for (const service of wpServices) {
    const body = cleanText(service.content?.rendered ?? service.excerpt?.rendered ?? "");
    for (const language of LANGUAGES) {
      posts.push({
        language_code: language,
        source_type: "service",
        slug: service.slug,
        path: pathnameFromUrl(service.link),
        source_url: service.link,
        title: cleanText(service.title?.rendered ?? service.slug),
        post_date: service.date?.slice(0, 10) || "2026-04-29",
        excerpt: excerpt(cleanText(service.excerpt?.rendered ?? body)),
        body,
        published: 1
      });
    }
    htmlUrls.add(service.link);
  }

  for (const url of [...htmlUrls]) {
    const pathname = pathnameFromUrl(url);
    if (pathname.startsWith("/case_study/")) {
      const html = fetchText(url);
      const body = cleanText(html);
      const slug = slugFromPath(pathname);
      for (const language of LANGUAGES) {
        posts.push({
          language_code: language,
          source_type: "case_study",
          slug,
          path: pathname,
          source_url: url,
          title: titleFromHtml(html).replace(" - Local Capital", "") || slug,
          post_date: "2026-04-29",
          excerpt: excerpt(body),
          body,
          published: 1
        });
      }
    }
  }

  for (const sourceUrl of [...htmlUrls]) {
    try {
      const html = fetchText(sourceUrl);
      const sourceLanguages = sourceUrl.includes("/hu/")
        ? ["hu"]
        : sourceUrl.includes("/ro/")
          ? ["ro"]
          : LANGUAGES;
      for (const language of sourceLanguages) {
        links.push(...extractLinks(html, sourceUrl, language));
      }
    } catch (error) {
      console.error(`Could not fetch ${sourceUrl}: ${error.message}`);
    }
  }

  const pageRows = uniqueBy(pages, (page) => `${page.language_code}:${page.page_key}`);
  const postRows = uniqueBy(posts, (post) => `${post.language_code}:${post.source_type}:${post.slug}`);
  const linkRows = uniqueBy(links, (link) => `${link.language}:${link.sourceUrl}:${link.href}`).map((link) => ({
    language_code: link.language,
    source_url: link.sourceUrl,
    href: link.href,
    label: link.label,
    is_internal: link.internal ? 1 : 0
  }));

  const chunks = [
    "-- Generated by scripts/import-wordpress-content.js",
    "START TRANSACTION;",
    pageRows.length
      ? `INSERT INTO pages (language_code, page_key, path, title, summary, body, cta_label, cta_href, secondary_cta_label, secondary_cta_href, extra_json) VALUES\n${valueList(pageRows, ["language_code", "page_key", "path", "title", "summary", "body", "cta_label", "cta_href", "secondary_cta_label", "secondary_cta_href", "extra_json"])}\nON DUPLICATE KEY UPDATE path = VALUES(path), title = VALUES(title), summary = VALUES(summary), body = VALUES(body), cta_label = VALUES(cta_label), cta_href = VALUES(cta_href), secondary_cta_label = VALUES(secondary_cta_label), secondary_cta_href = VALUES(secondary_cta_href);`
      : "",
    postRows.length
      ? `INSERT INTO posts (language_code, source_type, slug, path, source_url, title, post_date, excerpt, body, published) VALUES\n${valueList(postRows, ["language_code", "source_type", "slug", "path", "source_url", "title", "post_date", "excerpt", "body", "published"])}\nON DUPLICATE KEY UPDATE path = VALUES(path), source_url = VALUES(source_url), title = VALUES(title), post_date = VALUES(post_date), excerpt = VALUES(excerpt), body = VALUES(body), published = VALUES(published);`
      : "",
    "DELETE FROM site_links;",
    linkRows.length
      ? `INSERT INTO site_links (language_code, source_url, href, label, is_internal) VALUES\n${valueList(linkRows, ["language_code", "source_url", "href", "label", "is_internal"])};`
      : "",
    "COMMIT;",
    ""
  ].filter(Boolean);

  fs.writeFileSync(OUT_SQL, chunks.join("\n\n"));
  fs.writeFileSync(
    REPORT_JSON,
    JSON.stringify(
      {
        generatedAt: new Date().toISOString(),
        pages: pageRows.length,
        posts: postRows.length,
        links: linkRows.length,
        crawledUrls: [...htmlUrls].sort()
      },
      null,
      2
    ) + "\n"
  );

  console.log(`Wrote ${OUT_SQL}`);
  console.log(`Pages: ${pageRows.length}, posts/services/case studies: ${postRows.length}, links: ${linkRows.length}`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});

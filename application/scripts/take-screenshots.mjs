import * as url from 'node:url';
import { promises as fs, createWriteStream } from 'node:fs';
import puppeteer from 'puppeteer';
import groupBy from 'lodash/groupBy.js';
import sortBy from 'lodash/sortBy.js';
import { finished } from 'stream/promises';

class Navigator {
    constructor(siteUrl, phpSessionCookie) {
        this.baseUrl = url.parse(siteUrl);
        this.sessionCookie = phpSessionCookie;
    }

    async setup() {
        this.browser = await puppeteer.launch({ headless: true });
        this.page = await this.browser.newPage();
        await this.page.setCookie({
            name: 'PHPSESSID',
            value: this.sessionCookie,
            domain: this.baseUrl.hostname,
            path: '/',
            httpOnly: true,
        });
    }

    async teardown() {
        await this.browser.close();
    }

    getUrl(path) {
        const newUrl = new url.URL(this.baseUrl.href);
        newUrl.pathname = path;
        return newUrl.href;
    }

    async gotoPreferences() {
        await this.page.goto(this.getUrl('/account/preferences'));
    }
}

async function updatePreferences(navigator, prefs) {
    await navigator.gotoPreferences();
    for (const choice of prefs) {
        const selector = `[name="${choice.name}"][value="${choice.value}"]`;
        await navigator.page.click(selector);
        await navigator.page.waitForSelector(`${selector}:checked`);
    }
    await Promise.all([
        navigator.page.waitForNavigation({waitUntil: 'networkidle2'}),
        navigator.page.click(`::-p-text(Update)`),
    ]);
}

function capitalize(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

const INDEX_HTML_PRELUDE = `
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-slate-100">
`;

const INDEX_HTML_POSTLUDE = `
  </body>
</html>
`;

function formatTestcase(testcase) {
    const preconditions = [];
    preconditions.push(`Site: ${testcase.siteColor}`);
    preconditions.push(`OS: ${testcase.osColor}`);
    if (isViewModeRelevant(testcase)) {
        preconditions.push(`List mode: ${testcase.viewMode}`);
    }
    preconditions.push(`Viewport: ${testcase.viewport}`);

    return `
      <div class="flex flex-col w-1/4 p-2 mb-5 gap-1 max-flex-wrap">
        <div class="text-center text-xs text-slate-600">
          ${preconditions.join(' | ')}
        </div>
        <a href="./${testcase.filename}">
          <img
            class="block max-h-80 h-full w-full object-contain object-center"
            src="./${testcase.filename}" />
        </a>
      </div>
    `;
}

async function writeIndexPage(navigator, testcases) {
    const stream = createWriteStream('screenshots/index.html');
    stream.write(INDEX_HTML_PRELUDE);
    const pages = groupBy(testcases, 'pagePath');
    for (const [path, pageTestcases] of Object.entries(pages)) {
        stream.write(`<h1 class="bg-slate-800 text-slate-200 text-4xl my-8 py-4 text-center">${path}</h1>`);
        stream.write('<div class="flex flex-wrap items-start">');
        const sortedTestcases = sortBy(pageTestcases, [
            'osColor',
            'siteColor',
            'viewMode',
            'viewport',
        ]);
        for (const testcase of pageTestcases) {
            stream.write(formatTestcase(testcase));
        }
        stream.write('</div>');
    }
    stream.write(INDEX_HTML_POSTLUDE);
    stream.end();
    await finished(stream);
}

function isViewModeRelevant({pageName}) {
    return pageName === 'community';
}

function shouldSkip({pageName, viewMode}) {
    // If this is a non-community page, view mode doesn't matter
    if (!isViewModeRelevant({pageName}) && viewMode !== viewModePreferences[0]['name']) {
        return true;
    }
    return false;
}

const osColorSchemes = ["dark", "light"];
const colorPreferences = [
    {name: "dark", choice: {
        name: "user_preferences[colors_mode_picker]",
        value: "dark",
    }},
    {name: "light", choice: {
        name: "user_preferences[colors_mode_picker]",
        value: "light",
    }},
    {name: "os", choice: {
        name: "user_preferences[colors_mode_picker]",
        value: "os",
    }},
];
const viewModePreferences = [
    {name: "expanded", choice: {
        name: "user_preferences[all_watches_view_mode_picker]",
        value: "expanded",
    }},
    {name: "condensed", choice: {
        name: "user_preferences[all_watches_view_mode_picker]",
        value: "condensed",
    }},
];
const pages = [
    {name: 'home', path: '/'},
    {name: 'personal', path: '/personal/watch'},
    {name: 'community', path: '/community/watch'},
    {name: 'vote', path: '/vote'},
];
const viewports = [
    {name: 'small', value: {width: 360, height: 640 * 2}},
    {name: 'medium', value: {width: 960, height: 540 * 2.5}},
    {name: 'large', value: {width: 1024, height: 640 * 2}},
    {name: 'xlarge', value: {width: 1920, height: 1080}},
];

async function takeScreenshots(navigator, indexPageOnly) {
    const testcases = [];
    for (const colorPref of colorPreferences) {
        for (const viewMode of viewModePreferences) {
            if (!indexPageOnly) {
                await updatePreferences(navigator, [colorPref.choice, viewMode.choice]);
            }
            for (const page of pages) {
                if (!indexPageOnly) {
                    await navigator.page.goto(
                        navigator.getUrl(page.path),
                        { waitUntil: ["load","networkidle0"] },
                    );
                }
                for (const osColorScheme of osColorSchemes) {
                    if (!indexPageOnly) {
                        await navigator.page.emulateMediaFeatures([{
                            name: 'prefers-color-scheme',
                            value: osColorScheme,
                        }]);
                    }
                    for (const viewport of viewports) {
                        const filename = `osc-${osColorScheme}-stc-${colorPref.name}-vm-${viewMode.name}-vp-${viewport.name}-pg-${page.name}.jpg`;
                        const testcase = {
                            osColor: osColorScheme,
                            siteColor: colorPref.name,
                            viewMode: viewMode.name,
                            viewport: viewport.name,
                            pagePath: page.path,
                            pageName: page.name,
                            filename: filename,
                        };
                        if (shouldSkip(testcase)) {
                            continue;
                        }
                        testcases.push(testcase);
                        const screenshotPath = `screenshots/${filename}`;
                        if (!indexPageOnly) {
                            console.log(`Capturing screenshot ${screenshotPath}`);
                            await navigator.page.setViewport(viewport.value);
                            await navigator.page.screenshot({ path: screenshotPath });
                        }
                    }
                }
            }
        }
    }
    return testcases;
}

async function main(siteUrl, phpSessionCookie, indexPageOnly) {
    const navigator = new Navigator(siteUrl, phpSessionCookie);
    if (!indexPageOnly) {
        await navigator.setup();
    }
    try {
        const testcases = await takeScreenshots(navigator, indexPageOnly);
        await writeIndexPage(navigator, testcases);
    } finally {
        if (!indexPageOnly) {
            await navigator.teardown();
        }
    }
}

main(process.env.SITE_URL || 'http://localhost:8000', process.env.PHP_SESSION_COOKIE, process.env.INDEX_PAGE_ONLY);

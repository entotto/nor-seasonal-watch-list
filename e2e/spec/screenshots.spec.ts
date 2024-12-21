import { URL } from 'node:url';
import { test, type Page } from '@playwright/test';

import { getScreenshotPathForTestCase, ScreenshotTestCase, enumerateTestCases } from '../utils/screenshots';

interface RadioOption {
  name: string;
  value: string;
}

interface ViewportSize {
  width: number;
  height: number;
}

const siteColorToRadioOption: Record<ScreenshotTestCase["siteColor"], RadioOption> = {
  "dark": {
    name: "user_preferences[colors_mode_picker]",
    value: "dark",
  },
  "light": {
    name: "user_preferences[colors_mode_picker]",
    value: "light",
  },
  "os": {
    name: "user_preferences[colors_mode_picker]",
    value: "os",
  },
};

const viewModeToRadioOption: Record<ScreenshotTestCase["viewMode"], RadioOption | null> = {
  "expanded": {
    name: "user_preferences[all_watches_view_mode_picker]",
    value: "expanded",
  },
  "condensed": {
    name: "user_preferences[all_watches_view_mode_picker]",
    value: "condensed",
  },
  "none": null,
};

const pageNameToPagePath: Record<ScreenshotTestCase["pageName"], string> = {
  "home": "/",
  "personal": "/personal/watch",
  "community": "/community/watch",
  "vote": "/vote",
};

const viewportToSize: Record<ScreenshotTestCase["viewport"], ViewportSize> = {
  "small": {width: 360, height: 640},
  "medium": {width: 960, height: 540},
  "large": {width: 1024, height: 640},
  "xlarge": {width: 1920, height: 1080},
};


class PreferencesPage {
  page: Page;
  
  constructor(page: Page) {
    this.page = page;
  }
  async navigate() {
    await this.page.goto('/account/preferences');
  }
  async select(option: RadioOption | null) {
    if (!option) return;
    const selector = `[name="${option.name}"][value="${option.value}"]`;
    await this.page.locator(selector).check();
  }
  async submit() {
    const responsePromise = this.page.waitForResponse(this.page.url());
      await this.page.getByText("Update").click();
    await responsePromise;
  }
}

async function configureViewContext(page: Page, testcase: ScreenshotTestCase) {
  await page.emulateMedia({ colorScheme: testcase.osColor });
  await page.setViewportSize(viewportToSize[testcase.viewport]);

  const prefsPage = new PreferencesPage(page);
  await prefsPage.navigate();
  await prefsPage.select(siteColorToRadioOption[testcase.siteColor]);
  await prefsPage.select(viewModeToRadioOption[testcase.viewMode]);
  await prefsPage.submit();
}

enumerateTestCases().forEach((testcase) => {
  test(testcase.getFilename(), {
    tag: '@screenshot',
    annotation: [
      { type: 'screenshot-testcase', description: JSON.stringify(testcase) },
    ],
  }, async ({ page, baseURL }) => {
    if (process.env.SCREENSHOT_INDEX_ONLY) {
      return;
    }
    const cookie = {
      name: 'PHPSESSID',
      value: process.env.PHP_SESSION_COOKIE,
      domain: (new URL(baseURL)).hostname,
      path: '/',
      httpOnly: true,
    };
    await page.context().addCookies([cookie]);
    await configureViewContext(page, testcase);
    
    await page.goto(pageNameToPagePath[testcase.pageName]);
    await page.waitForLoadState("load");

    if (testcase.pageName === "community") {
        const detailsLinks = await page.getByText("Details").and(page.locator(":visible")).all();
      if (detailsLinks.length > 0) {
        const detailsLink = detailsLinks[0];
        await detailsLink.evaluate((node: HTMLElement) => node.click());
        const targetElementId = await detailsLink.getAttribute("aria-controls");
          await page.waitForFunction((selector) => {
              const match = document.querySelector(selector);
              console.log(selector, match);
              return !!match;
          }, `#${targetElementId}.collapse.show`);
      }
    }
    
    await page.screenshot({ path: getScreenshotPathForTestCase(testcase, false), disableAnimations: true, fullPage: false });
    await page.screenshot({ path: getScreenshotPathForTestCase(testcase, true), disableAnimations: true, fullPage: true });
  });
});

import * as path from 'node:path';

export class ScreenshotTestCase {  
  pageName: "home" | "personal" | "community" | "vote";
  osColor: "dark" | "light";
  siteColor: "dark" | "light" | "os";
  viewMode: "expanded" | "condensed" | "none";
  viewport: "small" | "medium" | "large" | "xlarge";

  constructor({ pageName, osColor, siteColor, viewMode, viewport }: Pick<ScreenshotTestCase, "pageName" | "osColor" | "siteColor" | "viewMode" | "viewport">) {
    this.pageName = pageName;
    this.osColor = osColor;
    this.siteColor = siteColor;
    this.viewMode = viewMode;
    this.viewport = viewport;
  }

  getFilename(): string {
    return `${this.basename}.png`
  }

  getFilenameFull(): string {
    return `${this.basename}-full.png`
  }

  get basename(): string {
    return `osc-${this.osColor}-stc-${this.siteColor}-vm-${this.viewMode}-vp-${this.viewport}-pg-${this.pageName}`
  }

  static fromJSON(s: string) {
    return new ScreenshotTestCase(JSON.parse(s));
  }
}

export function getScreenshotPathForTestCase(test: ScreenshotTestCase, full: boolean = false): string {
  return path.join(getOutputDir(), full ? test.getFilenameFull() : test.getFilename());
}

export function getScreenshotReportPath(): string {
  return path.join(getOutputDir(), 'index.html');
}

function getOutputDir(): string {
  return process.env.SCREENSHOTS_OUTPUT_DIR || 'screenshots';
}


function isViewModeRelevant(pageName: ScreenshotTestCase["pageName"]): boolean {
    return pageName === 'community';
}

interface TestCaseAttributes {
  pageNames: Array<ScreenshotTestCase["pageName"]>;
  osColors: Array<ScreenshotTestCase["osColor"]>;
  siteColors: Array<ScreenshotTestCase["siteColor"]>;
  viewModes: Array<ScreenshotTestCase["viewMode"]>;
  viewports: Array<ScreenshotTestCase["viewport"]>;
}

export const TESTCASE_ATTRIBUTES: TestCaseAttributes = {
  pageNames: ["home", "personal", "community", "vote"],
  osColors: ["dark", "light"],
  siteColors: ["dark", "light", "os"],
  viewModes: ["expanded", "condensed", "none"],
  viewports: ["small", "medium", "large", "xlarge"],
};

export function enumerateTestCases(): Array<ScreenshotTestCase> {
  const testcases: Array<ScreenshotTestCase> = [];
  for (const osColor of TESTCASE_ATTRIBUTES.osColors) {
      //if (osColor != "dark") continue;
    for (const siteColor of TESTCASE_ATTRIBUTES.siteColors) {
      //if (siteColor != "os") continue;
      for (const pageName of TESTCASE_ATTRIBUTES.pageNames) {
        for (const viewport of TESTCASE_ATTRIBUTES.viewports) {
      //if (viewport != "medium") continue;
            const pageViewModes: Array<ScreenshotTestCase["viewMode"]> = isViewModeRelevant(pageName) ? ["expanded", "condensed"] : ["none"];
            
            for (const viewMode of pageViewModes) {
                //if (viewMode !== "condensed") continue;
            testcases.push(new ScreenshotTestCase({
              pageName,
              osColor,
              siteColor,
              viewMode,
              viewport,
            }));
          }
        }
      }
    }
  }
  return testcases;
}

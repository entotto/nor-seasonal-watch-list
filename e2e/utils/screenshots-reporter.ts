import { createWriteStream } from 'node:fs';
import { finished } from 'stream/promises';

import groupBy from 'lodash-es/groupBy.js';
import indexOf from 'lodash-es/indexOf.js';
import sortBy from 'lodash-es/sortBy.js';

import type {
  Reporter, TestCase, TestResult, FullResult
} from '@playwright/test/reporter';

import { TESTCASE_ATTRIBUTES, ScreenshotTestCase, getScreenshotReportPath } from './screenshots';

class ScreenshotReporter implements Reporter {
  screenshots: Array<ScreenshotTestCase>;
  
  constructor() {
    this.screenshots = [];
  }

  onTestEnd(test: TestCase, _result: TestResult) {
    const testcaseAnnotation = test.annotations.find(x => x.type === "screenshot-testcase")?.description;
    if (testcaseAnnotation) {
      this.screenshots.push(ScreenshotTestCase.fromJSON(testcaseAnnotation));
    }
  }

  onEnd(_result: FullResult) {
    return writeIndexPage(getScreenshotReportPath(), this.screenshots);
  }

  printsToStdio() {
    return false;
  }
}
export default ScreenshotReporter;

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

async function writeIndexPage(outputPath: string, testcases: Array<ScreenshotTestCase>) {
  const stream = createWriteStream(outputPath);
  stream.write(INDEX_HTML_PRELUDE);
  const pages = groupBy(testcases, 'pageName');
  for (const [path, pageTestcases] of Object.entries(pages)) {
    stream.write(`<h1 class="bg-slate-800 text-slate-200 text-4xl my-8 py-4 text-center">${path}</h1>`);
    stream.write('<ol class="flex flex-wrap items-start list-none">');
    const sortedTestcases = sortBy(pageTestcases, [
      (x) => indexOf(TESTCASE_ATTRIBUTES.osColors, x.osColor),
      (x) => indexOf(TESTCASE_ATTRIBUTES.siteColors, x.siteColor),
      (x) => indexOf(TESTCASE_ATTRIBUTES.viewModes, x.viewMode),
      (x) => indexOf(TESTCASE_ATTRIBUTES.viewports, x.viewport),
    ]);
    for (const testcase of sortedTestcases) {
      stream.write(formatTestcase(testcase));
    }
    stream.write('</ol>');
  }
  stream.write(INDEX_HTML_POSTLUDE);
  stream.end();
  await finished(stream);
}

function formatTestcase(testcase: ScreenshotTestCase) {
  const contexts = [];
  contexts.push(`OS: ${testcase.osColor}`);
  contexts.push(`Site: ${testcase.siteColor}`);
  if (testcase.viewMode !== "none") {
      contexts.push(`List mode: ${testcase.viewMode}`);
  }
  contexts.push(`Viewport: ${testcase.viewport}`);

  // We know that index.html and screenshots are co-located in the same directory, so we'll use relative paths.
  return `
    <li class="flex flex-col items-center w-1/4 p-2 mb-5 gap-1 max-flex-wrap">
      <div class="text-center text-xs text-slate-600">
        ${contexts.join(' | ')}
      </div>
      <a href="./${testcase.getFilenameFull()}" class="block hover:ring-2 focus:ring-2">
        <img
          class="block max-h-80 h-full w-full object-contain object-top"
          src="./${testcase.getFilename()}" />
      </a>
    </li>
    `;
}

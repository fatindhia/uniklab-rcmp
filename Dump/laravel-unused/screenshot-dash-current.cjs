const { chromium } = require('/private/tmp/claude-501/-Users-fatindhiaa-projects-src-lab-booking/7c55907a-3f27-4e41-9193-8bdfb2ad6efa/scratchpad/node_modules/playwright');
const path = require('path');
const OUT = '/private/tmp/claude-501/-Users-fatindhiaa-projects-src-lab-booking/7c55907a-3f27-4e41-9193-8bdfb2ad6efa/scratchpad/screenshots';

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
    const page = await context.newPage();
    await page.goto('http://lab-booking.test/admin/login', { waitUntil: 'networkidle' });
    await page.fill('#staff_id', '620798');
    await page.fill('#password', 'Rcmp@1234');
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'networkidle' }),
        page.click('button[type="submit"]'),
    ]);
    await page.screenshot({ path: path.join(OUT, 'dash-current-full.png'), fullPage: true });
    await browser.close();
    console.log('done');
})();

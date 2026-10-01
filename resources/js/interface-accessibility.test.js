import test, { before, after } from 'node:test';
import assert from 'node:assert/strict';
import { readFile, mkdir } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import postcss from 'postcss';
import tailwind from '@tailwindcss/postcss';
import { chromium } from 'playwright';

const root = fileURLToPath(new URL('../../', import.meta.url));
const source = async (path) => readFile(new URL(`../../${path}`, import.meta.url), 'utf8');
let browser;
let css;

before(async () => {
    const input = await source('resources/css/app.css');
    css = (await postcss([tailwind()]).process(input, { from: `${root}resources/css/app.css` })).css;
    browser = await chromium.launch({
        channel: process.env.PLAYWRIGHT_CHROMIUM_CHANNEL || (process.platform === 'darwin' ? 'chrome' : undefined),
        headless: true,
    });
});

after(async () => browser?.close());

async function renderedPage(markup) {
    const page = await browser.newPage();
    await page.setContent(`<html class="dark"><head><style>${css}</style></head><body>${markup}</body></html>`);
    return page;
}

async function finishTransitions(page, selector) {
    await page.evaluate(async (selector) => {
        await new Promise(requestAnimationFrame);
        await Promise.all(document.querySelector(selector).getAnimations().map((animation) => animation.finished));
    }, selector);
}

// Resolve component-owned classes so a Blade change can break these checks too.
async function badgeMarkup(type, label) {
    const blade = await source('resources/views/components/status-badge.blade.php');
    const classes = blade.match(/\$baseClasses = '([^']+)'/)[1];
    const dot = blade.match(new RegExp(`'${type}' => '([^']+)'`))[1];
    return `<span class="${classes}"><span class="size-1.5 shrink-0 rounded-full ${dot}" data-dot="${type}"></span>${label}</span>`;
}

async function calloutMarkup(type) {
    const blade = await source('resources/views/components/callout.blade.php');
    const styles = blade.match(new RegExp(`'${type}' => \\[([\\s\\S]*?)\\n        \\]`))[1];
    const shell = styles.match(/'shell' => '([^']+)'/)[1];
    const icon = styles.match(/'iconClass' => '([^']+)'/)[1];
    const title = styles.match(/'titleClass' => '([^']+)'/)[1];
    const base = blade.match(/'class' => '([^']+)'/)[1];
    return `<div class="${base} ${shell}"><div class="flex items-start gap-2.5"><svg data-callout="${type}" class="mt-0.5 size-4 shrink-0 ${icon}" viewBox="0 0 24 24"><path d="M12 3 22 21H2Z" fill="none" stroke="currentColor"/><path d="M12 9v5m0 3v1" stroke="currentColor"/></svg><span class="${title}">Health check is failing</span></div></div>`;
}

async function checkboxMarkup(id, component = 'checkbox') {
    const blade = await source(`resources/views/components/forms/${component}.blade.php`);
    const classes = blade.match(/class="(pointer-events-none absolute inset-0 rounded-\[5px\][^"]+)"/)[1];
    return `<label class="group relative inline-flex size-5"><input class="peer" type="checkbox" aria-label="${id}"><span id="${id}" class="${classes}"></span></label>`;
}

async function installContrastMeasurement(page) {
    await page.evaluate(() => {
        const channels = (color) => {
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            context.fillStyle = color;
            context.fillRect(0, 0, 1, 1);
            return Array.from(context.getImageData(0, 0, 1, 1).data);
        };
        const blend = (front, back) => front.slice(0, 3).map((value, index) => value * front[3] / 255 + back[index] * (1 - front[3] / 255));
        const luminance = (color) => color.slice(0, 3).reduce((sum, value, index) => {
            const channel = value / 255;
            return sum + (channel <= 0.04045 ? channel / 12.92 : ((channel + 0.055) / 1.055) ** 2.4) * [0.2126, 0.7152, 0.0722][index];
        }, 0);
        const background = (element) => {
            if (!element) return [255, 255, 255];
            return blend(channels(getComputedStyle(element).backgroundColor), background(element.parentElement));
        };
        const ratio = (first, second) => (Math.max(luminance(first), luminance(second)) + 0.05) / (Math.min(luminance(first), luminance(second)) + 0.05);
        window.measureControlContrast = (selector) => {
            const element = document.querySelector(selector);
            const surrounding = background(element.parentElement);
            const fill = background(element);
            const border = blend(channels(getComputedStyle(element).borderTopColor), fill);
            return Math.min(ratio(border, surrounding), ratio(border, fill));
        };
        window.colorChannels = (selector, property = 'backgroundColor') => channels(getComputedStyle(document.querySelector(selector))[property]);
        window.textContrast = (selector) => {
            const element = document.querySelector(selector);
            return ratio(channels(getComputedStyle(element).color), background(element));
        };
        window.measureFillContrast = (selector) => {
            const element = document.querySelector(selector);
            return ratio(background(element), background(element.parentElement));
        };
    });
}

test('warning statuses and callouts render amber distinctly from healthy green', async () => {
    const page = await renderedPage([
        await badgeMarkup('success', 'Running'),
        await badgeMarkup('warning', 'Unhealthy'),
        await calloutMarkup('warning'),
    ].join(''));
    try {
        await installContrastMeasurement(page);
        const healthy = await page.evaluate(() => window.colorChannels('[data-dot="success"]'));
        const warning = await page.evaluate(() => window.colorChannels('[data-dot="warning"]'));
        const callout = await page.evaluate(() => window.colorChannels('[data-callout="warning"]', 'color'));
        assert.ok(healthy[1] > healthy[0] + 30, `healthy dot must be green: ${healthy}`);
        for (const color of [warning, callout]) {
            assert.ok(color[0] > color[1] + 20 && color[1] > color[2] + 50, `warning must be amber: ${color}`);
        }
        const warningContrast = await page.evaluate(() => window.textContrast('[data-callout="warning"]'));
        assert.ok(warningContrast >= 4.5, `warning foreground contrast ${warningContrast.toFixed(2)}:1 is below 4.5:1`);
        await mkdir(`${root}tests/Browser/Screenshots`, { recursive: true });
        await page.screenshot({ path: `${root}tests/Browser/Screenshots/interface-status-colors.png` });
    } finally {
        await page.close();
    }
});

test('enabled form boundaries have at least 3:1 contrast inside and outside settings cards', async () => {
    const controls = (prefix) => `
        <input id="${prefix}-input" class="input" aria-label="Name">
        <textarea id="${prefix}-textarea" class="input" aria-label="Description"></textarea>
        <select id="${prefix}-select" class="select" aria-label="Region"><option>Europe</option></select>
        <button id="${prefix}-listbox" class="listbox-trigger">Region</button>
        <input id="${prefix}-search" class="searchable-listbox-search-input" aria-label="Search">
        <div id="${prefix}-chips" class="chip-input"><input aria-label="Domains"></div>`;
    const page = await renderedPage(`${controls('page')}<section class="application-settings-section application-settings-form">${controls('settings')}</section>`);
    try {
        await installContrastMeasurement(page);
        for (const prefix of ['page', 'settings']) {
            for (const control of ['input', 'textarea', 'select', 'listbox', 'search', 'chips']) {
                const selector = `#${prefix}-${control}`;
                const contrast = await page.evaluate((selector) => window.measureControlContrast(selector), selector);
                assert.ok(contrast >= 3, `${selector} contrast ${contrast.toFixed(2)}:1 is below 3:1`);
                await page.focus(control === 'chips' ? `${selector} input` : selector);
                await finishTransitions(page, selector);
                const focusContrast = await page.evaluate((selector) => window.measureControlContrast(selector), selector);
                assert.ok(focusContrast >= 3, `${selector} focus contrast ${focusContrast.toFixed(2)}:1 is below 3:1`);
            }
        }
        for (const selector of ['#settings-input', '#page-input']) {
            await page.$eval(selector, (element) => element.setAttribute('aria-invalid', 'true'));
            await finishTransitions(page, selector);
            const errorContrast = await page.evaluate((selector) => window.measureControlContrast(selector), selector);
            assert.ok(errorContrast >= 3, `${selector} invalid boundary contrast ${errorContrast.toFixed(2)}:1 is below 3:1`);
        }
        await mkdir(`${root}tests/Browser/Screenshots`, { recursive: true });
        await page.screenshot({ path: `${root}tests/Browser/Screenshots/interface-accessibility.png` });
    } finally {
        await page.close();
    }
});

test('legacy utility shadows still provide depth for floating elements', async () => {
    const page = await renderedPage('<div class="shadow-sm" id="floating-menu">Menu</div>');
    try {
        const shadow = await page.$eval('#floating-menu', (element) => getComputedStyle(element).boxShadow);
        assert.notEqual(shadow, 'none');
        assert.ok(!shadow.split(', ').every((part) => part.includes('rgba(0, 0, 0, 0)')), shadow);
        assert.match(shadow, /(?:[1-9]\d*|0\.\d+)px/, `shadow must have a visible offset or blur: ${shadow}`);
    } finally {
        await page.close();
    }
});

test('checkbox boundaries remain visible before selection', async () => {
    const page = await renderedPage(`${await checkboxMarkup('checkbox')} ${await checkboxMarkup('datalist-checkbox', 'datalist')}`);
    try {
        await installContrastMeasurement(page);
        for (const selector of ['#checkbox', '#datalist-checkbox']) {
            const contrast = await page.evaluate((selector) => window.measureControlContrast(selector), selector);
            assert.ok(contrast >= 3, `${selector} contrast ${contrast.toFixed(2)}:1 is below 3:1`);
        }
        await page.check('input[aria-label="checkbox"]');
        await finishTransitions(page, '#checkbox');
        const selectedContrast = await page.evaluate(() => window.measureFillContrast('#checkbox'));
        assert.ok(selectedContrast >= 3, `checked boundary contrast ${selectedContrast.toFixed(2)}:1 is below 3:1`);
    } finally {
        await page.close();
    }
});

test('page navigation preserves existing theme and layout preferences', async () => {
    const blade = await source('resources/views/layouts/base.blade.php');
    const bootTheme = blade.match(/<script data-navigate-once>([\s\S]*?)<\/script>/)[1];
    const page = await browser.newPage();
    try {
        await page.route('http://coolify.test/**', (route) => route.fulfill({ contentType: 'text/html', body: '<html><body>Preferences</body></html>' }));
        await page.goto('http://coolify.test/');
        await page.evaluate(() => {
            localStorage.setItem('theme', 'light');
            localStorage.setItem('themeColor', '#6b16ed');
            localStorage.setItem('customMode', 'light');
            localStorage.setItem('pageWidth', 'centered');
        });
        await page.addScriptTag({ content: bootTheme });
        await page.evaluate(() => document.dispatchEvent(new Event('livewire:navigated')));
        const preferences = await page.evaluate(() => ({ ...localStorage }));
        assert.deepEqual(preferences, { theme: 'light', themeColor: '#6b16ed', customMode: 'light', pageWidth: 'centered' });
        const width = await page.evaluate(() => window.themeControls().pageWidth);
        assert.equal(width, 'centered');
    } finally {
        await page.close();
    }
});

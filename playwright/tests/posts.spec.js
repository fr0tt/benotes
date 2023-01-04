// @ts-check
import { expect, test } from '@playwright/test'
import beforeEach from './../beforeEach'

test.beforeEach(beforeEach)

test('create link post without image', async ({ page }) => {
    const link = 'https://benotes.org'
    await page.getByRole('button', { name: 'Create' }).click()
    await page.locator('.ProseMirror').fill(link)
    await page.getByRole('button', { name: 'Save' }).click()

    await page.waitForLoadState('networkidle')
    await expect.poll(async () => page.locator('#view div.card').count()).toBeGreaterThanOrEqual(1)
    expect(
        await page.locator('#view div.card').nth(0).locator('a[title]').getAttribute('href'),
        link
    )

    await page
        .locator('#view div.card')
        .nth(0)
        .locator('[title]')
        .nth(0)
        .textContent()
        .toString()
        .includes('Benotes')

    expect(
        await page.locator('#view div.card').nth(0).locator('[title]').nth(0).getAttribute('title')
    ).toContain('Benotes')
})

test('create link post with image', async ({ page }) => {
    const link = 'https://github.com'
    await page.getByRole('button', { name: 'Create' }).click()
    await page.locator('.ProseMirror').fill(link)
    await page.getByRole('button', { name: 'Save' }).click()

    await page.waitForLoadState('networkidle')
    //await page.waitForResponse((res) => res.url().includes('/api/posts'))

    await expect.poll(async () => page.locator('#view div.card').count()).toBeGreaterThanOrEqual(1)
    await page.locator('#view div.card').nth(0).getByRole('link', { name: link }).isVisible()

    expect(
        await page.locator('#view div.card').nth(0).locator('a[title]').getAttribute('href'),
        link
    )

    await page
        .locator('#view div.card')
        .nth(0)
        .locator('[title]')
        .nth(0)
        .textContent()
        .toString()
        .includes('GitHub')

    const thumbnail = expect(
        await page
            .locator('#view div.card')
            .nth(0)
            .locator('.bg-cover')
            .evaluate((node) => node.style.backgroundImage)
    )
    thumbnail.toContain('.jpg')
    thumbnail.toContain('thumbnails/thumbnail_')
})

test('infinite scroll', async ({ page, request }) => {
    const post = {
        title: 'Infinite',
        content: 'This is just a simple example',
    }
    const numberOfItems = 25

    await page.waitForLoadState('networkidle')
    const token = await (await page.context().cookies()).at(0)?.value

    for (let i = 0; i < numberOfItems; i++) {
        await request.post('/api/posts', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            data: post,
        })
    }

    await page.waitForLoadState('networkidle')
    // await page.waitForTimeout(3000)  if it does not work try that
    await page.goto('/')
    await page.waitForLoadState('networkidle')

    await expect.poll(async () => page.locator('#view div.card').count()).toBe(20)

    await page.waitForLoadState()

    await page.evaluate(() => scrollTo(0, document.body.clientHeight))
    await expect
        .poll(async () =>
            page.locator('#view div.card .editorContent').getByText(post.content).count()
        )
        .toBe(numberOfItems)
})

test('paste link as new post with image', async ({ page }, testInfo) => {
    /*
     * only execute this test in chrome and on localhost because
     * otherwise the app is considered unsafe without ssl
     **/
    test.skip(testInfo.project.name !== 'chromium')

    await page.goto('/')

    test.skip(!page.url().includes('localhost'))

    const link = 'https://github.com'

    await page.evaluate(() => window.navigator.clipboard.writeText('https://github.com'))
    await page.getByRole('button', { name: 'Paste' }).click()

    await expect.poll(async () => page.locator('#view div.card').count()).toBeGreaterThanOrEqual(1)
    await page.locator('#view div.card').nth(0).getByRole('link', { name: link }).isVisible()

    expect(
        await page.locator('#view div.card').nth(0).locator('a[title]').getAttribute('href'),
        link
    )

    await page
        .locator('#view div.card')
        .nth(0)
        .locator('[title]')
        .nth(0)
        .textContent()
        .toString()
        .includes('GitHub')

    const thumbnail = expect(
        await page
            .locator('#view div.card')
            .nth(0)
            .locator('.bg-cover')
            .evaluate((node) => node.style.backgroundImage)
    )
    thumbnail.toContain('.jpg')
    thumbnail.toContain('thumbnails/thumbnail_')
})

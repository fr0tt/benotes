// @ts-check
import { expect, test } from '@playwright/test'
import { beforeEach, beforeEachSetup } from '../beforeEach'
import afterEachTeardown from '../afterEach'

test.beforeEach(beforeEachSetup)
test.beforeEach(beforeEach)
test.afterEach(afterEachTeardown)

test('delete post and update order', async ({ page }) => {
    await page.waitForLoadState('networkidle')

    const post = {
        content: 'This is just a simple example',
    }
    const numberOfItems = 5
    let ids = []

    for (let i = 0; i < numberOfItems; i++) {
        post.title = 'Test Content ' + (i + 1)
        const response = await page.request.post('/api/posts', {
            data: post,
        })
        response.json().then((data) => {
            ids.push(data.data.id)
        })
        await expect(response.status()).toBe(201)
    }

    await page.goto('/')

    await expect.poll(async () => page.locator('#view .post').count()).toBe(numberOfItems)

    await page
        .locator('#view .post input')
        .nth(numberOfItems - 1)
        .textContent()
        .toString()
        .includes('Test Content')

    // delete first post
    await page.locator(`[post-id="${ids[0]}"] svg`).first().click()
    await page.locator(`[post-id="${ids[0]}"] li`).nth(2).click()
    // wait long enough
    await expect
        .poll(async () => page.locator('#view .post').count())
        .toBe(numberOfItems - 1)

    await expect(
        page.locator(`[post-id="5"] > div`).nth(1).locator('span').nth(1)
    ).toHaveText(`o:${numberOfItems - 1}`)

    let posts = await page.request.get('/api/posts')
    expect((await posts.json()).data[0].order).toBe(numberOfItems - 1)

    await page.screenshot()
})

test('drag post multiple times', async ({ page }) => {
    await page.waitForLoadState('networkidle')

    const post = {
        content: 'This is just a simple example',
    }
    const numberOfItems = 12
    let ids = []

    for (let i = 0; i < numberOfItems; i++) {
        post.title = 'Test Content ' + (i + 1)
        const response = await page.request.post('/api/posts', {
            data: post,
        })
        response.json().then((data) => {
            ids.push(data.data.id)
        })
        await expect(response.status()).toBe(201)
    }

    await page.goto('/')

    await expect.poll(async () => page.locator('#view .post').count()).toBe(numberOfItems)

    await page
        .locator('#view .post input')
        .nth(numberOfItems - 1)
        .textContent()
        .toString()
        .includes('Test Content')

    await page
        .locator(`[post-id="${ids[10]}"]`)
        .first()
        .dragTo(page.locator(`[post-id="${ids[1]}"]`).first())

    await page
        .locator(`[post-id="${ids[1]}"]`)
        .first()
        .dragTo(page.locator(`[post-id="${ids[10]}"]`).first())

    await page.waitForTimeout(100)

    await page.locator(`[post-id="${ids[0]}"] svg`).first().click()
    await page.locator(`[post-id="${ids[0]}"] li`).nth(2).click()

    await expect
        .poll(async () => page.locator('#view .post').count())
        .toBe(numberOfItems - 1)

    await page
        .locator(`[post-id="${ids[10]}"]`)
        .first()
        .dragTo(page.locator(`[post-id="${ids[1]}"]`).first())

    await page
        .locator(`[post-id="${ids[1]}"]`)
        .first()
        .dragTo(page.locator(`[post-id="${ids[10]}"]`).first())

    await page.waitForTimeout(2 * 1000)

    let response = await page.request.get('/api/posts')
    response = await response.json()

    await expect([12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2]).toEqual(
        response.data.map((post) => post.id)
    )

    await page.screenshot()
})

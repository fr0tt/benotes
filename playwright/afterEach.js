import { expect } from '@playwright/test'

const afterEachTeardown = async ({ page, request }) => {
    let response = await page.request.post('/api/__e2e__/teardown')
    await expect(response.ok()).toBeTruthy()
}

export default afterEachTeardown

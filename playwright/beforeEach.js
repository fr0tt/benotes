import { expect } from '@playwright/test'

export const beforeEach = async ({ page, request }) => {
    const user = {
        email: Date.now() + 'test@benotes.org',
        password: 'password',
    }

    let response = await request.post('/api/__e2e__/user', {
        data: user,
    })
    await expect(response.ok()).toBeTruthy()

    await page.context().clearCookies()

    await page.goto('/login')
    await page.getByPlaceholder('Email Address').fill(user.email)
    await page.getByPlaceholder('Password').fill(user.password)
    await page.getByRole('button', { name: 'Login' }).click()

    //await page.waitForNavigation() // should work without it
}

export const beforeEachSetup = async ({ page, request }) => {
    let response = await page.request.post('/api/__e2e__/setup')
    await expect(response.ok()).toBeTruthy()
}

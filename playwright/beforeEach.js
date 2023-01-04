import { expect } from '@playwright/test'

const beforeEach = async ({ page, request }) => {
    let response = null

    const user = {
        email: Date.now() + 'test@benotes.org',
        password: 'password',
    }

    response = await request.post('/api/__e2e__/user', {
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

export default beforeEach

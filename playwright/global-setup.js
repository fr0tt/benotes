const { chromium, request, expect } = require('@playwright/test')
const fs = require('fs')

module.exports = async (config) => {
    const { baseURL, storageState } = config.projects[0].use

    if (fs.existsSync('.env.playwright')) {
        fs.renameSync('.env', '.env.original')
        fs.renameSync('.env.playwright', '.env')
    }

    if (!fs.existsSync(storageState)) {
        fs.writeFileSync(storageState, JSON.stringify({}))
    }

    const requestContext = await request.newContext({
        baseURL: baseURL,
    })

    let response = await requestContext.post('/api/__e2e__/setup')
    await expect(response.ok()).toBeTruthy()
}

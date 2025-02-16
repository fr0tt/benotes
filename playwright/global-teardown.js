const { request, expect } = require('@playwright/test')
const fs = require('fs')

module.exports = async (config) => {
    const { baseURL } = config.projects[0].use
    if (fs.existsSync('.env.original')) {
        fs.renameSync('.env', '.env.playwright')
        fs.renameSync('.env.original', '.env')
    }

    const requestContext = await request.newContext({
        baseURL: baseURL,
    })

    let response = await requestContext.post('/api/__e2e__/teardown')
    await expect(response.ok()).toBeTruthy()
}

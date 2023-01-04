const { request } = require('@playwright/test')
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

    await requestContext.post(`${baseURL}/__e2e__/teardown`)
}

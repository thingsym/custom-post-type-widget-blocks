import { chromium } from '@playwright/test'

async function globalSetup( config ) {
	process.env.WP_BASE_URL = 'http://localhost:8080';
	process.env.WP_USERNAME = 'admin';
	process.env.WP_PASSWORD = 'admin';

	const { baseURL } = config.projects[0].use

	const browser = await chromium.launch()

	const page = await browser.newPage()

	try {
		await page.goto( baseURL )

		await browser.close()
	} catch ( error ) {
		await browser.close()

		throw error
	}
}

export default globalSetup

/**
 * External dependencies
 */
import { defineConfig, devices } from '@playwright/test';

const config = defineConfig( {
	globalSetup: require.resolve( './tests/playwright/config/global-setup.ts' ),
  // Glob patterns or regular expressions that match test files.
	testDir: './tests/playwright',
	testMatch: '*tests/playwright/*.spec.ts',
	outputDir: './tests/playwright-report',
	workers: 1,
	use: {
		baseURL: process.env.WP_BASE_URL || 'http://localhost:8080/',
		headless: true,
		ignoreHTTPSErrors: true,
    permissions: [ 'clipboard-read' ],
		trace: 'on-first-retry', // record traces on first retry of each test
	}
} );

export default config;

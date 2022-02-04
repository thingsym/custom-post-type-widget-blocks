const config = require( '@wordpress/scripts/config/jest-e2e.config' );

module.exports = {
	...config,
	rootDir: './',
	preset: 'jest-puppeteer',
	testMatch: [
		'<rootDir>/tests/e2e/*.test.js?(x)',
		'<rootDir>/src/blocks/**/tests/e2e/*.test.js?(x)',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git/',
		'<rootDir>/node_modules/',
		'<rootDir>/vendor/',
	]
};

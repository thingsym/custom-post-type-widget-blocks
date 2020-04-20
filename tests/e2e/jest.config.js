module.exports = {
	rootDir: '../../',
	preset: '@wordpress/jest-preset-default',
	roots: [
		'<rootDir>/tests/e2e/',
	],
	testMatch: [
		'<rootDir>/tests/e2e/*.test.js?(x)',
		'<rootDir>/tests/e2e/**/*.test.js?(x)',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git/',
		'<rootDir>/node_modules/',
		'<rootDir>/vendor/',
	]
};

module.exports = {
	rootDir: '../../',
	preset: '@wordpress/jest-preset-default',
	roots: [
		'<rootDir>/tests/jest/'
	],
	testMatch: [
		'<rootDir>/tests/jest/*.test.js?(x)',
		'<rootDir>/tests/jest/**/*.test.js?(x)',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git/',
		'<rootDir>/node_modules/',
		'<rootDir>/vendor/',
	]
};

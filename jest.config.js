const config = require( '@wordpress/scripts/config/jest-unit.config.js' );

module.exports = {
	...config,
	rootDir: './',
	silent: false,
	testMatch: [
		'<rootDir>/tests/jest/*.test.js?(x)',
		'<rootDir>/src/blocks/**/jest/*.test.js?(x)',
	],
	testPathIgnorePatterns: [
		'<rootDir>/.git/',
		'<rootDir>/node_modules/',
		'<rootDir>/vendor/',
	]
};

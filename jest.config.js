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
	],
  testEnvironment: 'jsdom',
	moduleNameMapper: {
		// Force module uuid to resolve with the CJS entry point, because Jest does not support package.json.exports. See https://github.com/uuidjs/uuid/issues/451
    "uuid": require.resolve('uuid'),
  }
};

/**
 * External dependencies
 */
import moment from 'moment-timezone';
import React from 'react';
import renderer from 'react-test-renderer';
import $ from 'jquery';

// Try to configure Enzyme, but don't fail if it's not available
try {
	const Enzyme = require( 'enzyme' );
	const Adapter = require( 'enzyme-adapter-react-16' );
	Enzyme.configure( { adapter: new Adapter() } );

	global.shallow = Enzyme.shallow;
	global.render = Enzyme.render;
	global.mount = Enzyme.mount;
} catch ( e ) {
	// Enzyme not available or has dependency issues, skip it
}

global.jQuery = $;
global.$ = $;
global.wp = {
	element: React,
	api: {},
	apiRequest: () => $.Deferred(),
	components: {},
	data: {},
	blockEditor: {},
	editor: {},
	hooks: {},
};
global.renderer = renderer;
global.console = {
	error: jest.fn(),
	log: jest.fn(),
	warning: jest.fn(),
};

moment.tz.setDefault( 'UTC' );

// Mock webpack public path global for tests.
global.__webpack_public_path__ = '';

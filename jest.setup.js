// jest.setup.js

// Mock TextEncoder and TextDecoder if they are not available globally.
if (typeof TextEncoder === 'undefined') {
console.log('require(\'util\').TextEncoder', require('util').TextEncoder);
    global.TextEncoder = require('util').TextEncoder;
}

if (typeof TextDecoder === 'undefined') {
    global.TextDecoder = require('util').TextDecoder;
}

// Now, import all other dependencies.
import moment from 'moment-timezone';
import React from 'react';
import renderer from 'react-test-renderer';
import $ from 'jquery';
import Enzyme, { shallow, render, mount } from 'enzyme';
import Adapter from 'enzyme-adapter-react-16';

Enzyme.configure({ adapter: new Adapter() });

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
global.shallow = shallow;
global.render = render;
global.mount = mount;
global.renderer = renderer;
global.console = {
    error: jest.fn(),
    log: jest.fn(),
    warning: jest.fn(),
};

moment.tz.setDefault('UTC');

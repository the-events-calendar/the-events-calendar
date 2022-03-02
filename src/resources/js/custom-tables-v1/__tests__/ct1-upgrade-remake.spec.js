/**
 * @jest-environment jsdom
 */

import {
	init,
	getUpgradeBoxElement,
	ajaxGet,
	selectors,
	recursePollForReport, buildQueryString,
} from '../ct1-upgrade-remake';

const upgradeBoxId = selectors.upgradeBox.substr(1);

const mockOpen = jest.fn();
const mockSend = jest.fn();

const mockXMLHttpRequest = (mocks) => {
	window.XMLHttpRequest = jest.fn().mockImplementation(() => mocks);
};

describe('CT1 Upgrade UI', () => {
	// Replace setTimeout to control it.
	jest.useFakeTimers();
	jest.spyOn(global, 'setTimeout');
	// Setup the DOM as it should should look.
	document.body.innerHTML = `<div id="${upgradeBoxId}"></div>`;
	// Mock the localized data.
	window.tecCt1Upgrade = {
		ajaxUrl: '/admin-ajax.php',
		pollInterval: 2300,
		actions: {get_report: 'test'},
	};

	it('should correctly initialize', () => {
		init();

		expect(getUpgradeBoxElement()).toBeInstanceOf(Element);
		expect(setTimeout).toHaveBeenCalledTimes(1);
		expect(setTimeout).
				toHaveBeenLastCalledWith(recursePollForReport, 2300);
	});

	describe('buildQueryString', () => {

		it('should throw if data is not string or object',()=>{
			expect(() => {buildQueryString(['foo', 'bar']);}).toThrowError();
		});

		it('should correctly build on empty object', () => {
			const built = buildQueryString({});
			expect(built).toBe('');
		});

		it('should correctly build on one prop object', () => {
			const built = buildQueryString({action: 'do-something'});
			expect(built).toBe('?action=do-something');
		});

		it('should correctly build on many props object', () => {
			const built = buildQueryString({
				action: 'do-something',
				url: '/foo/bar?and-then=some&non-utf=é',
				méh: '<-this should be encoded',
			});
			expect(built).
					toBe(
							'?action=do-something&url=%2Ffoo%2Fbar%3Fand-then%3Dsome%26non-utf%3D%C3%A9&m%C3%A9h=%3C-this%20should%20be%20encoded');
		});

		it('should correctly encode non-encoded string', () => {
			const built = buildQueryString('léh=Düsseldorf&foo=bar');
			expect(built).toBe('?l%C3%A9h=D%C3%BCsseldorf&foo=bar');
		});
	});

	describe('ajaxGet', () => {
		it('should not send request on missing URL', () => {
			mockXMLHttpRequest({
				open: mockOpen,
				send: mockSend,
			});
			const callback = jest.fn();

			ajaxGet('', {}, callback, callback, callback);

			expect(mockOpen).not.toHaveBeenCalled();
			expect(callback).not.toHaveBeenCalled();
		});

		it('should open and send a request when provided URL', () => {
			mockXMLHttpRequest({
				open: mockOpen,
				send: mockSend,
			});
			const callback = jest.fn();

			ajaxGet('/some-url.php', {}, callback, callback, callback);

			expect(mockOpen).toHaveBeenCalledWith('GET', '/some-url.php', true);
			expect(mockSend).toHaveBeenCalled();
		});

		it('should call onSuccess on success', () => {
			const successCallback = jest.fn();
			const failureCallback = jest.fn();
			const errorCallback = jest.fn();
			mockXMLHttpRequest({
				open: mockOpen,
				send: mockSend,
				status: 200,
				response: 'hello there',
			});

			ajaxGet('/some-url.php', {}, successCallback, failureCallback,
					errorCallback);

			expect(successCallback).toHaveBeenCalledWith('hello there');
		});
	});
});
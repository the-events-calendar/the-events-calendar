/**
 * @jest-environment jsdom
 */

import {
	init,
	getUpgradeBoxElement,
	selectors,
	ajaxGet,
	onSuccess,
	onFailure,
	onError,
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
	// Replace the XMLHttpRequest built-in class to intercept requests.
	// Setup the DOM as it should should look.
	document.body.innerHTML = `<div id="${upgradeBoxId}"></div>`;
	// Mock the localized data.
	window.tecCt1Upgrade = {
		ajaxUrl: '/admin-ajax.php',
		pollInterval: 2300,
	};

	it('should correctly initialize', () => {
		init();

		expect(getUpgradeBoxElement()).toBeInstanceOf(Element);
		expect(setTimeout).toHaveBeenCalledTimes(1);
		expect(setTimeout).
				toHaveBeenLastCalledWith(ajaxGet, 2300, onSuccess, onFailure, onError);
	});

	describe('ajaxGet', () => {
		it('should not send request on missing URL', () => {
			mockXMLHttpRequest({
				open: mockOpen,
				send: mockSend,
			});
			const callback = jest.fn();

			ajaxGet('', callback, callback, callback);

			expect(mockOpen).not.toHaveBeenCalled();
			expect(callback).not.toHaveBeenCalled();
		});

		it('should open and send a request when provided URL', () => {
			mockXMLHttpRequest({
				open: mockOpen,
				send: mockSend,
			});
			const callback = jest.fn();

			ajaxGet('/some-url.php', callback, callback, callback);

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

			ajaxGet('/some-url.php', successCallback, failureCallback, errorCallback);

			expect(successCallback).toHaveBeenCalledWith('hello there');
		});
	});
});
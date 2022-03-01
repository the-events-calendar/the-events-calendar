/**
 * @jest-environment jsdom
 */

import {
	init,
	getUpgradeBoxElement,
	selectors,
	recursePollForReport,
} from '../ct1-upgrade-remake';

const upgradeBoxId = selectors.upgradeBox.substr(1);

const open = jest.fn();
const send = jest.fn();
const mockXMLHttpRequest = () => ({
	open: open,
	send: send,
});

describe('CT1 Upgrade UI', () => {
	// Replace setTimeout to control it.
	jest.useFakeTimers();
	jest.spyOn(global, 'setTimeout');
	// Replace the XMLHttpRequest built-in class to intercept requests.
	window.XMLHttpRequest = jest.fn().mockImplementation(mockXMLHttpRequest);
	// Setup the DOM as it should should look.
	document.body.innerHTML = `<div id="${upgradeBoxId}"></div>`;
	// Mock the localized data.
	window.tecCt1Upgrade = {
		ajaxUrl: '/admin-ajax.php',
		pollInterval: 2300,
		actions:{get_report:'test'}
	};

	it('should correctly initialize', () => {
		init();

		expect(getUpgradeBoxElement()).toBeInstanceOf(Element);
		expect(setTimeout).toHaveBeenCalledTimes(1);
		expect(setTimeout).
				toHaveBeenLastCalledWith(recursePollForReport, 2300);
	});
});
let upgradeBoxElement = null;
let localizedData = null;
let ajaxUrl = null;
let pollInterval = 5000;
let pollTimeoutId = null;
let currentViewState = {};

export const selectors = {
	// @todo review these and remove the ones we do not need anymore.
	v2DisableDialog: '#tec-ct1-migration__v2-disable-dialog',
	v2Enabled: 'input[name="views_v2_enabled"]',
	alertOkButton: '.tec-ct1-upgrade-__modal-container--v2-disable-dialog .tribe-alert__continue',
	alertCloseButton: '.tec-ct1-upgrade-__modal-container--v2-disable-dialog .tribe-modal__close-button',
	rootReportNode: '.tec-ct1-upgrade__row', // Used to constrain some selectors
	barsSelector: '.tec-ct1-upgrade-bar .bar',
	barsProgressSelector: '.tec-ct1-upgrade-bar .progress',
	upgradeBox: '#tec-ct1-upgrade-box',
};

export const buildDataString = (data = {}) => {
	return Object.keys(data).map(
			function(k) {
				return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
			},
	).join('&');
};

export const ajaxGet = (url, data = {}, onSuccess, onFailure, onError) => {
	if (!url) {
		return;
	}

	const params = typeof data == 'string' ? data : buildDataString(data);
	const compiledUrl = params ? url + '?' + params : url;

	const request = new XMLHttpRequest();
	request.open('GET', compiledUrl, true);

	request.onload = function () {
		if (this.status >= 200 && this.status < 400) {
			onSuccess && onSuccess(JSON.parse(this.response));
		} else {
			onFailure && onFailure(this.response);
		}
	};

	request.onerror = function () {
		onError && onError();
	};

	request.send();
};

export const getUpgradeBoxElement = (refresh) => {
	if (refresh || !(upgradeBoxElement instanceof Element)) {
		upgradeBoxElement = document.getElementById(selectors.upgradeBox.substr(1));
	}

	return upgradeBoxElement;
};

export const onSuccess = () => {

};

export const onFailure = () => {

};

export const onError = () => {

};

export const recursePollForReport = () => {
	syncReportData(
		pollForReport
	);
};

export const pollForReport = () => {
	// Start polling
	pollTimeoutId = setTimeout(recursePollForReport, pollInterval);
};

export const handleReportData = function (data) {
	const {nodes, key, html} = data;

	// Write our HTML if we are new
	if (!currentViewState.key || currentViewState.key !== key) {
		upgradeBoxElement.innerHTML = html;
	}
	// Iterate on nodes
	nodes.forEach(
		(node) => {
			if (isNodeDiff(node.key, node.hash)) {
				// Write new content
				let element;
				if (element = document.querySelector(node.target)) {
					element.innerHTML = node.html;
				}
			}
		}
	)
	// Store changes locally for next request
	currentViewState = data;
}
export const isNodeDiff = (searchKey, searchHash) => {
	const {nodes} = currentViewState;
	if (!nodes) {
		return true;
	}
	const node = nodes.find(
		({key}) => key === searchKey
	);

	if (!node) {
		return true;
	}

	return node.hash !== searchHash;
}

/**
 * Fetches the report data, and delegates to the handlers
 *
 * @param successCallback
 */
export const syncReportData = function (successCallback) {
	getReport(
		function (response) {
			handleReportData(response);
			if (successCallback) {
				successCallback(response);
			}
		}
	);
}

export const getReport = function (successCallback) {
	ajaxGet(
		tecCt1Upgrade.ajaxUrl,
		{action: tecCt1Upgrade.actions.get_report},
		successCallback
	)
}

export const init = () => {
	localizedData = window.tecCt1Upgrade;

	if (!localizedData) {
		return;
	}

	upgradeBoxElement = getUpgradeBoxElement(true);
	ajaxUrl = localizedData.ajaxUrl;
	pollInterval = localizedData.pollInterval || pollInterval;

	if (pollInterval === 0) {
		return;
	}

	// Get initial report data immediately
	syncReportData();

	// Initialize our report - heartbeat polling.
	pollForReport();
};

// On DOM ready, init.
if (document.readyState !== 'loading') {
	init();
} else {
	document.addEventListener('DOMContentLoaded', init);
}
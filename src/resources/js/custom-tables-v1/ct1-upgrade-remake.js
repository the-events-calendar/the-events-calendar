let upgradeBoxElement = null;
let localizedData = null;
let ajaxUrl = null;
let pollInterval = 5000;

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

export const ajaxGet = (url, onSuccess, onFailure, onError) => {
	if (!url) {
		return;
	}
	const request = new XMLHttpRequest();
	request.open('GET', url, true);

	request.onload = function() {
		if (this.status >= 200 && this.status < 400) {
			onSuccess && onSuccess(this.response);
		}
		else {
			onFailure && onFailure(this.response);
		}
	};

	request.onerror = function() {
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

const pollForReport = () => {
	setTimeout(ajaxGet, pollInterval, onSuccess, onFailure, onError);
};

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

	// Initialize our report - heartbeat polling.
	pollForReport();
};

// On DOM ready, init.
if (document.readyState !== 'loading') {
	init();
}
else {
	document.addEventListener('DOMContentLoaded', init);
}
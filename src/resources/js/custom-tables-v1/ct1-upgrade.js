let upgradeBoxElement = null;
let localizedData = null;
let ajaxUrl = null;
let pollInterval = 5000;
let pollTimeoutId = null;
let currentViewState = {
	poll: true,
};

export const selectors = {
	upgradeBox: '#tec-ct1-upgrade-dynamic',
	startPreviewButton: '.tec-ct1-upgrade-start-migration-preview',
	startMigrationButton: '.tec-ct1-upgrade-start-migration',
	cancelMigrationButton: '.tec-ct1-upgrade-cancel-migration',
};

/**
 * Builds a URL-encoded query string from an object or string.
 *
 * @param {Object|string} data The data object, or string, to convert to query
 * 												     string.
 *
 * @returns {string} The data converted to a URL-encoded query string, including
 * 									 the leading `?`.
 *
 * @throws {Error} If the data is not a string or an object.
 */
export const buildQueryString = (data = {}) => {
	if (!(
			(data instanceof Object && !Array.isArray(data))
			|| typeof data === 'string')
	) {
		throw new Error('data must be an object or a string');
	}

	if ('string' === typeof data) {
		const extractedData = {};
		data.split('&').map((keyAndValue) => {
			const [key, value] = keyAndValue.split('=', 2);
			extractedData[key] = value;
		});
		data = extractedData;
	}

	const queryString =  Object.keys(data).map(
			function(k) {
				return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
			},
	).join('&');

	return queryString ? '?' + queryString : '';
};

/**
 * Sends an AJAX GET request to the specified URL.
 *
 * @since TBD
 *
 * @param {string} url The URL to send the GET request to.
 * @param {string|Object|null} data The data object or string, it will be
 * 																  URL-encoded.
 * @param {function|null} onSuccess The function that will be called on a
 * 																	successful  request, it will be passed the
 * 																	JSON parsed response data.
 * @param {function|null} onFailure The function that will be called if the
 * 													        request fails, it will be passed the raw
 * 													        response body.
 * @param {function|null} onError   The function that will be called if there's
 * 																  an error with the request or if there's an
 * 																  error	parsing the response JSON.
 */
export const ajaxGet = (url, data = {}, onSuccess, onFailure, onError) => {
	if (!url) {
		return;
	}

	const compiledUrl = url + buildQueryString(data);
	const request = new XMLHttpRequest();
	request.open('GET', compiledUrl, true);

	request.onreadystatechange = function() {
		// In local files, status is 0 upon success in Mozilla Firefox
		if (request.readyState === XMLHttpRequest.DONE) {
			const status = request.status;
			if (status === 0 || (status >= 200 && status < 400)) {
				try {
					onSuccess && onSuccess(JSON.parse(this.response));
				} catch (e) {
					onError && onError(this.response);
				}
			} else {
				onFailure && onFailure(this.response);
			}
		}
	};

	request.onerror = function() {
		onError && onError();
	};

	request.send();
};

/**
 * Return the main node that wraps our dynamic content.
 *
 * @since TBD
 *
 * @param {boolean} refresh Fetch from cache of the node or reselect it.
 *
 */
export const getUpgradeBoxElement = () => {
	return document.getElementById(selectors.upgradeBox.substr(1));
};

export const onSuccess = () => {

};

export const onFailure = () => {

};

export const onError = () => {

};

/**
 * Recursively sync and poll report data.
 *
 * @since TBD
 */
export const recursePollForReport = () => {
	syncReportData(
			pollForReport,
	);
};

export const shouldPoll = () => {
	return currentViewState.poll;
};

/**
 * Start the recursive poll for report changes.
 *
 * @since TBD
 */
export const pollForReport = () => {
	if (!shouldPoll()) {
		return;
	}
	// Start polling.
	pollTimeoutId = setTimeout(recursePollForReport, pollInterval);
};

/**
 * Handles the response from the report request.
 *
 * @since TBD
 *
 * @param {object} data The response object with the compiled report data.
 */
export const handleReportData = function(data) {
	const {nodes, key, html} = data;

	// Write our HTML if we are new.
	if (!currentViewState.key || currentViewState.key !== key) {
		getUpgradeBoxElement().innerHTML = html;
		bindNodes(key);
	}
	// Iterate on nodes.
	nodes.forEach(
			(node) => {
				if (isNodeDiff(node.key, node.hash)) {
					// Write new content.
					let element = document.querySelector(node.target);
					if (element) {
						element.innerHTML = node.html;
						bindNodes(node.key);
					}
				}
			},
	);
	// Store changes locally for next request.
	currentViewState = data;
};

/**
 * Binds the dynamic nodes with their listeners.
 *
 * @since TBD
 *
 * @param {string} key The node key.
 */
export const bindNodes = (key) => {
	let element;

	// Start preview button.
	element = document.querySelector(selectors.startPreviewButton);
	if (element) {
		element.addEventListener('click', handleStartMigration(true));
	}

	// Start migration button.
	element = document.querySelector(selectors.startMigrationButton);
	if (element) {
		element.addEventListener('click', handleStartMigration(false));
	}

	// Revert migration button.
	element = document.querySelector(selectors.cancelMigrationButton);
	if (element) {
		element.addEventListener('click', handleCancelMigration);
	}
}

/**
 * Handle the cancel migration action.
 *
 * @since TBD
 *
 * @param e
 */
export const handleCancelMigration = (e) => {
	e.preventDefault();
	e.target.setAttribute('disabled', 'disabled');
	// Stop our render check momentarily.
	// We will have a new state immediately after our cancel migration finishes.
	cancelReportPoll();
	ajaxGet(
		tecCt1Upgrade.ajaxUrl,
		{
			action: tecCt1Upgrade.actions.cancelMigration,
			_ajax_nonce: tecCt1Upgrade.nonce,
		},
		(response) => {
			// Sync + Restart polling, now we will have a new view.
			handleReportData(response);
			pollForReport();
		}
	);
}

/**
 * Handle the start migration action.
 *
 * @since TBD
 *
 * @param {boolean} isPreview Flag to denote if we are doing a dry run or a
 *     real migration.
 *
 * @returns {(function(*): void)|*}
 */
export const handleStartMigration = (isPreview) => (e) => {
	e.preventDefault();
	e.target.setAttribute('disabled', 'disabled');
	// Stop our render check momentarily.
	// We will have a new state immediately after our start migration finishes.
	cancelReportPoll();
	ajaxGet(
		tecCt1Upgrade.ajaxUrl,
		{
			action: tecCt1Upgrade.actions.startMigration,
			tec_events_custom_tables_v1_migration_dry_run: isPreview ? 1 : 0,
			_ajax_nonce: tecCt1Upgrade.nonce,
		},
		(response) => {
			// Sync + Restart polling, now we will have a new view.
			handleReportData(response);
			pollForReport();
		}
	);
}
/**
 * Cancel our report polling.
 *
 * @since TBD
 */
export const cancelReportPoll = () => {
	clearTimeout(pollTimeoutId);
}

/**
 * Checks if the node changed in the poll intervals.
 *
 * @since TBD
 *
 * @param {string} searchKey The node key to reference if changes.
 * @param {string} searchHash The hash that might change for a particular node
 *     key.
 *
 * @returns {boolean} True if the node changed, false if not.
 */
export const isNodeDiff = (searchKey, searchHash) => {
	const {nodes} = currentViewState;
	if (!nodes) {
		return true;
	}
	const node = nodes.find(
			({key}) => key === searchKey,
	);

	if (!node) {
		return true;
	}

	return node.hash !== searchHash;
};

/**
 * Fetches the report data, and delegates to the handlers.
 *
 * @since TBD
 *
 * @param {function|null} successCallback Callback fired on success.
 */
export const syncReportData = function(successCallback = null) {
	getReport(
			function(response) {
				handleReportData(response);
				if (successCallback) {
					successCallback(response);
				}
			},
	);
};

/**
 * Get the report data from the backend.
 *
 * @since TBD
 *
 * @param {function} successCallback Callback fired on success.
 */
export const getReport = (successCallback) => {
	ajaxGet(
			tecCt1Upgrade.ajaxUrl,
			{
				action: tecCt1Upgrade.actions.getReport,
				_ajax_nonce: tecCt1Upgrade.nonce,
			},
			successCallback,
	);
};

/**
 * Kick off the CT1 upgrade loop and node updates.
 *
 * @since TBD
 */
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

	// Get initial report data immediately, then start polling.
	syncReportData(pollForReport);
};

// On DOM ready, init.
if (document.readyState !== 'loading') {
	init();
}
else {
	document.addEventListener('DOMContentLoaded', init);
}
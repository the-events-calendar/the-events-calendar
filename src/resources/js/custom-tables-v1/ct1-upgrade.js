let upgradeBoxElement = null;
let localizedData = null;
let ajaxUrl = null;
let pollInterval = 5000;
let pollTimeoutId = null;
let currentViewState = {
	poll: true,
};
let pastCurrentPage;
let upcomingCurrentPage;

export const selectors = {
	upgradeBox: '#tec-ct1-upgrade-dynamic',
	startPreviewButton: '.tec-ct1-upgrade-start-migration-preview',
	startMigrationButton: '.tec-ct1-upgrade-start-migration',
	cancelMigrationButton: '.tec-ct1-upgrade-cancel-migration',
	revertMigrationButton: '.tec-ct1-upgrade-revert-migration',
	paginateButton: '[data-events-paginate]'
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

	const queryString = Object.keys(data).map(
		function (k) {
			return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]);
		},
	).join('&');

	return queryString ? '?' + queryString : '';
};

/**
 * Sends an AJAX GET request to the specified URL.
 *
 * @since 6.0.0
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

	request.onreadystatechange = function () {
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

	request.onerror = function () {
		onError && onError();
	};

	request.send();
};

/**
 * Return the main node that wraps our dynamic content.
 *
 * @since 6.0.0
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
 * @since 6.0.0
 */
export const recursePollForReport = () => {
	syncReportData(
		pollForReport,
	);
};

export const shouldPoll = () => {
	return currentViewState.poll || tecCt1Upgrade.forcePolling;
};

/**
 * Start the recursive poll for report changes.
 *
 * @since 6.0.0
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
 * @since 6.0.0
 *
 * @param {object} data The response object with the compiled report data.
 */
export const handleReportData = function (data) {
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
					if (node.prepend) {
						element.innerHTML = node.html + element.innerHTML;
					} else if (node.append) {
						element.innerHTML = element.innerHTML + node.html;
					} else {
						element.innerHTML = node.html;
					}
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
 * @since 6.0.0
 *
 * @param {string} key The node key.
 */
export const bindNodes = (key) => {
	let element;

	// Start preview button.
	element = document.querySelectorAll(selectors.startPreviewButton);
	if (element) {
		element.forEach(function (node) {
			node.removeEventListener('click', handleStartMigrationWithPreview);
			node.addEventListener('click', handleStartMigrationWithPreview);
		});
	}

	// Start migration button.
	element = document.querySelectorAll(selectors.startMigrationButton);
	if (element) {
		element.forEach(function (node) {
			node.removeEventListener('click', handleStartMigration);
			node.addEventListener('click', handleStartMigration);
		});
	}

	// Cancel migration button.
	element = document.querySelectorAll(selectors.cancelMigrationButton);
	if (element) {
		element.forEach(function (node) {
			node.removeEventListener('click', handleCancelMigration);
			node.addEventListener('click', handleCancelMigration);
		});
	}

	// Revert migration button.
	element = document.querySelectorAll(selectors.revertMigrationButton);
	if (element) {
		element.forEach(function (node) {
			node.removeEventListener('click', handleRevertMigration);
			node.addEventListener('click', handleRevertMigration);
		});
	}

	// Paginate events
	element = document.querySelectorAll(selectors.paginateButton);
	if (element) {
		element.forEach(function (node) {
			node.removeEventListener('click', handlePaginateClick(node));
			node.addEventListener('click', handlePaginateClick(node));
		});
	}
}

const handlePaginateClick = (node) => (e) => {
	e.preventDefault();
	const isUpcoming = !!node.dataset.eventsPaginateUpcoming;
	const category = node.dataset.eventsPaginateCategory;
	const defaultPage = node.dataset.eventsPaginateStartPage;

	if (isUpcoming) {
		if (!upcomingCurrentPage) {
			upcomingCurrentPage = defaultPage;
		}
	} else {
		if (!pastCurrentPage) {
			pastCurrentPage = defaultPage;
		}
	}
	const page = isUpcoming ? upcomingCurrentPage++ : pastCurrentPage++;

	ajaxGet(
		tecCt1Upgrade.ajaxUrl,
		{
			action: tecCt1Upgrade.actions.paginateEvents,
			page: page,
			upcoming: isUpcoming ? 1 : 0,
			report_category: category,
			_ajax_nonce: tecCt1Upgrade.nonce,
		},
		({html, append, prepend, has_more}) => {
			const element = document.querySelector(`.tec-ct1-upgrade-events-category-${category}`);
			if (prepend) {
				element.innerHTML = html + element.innerHTML;
			} else if (append) {
				element.innerHTML = element.innerHTML + html;
			} else {
				element.innerHTML = html;
			}
			if (!has_more) {
				let element = document.querySelector('.tec-ct1-upgrade-migration-pagination-separator');
				if (element) {
					element.remove();
				}
				e.target.remove();
			}
		}
	);
}

/**
 * Handle the cancel migration action.
 *
 * @since 6.0.0
 *
 * @param {Event} e
 */
export const handleCancelMigration = (e) => {
	e.preventDefault();
	if (confirm(tecCt1Upgrade.text_dictionary.confirm_cancel_migration)) {
		e.target.setAttribute('disabled', 'disabled');
		e.target.removeEventListener('click', handleCancelMigration);

		// Stop our render check momentarily.
		// We will have a new state immediately after our cancel migration finishes.
		undoMigration(tecCt1Upgrade.actions.cancelMigration);
	}
}

/**
 * Handle the revert migration action.
 *
 * @since 6.0.0
 *
 * @param {Event} e
 */
export const handleRevertMigration = (e) => {
	e.preventDefault();
	if (confirm(tecCt1Upgrade.text_dictionary.confirm_revert_migration)) {
		e.target.setAttribute('disabled', 'disabled');
		e.target.removeEventListener('click', handleRevertMigration);

		// Stop our render check momentarily.
		// We will have a new state immediately after our cancel migration finishes.
		undoMigration(tecCt1Upgrade.actions.revertMigration);
	}
}

/**
 * Handles the AJAX call to cancel/revert.
 */
export const undoMigration = (action) => {
	cancelReportPoll();
	ajaxGet(
		tecCt1Upgrade.ajaxUrl,
		{
			action: action,
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
 * Handle the start migration preview click event.
 *
 * @since 6.0.0
 *
 * @param {Event} e
 */
export const handleStartMigrationWithPreview = (e) => {
	e.preventDefault();
	e.target.setAttribute('disabled', 'disabled');
	e.target.removeEventListener('click', handleStartMigrationWithPreview);
	startMigration(true);
}

/**
 * Handle the start migration click event.
 *
 * @since 6.0.0
 *
 * @param {Event}
 */
export const handleStartMigration = (e) => {
	e.preventDefault();
	const message = tecCt1Upgrade.text_dictionary.migration_in_progress_paragraph + ' '
		+ tecCt1Upgrade.text_dictionary.migration_prompt_plugin_state_addendum;
	// @todo Move these confirm boxes to the preferred TEC dialog library.
	if (confirm(message)) {
		e.target.setAttribute('disabled', 'disabled');
		e.target.removeEventListener('click', handleStartMigration);
		startMigration(false);
	}
}

/**
 * Will start either a preview or migration, sending a request to the backend to queue workers.
 *
 * @since 6.0.0
 *
 * @param {boolean} isPreview Flag to denote if we are doing a dry run or a
 *     real migration.
 */
export const startMigration = (isPreview) => {
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
 * @since 6.0.0
 */
export const cancelReportPoll = () => {
	clearTimeout(pollTimeoutId);
}

/**
 * Checks if the node changed in the poll intervals.
 *
 * @since 6.0.0
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
 * @since 6.0.0
 *
 * @param {function|null} successCallback Callback fired on success.
 */
export const syncReportData = function (successCallback = null) {
	getReport(
		function (response) {
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
 * @since 6.0.0
 *
 * @param {function} successCallback Callback fired on success.
 */
export const getReport = (successCallback) => {
	var queryArgs = {
		action: tecCt1Upgrade.actions.getReport,
		_ajax_nonce: tecCt1Upgrade.nonce,
	};
	if (tecCt1Upgrade.isMaintenanceMode) {
		queryArgs.is_maintenance_mode = '1';
	}
	ajaxGet(
		tecCt1Upgrade.ajaxUrl,
		queryArgs,
		successCallback,
	);
};

/**
 * Kick off the CT1 upgrade loop and node updates.
 *
 * @since 6.0.0
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
} else {
	document.addEventListener('DOMContentLoaded', init);
}
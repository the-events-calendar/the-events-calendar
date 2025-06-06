/* global jQuery, YoastSEO */
var pluginName = "eventsVariablePlugin";
var ReplaceVar = window.YoastReplaceVarPlugin && window.YoastReplaceVarPlugin.ReplaceVar;
var placeholders = {};
var config = window.tecYoastEvents || {};

var modifiableFields = [
    "content",
    "title",
    "snippet_title",
    "snippet_meta",
    "primary_category",
    "data_page_title",
    "data_meta_desc",
];

var replaceVarPluginAvailable = function() {
    if (typeof ReplaceVar === "undefined") {
        if (config.debug) {
            console.log("Events replace variables in the Snippet Window requires Yoast SEO >= 5.3.");
        }
        return false;
    }
    return true;
};

/**
 * Gets event start date
 *
 * @returns {string} Event start date
 */
function getEventStartDate() {
    return jQuery("#EventStartDate").val() || '';
}

/**
 * Gets event end date
 *
 * @returns {string} Event end date
 */
function getEventEndDate() {
    return jQuery("#EventEndDate").val() || '';
}

/**
 * Gets venue title
 *
 * @returns {string} Venue title
 */
function getVenueTitle() {
    return jQuery("#VenueTitle").val() || '';
}

/**
 * Gets venue city
 *
 * @returns {string} Venue city
 */
function getVenueCity() {
    return jQuery("#VenueCity").val() || '';
}

/**
 * Gets venue state
 *
 * @returns {string} Venue state
 */
function getVenueState() {
    return jQuery("#VenueState").val() || '';
}

/**
 * Gets organizer title
 *
 * @returns {string} Organizer title
 */
function getOrganizerTitle() {
    return jQuery("#OrganizerTitle").val() || '';
}

/**
 * Variable replacement plugin for Events.
 *
 * @returns {void}
 */
var YoastEventsReplaceVarPlugin = function() {
    if (!replaceVarPluginAvailable()) {
        return;
    }

    this._app = YoastSEO.app;
    this._app.registerPlugin(pluginName, {status: "ready"});
    this._store = YoastSEO.store;

    this.registerReplacements();
    this.registerModifications(this._app);
    this.registerEvents();
};

/**
 * Register the events that might have influence for the replace vars.
 *
 * @returns {void}
 */
YoastEventsReplaceVarPlugin.prototype.registerEvents = function() {
    // Watch for changes in event fields
    jQuery(document).on("change", "#EventStartDate, #EventEndDate, #VenueTitle, #VenueCity, #VenueState, #OrganizerTitle",
        this.declareReloaded.bind(this)
    );
};

/**
 * Registers all the placeholders and their replacements.
 *
 * @returns {void}
 */
YoastEventsReplaceVarPlugin.prototype.registerReplacements = function() {
    this.addReplacement(new ReplaceVar("%%event_start_date%%", "event_start_date"));
    this.addReplacement(new ReplaceVar("%%event_end_date%%", "event_end_date"));
    this.addReplacement(new ReplaceVar("%%venue_title%%", "venue_title"));
    this.addReplacement(new ReplaceVar("%%venue_city%%", "venue_city"));
    this.addReplacement(new ReplaceVar("%%venue_state%%", "venue_state"));
    this.addReplacement(new ReplaceVar("%%organizer_title%%", "organizer_title"));
};

/**
 * Registers the modifications for the plugin on initial load.
 *
 * @param {app} app The app object.
 *
 * @returns {void}
 */
YoastEventsReplaceVarPlugin.prototype.registerModifications = function(app) {
    var callback = this.replaceVariables.bind(this);

    for (var i = 0; i < modifiableFields.length; i++) {
        app.registerModification(modifiableFields[i], callback, pluginName, 10);
    }
};

/**
 * Runs the different replacements on the data-string.
 *
 * @param {string} data The data that needs its placeholders replaced.
 *
 * @returns {string} The data with all its placeholders replaced by actual values.
 */
YoastEventsReplaceVarPlugin.prototype.replaceVariables = function(data) {
    if (typeof data !== "undefined") {
        data = data.replace(/%%event_start_date%%/g, getEventStartDate());
        data = data.replace(/%%event_end_date%%/g, getEventEndDate());
        data = data.replace(/%%venue_title%%/g, getVenueTitle());
        data = data.replace(/%%venue_city%%/g, getVenueCity());
        data = data.replace(/%%venue_state%%/g, getVenueState());
        data = data.replace(/%%organizer_title%%/g, getOrganizerTitle());

        data = this.replacePlaceholders(data);
    }

    return data;
};

/**
 * Adds a replacement object to be used when replacing placeholders.
 *
 * @param {ReplaceVar} replacement The replacement to add to the placeholders.
 *
 * @returns {void}
 */
YoastEventsReplaceVarPlugin.prototype.addReplacement = function(replacement) {
    placeholders[replacement.placeholder] = replacement;
    this._store.dispatch({
        type: "SNIPPET_EDITOR_UPDATE_REPLACEMENT_VARIABLE",
        name: replacement.placeholder.replace(/^%%|%%$/g, ""),
        value: replacement.placeholder,
    });
};

/**
 * Reloads the app to apply possibly made changes in the content.
 *
 * @returns {void}
 */
YoastEventsReplaceVarPlugin.prototype.declareReloaded = function() {
    this._app.pluginReloaded(pluginName);
    this._store.dispatch({type: "SNIPPET_EDITOR_REFRESH"});
};

/**
 * Replaces placeholder variables with their replacement value.
 *
 * @param {string} text The text to have its placeholders replaced.
 *
 * @returns {string} The text in which the placeholders have been replaced.
 */
YoastEventsReplaceVarPlugin.prototype.replacePlaceholders = function(text) {
    for (var i = 0; i < placeholders.length; i++) {
        var replaceVar = placeholders[i];
        text = text.replace(
            new RegExp(replaceVar.getPlaceholder(true), "g"),
            replaceVar.replacement
        );
    }
    return text;
};

/**
 * Initializes the Events ReplaceVars plugin.
 *
 * @returns {void}
 */
function initializeEventsReplacevarPlugin() {
    // When YoastSEO is available, just instantiate the plugin.
    if (typeof YoastSEO !== "undefined" && typeof YoastSEO.app !== "undefined") {
        new YoastEventsReplaceVarPlugin(); // eslint-disable-line no-new
        return;
    }

    // Otherwise, add an event that will be executed when YoastSEO will be available.
    jQuery(window).on(
        "YoastSEO:ready",
        function() {
            new YoastEventsReplaceVarPlugin(); // eslint-disable-line no-new
        }
    );
}

initializeEventsReplacevarPlugin();

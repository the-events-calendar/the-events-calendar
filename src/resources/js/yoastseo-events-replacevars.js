/* global jQuery, YoastSEO */

class EventsReplaceVarPlugin {
    static PLUGIN_NAME = 'eventsVariablePlugin';
    static MODIFIABLE_FIELDS = [
        'content',
        'title',
        'snippet_title',
        'snippet_meta',
        'primary_category',
        'data_page_title',
        'data_meta_desc',
    ];

    constructor() {
        if (!this.isReplaceVarPluginAvailable()) {
            return;
        }

        this.app = YoastSEO.app;
        this.store = YoastSEO.store;
        this.placeholders = new Map();

        this.app.registerPlugin(EventsReplaceVarPlugin.PLUGIN_NAME, { status: 'ready' });

        this.registerReplacements();
        this.registerModifications();
        this.registerEvents();
    }

    isReplaceVarPluginAvailable() {
        const ReplaceVar = window.YoastReplaceVarPlugin?.ReplaceVar;
        if (!ReplaceVar) {
            if (window.tecYoastEvents?.debug) {
                console.log('Events replace variables in the Snippet Window requires Yoast SEO >= 5.3.');
            }
            return false;
        }
        this.ReplaceVar = ReplaceVar;
        return true;
    }

    registerEvents() {
        const eventFields = [
            '#EventStartDate',
            '#EventEndDate',
            '[name="venue[City]"]',
            '[name="venue[State]"]',
            '[name="organizer[Organizer]"]'
        ].join(', ');

        jQuery(document).on('change', eventFields, () => this.declareReloaded());
    }

    registerReplacements() {
        const replacements = [
            { name: 'event_start_date', getter: () => this.getEventStartDate() },
            { name: 'event_end_date', getter: () => this.getEventEndDate() },
            { name: 'venue_title', getter: () => this.getVenueTitle() },
            { name: 'venue_city', getter: () => this.getVenueCity() },
            { name: 'venue_state', getter: () => this.getVenueState() },
            { name: 'organizer_title', getter: () => this.getOrganizerTitle() }
        ];

        replacements.forEach(({ name, getter }) => {
            const placeholder = `%%${name}%%`;
            const replacement = new this.ReplaceVar(placeholder, name);
            this.placeholders.set(placeholder, { replacement, getter });

            this.store.dispatch({
                type: 'SNIPPET_EDITOR_UPDATE_REPLACEMENT_VARIABLE',
                name,
                value: placeholder
            });
        });
    }

    registerModifications() {
        const callback = this.replaceVariables.bind(this);
        EventsReplaceVarPlugin.MODIFIABLE_FIELDS.forEach(field => {
            this.app.registerModification(field, callback, EventsReplaceVarPlugin.PLUGIN_NAME, 10);
        });
    }

    replaceVariables(data) {
        if (typeof data === 'undefined') {
            return data;
        }

        let result = data;
        for (const [placeholder, { getter }] of this.placeholders) {
            const value = getter();
            result = result.replace(new RegExp(placeholder, 'g'), value);
        }

        return result;
    }

    declareReloaded() {
        this.app.pluginReloaded(EventsReplaceVarPlugin.PLUGIN_NAME);
        this.store.dispatch({ type: 'SNIPPET_EDITOR_REFRESH' });
    }

    // Event data getters
    getEventStartDate() {
        return jQuery('#EventStartDate').val() || '';
    }

    getEventEndDate() {
        return jQuery('#EventEndDate').val() || '';
    }

    getVenueTitle() {
        // Get the selected venue name from the dropdown
        const $venueSelect = jQuery('select[name="venue[VenueID][]"]');
        if ($venueSelect.length) {
            return $venueSelect.find('option:selected').text().trim() || '';
        }
        // Fallback: try the input (if exists)
        return jQuery('input[name="venue[Venue][]"]').val() || '';
    }

    getVenueCity() {
        return jQuery('[name="venue[City]"]').val() || '';
    }

    getVenueState() {
        return jQuery('[name="venue[State]"]').val() || '';
    }

    getOrganizerTitle() {
        // Get the selected organizer name from the dropdown
        const $orgSelect = jQuery('select[name="organizer[OrganizerID][]"]');
        if ($orgSelect.length) {
            return $orgSelect.find('option:selected').text().trim() || '';
        }
        // Fallback: try the input (if exists)
        return jQuery('input[name="organizer[Organizer][]"]').val() || '';
    }
}

function initializeEventsReplacevarPlugin() {
    if (typeof YoastSEO !== 'undefined' && typeof YoastSEO.app !== 'undefined') {
        new EventsReplaceVarPlugin();
        return;
    }

    jQuery(window).on('YoastSEO:ready', () => {
        new EventsReplaceVarPlugin();
    });
}

initializeEventsReplacevarPlugin();

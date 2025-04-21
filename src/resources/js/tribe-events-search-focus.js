/**
 * Handles focus management for The Events Calendar search results.
 *
 * @since TBD
 */
(function($) {
    'use strict';

    /**
     * Focuses on the appropriate container after search is performed.
     * Prioritizes the events list container if it has events, otherwise focuses
     * on the messages container.
     *
     * This function runs when the container is replaced after an AJAX request,
     * which happens after search form submission.
     * 
     * @since TBD
     * 
     * @param {Event} event The dispatched event.
     * @param {Object} detail The event details containing the container.
     */
    function focusOnSearchResults(event) {
        const $container = $(event.detail);
        
        // Find the events list container if it exists and has events.
        const $eventsContainer = $container.find('.tribe-events-calendar-list');
        const eventRows = $eventsContainer.find('.tribe-events-calendar-list__event-row');
        const hasEvents = eventRows.length > 0;
        
        // Find the messages container if it exists (which appears when no events are found).
        const $messagesContainer = $container.find('.tribe-events-c-messages__message-list');
        
        // Determine which element to focus on.
        if (hasEvents && $eventsContainer.length) {
            // Focus on the events list container if it has events.
            $eventsContainer.get(0).focus();
        } else if ($messagesContainer.length) {
            // Otherwise focus on the messages container (no events found).
            $messagesContainer.get(0).focus();
        }
    }

    /**
     * Setup the event listeners.
     *
     * @since TBD
     */
    function setup() {
        // Listen for the event triggered after the container is replaced.
        document.addEventListener('containerReplaceAfter.tribeEvents', focusOnSearchResults);
    }

    $(document).ready(setup);

})(jQuery); 
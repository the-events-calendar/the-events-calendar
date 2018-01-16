(function () {
	const { Component } = wp.element;
	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;

	class EventDetails extends Component {
		render() {
			return React.createElement(
				'p',
				{ className: 'asdoa' },
				'Development On WP'
			);
		}
	}

	console.log(wp.element, wp.blocks);
	//

	var blockName = 'tribe/event-details';
	var blockConfiguration = {
		title: __('Event Details', 'the-events-calendar'),
		description: __('Configuration for the Event', 'the-events-calendar'),
		icon: 'calendar',
		category: 'layout',
		keywords: ['event', 'the-events-calendar', 'tribe'],

		useOnce: true,

		attributes: {},

		// The "edit" property must be a valid function.
		edit: function (props) {
			return React.createElement(EventDetails, null);
		},

		// The "save" property must be specified and must be a valid function.
		save: function (props) {
			return null;
		}
	};

	// Actually Register the block on Editor
	registerBlockType(blockName, blockConfiguration);
})();

( function( $ ) {
	const { Component } = wp.element;
	const { Placeholder, Spinner } = wp.components;
	const { __ } = wp.i18n;
	const { registerBlockType } = wp.blocks;


	var requestAjax = function ( action, data ) {
		var request = {
			dataType: 'json',
			method: 'POST',
			url: window.ajaxurl + '?action=' + encodeURIComponent( action ),
			async: false,
			data: data
		};

		return JSON.parse( $.ajax( request ).responseText );
	};


	class EventDetails extends Component {
		componentDidMount() {
			this.request();
		}

		componentDidUpdate( previousProps ) {
			const { modelName, query } = this.props;
			if (
				modelName !== previousProps.modelName ||
				! _.isEqual( query, previousProps.query )
			) {
				this.request();
			}
		}

		request() {

		}

		render() {
			const { focus } = this.props;

			var response = requestAjax( 'tribe_editor_block_event-details', { test: 'dev' } );
			var html = '';

			if ( response.success ) {
				html = response.data;
			}

			return (
				<div className="tribe-editor-block-wrap">
					{ HTMLReactParser( html ) }
				</div>
			);//

		}
	}//

	var blockName = 'tribe/event-details';
	var blockConfiguration = {
		title: __( 'Event Details', 'the-events-calendar' ),
		description: __( 'Configuration for the Event', 'the-events-calendar' ),
		icon: 'calendar',
		category: 'layout',
		keywords: [ 'event', 'the-events-calendar', 'tribe' ],

		useOnce: true,

		attributes: {
		},

		// The "edit" property must be a valid function.
		edit: function( props ) {
			return <EventDetails />;
		},

		// The "save" property must be specified and must be a valid function.
		save: function( props ) {
			return null;
		},
	};

	// Actually Register the block on Editor
	registerBlockType( blockName, blockConfiguration );
} )( jQuery );
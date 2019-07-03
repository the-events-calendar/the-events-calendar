/**
 * Makes sure we have all the required levels on the Tribe Object
 *
 * @since 4.9.4
 *
 * @type   {PlainObject}
 */
tribe.events = tribe.events || {};
tribe.events.views = tribe.events.views || {};

/**
 * Configures Month Grid Object in the Global Tribe variable
 *
 * @since 4.9.4
 *
 * @type  {PlainObject}
 */
tribe.events.views.monthGrid = {};

/**
 * Initializes in a Strict env the code that manages the Event Views
 *
 * @since 4.9.4
 *
 * @param  {PlainObject} $   jQuery
 * @param  {PlainObject} obj tribe.events.views.manager
 *
 * @return {void}
 */
( function( $, obj ) {
	'use strict';
	var $document = $( document );

	/**
	 * Selectors used for configuration and setup
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.selectors = {
		grid: '[data-js="tribe-events-month-grid"]',
		row: '[data-js="tribe-events-month-grid-row"]',
		cell: '[data-js="tribe-events-month-grid-cell"]',
		focusable: '[tabindex]',
		focused: '[tabindex="0"]',
	};

	/**
	 * State data for month grid
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.state = {
		grid: [],
		currentRow: 0,
		currentCol: 0,
	};

	/**
	 * Object of key codes
	 *
	 * @since 4.9.4
	 *
	 * @type {PlainObject}
	 */
	obj.keyCode = {
		END: 35,
		HOME: 36,
		LEFT: 37,
		UP: 38,
		RIGHT: 39,
		DOWN: 40,
	};

	/**
	 * Check if cell described by row and col is valid
	 *
	 * @since 4.9.4
	 *
	 * @param {integer} row row number of cell, 0 index
	 * @param {integer} col column number of cell, 0 index
	 *
	 * @return {boolean} true if cell is valid, false otherwise
	 */
	obj.isValidCell = function( row, col ) {
		return (
			! isNaN( row ) &&
			! isNaN( col ) &&
			row >= 0 &&
			col >= 0 &&
			obj.state.grid &&
			obj.state.grid.length &&
			row < obj.state.grid.length &&
			col < obj.state.grid[ row ].length
		);
	};

	/**
	 * Get next cell from current row, current column, and direction changes
	 *
	 * @since 4.9.4
	 *
	 * @param {integer} currentRow index of current row
	 * @param {integer} currentCol index of current column
	 * @param {integer} directionX number of steps to take in the X direction
	 * @param {integer} directionY number of steps to take in the Y direction
	 *
	 * @return {PlainObject} object containing next row and column indices
	 */
	obj.getNextCell = function( currentRow, currentCol, directionX, directionY ) {
		var row = currentRow + directionY;
		var col = currentCol + directionX;

		if ( obj.isValidCell( row, col ) ) {
			return {
				row: row,
				col: col,
			};
		}

		return {
			row: currentRow,
			col: currentCol,
		};
	};

	/**
	 * Set focus pointer to given row and column
	 *
	 * @since 4.9.4
	 *
	 * @param {integer} row index of row
	 * @param {integer} col index of column
	 *
	 * @return {boolean} boolean of whether focus pointer was set or not
	 */
	obj.setFocusPointer = function( row, col ) {
		if ( obj.isValidCell( row, col ) ) {
			$( obj.state.grid[ obj.state.currentRow ][ obj.state.currentCol ] ).attr( 'tabindex', '-1' );
			$( obj.state.grid[ row ][ col ] ).attr( 'tabindex', '0' );
			obj.state.currentRow = row;
			obj.state.currentCol = col;
			return true;
		}
		return false;
	};

	/**
	 * Focus cell at given row and column
	 *
	 * @since 4.9.4
	 *
	 * @param {integer} row index of row
	 * @param {integer} col index of column
	 *
	 * @return {void}
	 */
	obj.focusCell = function( row, col ) {
		if ( obj.setFocusPointer( row, col ) ) {
			$( obj.state.grid[ row ][ col ] ).focus();
		}
	};

	/**
	 * Handle keydown event to move focused grid cell
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleKeydown = function( event ) {
		var key = event.which || event.keyCode;
		var row = obj.state.currentRow;
		var col = obj.state.currentCol;
		var nextCell;

		switch ( key ) {
			case obj.keyCode.UP:
				nextCell = obj.getNextCell( row, col, 0, -1 );
				row = nextCell.row;
				col = nextCell.col;
				break;
			case obj.keyCode.DOWN:
				nextCell = obj.getNextCell( row, col, 0, 1 );
				row = nextCell.row;
				col = nextCell.col;
				break;
			case obj.keyCode.LEFT:
				nextCell = obj.getNextCell( row, col, -1, 0 );
				row = nextCell.row;
				col = nextCell.col;
				break;
			case obj.keyCode.RIGHT:
				nextCell = obj.getNextCell( row, col, 1, 0 );
				row = nextCell.row;
				col = nextCell.col;
				break;
			case obj.keyCode.HOME:
				if ( event.ctrlKey ) {
					row = 0;
				}
				col = 0;
				break;
			case obj.keyCode.END:
				if ( event.ctrlKey ) {
					row = obj.state.grid.length - 1;
				}
				col = obj.state.grid[ obj.state.currentRow ].length - 1;
				break;
			default:
				return;
		}

		obj.focusCell( row, col );
		event.preventDefault();
	};

	/**
	 * Handle click event to focus cell
	 *
	 * @since 4.9.4
	 *
	 * @param {Event} event event object
	 *
	 * @return {void}
	 */
	obj.handleClick = function( event ) {
		var $clickedCell = $( event.target ).closest( obj.selectors.focusable );

		for ( var row = 0; row < obj.state.grid.length; row++ ) {
			for ( var col = 0; col < obj.state.grid[ row ].length; col++ ) {
				if ( obj.state.grid[ row ][ col ].is( $clickedCell ) ) {
					obj.focusCell( row, col );
					return;
				}
			}
		}
	};

	/**
	 * Set up grid to state array
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $grid jQuery object of grid.
	 *
	 * @return {void}
	 */
	obj.setupGrid = function( $grid ) {
		$grid
			.find( obj.selectors.row )
			.each( function( rowIndex, row ) {
				var gridRow = [];

				$( row )
					.find( obj.selectors.cell )
					.each( function( colIndex, cell ) {
						var $cell = $( cell );

						// if cell is focusable (has tabindex attribute)
						if ( $cell.is( obj.selectors.focusable ) ) {
							// if cell is focusable and has tabindex of 0
							if ( $cell.is( obj.selectors.focused ) ) {
								obj.state.currentRow = obj.state.grid.length;
								obj.state.currentCol = gridRow.length;
							}

							// add focusable cell to gridRow
							gridRow.push( $cell );
						} else {
							var $focusableCell = $cell.find( obj.selectors.focusable );

							// if element is focusable (has tabindex attribute)
							if ( $focusableCell.is( obj.selectors.focusable ) ) {
								// if element is focusable and has tabindex of 0
								if ( $cell.is( obj.selectors.focused ) ) {
									obj.state.currentRow = obj.state.grid.length;
									obj.state.currentCol = gridRow.length;
								}

								// add focusable element to gridRow
								gridRow.push( $focusableCell );
							}
						}
					} );

				// add gridRow to grid if gridRow has focusable cells
				if ( gridRow.length ) {
					obj.state.grid.push( gridRow );
				}
			} );
	};

	/**
	 * Bind events for keydown and click on grid
	 *
	 * @since 4.9.4
	 *
	 * @param {jQuery} $grid jQuery object of grid.
	 *
	 * @return {void}
	 */
	obj.bindEvents = function( $grid ) {
		$grid
			.on( 'keydown', obj.handleKeydown )
			.on( 'click', obj.handleClick );
	};

	/**
	 * Initialize grid.
	 *
	 * @since 4.9.4
	 *
	 * @param {Event}   event      JS event triggered.
	 * @param {integer} index      jQuery.each index param from 'afterSetup.tribeEvents' event.
	 * @param {jQuery}  $container jQuery object of view container.
	 * @param {object}  data       data object passed from 'afterSetup.tribeEvents' event.
	 *
	 * @return {void}
	 */
	obj.init = function( event, index, $container, data ) {
		var $grid = $container.find( obj.selectors.grid );

		obj.setupGrid( $grid );
		obj.setFocusPointer( obj.state.currentRow, obj.state.currentCol );
		obj.bindEvents( $grid );
	};

	/**
	 * Handles the initialization of the multiday events when Document is ready
	 *
	 * @since 4.9.4
	 *
	 * @return {void}
	 */
	obj.ready = function() {
		$document.on( 'afterSetup.tribeEvents', tribe.events.views.manager.selectors.container, obj.init );

		/**
		 * @todo: do below for ajax events
		 */
		// on 'beforeAjaxBeforeSend.tribeEvents' event, remove all listeners
		// on 'afterAjaxError.tribeEvents', add all listeners
	};

	// Configure on document ready
	$document.ready( obj.ready );
} )( jQuery, tribe.events.views.monthGrid );

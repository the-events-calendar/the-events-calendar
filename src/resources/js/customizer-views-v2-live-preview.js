/**
 * File customizer-views-v2-live-preview.js.
 *
 * Plugin Customizer enhancements for a better user experience.
 *
 * Contains handlers to make TEC Customizer preview reload changes asynchronously.
 *
 * Please, for sanity's sake - try to keep controls organized by how they appear in the customizer!
 */

var tribe_events_customizer_live_preview_js_config =
	tribe_events_customizer_live_preview_js_config || {};

( function( $, api, obj ) {
	// All of these are in the format 'tribe_customizer[section_name][control_name]'!

	/* eslint-disable max-len */
	obj.selectors = {
		/* Global Elements */
		globalFontFamily: 'tribe_customizer[global_elements][font_family]',
		globalFontSizeBase: 'tribe_customizer[global_elements][font_size_base]',
		globalEventTitleColor: 'tribe_customizer[global_elements][event_title_color]',
		globalEventDateColor: 'tribe_customizer[global_elements][event_date_time_color]',
		globalBackgroundColor: 'tribe_customizer[global_elements][background_color]',
		globalBackgroundColorChoice: 'tribe_customizer[global_elements][background_color_choice]',
		globalAccentColor: 'tribe_customizer[global_elements][accent_color]',

		/* Events Bar */
		eventsBarTextColor: 'tribe_customizer[tec_events_bar][events_bar_text_color]',
		eventsBarButtonTextColor: 'tribe_customizer[tec_events_bar][find_events_button_text_color]',
		eventsBarIconColorChoice: 'tribe_customizer[tec_events_bar][events_bar_icon_color_choice]',
		eventsBarIconColor: 'tribe_customizer[tec_events_bar][events_bar_icon_color]',
		eventsBarButtonColorChoice: 'tribe_customizer[tec_events_bar][find_events_button_color_choice]',
		eventsBarButtonColor: 'tribe_customizer[tec_events_bar][find_events_button_color]',
		eventsBarBackgroundColorChoice: 'tribe_customizer[tec_events_bar][events_bar_background_color_choice]',
		eventsBarBackgroundColor: 'tribe_customizer[tec_events_bar][events_bar_background_color]',
		eventsBarBorderColorChoice: 'tribe_customizer[tec_events_bar][events_bar_border_color_choice]',
		eventsBarBorderColor: 'tribe_customizer[tec_events_bar][events_bar_border_color]',
		// Pro-added Control
		eventsBarViewSelectorBackgroundColorChoice: 'tribe_customizer[tec_events_bar][view_selector_background_color_choice]',
		eventsBarViewSelectorBackgroundColor: 'tribe_customizer[tec_events_bar][view_selector_background_color]',

		/* Month View */
		monthDaysOfWeekColor: 'tribe_customizer[month_view][days_of_week_color]',
		monthDateMarkerColor: 'tribe_customizer[month_view][date_marker_color]',
		monthMultidayEventBarChoice: 'tribe_customizer[month_view][multiday_event_bar_color_choice]',
		monthMultidayEventBar: 'tribe_customizer[month_view][multiday_event_bar_color]',
		monthGridLinesColor: 'tribe_customizer[month_view][grid_lines_color]',
		monthGridHoverColor: 'tribe_customizer[month_view][grid_hover_color]',
		monthGridBackgroundColorChoice: 'tribe_customizer[month_view][grid_background_color_choice]',
		monthGridBackgroundColor: 'tribe_customizer[month_view][grid_background_color]',
		monthTooltipBackgroundColor: 'tribe_customizer[month_view][tooltip_background_color]',

		/* Single Event */
		singleEventTitleColorChoice: 'tribe_customizer[single_event][post_title_color_choice]',
		singleEventTitleColor: 'tribe_customizer[single_event][post_title_color]'
	};
	/* eslint-enable max-len */

	obj.customProps = {
		/* Global Elements */
		globalFontFamily: [
			'--tec-font-family-sans-serif',
			'--tec-font-family-base',
		],
		globalFontSizeBase: [
			'--tec-font-size-0',
			'--tec-font-size-1',
			'--tec-font-size-2',
			'--tec-font-size-3',
			'--tec-font-size-4',
			'--tec-font-size-5',
			'--tec-font-size-6',
			'--tec-font-size-7',
			'--tec-font-size-8',
			'--tec-font-size-9',
			'--tec-font-size-10',
		],
		globalFontSizeKeys: [ 11, 12, 14, 16, 18, 20, 22, 24, 28, 32, 42 ],
		globalEventTitleColor: [
			'--tec-color-text-events-title',
		],
		globalEventDateColor: [
			'--tec-color-text-event-date',
			'--tec-color-text-event-date-secondary',
		],
		globalBackgroundColor: '--tec-color-background-events',
		globalAccentColor: [
			'--tec-color-accent-primary',
			'--tec-color-accent-primary-hover',
			'--tec-color-accent-primary-multiday',
			'--tec-color-accent-primary-multiday-hover',
			'--tec-color-accent-primary-active',
			'--tec-color-accent-primary-background',
			'--tec-color-background-secondary-datepicker',
			'--tec-color-accent-primary-background-datepicker',
			'--tec-color-button-primary',
			'--tec-color-button-primary-hover',
			'--tec-color-button-primary-active',
			'--tec-color-button-primary-background',
			'--tec-color-day-marker-current-month',
			'--tec-color-day-marker-current-month-hover',
			'--tec-color-day-marker-current-month-active',
		],

		/* Events Bar */
		eventsBarTextColor: [
			'--tec-color-text-events-bar-input',
			'--tec-color-text-events-bar-input-placeholder',
			'--tec-color-text-view-selector-list-item',
			'--tec-color-text-view-selector-list-item-hover',
		],
		eventsBarButtonTextColor: [
			'--tec-color-text-events-bar-submit-button',
			'--tec-color-text-events-bar-submit-button-active',
			'--tec-color-text-events-bar-submit-button-hover',
		],
		eventsBarIconColor: [
			'--tec-color-icon-events-bar',
			'--tec-color-icon-events-bar-hover',
			'--tec-color-icon-events-bar-active',
		],
		eventsBarButtonColor: [
			'--tec-color-background-events-bar-submit-button',
			'--tec-color-background-events-bar-submit-button-hover',
			'--tec-color-background-events-bar-submit-button-active',
		],
		eventsBarBackgroundColor: [
			'--tec-color-background-events-bar',
			'--tec-color-background-events-bar-tabs',
		],
		eventsBarBackgroundColorOpacity: '--tec-opacity-events-bar-input-placeholder',
		eventsBarBorderColor: '--tec-color-border-events-bar',
		// Pro-added Control
		eventsBarViewSelectorBackgroundColor:'--tec-color-background-view-selector',

		/* Month View */
		monthDaysOfWeekColor: '--tec-color-text-day-of-week-month',
		monthDateMarkerColor: [
			'--tec-color-day-marker-month',
			'--tec-color-day-marker-past-month',
		],
		monthMultidayEventBarColor: [
			'--tec-color-background-primary-multiday',
			'--tec-color-background-primary-multiday-hover',
			'--tec-color-background-primary-multiday-active',
			'--tec-color-background-secondary-multiday',
			'--tec-color-background-secondary-multiday-hover',
		],
		monthGridLinesColor: '--tec-color-border-secondary-month-grid',
		monthGridHoverColor: '--tec-color-border-active-month-grid-hover',
		monthGridBackgroundColor: '--tec-color-background-month-grid',
		monthTooltipBackgroundColor: '--tec-color-background-tooltip',

		/* Single Event */
		singleEventTitleColor: '--tec-color-text-event-title',
	};

	obj.root = document.querySelectorAll( tribe_events_customizer_live_preview_js_config.selector );

	/*--------- Global Elements ---------*/

	// Font Family
	api(
		obj.selectors.globalFontFamily,
		function( value ) {
			// Bind to the value change
			value.bind(
				function( to ) {
					const fontFamily = 'theme' === to
						? 'inherit'
						: tribe_events_customizer_live_preview_js_config.default_font;

					obj.customProps.globalFontFamily.forEach(
						function( fontFamilySelector ) {
							// Note: "inherit" won't work if we put it on 'tribe-events' - it needs to be on :root{}
							document.documentElement.style.setProperty( fontFamilySelector, fontFamily );
						}
					);
				}
			);
		}
	);

	// Font Size
	api(
		obj.selectors.globalFontSizeBase,
		function( value ) {
			value.bind(
				function( to ) {
					const fontSizeMultiplier = parseInt( to ) / 16;

					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.globalFontSizeBase.forEach(
								function( fontSizeSelector, index ) {
									const newSize =
										fontSizeMultiplier * parseInt( obj.customProps.globalFontSizeKeys[index] );
									tribeElement.style.setProperty( fontSizeSelector, newSize.toFixed(3) + 'px' );
								}
							);
						}
					);
				}
			);
		}
	);

	// Event Title
	api(
		obj.selectors.globalEventTitleColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.globalEventTitleColor.forEach(
								function( eventTitleSelector ) {
									tribeElement.style.setProperty( eventTitleSelector, to );
								}
							);

							// Event Single Title
							const singleEventTitleColorChoice =
								api( obj.selectors.singleEventTitleColorChoice ).get();

							if ( 'default' === singleEventTitleColorChoice ) {
								obj.customProps.singleEventTitleColor.forEach(
									function( eventTitleSelector ) {
										tribeElement.style.setProperty( eventTitleSelector, to );
									}
								);
							}
						}
					);
				}
			);
		}
	);

	// Event Date
	api(
		obj.selectors.globalEventDateColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.globalEventDateColor.forEach(
								function( eventDateSelector ) {
									tribeElement.style.setProperty( eventDateSelector, to );
								}
							);
						}
					);
				}
			);
		}
	);

	// Events Background Color Choice
	api(
		obj.selectors.globalBackgroundColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					const backgroundColor = 'transparent' !== to
						? api( obj.selectors.globalBackgroundColor ).get()
						: to;

					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty(
								obj.customProps.globalBackgroundColor,
								backgroundColor
							);

							const eventsBarBackgroundColorChoice =
								api( obj.selectors.eventsBarBackgroundColorChoice ).get();

							if  ( 'global_background' === eventsBarBackgroundColorChoice ) {
								let eventsBarBackgroundColor =
									'transparent' === to
										? 'var(--tec-color-background)'
										: backgroundColor;

								const backgroundColorSelectors = obj.customProps.eventsBarBackgroundColor;

								backgroundColorSelectors.forEach(
									function( colorSelector ) {
										tribeElement.style.setProperty( colorSelector, eventsBarBackgroundColor );
									}
								);

								const eventsBarViewSelectorBackgroundColorChoice =
									api( obj.selectors.eventsBarViewSelectorBackgroundColorChoice ).get();

								if ( 'default' === eventsBarViewSelectorBackgroundColorChoice ) {
									tribeElement.style.setProperty(
										obj.customProps.eventsBarViewSelectorBackgroundColor,
										eventsBarBackgroundColor
									);
								}
							}
						}
					);
				}
			);
		}
	);

	// Events Background Color
	api(
		obj.selectors.globalBackgroundColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.globalBackgroundColor, to );

							const eventsBarBackgroundColorChoice =
								api( obj.selectors.eventsBarBackgroundColorChoice ).get();

							if ( 'global_background' === eventsBarBackgroundColorChoice ) {
								const backgroundColorSelectors = obj.customProps.eventsBarBackgroundColor;

								backgroundColorSelectors.forEach(
									function( colorSelector ) {
										tribeElement.style.setProperty( colorSelector, to );
									}
								);
							}
						}
					);
				}
			);
		}
	);

	// Accent Color
	api(
		obj.selectors.globalAccentColor,
		function( value ) {
			value.bind(
				function( to ) {
					const accentColor = to;
					const accentColorSelectors = obj.customProps.globalAccentColor;

					obj.root.forEach(
						function( tribeElement ) {
							accentColorSelectors.forEach(
								function( accentColorSelector ) {
									tribeElement.style.setProperty( accentColorSelector, accentColor );
								}
							);

							// Events Bar "Find Events" button
							const eventsBarButtonColorChoice =
								api( obj.selectors.eventsBarButtonColorChoice ).get();

							if ( 'default' === eventsBarButtonColorChoice ) {
								const eventsBarButtonColor = obj.customProps.eventsBarButtonColor;

								eventsBarButtonColor.forEach(
									function( eventsBarButtonColorSelector ) {
										tribeElement.style.setProperty( eventsBarButtonColorSelector, accentColor );
									}
								);
							}

							// Events Bar Icon Color
							const eventsBarIconColorChoice = api( obj.selectors.eventsBarIconColorChoice ).get();
							if ( 'accent' === eventsBarIconColorChoice ) {
								const eventsBarIconColor = obj.customProps.eventsBarIconColor;

								eventsBarIconColor.forEach(
									function( eventsBarIconColorSelector ) {
										tribeElement.style.setProperty( eventsBarIconColorSelector, accentColor );
									}
								);
							}

							// @todo: Multiday Event Span?
						}
					);
				}
			);
		}
	);

	/*--------- Events Bar ---------*/

	// Text Color
	api(
		obj.selectors.eventsBarTextColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarTextColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, to );
								}
							);

							tribeElement.style.setProperty( '--tec-opacity-events-bar-input-placeholder', '0.6' );
						}
					);
				}
			);
		}
	);

	// Button Text Color
	api(
		obj.selectors.eventsBarButtonTextColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarButtonTextColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, to );
								}
							);
						}
					);
				}
			);
		}
	);

	// Icon Color Choice
	api(
		obj.selectors.eventsBarIconColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					let iconColor = 'var(--tec-color-icon-primary)';

					if ( 'custom' === to ) {
						iconColor = api(obj.selectors.eventsBarIconColor ).get();
					} else if ( 'accent' === to ) {
						iconColor = api( obj.selectors.globalAccentColor ).get();
					}

					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarIconColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, iconColor );
								}
							);
						}
					);
				}
			);
		}
	);

	// Icon Color
	api(
		obj.selectors.eventsBarIconColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarIconColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, to );
								}
							);
						}
					);
				}
			);
		}
	);

	// Button Background Color Choice
	api(
		obj.selectors.eventsBarButtonColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					const buttonColor = 'custom' === to
						? api( obj.selectors.eventsBarButtonColor ).get()
						: api( obj.selectors.globalAccentColor ).get();

					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarButtonColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, buttonColor );
								}
							);
						}
					);
				}
			);
		}
	);

	// Button Background Color
	api(
		obj.selectors.eventsBarButtonColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarButtonColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, to );
								}
							);
						}
					);
				}
			);
		}
	);

	// Events Bar Background Color Choice
	api(
		obj.selectors.eventsBarBackgroundColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					let backgroundColor = '#fff';

					if ( 'custom' === to ) {
						backgroundColor = api(obj.selectors.eventsBarBackgroundColor ).get();
					} else if ( 'global_background' === to ) {
						const globalBackgroundColorChoice = api( obj.selectors.globalBackgroundColorChoice ).get(); /* eslint-disable-line max-len */
						if ( 'transparent' !== globalBackgroundColorChoice ) {
							backgroundColor = api( obj.selectors.globalBackgroundColor ).get();
						}
					}

					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarBackgroundColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, backgroundColor );
								}
							);

							const eventsBarViewSelectorBackgroundColorChoice =
								api( obj.selectors.eventsBarViewSelectorBackgroundColorChoice ).get();

							api( obj.selectors.eventsBarBackgroundColor ).change();


							if ( 'default' === eventsBarViewSelectorBackgroundColorChoice ) {
								// @todo: make aware of other controls
							}
						}
					);
				}
			);
		}
	);

	// Events Bar Background Color
	api(
		obj.selectors.eventsBarBackgroundColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							obj.customProps.eventsBarBackgroundColor.forEach(
								function( colorSelector ) {
									tribeElement.style.setProperty( colorSelector, to );
								}
							);
						}
					);
				}
			);
		}
	);

	// Events Bar View Selector Background Color Choice
	api(
		obj.selectors.eventsBarViewSelectorBackgroundColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					let backgroundColor = api(obj.selectors.eventsBarBackgroundColor ).get();

					if ( 'custom' === to ) {
						backgroundColor = api(obj.selectors.eventsBarViewSelectorBackgroundColor ).get();
					}

					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty(
								obj.customProps.eventsBarViewSelectorBackgroundColor,
								backgroundColor
							);
						}
					);
				}
			);
		}
	);

	// Events Bar View Selector Background Color
	api(
		obj.selectors.eventsBarViewSelectorBackgroundColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty(
								obj.customProps.eventsBarViewSelectorBackgroundColor,
								to
							);
						}
					);
				}
			);
		}
	);

	// Events Bar Border Color Choice
	api(
		obj.selectors.eventsBarBorderColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					const borderColor = 'custom' === to
						? api( obj.selectors.eventsBarBorderColor ).get()
						: 'var(--tec-color-border-secondary)';

					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.eventsBarBorderColor, borderColor );
						}
					);
				}
			);
		}
	);

	// Events Bar Border Color
	api(
		obj.selectors.eventsBarBorderColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.eventsBarBorderColor, to );
						}
					);
				}
			);
		}
	);

	/*--------- Month View ---------*/

	// Days of Week Color
	api(
		obj.selectors.monthDaysOfWeekColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.monthDaysOfWeekColor, to );
						}
					);
				}
			);
		}
	);

	// Date Marker Color
	api(
		obj.selectors.monthDateMarkerColor,
		function( value ) {
			value.bind(
				function( to ) {
					const monthDateMarkerColorSelectors = obj.customProps.monthDateMarkerColor;

					obj.root.forEach(
						function( tribeElement ) {
							monthDateMarkerColorSelectors.forEach(
								function( monthDateMarkerColorSelector ) {
									tribeElement.style.setProperty( monthDateMarkerColorSelector, to );
								}
							);
						}
					);
				}
			);
		}
	);

	// Grid Lines Color
	api(
		obj.selectors.monthGridLinesColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.monthGridLinesColor, to );
						}
					);
				}
			);
		}
	);

	// Grid Hover Color
	api(
		obj.selectors.monthGridHoverColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.monthGridHoverColor, to );
						}
					);
				}
			);
		}
	);

	// Grid Background Color Choice
	api(
		obj.selectors.monthGridBackgroundColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					const backgroundColor = 'custom' === to
						? api( obj.selectors.monthGridBackgroundColor ).get()
						: 'transparent';
					const tooltipBackgroundColor = 'custom' === to
						? '#fff'
						: api( obj.selectors.globalBackgroundColor ).get();

					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty(
								obj.customProps.monthGridBackgroundColor,
								backgroundColor
							);
						}
					);

					document.documentElement.style.setProperty(
						obj.customProps.monthTooltipBackgroundColor,
						tooltipBackgroundColor
					);
				}
			);
		}
	);

	// Grid Background Color
	api(
		obj.selectors.monthGridBackgroundColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.monthGridBackgroundColor, to );
						}
					);
				}
			);
		}
	);

	// Tooltip Background Color
	api(
		obj.selectors.monthTooltipBackgroundColor,
		function( value ) {
			value.bind(
				function( to ) {
					let tooltipBackgroundColor = '#fff';
					let monthBackgroundColorChoice =
						api( obj.selectors.monthGridBackgroundColorChoice ).get();
					let globalBackgroundColorChoice = api( obj.selectors.globalBackgroundColorChoice ).get();

					if (
						'event' === to
						&& 'transparent' === monthBackgroundColorChoice
						&& 'transparent' !== globalBackgroundColorChoice
					) {
						tooltipBackgroundColor = api( obj.selectors.globalBackgroundColor ).get();
					}

					// Tooltips are appended to the body and are not inside a .tribe-events or .tribe-common element!
					document.documentElement.style.setProperty(
						obj.customProps.monthTooltipBackgroundColor,
						tooltipBackgroundColor
					);
				}
			);
		}
	);

	/*--------- Single Event ---------*/

	// Event Single Title Color Choice
	api(
		obj.selectors.singleEventTitleColorChoice,
		function( value ) {
			value.bind(
				function( to ) {
					const eventTitleColor = 'custom' === to
						? api( obj.selectors.singleEventTitleColor ).get()
						: api( obj.selectors.globalEventTitleColor ).get();

					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty(
								obj.customProps.singleEventTitleColor,
								eventTitleColor
							);
						}
					);
				}
			);
		}
	);

	// Event Single Title Color
	api(
		obj.selectors.singleEventTitleColor,
		function( value ) {
			value.bind(
				function( to ) {
					obj.root.forEach(
						function( tribeElement ) {
							tribeElement.style.setProperty( obj.customProps.singleEventTitleColor, to );
						}
					);
				}
			);
		}
	);

} )( jQuery, wp.customize, tribe_events_customizer_live_preview_js_config );

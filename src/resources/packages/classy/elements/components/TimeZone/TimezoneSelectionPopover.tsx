import { VirtualElement } from '@wordpress/components/build-types/popover/types';
import { Popover, SelectControl } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { _x } from '@wordpress/i18n';
import CloseIcon from '../CloseIcon';
import { localizedData } from '../../../localized-data';
import { parse as hpqParse } from 'hpq';

// @see `wp_timezone_choice`.
const timezoneChoice = localizedData.settings.timezoneChoice;

export default function TimezoneSelectionPopover( props: {
	anchor: Element | VirtualElement;
	onClose: () => void;
	onTimezoneChange: ( timezone: string ) => void;
	timezone: string;
} ) {
	const { anchor, onClose, onTimezoneChange, timezone } = props;

	const timezoneOptions = useMemo( () => {
		const parsedOptions: HTMLCollection = hpqParse(
			timezoneChoice,
			( h ) => h
		).children;

		return Array.from( parsedOptions ).map(
			( optgroup: HTMLOptGroupElement, index ) => (
				<optgroup key={ index } label={ optgroup.label }>
					{ Array.from( optgroup.children ).map(
						( option: HTMLOptionElement, optionIndex ) => (
							<option key={ optionIndex } value={ option.value }>
								{ option.label }
							</option>
						)
					) }
				</optgroup>
			)
		);
	}, [ timezoneChoice ] );

	return (
		<Popover
			anchor={ anchor }
			className="classy-component_popover classy-component_popover--timezone"
			expandOnMobile={ true }
			placement="bottom-end"
			noArrow={ true }
			offset={ 4 }
			onClose={ onClose }
		>
			<div className="classy-component-popover__content classy-component-popover__content--timezone">
				<CloseIcon onClick={ onClose } />

				<h4 className="classy-component-popover__title">
					{ _x(
						'Event Time Zone',
						'Timezone selector popover title',
						'the-events-calendar'
					) }
				</h4>

				<p className="classy-component-popover__description">
					{ _x(
						'Choose a different time zone than your default for this event.',
						'Timezone selector popover description',
						'the-events-calendar'
					) }
				</p>

				<SelectControl
					className="classy-component-popover__input classy-component-popover__input--select classy-component-popover__input--timezone"
					__next40pxDefaultSize
					__nextHasNoMarginBottom
					value={ timezone }
					onChange={ onTimezoneChange }
				>
					{ timezoneOptions }
				</SelectControl>
			</div>
		</Popover>
	);
}

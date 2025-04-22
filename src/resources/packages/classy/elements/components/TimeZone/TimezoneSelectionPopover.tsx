import { VirtualElement } from '@wordpress/components/build-types/popover/types';
import { Popover, SelectControl } from '@wordpress/components';
import { useMemo } from '@wordpress/element';
import { _x } from '@wordpress/i18n';
import CloseIcon from '../CloseIcon';

export default function TimezoneSelectionPopover( props: {
	anchor: Element | VirtualElement;
	onClose: () => void;
	onTimezoneChange: ( timezone: string ) => void;
	timezone: string;
} ) {
	const { anchor, onClose, onTimezoneChange, timezone } = props;

	// @todo get them from the backend
	const timezoneOptions = useMemo( () => {
		return [
			{
				label: 'Europe/Paris',
				value: 'Europe/Paris',
			},
			{
				label: 'America/New_York',
				value: 'America/New_York',
			},
			{
				label: 'Asia/Tokyo',
				value: 'Asia/Tokyo',
			},
			{
				label: 'Australia/Sydney',
				value: 'Australia/Sydney',
			},
			{
				label: 'America/Los_Angeles',
				value: 'America/Los_Angeles',
			},
			{
				label: 'Europe/London',
				value: 'Europe/London',
			},
			{
				label: 'Asia/Shanghai',
				value: 'Asia/Shanghai',
			},
			{
				label: 'Africa/Cairo',
				value: 'Africa/Cairo',
			},
			{
				label: 'America/Chicago',
				value: 'America/Chicago',
			},
			{
				label: 'Europe/Berlin',
				value: 'Europe/Berlin',
			},
		];
	}, [] );

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
					options={ timezoneOptions }
					onChange={ onTimezoneChange }
				/>
			</div>
		</Popover>
	);
}

import React from 'react';
import { useRef, useState } from '@wordpress/element';
import { _x } from '@wordpress/i18n';
import TimezoneSelectionPopover from './TimezoneSelectionPopover';

export default function TimeZone( props: {
	onTimezoneChange: ( timezone: string ) => void;
	timezone: string;
} ) {
	const { onTimezoneChange, timezone } = props;

	const [ isSelecting, setIsSelecting ] = useState( false );
	const ref = useRef( null );

	const onTimezoneChangeProxy = ( timezone: string ) => {
		onTimezoneChange( timezone );
		setIsSelecting( false );
	};

	return (
		<div
			className="classy-field__control classy-field__control--timezone"
			ref={ ref }
		>
			<span className="classy-field__timezone-label">
				{ _x(
					'Time Zone:',
					'Timezone component label',
					'the-events-calendar'
				) }
			</span>{ ' ' }
			<a
				className="classy-field__timezone-value"
				href="#"
				onClick={ () => setIsSelecting( true ) }
			>
				{ timezone }
			</a>
			{ isSelecting && (
				<TimezoneSelectionPopover
					anchor={ ref.current }
					onTimezoneChange={ onTimezoneChangeProxy }
					timezone={ timezone }
					onClose={ () => setIsSelecting( false ) }
				/>
			) }
		</div>
	);
}

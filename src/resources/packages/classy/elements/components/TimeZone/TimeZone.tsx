import React from 'react';
import { useRef, useState } from '@wordpress/element';
import { _x } from '@wordpress/i18n';
import TimezoneSelectionPopover from './TimezoneSelectionPopover';
import { Button } from '@wordpress/components';

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
			<Button
				variant="link"
				className="is-link classy-field__timezone-value"
				onClick={ () => setIsSelecting( true ) }
			>
				{ timezone }
			</Button>
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

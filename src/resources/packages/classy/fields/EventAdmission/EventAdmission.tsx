import React from 'react';
import { Button, ButtonGroup } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { IconTicket } from '@tec/common/classy/components';
import { currencyDollar } from '@wordpress/icons';

export default function EventAdmission( props: FieldProps ) {
	return (
		<div className="classy-field classy-field--event-admission">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<ButtonGroup>
				<Button
					__next40pxDefaultSize={ true }
					variant="primary"
				>
					<IconTicket/>
					{ _x(
						'Sell Tickets',
						'Event admission button label',
						'the-events-calendar'
					) }
				</Button>

				<Button
					__next40pxDefaultSize={ true }
					variant="secondary"
				>
					
					{ _x(
						'Manual Pricing',
						'Event admission button label',
						'the-events-calendar'
					) }
				</Button>
			</ButtonGroup>
		</div>
	);
}

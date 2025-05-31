import React from 'react';
import { useEffect, useState } from 'react';
import { Button, ButtonGroup } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { _x } from '@wordpress/i18n';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { IconTicket } from '@tec/common/classy/components';
import { currencyDollar } from '@wordpress/icons';
import { EventCost } from "../EventCost";

export default function EventAdmission( props: FieldProps ) {

	const { meta } = useSelect( ( select ) => {
		const selector = select( 'core/editor' );
		return {
			// @ts-ignore
			meta: selector.getEditedPostAttribute( 'meta' ) || {},
		};
	}, [] );

	const [ isUsingTickets, setIsUsingTickets ] = useState< boolean >(
		true
	);

	return (
		<div className="classy-field classy-field--event-admission">
			<div className="classy-field__title">
				<h3>{ props.title }</h3>
			</div>

			<div className="classy-field__inputs classy-field__inputs--boxed">
				<div className="class-field__inputs-section classy-field__inputs-section--row">
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

				<div className="classy-field__inputs-section classy-field__inputs-section--row">
					<EventCost />
				</div>
			</div>
		</div>
	);
}

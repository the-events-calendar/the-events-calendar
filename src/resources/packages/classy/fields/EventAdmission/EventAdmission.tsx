import React from 'react';
import { Button, ButtonGroup } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import { FieldProps } from '@tec/common/classy/types/FieldProps.ts';
import { IconTicket } from '@tec/common/classy/components';
import { currencyDollar } from '@wordpress/icons';
import { EventCost } from "../EventCost";

export default function EventAdmission( props: FieldProps ) {
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
					<EventCost
						title={ _x(
							'Event Cost',
							'Event admission cost field title',
							'the-events-calendar'
						) }
					/>
				</div>
			</div>
		</div>
	);
}

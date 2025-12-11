import * as React from 'react';
import { Modal } from '@wordpress/components';
import { _x } from '@wordpress/i18n';
import { IconNew } from '@tec/common/classy/components';
import VenueUpsert from './VenueUpsert';
import { VenueData } from '../../types/VenueData';

export default function VenueUpsertModal( props: {
	isUpdate: boolean;
	onCancel: () => void;
	onClose: () => void;
	onSave: ( venueData: VenueData ) => void;
	values: VenueData;
} ) {
	const { isUpdate, onCancel, onClose, onSave, values } = props;

	const title = isUpdate
		? _x( 'Update Venue', 'Update venue modal header title', 'the-events-calendar' )
		: _x( 'New Venue', 'Insert venue modal header title', 'the-events-calendar' );

	return (
		<Modal
			className="classy-modal classy-modal--venue"
			icon={ <IconNew /> }
			onRequestClose={ onClose }
			overlayClassName="classy-modal__overlay classy-modal__overlay--venue"
			title={ title }
		>
			<VenueUpsert isUpdate={ isUpdate } onCancel={ onCancel } onSave={ onSave } values={ values } />
		</Modal>
	);
}

import * as React from 'react';
import { Modal } from '@wordpress/components';
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

	console.log( 'isUpdate', isUpdate );

	return (
		<Modal
			__experimentalHideHeader={ true }
			className="classy-modal classy-modal--venue"
			onRequestClose={ onClose }
			overlayClassName="classy-modal__overlay classy-modal__overlay--venue"
		>
			<VenueUpsert isUpdate={ isUpdate } onCancel={ onCancel } onSave={ onSave } values={ values } />
		</Modal>
	);
}

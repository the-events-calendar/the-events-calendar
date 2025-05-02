import React from 'react';
import { Modal } from "@wordpress/components";
import OrganizerUpsert from './OrganizerUpsert';

export default function OrganizerUpsertModal( props:{
    onCancel: ()=>void;
    onClose: ()=>void;
    onSave: ()=>void;
} ){
    const {
        onCancel,
        onClose, 
        onSave, 
    } = props;

    return (<Modal
       __experimentalHideHeader={true}
        className="classy-modal classy-modal--organizer"
        onRequestClose={onClose}
        overlayClassName="classy-modal__overlay classy-modal__overlay--organizer"
    >
        <OrganizerUpsert 
            onCancel={onCancel}
            onSave={onSave}
        />
    </Modal>);
}
/**
 * External dependencies
 */
import React, { PureComponent } from 'react';
import PropTypes from 'prop-types';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	Spinner,
	PanelBody,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { wpEditor } from '@moderntribe/common/utils/globals';
import {
	SearchOrCreate,
	EditLink,
} from '@moderntribe/events/elements';
import OrganizerDetails from './details';
import OrganizerForm from './form';
import { Organizer as OrganizerIcon } from '@moderntribe/events/icons';
import { toFields } from '@moderntribe/events/elements/organizer-form/utils';
const { InspectorControls } = wpEditor;

class EventOrganizer extends PureComponent {
	static propTypes = {
		details: PropTypes.object,
		create: PropTypes.bool,
		edit: PropTypes.bool,
		submit: PropTypes.bool,
		isLoading: PropTypes.bool,
		isSelected: PropTypes.bool,
		clientId: PropTypes.string,
		current: PropTypes.string,
		setPost: PropTypes.func,
		clear: PropTypes.func,
		editPost: PropTypes.func,
		onFormSubmit: PropTypes.func,
		onItemSelect: PropTypes.func,
		onCreateNew: PropTypes.func,
		onEdit: PropTypes.func,
		onRemove: PropTypes.func,
	};

	componentDidUpdate( prevProps ) {
		const {
			isSelected,
			edit,
			create,
			setSubmit,
		} = this.props;
		const unSelected = prevProps.isSelected && ! isSelected;
		if ( unSelected && ( edit || create ) ) {
			setSubmit();
		}
	}

	renderLoading = () => (
		<div className="tribe-editor__spinner-container">
			<Spinner />
		</div>
	);

	renderForm = () => {
		const { fields, submit, onFormSubmit } = this.props;

		if ( submit ) {
			return this.renderLoading();
		}

		return (
			<OrganizerForm
				{ ...toFields( fields ) }
				submit={ onFormSubmit }
			/>
		);
	}

	renderSearch() {
		const {
			clientId,
			isSelected,
			organizers,
			store,
			postType,
			onItemSelect,
			onCreateNew,
		} = this.props;

		return (
			<SearchOrCreate
				name={ clientId }
				store={ store }
				postType={ postType }
				isSelected={ isSelected }
				icon={ <OrganizerIcon /> }
				placeholder={ __( 'Add or find an organizer', 'the-events-calendar' ) }
				onItemSelect={ onItemSelect }
				onCreateNew={ onCreateNew }
				exclude={ organizers }
			/>
		);
	}

	renderDetails() {
		const { details, volatile, isSelected, onEdit, onRemove } = this.props;
		return (
			<OrganizerDetails
				organizer={ details }
				volatile={ volatile }
				selected={ isSelected }
				edit={ onEdit }
				remove={ onRemove }
			/>
		);
	}

	renderContent() {
		const { details, edit, create, isLoading } = this.props;

		if ( isLoading ) {
			return this.renderLoading();
		}

		if ( edit || create ) {
			return this.renderForm();
		}

		if ( isEmpty( details ) ) {
			return this.renderSearch();
		}

		return this.renderDetails();
	}

	renderBlock() {
		return (
			<section key={ this.props.clientId }>
				{ this.renderContent() }
			</section>
		);
	}

	renderSettings() {
		const { isSelected, attributes } = this.props;

		if ( ! isSelected || ! attributes.organizer ) {
			return null;
		}

		return (
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Organizer Settings', 'the-events-calendar' ) }>
					<EditLink
						postId={ attributes.organizer }
						label={ __( 'Edit Organizer', 'the-events-calendar' ) }
					/>
				</PanelBody>
			</InspectorControls>
		);
	}

	render() {
		return [
			this.renderBlock(),
			this.renderSettings(),
		];
	}
}

export default EventOrganizer;

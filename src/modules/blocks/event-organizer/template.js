/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';
import { isEmpty } from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import {
	Spinner,
	PanelBody,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
	InspectorControls,
} from '@wordpress/editor';

/**
 * Internal dependencies
 */
import {
	SearchOrCreate,
	EditLink,
} from '@moderntribe/events/elements';
import OrganizerDetails from './details';
import OrganizerForm from './details/form';
import { Organizer as OrganizerIcon } from '@moderntribe/events/icons';
import { toFields } from '@moderntribe/events/elements/organizer-form/utils';

class EventOrganizer extends Component {

	static propTypes = {
		details: PropTypes.object,
		create: PropTypes.bool,
		edit: PropTypes.bool,
		submit: PropTypes.bool,
		isLoading: PropTypes.bool,
		isSelected: PropTypes.bool,
		organizer: PropTypes.number,
		clientId: PropTypes.string,
		current: PropTypes.string,
		setPost: PropTypes.func,
		clear: PropTypes.func,
		createDraft: PropTypes.func,
		editPost: PropTypes.func,
		onFormSubmit: PropTypes.func,
		onSelectItem: PropTypes.func,
		onCreateNew: PropTypes.func,
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

	renderForm() {
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

	/* TODO: move into container component */
	edit = () => {
		const { details, editEntry } = this.props;
		editEntry( details );
	};

	/* TODO: move into container component */
	remove = () => {
		const {
			clientId,
			organizer,
			removeOrganizerInBlock,
			volatile,
			maybeRemoveEntry,
			removeOrganizerInClassic,
			details,
		} = this.props;

		removeOrganizerInBlock( clientId, organizer );

		/**
		 * @todo make sure this one is provided by the container in mapDispatchToProps, as both
		 * @todo methods / logic are very similar.
		 * @todo Seee https://github.com/moderntribe/events-gutenberg/pull/259/files#r211099809
		 */
		if ( volatile ) {
			maybeRemoveEntry( details );
			removeOrganizerInClassic( organizer );
		}
	};

	renderDetails() {
		const { details, volatile, isSelected } = this.props;
		return (
			<OrganizerDetails
				organizer={ details }
				volatile={ volatile }
				selected={ isSelected }
				edit={ this.edit }
				remove={ this.remove }
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
		const { isSelected, organizer } = this.props;

		return (
			isSelected &&
			organizer &&
			<InspectorControls key="inspector">
				<PanelBody title={ __( 'Organizer Settings', 'the-events-calendar' ) }>
					<EditLink
						postId={ organizer }
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

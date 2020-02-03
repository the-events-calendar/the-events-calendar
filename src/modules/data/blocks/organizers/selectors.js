/**
 * External dependencies
 */
import { createSelector } from 'reselect';
import { difference } from 'lodash';

export const getOrganizersInClassic = ( state ) => state.events.blocks.organizers.classic;

export const getOrganizersInBlock = ( state ) => state.events.blocks.organizers.blocks;

export const getMappedOrganizers = createSelector(
	[ getOrganizersInClassic, getOrganizersInBlock ],
	( classic, blocks ) => {
		return classic.map( ( id ) => ( {
			id,
			block: difference( [ id ], blocks ).length === 0,
		} ) );
	}
);

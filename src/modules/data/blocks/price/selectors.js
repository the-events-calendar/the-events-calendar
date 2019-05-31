/**
 * External dependencies
 */
import { createSelector } from 'reselect';

export const getPriceBlock = ( state ) => state.events.blocks.price;

export const getPrice = createSelector(
	[ getPriceBlock ],
	( block ) => block.cost,
);

export const getSymbol = createSelector(
	[ getPriceBlock ],
	( block ) => block.symbol,
);

export const getPosition = createSelector(
	[ getPriceBlock ],
	( block ) => block.position,
);

export const getDescription = createSelector(
	[ getPriceBlock ],
	( block ) => block.description,
);

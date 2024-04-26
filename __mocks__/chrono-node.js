import { constructNew, isValid } from 'date-fns';

export const parse = ( label ) => {
	const start = label ? constructNew( new Date( label ) ) : null;
	const results = [];

	if ( start && isValid( start ) ) {
		const date = {
			date: () => start
		}
		results.push( { start: date, end: date } );
	}

	return results;
};

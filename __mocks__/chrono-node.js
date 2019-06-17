import moment from 'moment';

export default {
	parse: ( label ) => {
		const start = label ? moment( label ) : null;
		const results = [];
		if ( start && start.isValid() ) {
			const date = {
				date: () => start
			}
			results.push( { start: date, end: date } );
		}
		return results;
	}
};

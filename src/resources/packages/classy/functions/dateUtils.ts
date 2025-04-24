/**
 * Checks if a given date string is valid.
 *
 * @since TBD
 *
 * @param {string} date The date string to validate.
 *
 * @returns {boolean} Returns true if the date is valid, otherwise false.
 */
export function isValidDate( date: string ): boolean {
	return ! isNaN( Date.parse( date ) );
}

/**
 * Converts a date string into a Date object if it's valid, otherwise returns null.
 *
 * @since TBD
 *
 * @param {string} date The date string to convert.
 *
 * @returns {Date|null} Returns a Date object if the date is valid, otherwise null.
 */
export function getValidDateOrNull( date: string ): Date | null {
	const parsedDate = Date.parse( date );
	return isNaN( parsedDate ) ? null : new Date( parsedDate );
}

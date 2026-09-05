/**
 * Internal dependencies
 */
import { globals } from '@moderntribe/common/utils';

const { tec } = globals;

/**
 * Returns whether the dates of the edited Event are locked.
 *
 * A rule-based recurring Event (Events Calendar Pro rules, Pro inactive) keeps its
 * dates frozen server-side until it is converted to individual dates: the date and
 * time controls of the datetime block follow.
 *
 * @since TBD
 *
 * @return {boolean} Whether the dates are locked.
 */
export const isDatesLocked = () => Boolean( ( tec().recurrenceDates || {} ).locked );

export default isDatesLocked;

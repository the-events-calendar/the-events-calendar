/**
 * External dependencies
 */
import { compose } from 'redux';

/**
 * Internal dependencies
 */
import { withStore, withBlockCloser } from '@moderntribe/common/hoc';
import EventDateTime from './template';

/**
 * Module Code
 */

export default compose(
	withStore(),
	withBlockCloser,
)( EventDateTime );

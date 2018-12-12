/**
 * External dependencies
 */
import React from 'react';
import PropTypes from 'prop-types';

import './style.pcss';

const HumanReadableInput = ( { onChange, naturalLanguageLabel, before, after } ) => (
	<div className="tribe-editor__date-input__container">
		{ before }
		<input
			type="text"
			name="date-input"
			className="tribe-editor__date-input"
			value={ naturalLanguageLabel }
			onChange={ onChange }
		/>
		{ after }
	</div>
);

HumanReadableInput.propTypes = {
	onChange: PropTypes.func,
	naturalLanguageLabel: PropTypes.string,
	before: PropTypes.node,
	after: PropTypes.node,
};

export default HumanReadableInput;


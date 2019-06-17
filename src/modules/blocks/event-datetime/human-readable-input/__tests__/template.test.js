import HumanReadableInput from '../template';

describe( 'Human readable input', () => {
	test( 'render component', () => {
		const component = renderer.create(
			<HumanReadableInput
				onChange={ jest.fn() }
				naturalLanguageLabel={ 'Initial label' }
			/>
		);
		expect( component.toJSON() ).toMatchSnapshot();
	} );
} );

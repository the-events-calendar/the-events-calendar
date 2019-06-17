/**
 * External dependencies
 */
import { PropTypes } from 'prop-types';
import classNames from 'classnames';
import { isEmpty, noop } from 'lodash';
import { decode } from 'he';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './style.pcss';

class SearchOrCreate extends Component {
	static defaultProps = {
		isSelected: false,
		term: '',
		placeholder: __( 'Add or Find', 'the-events-calendar' ),
		name: '',
		icon: null,
		posts: [],
		isLoading: false,
		clearBlock: noop,
		setFocus: noop,
		onInputChange: noop,
		onCreateClick: noop,
		onItemClick: noop,
	};

	static propTypes = {
		isSelected: PropTypes.bool,
		term: PropTypes.string,
		placeholder: PropTypes.string,
		name: PropTypes.string,
		icon: PropTypes.object,
		posts: PropTypes.array,
		isLoading: PropTypes.bool,
		clearBlock: PropTypes.func,
		setFocus: PropTypes.func,
		onInputChange: PropTypes.func,
		onCreateClick: PropTypes.func,
		onItemClick: PropTypes.func,
	};

	constructor( props ) {
		super( props );
		this.inputRef = React.createRef();
	}

	componentDidMount() {
		const { addBlock, setSearchPostType, name, postType, setFocus } = this.props;
		addBlock( name );
		setSearchPostType( name, postType );
		setFocus( this.inputRef );
	}

	componentDidUpdate() {
		this.props.setFocus( this.inputRef );
	}

	componentWillUnmount() {
		const { clearBlock, name } = this.props;
		clearBlock( name );
	}

	renderItem = ( item ) => {
		const { title = {}, id } = item;
		const { rendered = '' } = title;

		return (
			<li
				key={ id }
				onClick={ this.props.onItemClick( item ) }
			>
				{ decode( rendered ) }
			</li>
		);
	};

	renderResults = () => {
		const { isSelected, term, isLoading, posts, onCreateClick } = this.props;

		if ( ! isSelected || isEmpty( term ) ) {
			return null;
		}

		if ( isLoading ) {
			return (
				<div className="tribe-editor__soc__results--loading">
					<Spinner />
				</div>
			);
		}

		return (
			<ul className="tribe-editor__soc__results">
				<li onClick={ onCreateClick }><strong>Create</strong>: { this.props.term }</li>
				{ posts.map( this.renderItem ) }
			</ul>
		);
	}

	render() {
		const { isSelected, icon, term, placeholder, onInputChange } = this.props;
		const containerClass = classNames(
			'tribe-editor__soc__input__container',
			{ 'tribe-editor__soc__input__container--active': isSelected },
		);

		return (
			<section className="tribe-soc__container">
				<div className={ containerClass }>
					{ icon }
					<input
						className="tribe-editor__soc__input"
						ref={ this.inputRef }
						value={ term }
						placeholder={ placeholder }
						onChange={ onInputChange }
					/>
				</div>
				{ this.renderResults() }
			</section>
		);
	}
}

export default SearchOrCreate;

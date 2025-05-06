import React, { ReactNode, Component } from 'react';
import { compose } from '@wordpress/compose';
import { withRegistryProvider } from '../../store';
import { WPDataRegistry } from '@wordpress/data/build-types/registry';

class Provider extends Component< {
	registry: WPDataRegistry;
	children?: ReactNode;
} > {
	private unsubscribe: Function | null;

	attachChangeObserver( registry: WPDataRegistry ) {
		// console.log('Provider.attachChangeObserver called');

		if ( this.unsubscribe ) {
			this.unsubscribe();
		}

		// @todo here run some logic that should run when the fields change.
		// const {getFields}:{getFields: Function} = registry.select('tec/classy');
		// let fields = getFields();

		this.unsubscribe = registry.subscribe( () => {
			// console.log('Provider.attachChangeObserver.listener called');
			// @todo here fetch the fields and update the state.
		} );
	}

	componentDidMount() {
		// console.log('Provider.componentDidMount called');
		this.attachChangeObserver( this.props.registry );
	}

	componentWillUnmount() {
		// console.log('Provider.componentWillUnmount called');
		if ( this.unsubscribe ) {
			this.unsubscribe();
		}
	}

	componentDidUpdate( prevProps ) {
		// console.log('Provider.componentDidUpdate called');
		const { registry } = this.props;

		if ( registry !== prevProps.registry ) {
			this.attachChangeObserver( registry );
		}

		// @todo deal with in-flight requests here
	}

	render() {
		// console.log('Provider.render called');
		const { children } = this.props;

		return children;
	}
}

export default compose( withRegistryProvider )( Provider );

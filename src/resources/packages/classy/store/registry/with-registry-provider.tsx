import { createHigherOrderComponent } from '@wordpress/compose';
import { withRegistry } from '@wordpress/data';
import { STORE_NAME, storeConfig } from '../store';
import { WPDataRegistry } from '@wordpress/data/build-types/registry';

const withRegistryProvider = createHigherOrderComponent(
	( WrappedComponent ) => {
		return withRegistry(
			( {
				registry,
				...props
			}: {
				registry: WPDataRegistry;
				props: any[];
			} ) => {
				// Register the Classy store.
				registry.registerStore( STORE_NAME, storeConfig );

				return <WrappedComponent registry={ registry } { ...props } />;
			}
		);
	},
	'witRegistryProvider'
);

export default withRegistryProvider;

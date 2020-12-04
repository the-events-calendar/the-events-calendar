<?php


namespace Tribe\Events\Test\Traits\Aggregator;


trait AggregatorMaker {
	public function restore_aggregator() {
		// your tear down methods here
		tribe_singleton( 'events-aggregator.service', \Tribe__Events__Aggregator__Service::class );
		tribe_singleton( 'events-aggregator.main', \Tribe__Events__Aggregator::class, ['load', 'hook'] );
	}

	public function make_aggregator_instance() {
		return (new class() extends \Tribe__Events__Aggregator {
			public function load() {
				// No operation to load the instances.
			}
			public function hook() {
				// No operation to load the hooks.
			}
		});
	}
}

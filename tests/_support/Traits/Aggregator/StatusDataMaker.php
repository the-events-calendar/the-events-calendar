<?php
/**
 * Created by PhpStorm.
 * User: luca
 * Date: 26/04/2018
 * Time: 14:53
 */

namespace Tribe\Events\Test\Traits\Aggregator;


trait StatusDataMaker {
	protected function make_status_data( array $overrides = [] ) {
		return array_merge( [
			'batch_hash' => '2389',
			'status' => 'success',
			'message' => 'Some message',
			'message_slug' => 'message-slug',
			'done' => 50,
		], $overrides );
	}
}
<?php

namespace TEC\Source_Updater;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use PhpParser\Node\Expr\FuncCall;

class Tribe_To_Tec_Assets extends AbstractRector {

	public function getNodeTypes(): array {
		return [FuncCall::class];
	}

	public function refactor( Node $node ) {
		$function_name = $this->getName($node->name);

		if(!in_array($function_name,['tribe_asset', 'tribe_assets'],true)){
			// Skip this, not interested.
			return null;
		}

		// Change the called function name from 'tribe_` to `tec_`.
		$new_function_name = str_replace('tribe_', 'tec_', $function_name);
		$node->name = new  Node\Name($new_function_name);

		return $node;
	}
}

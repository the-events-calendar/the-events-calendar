<?php

use TEC\Events\Custom_Tables\V1\Migration\String_Dictionary;

/**
 * @var string            $template_directory The absolute path to the Migration template root directory.
 * @var String_Dictionary $text               The text dictionary.
 * @var string            $phase              The current phase.
 */
include($template_directory.'/phase/cancel-in-progress.php');

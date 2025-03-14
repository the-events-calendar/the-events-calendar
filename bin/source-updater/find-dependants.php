<?php

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

if ( $argc < 1 ) {
	printf( "Usage: php {$argv[0]} <handle> [...<directory>]\n" );
	exit( 1 );
}

if ( empty( $argv[1] ) ) {
	printf( "Usage: php {$argv[0]} <handle> [...<directory>]\n" );
	print "Handle name cannot be empty\n";
	exit( 1 );
}

if ( $argc === 2 ) {
	// Assume the dir to scan is the current directory.
	$dir_realpaths = [ getcwd() ];
} else {
	$dirs          = array_slice( $argv, 2 );
	$dir_realpaths = [];
	foreach ( $dirs as $dir ) {
		$dir_realpath = realpath( $dir );
		if ( $dir_realpath === false ) {
			printf( "directory %s does not exist\n", $dir );
			exit( 1 );
		}
		$dir_realpaths[] = $dir_realpath;
	}
}

// Using PHPParser visit each directory recursively and find any script or style registered using one of the `wp_register_script`, `wp_enqueue_script`, `wp_register_style`,`wp_enqueue_style`, `tribe_asset`, `tribe_assets`, `tec_asset`, `tec_assets`

// Find all .php files in the directories that contain the handle.
$command = "grep -rli '{$argv[1]}' --include='*.php' " . implode( ' ', $dir_realpaths );
exec( $command, $files, $result_code );

// If there are no PHP files containing the handle in any directory, we're done.
if ( $result_code !== 0 ) {
	printf( "There was an error running the grep command\n" );
	exit( 1 );
}

if ( empty( $files ) ) {
	printf( "No files found containing the handle \"%s\"\n", $argv[1] );
	exit( 0 );
}

// Iterate over each file in the output and, using PHPParser, find any call to the following functions:
// * wp_register_script => 3rd argument
// * wp_register_style => 3rd argument
// * wp_enqueue_script => 3rd argument
// * wp_enqueue_style => 3rd argument
// * tribe_asset => 4th argument
// * tribe_assets => 3rd argument of each array in the 2nd argument
// * tec_asset => 4th argument
// * tec_assets => 3rd argument of each array in the 2nd argument

require_once __DIR__ . '/vendor/autoload.php';

$handle    = $argv[1];
$traverser = new NodeTraverser();
$visitor   = new class( $handle ) extends NodeVisitorAbstract {
	private static array $functions = [
		'wp_register_script' => 2,
		'wp_register_style'  => 2,
		'wp_enqueue_script'  => 2,
		'wp_enqueue_style'   => 2,
		'tribe_asset'        => 3,
		'tribe_assets'       => [ 1, 2 ],
		'tec_asset'          => 3,
		'tec_assets'         => [ 1, 2 ],
	];
	private string $handle;
	private ?string $currentFile = null;
	/**
	 * @var list<array{0: non-empty-string, 1: positive-int }> $matches
	 */
	private array $matches = [];

	public function __construct( string $handle ) {
		$this->handle = $handle;
	}

	public function setCurrentFile( string $file ) {
		$this->currentFile = $file;
	}

	private function checkDependencies( Node\Expr\Array_ $dependencies ): void {
		/** @var Node\ArrayItem $dependency */
		foreach ( $dependencies->items as $dependency ) {
			/** @var string $handle */
			$handle = $dependency->value->value;
			if ( $handle === $this->handle ) {
				$this->matches[] = [ $this->currentFile, $dependency->getLine() ];

				// No need to continue: we found it.
				return;
			}
		}
	}

	public function enterNode( Node $node ) {
		if ( ! ( $node instanceof FuncCall &&
		         isset( $node->name )
		         && $node->name instanceof Node\Name
		         && array_key_exists( $node->name->__toString(), self::$functions ) ) ) {
			// Not a function call we're looking for.
			return $node;
		}

		$function = $node->name->__toString();

		switch ( $function ) {
			case 'wp_register_script':
			case 'wp_register_style':
			case 'wp_enqueue_script':
			case 'wp_enqueue_style':
				if ( ! isset( $node->args[2] ) && $node->args[2]->value instanceof Node\Expr\Array_ ) {
					// Weird, but it's in production code: let it be.
					break;
				}
				$this->checkDependencies( $node->args[2]->value );
				break;
			case  'tribe_asset':
			case 'tec_asset':
				if ( ! ( isset( $node->args[3] ) && $node->args[3]->value instanceof Node\Expr\Array_ ) ) {
					// Weird, but it's in production code: let it be.
					break;
				}
				$this->checkDependencies( $node->args[3]->value );
				break;
			case 'tribe_assets':
			case 'tec_assets':
				// The 2nd argument is a list of assets to register.
				if ( ! isset( $node->args[1] ) && $node->args[1]->value instanceof Node\Expr\Array_ ) {
					// Weird, but it's in production code: let it be.
					break;
				}
				/** @var Node\ArrayItem $assets */
				foreach ( $node->args[1]->value->items as $asset ) {
					// The 3rd argument of each asset to register is a list of dependencies.
					if ( ! ( $asset->value instanceof Node\Expr\Array_
					         && isset( $asset->value->items[2] )
					         && $asset->value->items[2]->value instanceof Node\Expr\Array_ )
					) {
						// Weird, but it's in production code: let it be.
						continue;
					}
					$this->checkDependencies( $asset->value->items[2]->value );
				}
				break;
			default:
				throw new \Exception( 'Unexpected function' );
		}

		return $node;
	}

	public function getMatches(): array {
		return $this->matches;
	}
};

$traverser->addVisitor( $visitor );
$parser = ( new ParserFactory )->createForNewestSupportedVersion();
/** @var string $file */
foreach ( $files as $file ) {
	$code = file_get_contents( $file );
	$ast  = $parser->parse( $code );
	$visitor->setCurrentFile( $file );
	$traverser->traverse( $ast );
}

foreach ( $visitor->getMatches() as [$file, $line] ) {
	printf( "Found use at %s:%d\n", $file, $line );
}

<?php

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;

if ( $argc < 1 ) {
	printf( "Usage: php {$argv[0]} <dir_to_scan>\n" );
	exit( 1 );
}

// The directory to scan is the first argument.
$directory = $argv[1];

if ( ! is_dir( $directory ) ) {
	printf( "The path %s is not a valid directory\n", $directory );
	exit( 1 );
}

// Create a list of all the PHP files in the directory, recursively.
$files = new CallbackFilterIterator(
	new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $directory ),
		RecursiveIteratorIterator::SELF_FIRST,
		FilesystemIterator::SKIP_DOTS
	), function ( SplFileInfo $file ) {
	return str_ends_with( $file->getFilename(), '.php' );
} );

require_once __DIR__ . '/vendor/autoload.php';

// Use nikic/php-parser to scan each file for calls to the `tec_asset` function and print file and line to the terminal in file:line format.
$traverser = new NodeTraverser();
$visitor   = new class extends NodeVisitorAbstract {
	private ?SplFileInfo $currentFile = null;
	/**
	 * @var array<string,array{jsAssetFile: string, file: string, line: int}>
	 */
	private array $unregisteredCssAsset = [];
	/**
	 * @var array<string,true>
	 */
	private array $registeredAssets  = [];

	public function setCurrentFile( SplFileInfo $file ) {
		$this->currentFile = $file;
	}

	/**
	 * @return array<string,true>
	 */
	public function getRegisteredAssets(): array {
		return $this->registeredAssets;
	}

	public function enterNode( Node $node ) {
		if ( ! ( $node instanceof FuncCall &&
		         isset( $node->name )
		         && $node->name instanceof Node\Name
		         && in_array( $node->name->__toString(), [ 'tec_asset', 'tec_assets' ], true ) ) ) {
			// Not a function call we're looking for.
			return $node;
		}

		if ( $node->name->__toString() === 'tec_asset' ) {
			return $this->checkAssetCall( $node );
		}

		// The called function is the `tec assets` one.
		return $this->checkAssetsCall( $node );
	}

	private function checkAsset( string $path, Node $node ): void {
		if ( ! preg_match(
			'#(?P<path>.*)(\.js|\.css)$#',
			$path,
			$match
		) ) {
			return;
		}

		$extension = substr( $match[2], 1 );

		if (
			str_starts_with( $match[0], 'vendor' )
			|| str_starts_with( $match[0], 'node_modules' )
			|| str_starts_with( $match[0], 'common/node_modules' )
		) {
			// Assets loaded from vendor or node_modules don't need to be in ./build, but we still need to make sure they're not missing.
			$assetFile = '/' . $match[0];
		} else if ( str_starts_with( $match[0], 'app' ) ) {
			// The /app bundle will be packaged in the `/build/app` directory.
			$assetFile = '/build/' . $match[0];
		} else {
			$assetFile = "/build/{$extension}/{$match[0]}";
		}

		$assetFileRealpath = getcwd() . $assetFile;

		if ( ! is_file( $assetFileRealpath ) ) {
			printf(
				"Error at %s:%d\n└── Asset %s doesn't exist.\n",
				$this->currentFile->getRealPath(),
				$node->getLine(),
				'.' . $assetFile
			);
		}

		if ( $extension === 'js' ) {
			// If the file is a .js file, check if a `style-<asset>.css` file exists: if it exists, collect it for later checking.
			$basename = basename( $match[0]);
			$cssFile = str_replace( $basename, 'style-' . substr($basename,0,-3) . '.css', $assetFile );
			if(is_file(getcwd(). $cssFile)){
				// There is a style file: make sure it's registered along with the JS asset.
				$this->unregisteredCssAsset[ $cssFile ] = [
					'jsAssetFile' => $assetFile,
					'file'        => $this->currentFile->getRealPath(),
					'line'        => $node->getLine()
				];
			}
		} else {
			// The file is a CSS file: remove it from the unregistered CSS assets list.
			unset( $this->unregisteredCssAsset[ $assetFile ] );
		}
		$this->registeredAssets[$assetFile] = true;
	}

	/**
	 * Return an array of unregistered CSS assets.
	 *
	 * @since 6.13.0
	 *
	 * @return array<string,array{jsAssetFile: string, file: string, line: int}>
	 */
	public function getUnregisteredCssAssets(): array {
		return $this->unregisteredCssAsset;
	}

	private function checkAssetsCall( FuncCall $node ): Node {
		// The second argument will be an array of assets. Each asset is an array whose second argument is the asset path.
		if ( ! ( isset( $node->args[1] )
		         && $node->args[1]->value instanceof Node\Expr\Array_ ) ) {
			return $node;
		}

		/** @var Node\ArrayItem $asset */
		foreach ( $node->args[1]->value->items as $asset ) {
			/** @var string $path */
			$path = $asset->value->items[1]->value->value;
			if ( ! preg_match(
				'#(?P<path>.*)(\.js|\.css)$#',
				$path,
				$match
			) ) {
				continue;
			}

			$this->checkAsset( $path, $node );
		}

		return $node;
	}

	private function checkAssetCall( FuncCall $node ): Node {
		// The third argument is the asset path; if it's a .js or .css file, make sure it exists in relation to ./build.
		if ( isset( $node->args[2] ) && $node->args[2]->value instanceof Node\Scalar\String_ ) {
			$this->checkAsset( $node->args[2]->value->value, $node );
		}

		return $node;
	}
};
$traverser->addVisitor( $visitor );
$parser = ( new ParserFactory )->createForNewestSupportedVersion();

/** @var SplFileInfo $file */
foreach ( $files as $file ) {
	$code = file_get_contents( $file->getPathname() );
	$ast    = $parser->parse( $code );
	$visitor->setCurrentFile( $file );
	$traverser->traverse( $ast );
}

$registeredAssets = $visitor->getRegisteredAssets();
foreach ( $visitor->getUnregisteredCssAssets() as $cssFile => $cssFileData ) {
	if(isset($registeredAssets[$cssFile])){
		continue;
	}
	printf(
		"Warning at %s:%d\n└── JS Asset %s is registered, but CSS asset %s is not.\n",
		$cssFileData['file'],
		$cssFileData['line'],
		$cssFileData['jsAssetFile'],
		getcwd() . $cssFile
	);
}


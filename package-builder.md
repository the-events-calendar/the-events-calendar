# Package Builder work notes

## Finding redundant dependencies

The following commands can be used to find redundant dependencies when the `@wordpress/scripts` package is used as a dev dependency.

Check if the currently installed dependencies, all of them, are included in the `@wordpress/scripts` package:
```bash
node bin/listDependencies.js all | xargs -I{} node bin/checkIncluded.js @wordpress/scripts {} | grep -v NOT
```

Check if the currently installed `devDependencies` are included in the `@wordpress/scripts` package:
```bash
node bin/listDependencies.js dev | xargs -I{} node bin/checkIncluded.js @wordpress/scripts {} | grep -v NOT
```

Check if the currently installed `dependencies` are included in the `@wordpress/scripts` package:
```bash
node bin/listDependencies.js prod | xargs -I{} node bin/checkIncluded.js @wordpress/scripts {} | grep -v NOT
```

## Update scripts

The work includes a Rector-based script to update the source code automatically to the new version.
The script will:

* replace calls to the `tribe_asset` function with calls to the `tec_asset()` function.
* replace calls to the `tribe_assets` function with calls to the `tec_assets()` function.

If you did not already, install the `bin/source-updater` script dependencies:

```bash
(cd bin/source-updater && composer i)
```

Test the changes that would be applied by the update script:
```bash
bin/source-updater/vendor/bin/rector process --config=bin/source-updater/rector.php ./src --dry-run
```

Apply the changes to the source code:
```bash
bin/source-updater/vendor/bin/rector process --config=bin/source-updater/rector.php ./src
```

## Test scripts

Scan the source directory to find assets registered using the `tec_asset` or `tec_assets` function that are not available:

```bash
php bin/source-updater/find-broken-assets.php ./src
```

Scan a directory to find any asset registration (done using any `wp_`, `tribe_`, or `tec_` function)  that requires a handle as a dependency:

```bash
php bin/source-updater/find-dependants.php tribe-events-admin ./..
```

Scan the `src/resources/images` directory and find whether images are used or not and, if used, report what files are
using them:
```bash
node bin/listImagesByUse.js ./src/resources/images ./src
```

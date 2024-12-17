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

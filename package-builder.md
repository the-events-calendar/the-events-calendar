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

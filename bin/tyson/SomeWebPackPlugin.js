const {RawSource} = require('webpack-sources');

const pluginName = 'SomeWebPackPlugin';

class SomeWebPackPlugin {
  apply(compiler) {
    compiler.hooks.compilation.tap(pluginName, (compilation) => {
      compilation.hooks.processAssets.tap(
          {name: pluginName, stage: compiler.webpack.Compilation.PROCESS_ASSETS_STAGE_ADDITION},
          (assets) => {
            Object.entries(assets).forEach(([pathname, source]) => {
              if (!pathname.match(/(t|j)sx?$/)) {
                return;
              }
              console.log(`[${pluginName}] Doing things with file ${pathname}`);
              const updatedSource = source.source()
                .replace(/window\["__tyson_window\.(?<path>[^\]]*?)"]/gi, function(match){
                const path = match['path'];
                const pathFrags = path.split('.');
                const arrayPath= pathFrags.map(f => `['${f}']`);
                const nullSafeArrayPath = pathFrags.map(f => `?.[${f}]`);
                const jsonObject = '{}';
                return `window.${pathFrags[0]} = (window${nullSafeArrayPath} || JSON.parse(${jsonObject}); window${arrayPath}`;
              })
              assets[pathname] = new RawSource( updatedSource );
            });
          },
      );
    });
  }
}

module.exports = SomeWebPackPlugin;

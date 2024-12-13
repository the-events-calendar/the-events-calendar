echo "Moving the Webpack 4 directory out of the way to prevent conflicts."
mv node_modules/webpack /tmp/_webpack
mv node_modules/webpack-cli /tmp/_webpack-cli

echo "Moving configuration files out of the way."
mv webpack.config.js _webpack.config.js
mv babel.config.json _babel.config.json

echo "Building the JS with the correct Webpack version."
npx \
	--package="@tanstack/react-query" \
	--package="@wordpress/scripts@27.9.0" \
	--yes -- \
	wp-scripts build --webpack-src-dir=src/resources/packages/wizard/ --output-path=build/wizard

echo "Moving the Webpack 4 directory back to node_modules."
mv /tmp/_webpack node_modules/webpack
mv /tmp/_webpack-cli node_modules/webpack-cli

echo "Moving configuration files back."
mv _webpack.config.js webpack.config.js
mv _babel.config.json babel.config.json

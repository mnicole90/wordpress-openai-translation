const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require( 'path' );
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
	...defaultConfig,
	entry: {
		"editor": "./assets/js/editor.js",
		"block-editor": "./assets/js/block-editor.js"
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets/build' )
	}
};

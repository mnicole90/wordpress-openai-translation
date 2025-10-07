const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require( 'path' );
const MiniCssExtractPlugin = require("mini-css-extract-plugin");

module.exports = {
	...defaultConfig,
	entry: {
		"editor": "./assets/js/editor.js"
	},
	output: {
		filename: '[name].js',
		path: path.resolve( __dirname, 'assets/build' )
	}
};

const path = require( 'path' );
const ExtractTextPlugin = require( 'extract-text-webpack-plugin' );

// Set different CSS extraction for editor only and common block styles
const blocksCSSPlugin = new ExtractTextPlugin( {
	filename: './build/css/blocks.style.css'
} );
const editBlocksCSSPlugin = new ExtractTextPlugin( {
	filename: './build/css/editor-5-star-rating.css'
} );


// Configuration for the ExtractTextPlugin.
const extractConfig = {
	use: [
		{
			loader: 'raw-loader'
		},
		{
			loader: 'postcss-loader',
			options: {
				plugins: [ require( 'autoprefixer' ) ]
			}
		},
		{
			loader: 'sass-loader',
			query: {
				outputStyle:
					'production' === process.env.NODE_ENV ? 'compressed' : 'nested'
			}
		}
	]
};

const defaultConfig = {
	entry: {
		'./build/js/editor.five-star-block'  : './blocks/v2/index.js',
	},
	output: {
		path: path.resolve(__dirname),
		filename: '[name].js',
		library: [ 'wp', '[name]' ],
		libraryTarget: 'window',
	},
	devtool: 'production' !== process.env.NODE_ENV ? 'cheap-eval-source-map' : false,
	watch  : 'production' !== process.env.NODE_ENV,
   
	stats: { children: true },
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader'
				}
			},
			// {
			// 	test: /\.s?css$/, 
			// 	// test: /style\.s?css$/, 
			// 	use: blocksCSSPlugin.extract( extractConfig )
			// },
			{
				test: /\.s?css$/,
				// test: /editor\.s?css$/, 
				use: editBlocksCSSPlugin.extract( extractConfig )
			}
		]
	},
	externals: {
		'react': 'React',
		'react-dom': 'ReactDOM',
		'lodash': 'lodash',
		'wp':'wp',
		'wp.i18n': {
			window: [ 'wp', 'i18n' ]
		},
	},
	resolve: {
		// alias: {
		// 	GetwidControls: path.resolve( __dirname, 'src/controls/' ),
		// 	GetwidUtils   : path.resolve( __dirname, 'src/utils/'    ),
		// 	GetwidVendor  : path.resolve( __dirname, 'vendors/'      )
		// }
	},
	plugins: [
		blocksCSSPlugin, 
		editBlocksCSSPlugin,
	]
};

module.exports = (env) => {
	if (env && env.splitted) {
		// return buildSeparateFiles;
	}

	return defaultConfig;
};


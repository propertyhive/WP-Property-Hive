const path = require('path');

module.exports = {
  entry: {
    bundle: './includes/src/index.jsx',
  },
  externals: {
    react: 'React',
    '@wordpress/data': ['wp', 'data'],
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              compact: false,
              presets: [
                ['@babel/preset-env', { modules: false, targets: '> 5%' }],
                '@babel/preset-react'
              ],
            },
          },
        ],
      },
    ],
  },
  resolve: {
    extensions: ['.tsx', '.ts', '.js', '.jsx', '.json'],
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'includes/scripts'),
  },
};

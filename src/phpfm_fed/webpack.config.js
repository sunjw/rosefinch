const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyPlugin = require('copy-webpack-plugin');

// Extract CSS
const extractCSS = new MiniCssExtractPlugin({
  filename: 'styles.min.css'
});

// Copy file
const copyPlugin = new CopyPlugin({
  patterns: [{
      from: 'phpfm.config.js',
      to: '',
    }],
});

module.exports = {
  entry: './js/main.js',
  output: {
    path: __dirname + '/dist',
    filename: 'bundle.js'
  },
  module: {
    rules: [{
        test: /\.s?css$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader',
          'sass-loader'
        ]
      }, {
        test: /\.(woff|woff2|eot|ttf|otf)$/,
        type: 'asset/resource',
        generator: {
          filename: 'assets/[hash][ext]'
        }
      }
    ]
  },
  devServer: {
    static: __dirname
  },
  plugins: [
    extractCSS,
    copyPlugin
  ]
}

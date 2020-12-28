const MiniCssExtractPlugin = require('mini-css-extract-plugin');
// Extract CSS
const extractCSS = new MiniCssExtractPlugin({
  filename: 'styles.min.css'
});

module.exports = {
  entry: './js/main.js',
  output: {
    path: __dirname + '/dist',
    filename: 'bundle.js'
  },
  module: {
    rules: [{
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          'css-loader',
          'postcss-loader'
        ]
      }
    ]
  },
  plugins: [
    extractCSS
  ]
}

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CopyPlugin = require("copy-webpack-plugin");

// Extract CSS
const extractCSS = new MiniCssExtractPlugin({
  filename: 'styles.min.css'
});

// Copy file
const copyPlugin = new CopyPlugin({
  patterns: [{
      from: "phpfm.config.js",
      to: "",
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
      }
    ]
  },
  plugins: [
    extractCSS,
    copyPlugin
  ]
}

const path = require('path')
const {CleanWebpackPlugin} = require('clean-webpack-plugin')

module.exports = {
  plugins: [new CleanWebpackPlugin({cleanOnceBeforeBuildPatterns: ['js/chunks/*']})],
  output: {chunkFilename: 'js/chunks/[name].js?id=[chunkhash]'},
  resolve:{
    alias:{
      '@': path.resolve('resources/js'),
    },
  },
}

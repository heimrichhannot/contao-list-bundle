var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/js/')
    .addEntry('contao-list-bundle', './src/Resources/assets/js/contao-list-bundle.js')
    .setPublicPath('/public/js/')
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
var Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('src/Resources/public/js/')
    .addEntry('contao-list-bundle', './src/Resources/assets/js/contao-list-bundle.js')
    .setPublicPath('/public/js/')
    .setPublicPath('/bundles/heimrichhannotlistbundle/')
    .setManifestKeyPrefix('bundles/heimrichhannotlistbundle')
    .disableSingleRuntimeChunk()
    .addExternals({
            '@hundh/contao-utils-bundle': 'utilsBundle',
        }
    )
    .splitEntryChunks()
    .configureSplitChunks(function(splitChunks) {
        splitChunks.name =  function (module, chunks, cacheGroupKey) {
            const moduleFileName = module.identifier().split('/').reduceRight(item => item).split('.').slice(0, -1).join('.');
            return `${moduleFileName}`;
        };
    })
    .disableSingleRuntimeChunk()
    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
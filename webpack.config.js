const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

const isDevelopment = process.env.NODE_ENV !== 'production';

module.exports = {
    mode: isDevelopment ? 'development' : 'production',
    
    entry: {
        main: './src/js/main.js',
        admin: './src/js/admin.js',
        places: './src/js/places.js',
        styles: './src/scss/style.scss',
        'admin-styles': './src/scss/admin.scss',
        'places-admin': './src/scss/places-admin.scss',
        'places-styles': './src/scss/places.scss',
        'places-modal': './src/scss/places-modal.scss',
    },
    
    output: {
        path: path.resolve(__dirname, 'dist'),
        filename: 'js/[name].min.js',
        clean: true,
    },
    
    module: {
        rules: [
            // JavaScript
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [
                            ['@babel/preset-env', {
                                targets: {
                                    browsers: ['> 1%', 'last 2 versions', 'not dead']
                                },
                                useBuiltIns: 'usage',
                                corejs: 3
                            }]
                        ]
                    }
                }
            },
            
            // SCSS/CSS
            {
                test: /\.scss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    {
                        loader: 'css-loader',
                        options: {
                            sourceMap: isDevelopment,
                        }
                    },
                    {
                        loader: 'postcss-loader',
                        options: {
                            sourceMap: isDevelopment,
                            postcssOptions: {
                                plugins: [
                                    ['autoprefixer'],
                                    ['cssnano', {
                                        preset: ['default', {
                                            discardComments: { removeAll: true }
                                        }]
                                    }]
                                ]
                            }
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            sourceMap: isDevelopment,
                        }
                    }
                ]
            }
        ]
    },
    
    plugins: [
        new CleanWebpackPlugin(),
        new MiniCssExtractPlugin({
            filename: 'css/[name].min.css',
        }),
    ],
    
    optimization: {
        minimize: !isDevelopment,
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    format: {
                        comments: false,
                    },
                    compress: {
                        drop_console: !isDevelopment,
                    }
                },
                extractComments: false,
            }),
            new CssMinimizerPlugin(),
        ],
    },
    
    devtool: isDevelopment ? 'source-map' : false,
    
    stats: {
        colors: true,
        modules: false,
        children: false,
        chunks: false,
        chunkModules: false
    },
    
    performance: {
        hints: false,
        maxEntrypointSize: 512000,
        maxAssetSize: 512000
    }
};


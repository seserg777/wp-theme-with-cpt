const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');

const isDevelopment = process.env.NODE_ENV !== 'production';
const useBrowserSync = process.env.BROWSER_SYNC === 'true';

module.exports = {
    mode: isDevelopment ? 'development' : 'production',
    
    entry: {
        main: './src/js/main.js',
        admin: './src/js/admin.js',
        places: './src/js/places.js',
        styles: './src/scss/style.scss',
        theme: './src/scss/theme.scss',
        'admin-styles': './src/scss/admin.scss',
        'places-admin': './src/scss/places-admin.scss',
        'places-styles': './src/scss/places.scss',
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
        // BrowserSync - auto-reload browser on changes (only in watch mode).
        ...(useBrowserSync ? [
            new BrowserSyncPlugin({
                // Proxy your local WordPress site.
                proxy: {
                    target: 'https://wp.loc',
                    // Proxy WebSocket connections for AJAX.
                    ws: true,
                },
                // Port for BrowserSync UI.
                port: 3000,
                // Files to watch for changes.
                files: [
                    '**/*.php',
                    'dist/css/**/*.css',
                    'dist/js/**/*.js',
                ],
                // Delay reload after file change (ms).
                reloadDelay: 0,
                // Show notifications in browser.
                notify: true,
                // Open browser automatically.
                open: false,
                // Don't change URLs in HTML.
                rewriteRules: [],
                // CORS settings for AJAX.
                cors: true,
                // Middleware for handling requests.
                middleware: [],
            }, {
                // Prevent BrowserSync from reloading the page.
                // webpack will inject CSS changes without reload.
                reload: false,
            }),
        ] : []),
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


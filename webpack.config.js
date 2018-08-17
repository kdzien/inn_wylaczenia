const path = require('path');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');


module.exports = {
    entry: "./app/src/js/main.js",
    output: {
      path: path.resolve(__dirname, "dist"),
      filename: "bundle.js",
      publicPath: "/dist"
    },
    mode: "development",
    devtool: "source-map",
    watch:true,
    module: {
      rules: [
        {
            test: /\.css$/,
            use: ['style-loader', 'css-loader']
        },
        {
          test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: [["env"]]
                    }
                }
        },
        {
          test: /\.scss$/,
          use: [
            {
              loader: "style-loader" // creates style nodes from JS strings
            },
            {
              loader: "css-loader" // translates CSS into CommonJS
            },
            {
              loader: "sass-loader" // compiles Sass to CSS
            }
          ]
        }
      ]
    },
    plugins: [
        new BrowserSyncPlugin({
          // browse to http://localhost:3000/ during development,
          // ./public directory is being served
          host: 'localhost',
          port: 4000,
          files: ['./dist/*.html'],
          server: { baseDir: ['dist'] }
        })
    ]
  };
const path = require("path");
const webpack = require("webpack");
const UglifyJSPlugin = require("uglifyjs-webpack-plugin");
const { CleanWebpackPlugin } = require("clean-webpack-plugin");
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");

module.exports = (env) => {
  const pathToSave = env.production ? "./admin/js/prod" : "./admin/js/dev";

  return {
    entry: path.resolve(__dirname, "./react-app/index.js"),
    module: {
      rules: [
        {
          test: /\.(js|jsx)$/,
          exclude: /node_modules/,
          use: ["babel-loader"],
        },
      ],
    },
    resolve: {
      extensions: ["*", ".js", ".jsx"],
    },
    output: {
      path: path.resolve(__dirname, pathToSave),
      filename: "App.js",
    },
    plugins: [
      new webpack.HotModuleReplacementPlugin(),
      new UglifyJSPlugin(),
      new CleanWebpackPlugin(),
      new BrowserSyncPlugin({
        proxy: "http://aulasplugin.local/",
        port: 3000,
        files: ["**/*.php"],
        ghostMode: {
          clicks: false,
          location: false,
          forms: false,
          scroll: false,
        },
        injectChanges: true,
        logFileChanges: true,
        logLevel: "debug",
        logPrefix: "wepback",
        notify: true,
        reloadDelay: 0,
      }),
    ],
  };
};

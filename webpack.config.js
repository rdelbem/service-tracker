module.exports = {
  entry: "./react-app/index.js",
  output: {
    path: __dirname,
    filename: "./admin/js/App.js",
  },
  module: {
    loaders: [
      {
        test: /.js$/,
        loader: "babel-loader",
        exclude: /node_modules/,
        options: {
          presets: [["env", "react"]],
          plugins: ["transform-class-properties"],
        },
      },
    ],
  },
};

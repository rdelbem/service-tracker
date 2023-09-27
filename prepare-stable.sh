#!/bin/bash

# copy all files to new temp dir
ncp . ../service-tracker-olmc

# got to new temp dir
cd ../service-tracker-olmc

# remove unwanted dev files
rimraf .babelrc
rimraf .gitignore
rimraf .git
rimraf package.json
rimraf package-lock.json
rimraf README.md
rimraf prepare-stable.sh
rimraf webpack.config.js
rimraf react-app
rimraf node_modules
rimraf .github

# return to the project dir
cd -
# Tyson

A helper script to build StellarWP projects using `@wordpress/scripts`

## This is not a wrapper of `@wordpres/scripts`

Tyson does not wrap `@wordpress/scripts` or its commands.
It provides a utility to set up a customized `webpack.config.js` for your project to allow you to use `@wordpress/scripts`
to build and package it.

## Installation

To install `@stellarwp/tyson`, you can use `npm` or `yarn`.

Navigate to your project directory and run one of the following commands:

Using `npm`:
```bash
npm install @stellarwp/tyson --save-dev
```

Using `yarn`:
```bash
yarn add @stellarwp/tyson --dev
```

While installing Tyson, and with it the `@wordpress/scripts` package, you might run into issues with incompatible
dependencies, especially if your project is using old versions of libraries used by `@wordpress/scripts`.
Dealing with these incompatibilities is not something Tyson can do for you: each project is different and has its
own quirks.

Take courage in knowing that, once you've solved the issues, the hardest part is likely done.

## Usage

Initialize your custom `webpack.config.js` file using the default configuration:

```bash
bin/node_modules/tyson init
```

The default configuration will scaffold a `webpack.config.js` file that will allow you to customize the behaviour
of the [`@wordpress/scripts` library][1]. The created `webpack.config.js` file will **not** customize the behaviour in
any way, but it will provide commented examples of how you could do it.
By default `@wordpress/scripts` will build [from the `/src` directory to the `/build` one][2].

### Configuration presets

As a setup tool, the power of Tyson rests in the concept of **configuration presets**.

A configuration preset is a collection of customizations accommodating the structure and organization of a specific StellarWP
team approach.

If you want to set up a customized `webpack.config.js` file a project from The Events Calendar, then you might run the
command with:

```bash
node_modules/bin/tyson init --preset TEC
```

If you want to list what configuration presets are available run the `preset-list` command:

```
node_modules/bin/tyson preset-list
```

After the `init` command run you will be left with a custom `webpack.config.js` file where each customization is
extensively commented.

### API

TODO

[1]: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/#provide-your-own-webpack-config
[2]: https://developer.wordpress.org/block-editor/reference-guides/packages/packages-scripts/#build

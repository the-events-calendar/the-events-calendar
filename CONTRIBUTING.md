# How to contribute

We happily review/accept third-party Pull Requests. To help get your
patches adopted into our plugins, there's a few bits of info that are
worth knowing.

## Standards

We have a set of [coding standards](http://moderntribe.github.io/products-engineering/)
that we follow and encourage others to follow as well. When you submit
Pull Requests, you'll probably notice a friendly bot - `tr1b0t` - that
comments on your PR and suggests changes. Be gentle with him, he's only
trying to help. He also has a pretty good idea about what we'd like to
see in terms of code formatting, so don't ignore him.

## Gulp

We compress/uglify our CSS and JS files using [Gulp](http://gulpjs.com/)
via our very own [`product-taskmaster`](https://github.com/moderntribe/product-taskmaster)
repo - which is a collection of gulp tasks. Here's how you get rolling
with that.

### Prerequisites

#### Install Node.js

If you don't already have Node.js installed, please do so first:

[Download Node.js](http://nodejs.org/download/)

#### Install Gulp

The only requirement for Gulp is the Gulp CLI (Command Line Utility). If
you don't already have that installed, install it globally. You can do
that with the npm `-g` flag.

```
npm install -g gulp-cli
```

#### Install all the things

With all of those in place, simply run:

```
npm install
```

If you run into any issues with some of the tasks down the road, run npm
rebuild and try again.

```
npm rebuild
```

### Using Gulp

Our gulp tasks are documented in our [product-taskmanager README](https://github.com/moderntribe/product-taskmaster#gulp-tasks).

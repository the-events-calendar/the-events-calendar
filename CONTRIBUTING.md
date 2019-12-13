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

#### get the submodules

We rely on a few submodules, you'll need those to continue.

```
git submodule update --init --recursive
```

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

#### Install all the NPM packages

With all of those in place, run:

```
npm install
```

If you run into any issues with some of the tasks down the road, run npm
rebuild and try again.

```
npm rebuild
```

#### Install composer

We use composer for some critical packages. Please [install composer](https://getcomposer.org/download/) (if you do not already have it).

#### Install composer packages

With composer in place, let's get those composer packages:
```
composer install
```

#### Install npm and composer packages in common

One of our submodules is Tribe Common, which is a shared set of libraries in use by our plugins (see also [Event Tickets](https://github.com/moderntribe/event-tickets)). It uses npm and composer as well, so, let's get those as well.

```
cd common
npm install
composer install
```

### Using Gulp

Our gulp tasks are documented in our [product-taskmanager README](https://github.com/moderntribe/product-taskmaster#gulp-tasks).

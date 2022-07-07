# How to contribute

We happily review/accept third-party Pull Requests. To help get your
patches adopted into our plugins, there's a few bits of info that are
worth knowing.

## Standards

We have a set of [coding standards](http://the-events-calendar.github.io/products-engineering/)
that we follow and encourage others to follow as well. When you submit
Pull Requests, you'll probably notice a friendly bot - `tr1b0t` - that
comments on your PR and suggests changes. Be gentle with him, he's only
trying to help. He also has a pretty good idea about what we'd like to
see in terms of code formatting, so don't ignore him.

## Prepping the repository for usage/development

### Prerequisites

* [composer](https://getcomposer.org/download/) (composer v1 required)
* [node](https://nodejs.org/download/)
* [nvm](https://github.com/nvm-sh/nvm)

### Build steps

#### Get the submodules

We rely on a few submodules, you'll need those to continue.

```
git submodule update --init --recursive
```

#### Install composer packages

_Note: you'll need to do this for the common directory and the root directory._

```
cd common
composer install

cd ..
composer install
```

#### Install and build all the npm assets

_Note: you'll need to do this for the common directory and the root directory._

```
cd common
nvm use
npm ci
npm run build

cd ..
nvm use
npm ci
npm run build
```

##### Building specific assets

Our `npm run build` command is simply an alias to running both our Gulp and webpack builds. You can run them individually like so:

```
npm run build:gulp
npm run build:webpack
```

We also provide `gulp watch` tasks, so feel free to leverage those while tinkering with CSS or JS to run the build process automatically when you change files.

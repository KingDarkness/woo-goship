# Giới thiệu

Plugin kết nối goship cho woocommerce
[goship.io](https://goship.io)

## Changelog

### 1.0

- First release.
- Upload to WordPress.org

## dev

Clone this repository in your plugins folder

```bash
$ composer install
```

Activate the plugin

frontend

```bash

$ npm run dev
```

## build for production

```bash
$ rm -rf vendor
$ composer install --prefer-dist --no-dev
$ npm run build
$ cd ..
$ zip -r woo-goship.zip  ./woo-goship -x ./woo-goship/node_modules/\* -x ./woo-goship/.git/\* -x ./woo-goship/.github/\*

```

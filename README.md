# Contao List Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/travis/heimrichhannot/contao-list-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-list-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-list-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-list-bundle)

This bundle offers a generic list module to use with arbitrary contao entities containing standard list handling like pagination, sorting, and filtering.

## Installation

Install via composer: `composer require heimrichhannot/contao-encore-bundle` and update your database.

### Installation with frontend assets using webpack

If you want to add the frontend assets (JS & CSS) to your project using webpack, please
add [foxy/foxy](https://github.com/fxpio/foxy) to the depndencies of your project's `composer.json` and add the following to its `config` section:

```json
"foxy": {
  "manager": "yarn",
  "manager-version": "^1.5.0"
}
```

Using this, foxy will automatically add the needed yarn packages to your project's `node_modules` folder.

If you want to specify which frontend assets to use on a per page level, you can use [heimrichhannot/contao-encore-bundle](https://github.com/heimrichhannot/contao-encore-bundle).

## Concepts

### Inheritable list configuration

Since list configuration can be lots of data sometimes we decided to outsource it into a dedicated DCA entity.
These entities can be assigned to one or even multiple list modules in a reusable way.

In addition it's possible to create list configs that inherit from other list configs.
Hence overriding a single option while keeping everything else is possible.

### List config elements

Every list config can have one or more list config elements. These are designed to specify things that can occur multiple times (e.g. because there are many fields of one type).

Currently available list config element types:

Type  | Description
------|------------
image | Configure the output of one or more image fields separately (image size, placeholder handling, ...)

## Technical Instructions

### Masonry

#### Template

##### Stamps

Stamp content is found in `masonryStampContentElements` template variable.

```
masonryStampContentElements => [
    0 => [
        "content" => "<div>...</div>" // The rendered block
        "class" => "stamp-item ..." // The given css classes 
    ],
    ...
]
```

Output example (Twig):

```
{% for element in masonryStampContentElements %}
    <div class="stamp-item {{ element.class }}">
        {{ element.content|raw }}
    </div>
{% endfor %}
```

> The stamp item must use the css class `stamp-item` to be interpreted as stamp. 
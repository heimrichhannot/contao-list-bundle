# Contao List Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-list-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-list-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-list-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-list-bundle?branch=master)

This bundle offers a generic list module to use with arbitrary contao entities containing standard list handling like pagination, sorting, and filtering.

It makes it possible to generate lists not only for events, news or faq's but with every DCA you like.

## Features
- generic list module to use with arbitrary contao entities
- support all form of standard list handling like pagination, sorting, filtering
- works together with filter bundle
- inheritable list configurations
- template are build in twig
- support for [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle)

## Usage

### Install

1. Install via composer (`composer require heimrichhannot/contao-list-bundle`) or contao manager
1. Update your database

Recommendations:
- use this bundle together with [Reader Bundle](https://github.com/heimrichhannot/contao-reader-bundle).
- use [Encore Bundle](https://github.com/heimrichhannot/contao-encore-bundle) for managing your frontend assets

### Usage

**HINT: You can do the following steps for a basic list setup also in an interactive command. Simply run `vendor/bin/contao-console huh-list:make` in your contao root.**

1. Setup a filter (see [Filter Bundle](https://github.com/heimrichhannot/contao-filter-bundle) setup)
1. Create a list config (System -> List config)
    * To add list elements like images, videos, slider etc, add list config elements (see Concepts -> List config elements for explanation)
1. Create a list frontend module and output it, where you like it

## Concepts

### Inheritable list configuration

Since list configuration can be lots of data sometimes we decided to outsource it into a dedicated DCA entity.
These entities can be assigned to one or even multiple list modules in a reusable way.

In addition it's possible to create list configs that inherit from other list configs.
Hence overriding a single option while keeping everything else is possible.

### The Item class

Every database record output in a list (e.g. an event) is modelled and wrapped by the `Item` class. The concrete class is `DefaultItem`. You can imagine
the item as a kind of ORM (object-relational-mapping).

The most important properties of an item are the arrays `raw` and `formatted` which also can be iterated in the list item template:

- `raw`: contains the raw database values
- `formatted`: contains the formatted representation of the raw values

Example: Let's say a database record has a field `startDate` which holds a unix timestamp of the date chosen in the backend.
Then `raw` contains this unix timestamp and `formatted` contains the pretty printed date according to the `dateFormat` set in
the contao settings, i.e. "2020-09-12".

The list bundle uses the method `FormUtil::prepareSpecialValueForOutput()` in [heimrichhannot/contao-utils-bundle](https://github.com/heimrichhannot/contao-utils-bundle)
for handling special values. It supports a wide range of types of special values:
- date/time fields
- arrays
- fields with `options`, `options_callback` and entries in the DCA's `reference` key
- binary fields (files, images, ...)
- ...

You can access both of these arrays in your list item twig template as you normally would in twig:

```twig
{% for field, value in raw %}
{% endfor %}

{% for field, value in formatted %}
{% endfor %}
```

**CAUTION:** By default all values of a database record are formatted and accessible in the item template. As you can imagine
if some of the fields have thousands of options, the process of formatting can take some time and can reduce the peformance
of your website. **Hence you always should limit the formatted fields and only format these you really need.** You can adjust that
in the list configuration (field `limitFormattedFields`).

For convenience reasons you can also access the field values like so in your twig template:

```twig
{{ fieldname }}
```

If you configured the field `fieldname` to be formatted, it will contain the formatted value. If not, the raw one. If
it's formatted, you can still access its raw value by using:

```twig
{{ raw.fieldname }}
```

### List config elements

Every list config can have one or more list config elements. These are designed to specify things that can occur
multiple times (e.g. because there are many fields of one type).

Currently available list config element types:

Type          | Description
--------------|------------
image         | Configure the output of one or more image fields separately (image size, placeholder handling, ...)
tags          | Output one or more tag fields based on [codefog/tags-bundle](https://github.com/codefog/tags-bundle).
related items | Output related items based on given tags (needs [codefog/tags-bundle](https://github.com/codefog/tags-bundle)) or categories (needs [heimrichhannot/contao-categories-bundle](https://github.com/heimrichhannot/contao-categories-bundle)).

#### Image

You can add images either as formatted value of, if you also like to have additional features like image size processing
or automatic placeholders if no image is set, you can use the *image list config element*.

After the configuration you can output it as follows in your item template:

```twig
{% if images|default and images.myImage|default %}
    {{ include('@HeimrichHannotContaoUtils/image.html.twig', images.myImage) }}
{% endif %}
```

**IMPORTANT:** Note that by default the generated picture elements are added to an array called `images`. If your DCA
contains a field with the same name, you need to specify a different container name like e.g. `resizedImages`
(using `overrideTemplateContainerVariable`).

## Documentation

[Developer documentation](docs/developers.md)

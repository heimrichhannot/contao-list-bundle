# Contao List Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/travis/heimrichhannot/contao-list-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-list-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-list-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-list-bundle)

This bundle offers a generic list module to use with arbitrary contao entities containing standard list handling like pagination, sorting, and filtering.

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
# Contao List Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/travis/heimrichhannot/contao-list-bundle/master.svg)](https://travis-ci.org/heimrichhannot/contao-list-bundle/)
[![](https://img.shields.io/coveralls/heimrichhannot/contao-list-bundle/master.svg)](https://coveralls.io/github/heimrichhannot/contao-list-bundle)

This bundle offers a generic list module to use with arbitrary contao entities containing standard list handling like pagination, sorting, and filtering.



## Technical Introductiona

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
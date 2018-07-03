# Contao List Bundle

[![](https://img.shields.io/packagist/v/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![](https://img.shields.io/packagist/dt/heimrichhannot/contao-list-bundle.svg)](https://packagist.org/packages/heimrichhannot/contao-list-bundle)
[![Build Status](https://travis-ci.org/heimrichhannot/contao-list-bundle.svg?branch=master)](https://travis-ci.org/heimrichhannot/contao-list-bundle)
[![Coverage Status](https://coveralls.io/repos/github/heimrichhannot/contao-list-bundle/badge.svg?branch=master)](https://coveralls.io/github/heimrichhannot/contao-list-bundle?branch=master)

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

### Templates (list and item)

There are two ways to define your templates. 

#### 1. By Prefix

The first one is to simply deploy twig templates inside any `templates` or bundles `views` directory with the following prefixes:

** list template prefixes**

- `list_`

** item template prefixes**

- `list_item_`
- `item_`
- `news_`
- `event_`

**More prefixes can be defined, see 2nd way.**

#### 2. By config.yml

The second on is to extend the `config.yml` and define a strict template:

**Plugin.php**
```
<?php

class Plugin implements BundlePluginInterface, ExtensionPluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        …
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionConfig($extensionName, array $extensionConfigs, ContainerBuilder $container)
    {
        return ContainerUtil::mergeConfigFile(
            'huh_list',
            $extensionName,
            $extensionConfigs,
            __DIR__ .'/../Resources/config/config.yml'
        );
    }
}
```

**config.yml**
```
huh:
    list:
        templates:
            list:
                - { name: default, template: "@HeimrichHannotContaoList/list/list_default.html.twig" }
                - { name: table_default, template: "@HeimrichHannotContaoList/list/list_table_default.html.twig" }
            list_prefixes:
                - list_(?!item)
            item:
                - { name: default, template: "@HeimrichHannotContaoList/item/list_item_default.html.twig" }
                - { name: table_default, template: "@HeimrichHannotContaoList/item/list_item_table_default.html.twig" }
            item_prefixes:
                - list_item_
                - item_
                - news_
                - event_
```

### Custom ConfigElementTypes

You can add custom ConfigElementTypes by creating a class which inherits from `ConfigElementType` interface and register it in the list config file. 

```yaml
huh.list.config_element_types:
  - { name: image, class: HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType }
```

In the most cases you also need to add a palette for the ConfigElementType to the `tl_list_config_element` table dca.

#### Selector fields
A typical scenario to use ConfigElementTypes is when you want to add additional template variables, if list item has a specific property, for example an image, an video etc.  There are already two general dca fields defined for that purpose, `typeSelectorField` (the field containing a booloan selector for the type, like "addImage") and `typeField` (the field containing the reference to the type, like "singleSRC").
You can also customize the labels for this field by setting them in the messages.[local].yaml. The message ids are `huh.list.tl_list_config_element.field.[field].[type].name` and `huh.list.tl_list_config_element.field.[field].[type].desc`.

Example:

```yaml
# message.de.yaml from heimrichhannot/contao-youtube-bundle

huh.list.tl_list_config_element.field:
  typeSelectorField.youtube:
    name: YouTube Selektor Element
    desc: Wählen Sie hier das Feld aus, welches den boolschen Selektor für das YouTube Feld enthält.
  typeField.youtube:
    name: YouTube Field
    desc: Wählen Sie hier das Feld aus, welches die ID des YouTube Video enthält.
```

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
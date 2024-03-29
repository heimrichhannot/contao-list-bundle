# Developer documentation

## PHP Events

Following events are dispatched using [Symfony Event Dispatcher](https://symfony.com/doc/current/event_dispatcher.html).

Event                                              | Class | Description
-------------------------------------------------- | ----- | -----------
huh.list.event.list_after_parse_items              | ListAfterParseItemsEvent
huh.list.event.list_after_render                   | ListAfterRenderEvent
huh.list.event.list_before_parse_items             | ListBeforeParseItemsEvent
huh.list.event.list_before_render                  | ListBeforeRenderEvent
huh.list.event.item_before_render                  | ListBeforeRenderItemEvent
huh.list.event.list_compile                        | ListCompileEvent
huh.list.event.list_modify_query_builder           | ListModifyQueryBuilderEvent
huh.list.event.list_modify_query_builder_for_count | ListModifyQueryBuilderForCountEvent
FQCN of event class | ListBeforeApplyConfigElementsEvent | Allow to customize list configs elements before applying to an item (add, remove or modify). 

## JavaScript Events

Event | Description
----- | -----------
huh.list.list_update_complete | Is dispatched after the list was asynchronous updated through filter bundle. 
huh.list.ajax-pagination-loading | Is dispatched when ajax page loading is triggerd.
huh.list.ajax-pagination-loaded | Is dispatched when ajax page loading finished.
huh.list.modal_show | Is dispatched before a modal is opened and contains the modal element (`event.detail.modalElement`) and the modal css id (`event.detail.modalId`).
huh.list.modal_link_clicked | Is dispatched when the modal link is clicked. Passes the link element (`event.detail.linkElement`). Allows to stop opening the modal (set `event.detail.openModal` to false) or delay the opening of the modal (set `event.detail.timeout` in milliseconds).
huh.list.modal_load_error | Is dispatched when the modal ajax request fails. Passes status code (`event.detail.statusCode`), status text (`event.detail.statusText`), response (`event.detail.response`), respnse text (`event.detail.responseText`) and response url (`event.detail.url`).

## Create custom list config element types
  
1. Create a class that implements `HeimrichHannot\ConfigElementTypeBundle\ConfigElementType\ConfigElementTypeInterface`
1. Register the class as service with service tag `huh.list.config_element_type`
1. Add a friendly type name (translation) into the `$GLOBALS['TL_LANG']['tl_list_config_element']['reference']` variable

    ```php
    $lang['reference'][\HeimrichHannot\ListBundle\ConfigElementType\ImageConfigElementType::TYPE] = 'Image';
    ```
   
   
## Templates (list and item)

There are two ways to define your templates. 

### 1. By Prefix

The first one is to simply deploy twig templates inside any `templates` or bundles `views` directory with the following prefixes:

** list template prefixes**

- `list_`

** item template prefixes**

- `list_item_`
- `item_`
- `news_`
- `event_`

**More prefixes can be defined, see 2nd way.**

### 2. By config.yml

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

### Masonry

#### Stamps

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

## Accessibility

### Ajax pagination

By default, a list that gets items appended through ajax pagination, is marked as live region. Following attributes are added to the items wrapper element: `aria-busy="false" aria-live="polite" aria-relevant="additions text" aria-atomic="false"`. While loading, the `aria-busy` attribute is set to true. 

You can customize the bahavior with data attributes on the `pagination_list_ajax` list template:

Data attribute | Example | Description
-------------- | ------- | -----------
`data-enable-screen-reader-message` | data-enable-screen-reader-message="true" | Add an screen reader message within the list. Default: `"false"`
`data-screen-reader-message` | data-screen-reader-message="<span class=\"sr-only\">Es wurden neue Einträge zur Auflistung hinzugefügt.</span>" | A custom screen reader message. Default: `<span class="sr-only">Es wurden neue Einträge zur Liste hinzugefügt.</span>`
`data-disable-live-region` | data-disable-live-region="true" | Disables the live region functionality completely. Use this option if you implement a custom system. You can use the huh.list.ajax-pagination-loading and huh.list.ajax-pagination-loaded events. 


## Customizations

### Customize output of ajax pagination 

By default, when using ajax pagination, the pages are renderes by the server and the client just pick the item elements out
of the return value. If you need the complete content of the .items wrapper, add the mixedContent data attribute to the wrapper element.

```php
function onListBeforeRenderEvent(ListBeforeRenderEvent $event, Request $request): void
 {
    // Check conditions, add custom conditions for your project
    $request = $this->requestStack->getCurrentRequest();
    if (!$event->getListConfig()->addAjaxPagination
        || !$request->isXmlHttpRequest()
        || !$request->headers->has('Huh-List-Request')
        || 'Ajax-Pagination' != $request->headers->get('Huh-List-Request')
    ) {
        return;
    }

    $templateData = $event->getTemplateData();
    $templateData['dataAttributes'] = ' '.trim(($templateData['dataAttributes'] ?? '').' data-mixed-content="1"');
    $event->setTemplateData($templateData);
 }
```
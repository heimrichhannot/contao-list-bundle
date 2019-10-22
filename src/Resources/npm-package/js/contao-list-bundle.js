class ListBundle {

    static init() {
        ListBundle.initPagination();
        ListBundle.initMasonry();
        ListBundle.initEvents();
    }

    static initEvents() {
        document.addEventListener('filterAjaxComplete', function(e) {
            ListBundle.updateList(e.detail);
        });
    }

    static initPagination() {
        document.querySelectorAll('.huh-list .ajax-pagination').forEach(function(ajaxPagination) {
            import(/* webpackChunkName: "jscroll" */ 'jscroll').then(() => {
                let list = ajaxPagination.closest('.huh-list'),
                    $items = jQuery(list.querySelectorAll('.items')),
                    wrapper = list.querySelector('.wrapper'),
                    id = '#' + wrapper.getAttribute('id');

                jQuery(wrapper).jscroll({
                    loadingHtml: '<div class="loading"><span class="text">Lade...</span></div>',
                    nextSelector: '.ajax-pagination a.next',
                    autoTrigger: $items.data('add-infinite-scroll') == 1,
                    contentSelector: id,
                    callback: function() {
                        let $jscrollAdded = jQuery(this),
                            $newItems = $jscrollAdded.find('.item');

                        $newItems.hide();

                        import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(({default: imagesLoaded}) => {
                            imagesLoaded($newItems, function(instance) {
                                $items.append($newItems.fadeIn(300));

                                if ($items.attr('data-add-masonry') === "1") {
                                    import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(function() {
                                        ListBundle.initMasonry();
                                    });

                                    return;
                                }

                                // remove item counters...
                                $items.find('.item').removeClass((index, cssClass) => {
                                    let matches = cssClass.match(/item_\d+/g);

                                    if (matches instanceof Array && matches.length > 0) {
                                        return matches[0];
                                    }
                                });

                                //... and readd them again
                                $items.find('.item').each(index => {
                                    let $item = $(this),
                                        itemIndex = index + 1;

                                    $(this).addClass('item_' + itemIndex).removeClass('odd even first last');

                                    // odd/even
                                    if (itemIndex % 2 == 0) {
                                        $item.addClass('even');
                                    } else {
                                        $item.addClass('odd');
                                    }

                                    // add first and last
                                    if (itemIndex == 1) {
                                        $item.addClass('first');
                                    }

                                    if (itemIndex == $items.find('.item').length) {
                                        $item.addClass('last');
                                    }
                                });

                                if ($jscrollAdded.find('.pagination').length > 0) {
                                    $jscrollAdded.closest('.jscroll-inner').find('> .pagination').remove();
                                    $jscrollAdded.find('.pagination').appendTo($jscrollAdded.closest('.jscroll-inner'));
                                } else {
                                    $jscrollAdded.find('.ajax-pagination').appendTo($jscrollAdded.closest('.jscroll-inner'));
                                }

                                $jscrollAdded.remove();
                            });
                        });
                    }
                });
            });
        });
    }

    static initMasonry() {
        if (document.querySelectorAll('.huh-list .items[data-add-masonry="1"]').length < 1) {
            return;
        }

        import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(({default: Masonry}) => {
            import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(({default: imagesLoaded}) => {
                document.querySelectorAll('.huh-list .items[data-add-masonry="1"]').forEach(function(items, index, list) {
                    let options = {
                            itemSelector: '.item',
                            stamp: '.stamp-item'
                        },
                        listOptions = items.getAttribute('data-masonry');

                    if (listOptions !== null && listOptions !== '') {
                        options = Object.assign({}, options, JSON.parse(listOptions));
                    }

                    imagesLoaded(items, function(instance) {
                        let msnry = new Masonry(items, options);
                        msnry.layout();
                    })
                });
            });
        });
    }

    static updateList(target) {
        let data = JSON.parse(target.getAttribute('data-response')),
            id = target.getAttribute('data-list'),
            list = document.querySelector(id).parentNode;

        list.outerHTML = data.list;
    }
}

export {ListBundle};
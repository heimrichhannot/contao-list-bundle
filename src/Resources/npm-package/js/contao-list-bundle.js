class ListBundle {

    static init() {
        ListBundle.initPagination();
        ListBundle.initMasonry();
        ListBundle.initEvents();
        ListBundle.initModal();
    }

    static initEvents() {
        document.addEventListener('filterAjaxComplete', function(e) {
            ListBundle.updateList(e.detail);
        });
    }

    static initModal() {
        // only bootstrap is supported at the moment
        if ('undefined' === typeof window.jQuery) {
            return;
        }

        const currentUrl = location.href,
            lists = document.querySelectorAll('.huh-list .items[data-open-list-items-in-modal="1"]');

        if (lists.length < 1) {
            return;
        }

        // modal event listeners for history changing
        document.querySelectorAll('.huh-list .items[data-open-list-items-in-modal="1"]').forEach((list) => {
            const modalId = 'modal-' + list.parentNode.getAttribute('id');

            window.jQuery('#' + modalId).on('hidden.bs.modal', (e) => {
                history.pushState({
                    modalId: modalId
                }, '', currentUrl);
            });
        });

        // catch browser back button
        addEventListener('popstate', (e) => {
            window.jQuery('#' + e.state.modalId).modal('hide');
        });

        // modal links
        utilsBundle.event.addDynamicEventListener('click', '.huh-list .items[data-open-list-items-in-modal="1"] .item .details.modal-link', function(item, event) {
            event.preventDefault();

            utilsBundle.ajax.get(item.getAttribute('href'), {}, {
                onSuccess: (request) => {
                    const response = document.createElement('div'),
                        itemsWrapper = item.closest('.items'),
                        readerType = itemsWrapper.getAttribute('data-list-modal-reader-type'),
                        readerCssSelector = itemsWrapper.getAttribute('data-list-modal-reader-css-selector'),
                        readerModule = itemsWrapper.getAttribute('data-list-modal-reader-module'),
                        modalId = 'modal-' + itemsWrapper.parentNode.getAttribute('id');

                    let reader = null;

                    response.innerHTML = request.response.trim();

                    switch (readerType) {
                        case 'huh_reader':
                            reader = response.querySelector('#huh-reader-' + readerModule);

                            if (null === reader) {
                                console.log('Reader not found with selector: #huh-reader-' + readerModule);
                                return;
                            }

                            break;
                        case 'css_selector':
                            reader = response.querySelector(readerCssSelector);

                            if (null === reader) {
                                console.log('Reader not found with selector: ' + readerCssSelector);
                                return;
                            }

                            break;
                    }

                    if (null === reader) {
                        return;
                    }

                    document.getElementById(modalId).querySelector('.modal-content .modal-body').innerHTML = reader.outerHTML;

                    window.jQuery('#' + modalId).modal('show');

                    history.pushState({
                        modalId: modalId
                    }, '', item.getAttribute('href'));

                    history.pushState({
                        modalId: modalId
                    }, '', item.getAttribute('href'));
                }
            });
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
                    padding: 50,
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
        document.dispatchEvent(new CustomEvent('huh.list.list_update_complete', {
            detail: {
                list: list,
                listId: id,
                filter: target
            },
            bubbles: true,
            cancelable: true
        }));
    }
}

export {ListBundle};

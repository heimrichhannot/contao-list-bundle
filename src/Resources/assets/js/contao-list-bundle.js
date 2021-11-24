import UtilsBundle from "@hundh/contao-utils-bundle"

class ListBundle {

    static init() {
        ListBundle.initPagination();
        ListBundle.initMasonry();
        ListBundle.initEvents();
        ListBundle.initModal();
        ListBundle.initVideo();
    }

    static initVideo() {
        let videos = document.querySelectorAll('.video-player');

        videos.forEach((elem) => {
            let button = elem.querySelector('.play-button'),
                poster = elem.querySelector('.poster'),
                video  = elem.querySelector('video');

            if(button) {
                button.addEventListener('click', function() {
                    video.play();
                    poster.classList.add('d-none');
                });
            }
        });
    }

    static initEvents() {
        document.addEventListener('filterAjaxComplete', function(e) {
            ListBundle.updateList(e.detail);
        });
    }

    static initModal() {
        const currentUrl = location.href,
            lists = document.querySelectorAll('.huh-list .items[data-open-list-items-in-modal="1"]');

        if (lists.length < 1) {
            return;
        }

        // modal event listeners for history changing
        document.querySelectorAll('.huh-list .items[data-open-list-items-in-modal="1"]').forEach((list) => {
            const modalId = 'modal-' + list.closest('.wrapper').getAttribute('id');

            // bootstrap 4 and below
            if ('undefined' !== typeof window.jQuery) {
                window.jQuery('#' + modalId).on('hidden.bs.modal', (e) => {
                    history.pushState({
                        modalId: modalId
                    }, '', currentUrl);
                });
            } else {
                // initialize bootstrap modals
                import(/* webpackChunkName: "bootstrap" */ 'bootstrap').then((bootstrap) => {
                    new bootstrap.Modal(document.getElementById(modalId));
                });

                // bootstrap 5 and up
                document.getElementById(modalId).addEventListener('hidden.bs.modal', function (event) {
                    history.pushState({
                        modalId: modalId
                    }, '', currentUrl);
                });
            }
        });

        // catch browser back button
        addEventListener('popstate', (e) => {
            // bootstrap 4 and below
            if ('undefined' !== typeof window.jQuery) {
                window.jQuery('#' + e.state.modalId).modal('hide');
            } else {
                // bootstrap 5 and up
                import(/* webpackChunkName: "bootstrap" */ 'bootstrap').then((bootstrap) => {
                    bootstrap.Modal.getInstance(document.getElementById(e.state.modalId)).hide();
                });
            }
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
                        modalId = 'modal-' + itemsWrapper.closest('.wrapper').getAttribute('id');

                    let reader = null;

                    response.innerHTML = request.response.trim();

                    switch (readerType) {
                        case 'huh_reader':
                            reader = response.querySelector('#huh-reader-' + readerModule);

                            if (null === reader) {
                                console.warn('Reader not found with selector: #huh-reader-' + readerModule);
                                return;
                            }

                            break;
                        case 'css_selector':
                            reader = response.querySelector(readerCssSelector);

                            if (null === reader) {
                                console.warn('Reader not found with selector: ' + readerCssSelector);
                                return;
                            }

                            break;
                    }

                    if (null === reader) {
                        return;
                    }

                    let modalElement = document.getElementById(modalId);
                    modalElement.querySelector('.modal-content .modal-body').innerHTML = reader.outerHTML;

                    // bootstrap 4 and below
                    if ('undefined' !== typeof window.jQuery) {
                        window.jQuery('#' + modalId).modal('show');
                    } else {
                        // bootstrap 5 and up
                        import(/* webpackChunkName: "bootstrap" */ 'bootstrap').then((bootstrap) => {
                            bootstrap.Modal.getInstance(modalElement).show();
                        });
                    }

                    // console.log(item);

                    item.dispatchEvent(new CustomEvent('huh.list.modal_show', {bubbles: true, detail: {
                        modalElement: modalElement,
                        modalId: modalId
                    }}));

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

    static isAtBottom(element) {
        return (
            element.getBoundingClientRect().bottom <= (window.innerHeight || document.getElementById('main').clientHeight)
        );
    }

    static initPagination() {
        document.querySelectorAll('.huh-list .ajax-pagination').forEach(function(ajaxPagination) {

            const list = ajaxPagination.closest('.huh-list'),
                items = list.querySelector('.items');

            if (!list || !items) {
                console.warn('Ajax pagination does not contain list or item containers.');
                return;
            }

            let ajaxLoad = {
                loadingHtml: '<div class="loading"><span class="text">Lade...</span></div>',
                enableScreenReaderMessage: true,
                screenReaderMessage: 'Es wurden neue Einträge zur Liste hinzugefügt.',
                disableLiveRegion: false
            }

            if (ajaxPagination.hasAttribute('data-disable-live-region') && (
                ajaxPagination.getAttribute('data-disable-live-region') === true ||
                ajaxPagination.getAttribute('data-disable-live-region') === '1' ||
                ajaxPagination.getAttribute('data-disable-live-region') === 'true'
            )) {
                ajaxLoad.disableLiveRegion = true;
            }


            if (!ajaxLoad.disableLiveRegion) {
                items.setAttribute('aria-busy', 'false');
                items.setAttribute('aria-live', 'polite');
                items.setAttribute('aria-relevant', 'additions text');
                items.setAttribute('aria-atomic', 'false');

                if (ajaxPagination.hasAttribute('data-enable-screen-reader-message') && (
                    ajaxPagination.getAttribute('data-enable-screen-reader-message') === true ||
                    ajaxPagination.getAttribute('data-enable-screen-reader-message') === "1" ||
                    ajaxPagination.getAttribute('data-enable-screen-reader-message') === "true"
                )) {
                    ajaxLoad.enableScreenReaderMessage = true;
                }

                if (ajaxPagination.hasAttribute('data-screen-reader-message')) {
                    ajaxLoad.screenReaderMessage = ajaxPagination.getAttribute('data-screen-reader-message');
                }
            }

            document.addEventListener('scroll', e => {
                if (items.hasAttribute('data-add-infinite-scroll') && items.dataset.addInfiniteScroll === "1" && ListBundle.isAtBottom(list)) {
                    loadMoreItems(e);
                }
            }, {passive: true});

            ajaxPagination.querySelector('a.next').addEventListener('click', e => {
                e.stopPropagation();
                e.preventDefault();
                loadMoreItems(e);
            });

            let loadMoreItems = (event) => {
                const request = new XMLHttpRequest();

                if (!items.classList.contains('loading') && ajaxPagination.querySelector('.huh-list .ajax-pagination a.next')) {
                    request.onreadystatechange = () => {
                        if (request.readyState === 1) {
                            list.dispatchEvent(new CustomEvent('huh.list.ajax-pagination-loading', {
                                bubbles: true,
                                detail: {
                                    wrapper: list.querySelector('.wrapper'),
                                    pagination: ajaxPagination,
                                    items: items
                                }
                            }))

                            ajaxPagination.innerHTML = ajaxLoad.loadingHtml;

                            if (!ajaxLoad.disableLiveRegion) {
                                items.setAttribute('aria-busy', 'true');
                                let screenReaderElement = items.querySelector('span.sr-only');
                                if (screenReaderElement) {
                                    items.removeChild(screenReaderElement);
                                }
                            }

                            items.classList.add('loading');
                        }

                        if (request.readyState === 4 && request.status === 200) {
                            const response = request.responseText,
                                parser = new DOMParser(),
                                loadedDoc = parser.parseFromString(response, 'text/html'),
                                loadedItems = loadedDoc.querySelectorAll('.huh-list #' + list.querySelector('.wrapper').getAttribute('id') + ' .items .item');

                            import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(({default: imagesLoaded}) => {
                                imagesLoaded(loadedItems, function(instance) {
                                    if (true === ajaxLoad.enableScreenReaderMessage) {
                                        let span = document.createElement('span');
                                        span.classList.add('sr-only');
                                        span.textContent = ajaxLoad.screenReaderMessage;
                                        items.appendChild(span);
                                    }

                                    loadedItems.forEach(item => {
                                        items.appendChild(item);
                                    })
                                });

                                ajaxPagination.innerHTML = '';

                                if (loadedDoc.querySelector('.huh-list .ajax-pagination a.next')) {
                                    let nextButton = loadedDoc.querySelector('.huh-list .ajax-pagination a.next');

                                    nextButton.addEventListener('click', e => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        loadMoreItems(e);
                                    });

                                    ajaxPagination.appendChild(nextButton);
                                }

                                if (items.dataset.addMasonry === "1") {
                                    import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(function() {
                                        ListBundle.initMasonry();
                                    });

                                    return;
                                }

                                // remove item counters...
                                items.querySelectorAll('.item').forEach(item => {
                                    item.classList.forEach(cssClass => {
                                        if (cssClass.match(/item_\d+/g)) {
                                            item.classList.remove(cssClass);
                                        }
                                    })
                                });

                                items.querySelectorAll('.item').forEach((item, index,nodes) => {
                                    let itemIndex = index+1;
                                    item.classList.remove('odd', 'even', 'first', 'last');
                                    item.classList.add('item_'+itemIndex);

                                    // odd/even
                                    if (itemIndex % 2 === 0) {
                                        item.classList.add('even')
                                    } else {
                                        item.classList.add('odd');
                                    }

                                    // add first and last
                                    if (itemIndex === 1) {
                                        item.classList.add('first');
                                    }

                                    if (itemIndex === nodes.length) {
                                        item.classList.add('last');
                                    }
                                });

                                list.dispatchEvent(new CustomEvent('huh.list.ajax-pagination-loaded', {
                                    bubbles: true,
                                    detail: {
                                        wrapper: list.querySelector('.wrapper'),
                                        pagination: ajaxPagination,
                                        items: items
                                    }
                                }))

                                if (!ajaxLoad.disableLiveRegion) {
                                    items.setAttribute('aria-busy', 'false');
                                }

                                items.classList.remove('loading');
                            });
                        }
                    };

                    request.open("GET", ajaxPagination.querySelector('.huh-list .ajax-pagination a.next').href, true);
                    request.send();
                }
            };



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

document.addEventListener('DOMContentLoaded', ListBundle.init);

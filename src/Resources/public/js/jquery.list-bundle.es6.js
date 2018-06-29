let jQuery = require('jquery');

($ => {
    let listBundle = {
        init: function() {
            listBundle.initPagination();
            listBundle.initMasonry();
        },
        initPagination: function() {
            $('.huh-list .ajax-pagination').each(function() {
                import(/* webpackChunkName: "jscroll" */ 'jscroll').then(() =>
                {
                    let $list = $(this).closest('.huh-list'),
                        $items = $list.find('.items'),
                        $wrapper = $list.find('.wrapper'),
                        id = '#' + $wrapper.attr('id');

                    $wrapper.jscroll({
                        loadingHtml: '<div class="loading"><span class="text">Lade...</span></div>',
                        nextSelector: '.ajax-pagination a.next',
                        autoTrigger: $wrapper.data('add-infinite-scroll') == 1,
                        contentSelector: id,
                        callback: function() {
                            let $jscrollAdded = $(this),
                                $newItems = $jscrollAdded.find('.item');

                            $newItems.hide();

                            import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(function() {
                                $jscrollAdded.imagesLoaded(function() {
                                    $items.append($newItems.fadeIn(300));

                                    if ($wrapper.attr('data-add-masonry') === "1") {
                                        import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(function() {
                                            $items.masonry('appended', $newItems);
                                            $items.masonry();
                                        });
                                    }

                                    // remove item counters...
                                    $items.find('.item').removeClass((index, cssClass) => {
                                        let matches = cssClass.match(/item_\d+/g);

                                        if (matches instanceof Array && matches.length > 0)
                                        {
                                            return matches[0];
                                        }
                                    });

                                    //... and readd them again
                                    $items.find('.item').each(index => {
                                        let $item = $(this),
                                            itemIndex = index + 1;

                                        $(this).addClass('item_' + itemIndex).removeClass('odd even first last');

                                        // odd/even
                                        if (itemIndex % 2 == 0)
                                        {
                                            $item.addClass('even');
                                        }
                                        else
                                        {
                                            $item.addClass('odd');
                                        }

                                        // add first and last
                                        if (itemIndex == 1)
                                        {
                                            $item.addClass('first');
                                        }

                                        if (itemIndex == $items.find('.item').length)
                                        {
                                            $item.addClass('last');
                                        }
                                    });

                                    $jscrollAdded.find('.ajax-pagination').appendTo($jscrollAdded.closest('.jscroll-inner'));
                                    $jscrollAdded.remove();
                                });
                            });
                        }
                    });
                });
            });
        },
        initMasonry: function() {
            if (document.querySelectorAll('.huh-list .wrapper[data-add-masonry="1"]').length < 1)
            {
                return;
            }

            import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(function(Masonry) {
                import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(function(imagesLoaded) {
                    document.querySelectorAll('.huh-list .wrapper[data-add-masonry="1"]').forEach(function(elem, index, list) {
                        let items = elem.querySelector('.items');
                        let options = {
                            itemSelector: '.item',
                            stamp: '.stamp-item'
                        };
                        let listOptions = elem.getAttribute('data-masonry');
                        if (listOptions !== null && listOptions !== '') {
                            options = Object.assign({}, options, JSON.parse(listOptions));
                        }
                        let grid = imagesLoaded(items, function(instance) {
                            let msnry = new Masonry(items, options);
                            msnry.layout();
                        })
                    });

                    // $('.huh-list .wrapper[data-add-masonry="1"]').each(function() {
                    //     let $this = $(this).find('.items'),
                    //         options = $(this).data('masonry-options');
                    //
                    //     let $grid = $this.imagesLoaded(function() {
                    //         $grid.masonry({
                    //             // fitWidth: true,
                    //             itemSelector: '.item',
                    //             stamp: '.stamp-item'
                    //         });
                    //
                    //         // update due to stamps
                    //         $grid.masonry();
                    //     });
                    // });
                });
            });
        }
    };

    module.exports = listBundle;

    $(document).ready(function() {
        listBundle.init();
    });
})(jQuery);
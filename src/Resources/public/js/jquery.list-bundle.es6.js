let jQuery = require('jquery');

($ => {
    let listBundle = {
        init: () => {
            listBundle.initPagination();
            listBundle.initMasonry();
        },
        initPagination: () => {
            $('.huh-list .ajax-pagination').each(() => {
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
                        callback: () => {
                            let $jscrollAdded = $(this),
                                $newItems = $jscrollAdded.find('.item');

                            $newItems.hide();

                            import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(() => {
                                $jscrollAdded.imagesLoaded(() => {
                                    $items.append($newItems.fadeIn(300));

                                    if ($wrapper.attr('data-add-masonry') === "1") {
                                        import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(() => {
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
        initMasonry: () => {
            if ($('.huh-list .wrapper[data-add-masonry="1"]').length < 1)
            {
                return;
            }

            import(/* webpackChunkName: "masonry-layout" */ 'masonry-layout').then(() => {
                import(/* webpackChunkName: "imagesloaded" */ 'imagesloaded').then(() => {
                    $('.huh-list .wrapper[data-add-masonry="1"]').each(() => {
                        let $this = $(this).find('.items'),
                            options = $(this).data('masonry-options');

                        let $grid = $this.imagesLoaded(() => {
                            $grid.masonry({
                                // fitWidth: true,
                                itemSelector: '.item',
                                stamp: '.stamp-item'
                            });

                            // update due to stamps
                            $grid.masonry();
                        });
                    });
                });
            });
        }
    };

    module.exports = listBundle;

    $(document).ready(() => {
        listBundle.init();
    });
})(jQuery);
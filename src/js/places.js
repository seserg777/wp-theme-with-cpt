/**
 * Places functionality
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * Places Load More functionality
     */
    const PlacesLoadMore = {
        currentPage: 1,
        loading: false,
        orderby: 'date',
        order: 'desc',

        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
            this.initSorting();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            $(document).on('click', '.places-load-more-btn', this.loadMore.bind(this));
        },

        /**
         * Load more places
         *
         * @param {Event} e Click event
         */
        loadMore: function (e) {
            e.preventDefault();

            if (this.loading) {
                return;
            }

            this.loading = true;
            this.currentPage++;

            const $btn = $(e.currentTarget);
            const $btnText = $btn.find('.btn-text');
            const originalText = $btnText.text();

            $btnText.text('Loading...');
            $btn.prop('disabled', true);

            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'load_more_places',
                    nonce: mythemeData.nonce,
                    page: this.currentPage,
                    orderby: this.orderby,
                    order: this.order
                },
                success: this.onLoadMoreSuccess.bind(this),
                error: this.onLoadMoreError.bind(this),
                complete: () => {
                    this.loading = false;
                    $btnText.text(originalText);
                    $btn.prop('disabled', false);
                }
            });
        },

        /**
         * Load more success callback
         *
         * @param {Object} response AJAX response
         */
        onLoadMoreSuccess: function (response) {
            if (response.success && response.data.html) {
                $('.places-table tbody').append(response.data.html);
                
                if (!response.data.has_more) {
                    $('.places-load-more-btn').hide();
                }
            } else {
                console.error('Error loading more places:', response.data.message);
            }
        },

        /**
         * Load more error callback
         */
        onLoadMoreError: function () {
            console.error('Error loading more places');
        },

        /**
         * Initialize sorting
         */
        initSorting: function () {
            $(document).on('click', '.places-sort-btn', this.handleSort.bind(this));
        },

        /**
         * Handle sort
         *
         * @param {Event} e Click event
         */
        handleSort: function (e) {
            e.preventDefault();

            const $btn = $(e.currentTarget);
            const column = $btn.data('column');

            if (this.orderby === column) {
                this.order = this.order === 'asc' ? 'desc' : 'asc';
            } else {
                this.orderby = column;
                this.order = 'asc';
            }

            this.updateSortIndicators();
            this.reloadTable();
        },

        /**
         * Update sort indicators
         */
        updateSortIndicators: function () {
            $('.places-sort-btn').removeClass('active asc desc');
            $(`.places-sort-btn[data-column="${this.orderby}"]`)
                .addClass('active')
                .addClass(this.order);
        },

        /**
         * Reload table
         */
        reloadTable: function () {
            this.currentPage = 1;
            this.loading = false;

            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'load_more_places',
                    nonce: mythemeData.nonce,
                    page: 1,
                    orderby: this.orderby,
                    order: this.order
                },
                success: (response) => {
                    if (response.success && response.data.html) {
                        $('.places-table tbody').html(response.data.html);
                        $('.places-load-more-btn').show();
                    }
                },
                error: () => {
                    console.error('Error reloading table');
                }
            });
        }
    };

    /**
     * Places Edit functionality
     * Note: Edit links now work as regular links, no JavaScript needed
     */
    const PlacesEdit = {
        /**
         * Initialize
         */
        init: function () {
            // No JavaScript needed for edit links
            // They work as regular <a> tags
        }
    };

    // Initialize on document ready.
    $(function () {
        PlacesLoadMore.init();
        PlacesEdit.init();
    });

    // Initialize on window load.
    $(window).on('load', function () {
        console.log('Window loaded');
    });

})(jQuery);
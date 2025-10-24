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
            $(document).on('click', '.places-table th.sortable', this.handleSort.bind(this));
        },

        /**
         * Handle sort
         *
         * @param {Event} e Click event
         */
        handleSort: function (e) {
            e.preventDefault();

            const $th = $(e.currentTarget);
            const column = $th.data('sort');

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
            $('.places-table th.sortable').removeClass('sorted sorted-asc sorted-desc');
            $(`.places-table th.sortable[data-sort="${this.orderby}"]`)
                .addClass('sorted')
                .addClass(`sorted-${this.order}`);
        },

        /**
         * Reload table
         */
        reloadTable: function () {
            this.currentPage = 1;
            this.loading = true;

            // Show loading indicator
            const $tbody = $('.places-table tbody');
            const $loadMoreBtn = $('.places-load-more-btn');
            
            $tbody.addClass('loading');
            $tbody.html('<tr class="loading-row"><td colspan="6"><span class="spinner"></span> Loading...</td></tr>');

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
                        $tbody.html(response.data.html);
                        
                        // Update load more button visibility
                        if (response.data.has_more) {
                            $loadMoreBtn.show();
                        } else {
                            $loadMoreBtn.hide();
                        }
                    } else {
                        $tbody.html('<tr class="error-row"><td colspan="6">Error loading places</td></tr>');
                    }
                },
                error: () => {
                    console.error('Error reloading table');
                    $tbody.html('<tr class="error-row"><td colspan="6">Error loading places</td></tr>');
                },
                complete: () => {
                    $tbody.removeClass('loading');
                    this.loading = false;
                }
            });
        }
    };

    /**
     * Places Edit functionality
     * Handles disabled edit buttons for non-authenticated users
     */
    const PlacesEdit = {
        /**
         * Initialize
         */
        init: function () {
            this.handleDisabledButtons();
        },

        /**
         * Handle clicks on disabled edit buttons
         */
        handleDisabledButtons: function () {
            $(document).on('click', '.places-edit-btn[data-auth-required]', function (e) {
                e.preventDefault();
                
                // Show authentication required message
                PlacesEdit.showAuthMessage();
                
                return false;
            });
        },

        /**
         * Show authentication required message
         */
        showAuthMessage: function () {
            const message = mythemeData.i18n?.authRequired || 'Please log in to edit this place.';
            const loginUrl = mythemeData.loginUrl || '/wp-login.php';
            
            // Create modal overlay
            const modal = $('<div class="places-auth-modal"></div>');
            const modalContent = $('<div class="places-auth-modal-content"></div>');
            
            modalContent.html(`
                <div class="places-auth-modal-icon">
                    <span class="dashicons dashicons-lock"></span>
                </div>
                <h3 class="places-auth-modal-title">${mythemeData.i18n?.authTitle || 'Authentication Required'}</h3>
                <p class="places-auth-modal-message">${message}</p>
                <div class="places-auth-modal-actions">
                    <a href="${loginUrl}?redirect_to=${encodeURIComponent(window.location.href)}" class="places-auth-modal-btn places-auth-btn-login">
                        ${mythemeData.i18n?.loginButton || 'Log In'}
                    </a>
                    <button type="button" class="places-auth-modal-btn places-auth-btn-cancel">
                        ${mythemeData.i18n?.cancelButton || 'Cancel'}
                    </button>
                </div>
            `);
            
            modal.append(modalContent);
            $('body').append(modal);
            
            // Fade in modal
            setTimeout(() => {
                modal.addClass('active');
            }, 10);
            
            // Close modal on cancel
            modal.on('click', '.places-auth-btn-cancel', function () {
                PlacesEdit.closeAuthModal(modal);
            });
            
            // Close modal on overlay click
            modal.on('click', function (e) {
                if ($(e.target).is('.places-auth-modal')) {
                    PlacesEdit.closeAuthModal(modal);
                }
            });
            
            // Close on Escape key
            $(document).on('keydown.authModal', function (e) {
                if (e.key === 'Escape') {
                    PlacesEdit.closeAuthModal(modal);
                }
            });
        },

        /**
         * Close authentication modal
         * 
         * @param {jQuery} modal Modal element
         */
        closeAuthModal: function (modal) {
            modal.removeClass('active');
            
            setTimeout(() => {
                modal.remove();
                $(document).off('keydown.authModal');
            }, 300);
        }
    };

    // Initialize on document ready.
    $(function () {
        PlacesLoadMore.init();
        PlacesEdit.init();
    });
})(jQuery);
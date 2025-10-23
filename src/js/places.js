/**
 * Places load more functionality
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * Places Load More handler
     */
    const PlacesLoadMore = {
        /**
         * Current page number
         */
        currentPage: 1,

        /**
         * Is loading flag
         */
        isLoading: false,

        /**
         * Has more posts flag
         */
        hasMore: true,

        /**
         * Posts per page
         */
        postsPerPage: 10,

        /**
         * Category slug
         */
        category: '',

        /**
         * Order by field
         */
        orderby: 'date',

        /**
         * Order direction
         */
        order: 'DESC',

        /**
         * Initialize
         */
        init: function () {
            // Get initial data from data attributes.
            const $container = $('.places-load-more-container');
            
            if ($container.length === 0) {
                return;
            }

            this.currentPage = parseInt($container.data('current-page')) || 1;
            this.hasMore = $container.data('has-more') === true;
            this.postsPerPage = parseInt($container.data('posts-per-page')) || 10;
            this.category = $container.data('category') || '';
            this.orderby = $container.data('orderby') || 'date';
            this.order = $container.data('order') || 'DESC';

            this.bindEvents();
            this.updateButton();
            this.initSorting();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            $(document).on('click', '.places-load-more-btn', this.loadMore.bind(this));
            $(document).on('click', '.places-table th.sortable', this.handleSort.bind(this));
        },

        /**
         * Initialize sorting
         */
        initSorting: function () {
            // Set initial sort indicator.
            this.updateSortIndicators();
        },

        /**
         * Handle column sort
         *
         * @param {Event} e Click event
         */
        handleSort: function (e) {
            const $th = $(e.currentTarget);
            const sortField = $th.data('sort');

            if (!sortField) {
                return;
            }

            // Toggle order if clicking the same column.
            if (this.orderby === sortField) {
                this.order = this.order === 'ASC' ? 'DESC' : 'ASC';
            } else {
                this.orderby = sortField;
                this.order = 'ASC';
            }

            // Update data attributes.
            $('.places-load-more-container')
                .data('orderby', this.orderby)
                .data('order', this.order);

            // Reset pagination and reload.
            this.currentPage = 1;
            this.reloadTable();
        },

        /**
         * Update sort indicators
         */
        updateSortIndicators: function () {
            $('.places-table th.sortable').removeClass('sorted sorted-asc sorted-desc');
            
            const $activeTh = $('.places-table th.sortable[data-sort="' + this.orderby + '"]');
            $activeTh.addClass('sorted sorted-' + this.order.toLowerCase());
        },

        /**
         * Reload table with current sort
         */
        reloadTable: function () {
            if (this.isLoading) {
                return;
            }

            this.isLoading = true;
            $('.places-table-body').html('<tr><td colspan="6" class="loading-row">Loading...</td></tr>');

            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'load_more_places',
                    nonce: mythemeData.nonce,
                    page: 1,
                    posts_per_page: this.postsPerPage,
                    category: this.category,
                    orderby: this.orderby,
                    order: this.order
                },
                success: this.onReloadSuccess.bind(this),
                error: this.onError.bind(this)
            });
        },

        /**
         * Reload success callback
         *
         * @param {Object} response AJAX response
         */
        onReloadSuccess: function (response) {
            this.isLoading = false;

            if (response.success && response.data.html) {
                $('.places-table-body').html(response.data.html);

                this.currentPage = 1;
                this.hasMore = response.data.has_more;

                this.updateSortIndicators();
                this.updateButton();
            }
        },

        /**
         * Load more places
         *
         * @param {Event} e Click event
         */
        loadMore: function (e) {
            e.preventDefault();

            if (this.isLoading || !this.hasMore) {
                return;
            }

            this.isLoading = true;
            this.updateButton();

            const nextPage = this.currentPage + 1;

            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'load_more_places',
                    nonce: mythemeData.nonce,
                    page: nextPage,
                    posts_per_page: this.postsPerPage,
                    category: this.category,
                    orderby: this.orderby,
                    order: this.order
                },
                success: this.onSuccess.bind(this),
                error: this.onError.bind(this)
            });
        },

        /**
         * Success callback
         *
         * @param {Object} response AJAX response
         */
        onSuccess: function (response) {
            this.isLoading = false;

            if (response.success && response.data.html) {
                // Append new table rows with fade-in animation.
                const $newRows = $(response.data.html);
                $newRows.hide();
                $('.places-table-body').append($newRows);
                $newRows.fadeIn(400);

                // Update state.
                this.currentPage = response.data.next_page - 1;
                this.hasMore = response.data.has_more;

                // Log for debugging.
                console.log('Loaded places:', {
                    currentPage: this.currentPage,
                    hasMore: this.hasMore,
                    maxPages: response.data.max_pages
                });
            } else {
                this.hasMore = false;
                this.showMessage(response.data.message || 'No more places found');
            }

            this.updateButton();
        },

        /**
         * Error callback
         *
         * @param {Object} xhr XHR object
         * @param {string} status Status text
         * @param {string} error Error message
         */
        onError: function (xhr, status, error) {
            this.isLoading = false;
            this.updateButton();
            
            console.error('AJAX Error:', error);
            this.showMessage('Error loading places. Please try again.');
        },

        /**
         * Update button state
         */
        updateButton: function () {
            const $button = $('.places-load-more-btn');
            const $loader = $('.places-loader');

            if (this.isLoading) {
                $button.prop('disabled', true).addClass('loading');
                $loader.show();
            } else {
                $button.prop('disabled', false).removeClass('loading');
                $loader.hide();
            }

            if (!this.hasMore) {
                $button.hide();
                $('.places-load-more-container').addClass('all-loaded');
            }
        },

        /**
         * Show message
         *
         * @param {string} message Message text
         */
        showMessage: function (message) {
            const $container = $('.places-load-more-container');
            const $message = $('<div class="places-message"></div>').text(message);
            
            $container.append($message);
            
            setTimeout(function () {
                $message.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    /**
     * Places Edit handler
     */
    const PlacesEdit = {
        /**
         * Current editing post ID
         */
        currentPostId: null,

        /**
         * Is saving flag
         */
        isSaving: false,

        /**
         * Initialize
         */
        init: function () {
            this.createModal();
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            $(document).on('click', '.places-edit-btn', this.openEditModal.bind(this));
            $(document).on('click', '.places-modal-close', this.closeModal.bind(this));
            $(document).on('click', '.places-modal-overlay', this.closeModal.bind(this));
            $(document).on('submit', '.places-edit-form', this.savePlace.bind(this));
            
            // Close on ESC key.
            $(document).on('keydown', function (e) {
                if (e.key === 'Escape') {
                    PlacesEdit.closeModal();
                }
            });
        },

        /**
         * Create modal HTML
         */
        createModal: function () {
            const modalHtml = `
                <div class="places-modal-overlay" style="display: none;">
                    <div class="places-modal">
                        <div class="places-modal-header">
                            <h2>${mythemeData.i18n.editPlace || 'Edit Place'}</h2>
                            <button type="button" class="places-modal-close">&times;</button>
                        </div>
                        <div class="places-modal-body">
                            <form class="places-edit-form">
                                <input type="hidden" name="post_id" id="edit-post-id" />
                                
                                <div class="form-field">
                                    <label for="edit-title">${mythemeData.i18n.name || 'Name'} <span class="required">*</span></label>
                                    <input type="text" id="edit-title" name="title" required />
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-field">
                                        <label for="edit-street">${mythemeData.i18n.street || 'Street'} <span class="required">*</span></label>
                                        <input type="text" id="edit-street" name="street" required />
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="edit-number">${mythemeData.i18n.number || 'Number'} <span class="required">*</span></label>
                                        <input type="text" id="edit-number" name="number" required />
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-field">
                                        <label for="edit-region">${mythemeData.i18n.region || 'Region'} <span class="required">*</span></label>
                                        <input type="text" id="edit-region" name="region" required />
                                    </div>
                                    
                                    <div class="form-field">
                                        <label for="edit-nip">${mythemeData.i18n.nip || 'NIP'}</label>
                                        <input type="text" id="edit-nip" name="nip" pattern="[0-9]{10}" maxlength="10" placeholder="0000000000" />
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn-cancel places-modal-close">${mythemeData.i18n.cancel || 'Cancel'}</button>
                                    <button type="submit" class="btn-save">${mythemeData.i18n.save || 'Save'}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            $('body').append(modalHtml);
        },

        /**
         * Open edit modal
         *
         * @param {Event} e Click event
         */
        openEditModal: function (e) {
            e.preventDefault();
            const postId = $(e.currentTarget).data('post-id');
            
            if (!postId) {
                return;
            }

            this.currentPostId = postId;
            this.loadPlaceData(postId);
        },

        /**
         * Load place data
         *
         * @param {number} postId Post ID
         */
        loadPlaceData: function (postId) {
            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'get_place_data',
                    nonce: mythemeData.nonce,
                    post_id: postId
                },
                success: this.onDataLoaded.bind(this),
                error: function (xhr, status, error) {
                    console.error('Error loading place data:', error);
                    alert('Error loading place data');
                }
            });
        },

        /**
         * Data loaded callback
         *
         * @param {Object} response AJAX response
         */
        onDataLoaded: function (response) {
            if (response.success && response.data) {
                const data = response.data;
                
                $('#edit-post-id').val(data.id);
                $('#edit-title').val(data.title);
                $('#edit-street').val(data.street);
                $('#edit-number').val(data.number);
                $('#edit-region').val(data.region);
                $('#edit-nip').val(data.nip);
                
                $('.places-modal-overlay').fadeIn(200);
                $('body').addClass('modal-open');
            }
        },

        /**
         * Close modal
         */
        closeModal: function () {
            if (PlacesEdit.isSaving) {
                return;
            }

            $('.places-modal-overlay').fadeOut(200);
            $('body').removeClass('modal-open');
            $('.places-edit-form')[0].reset();
            PlacesEdit.currentPostId = null;
        },

        /**
         * Save place
         *
         * @param {Event} e Submit event
         */
        savePlace: function (e) {
            e.preventDefault();
            
            if (this.isSaving) {
                return;
            }

            const $form = $(e.currentTarget);
            const formData = {
                action: 'update_place',
                nonce: mythemeData.nonce,
                post_id: $('#edit-post-id').val(),
                title: $('#edit-title').val(),
                street: $('#edit-street').val(),
                number: $('#edit-number').val(),
                region: $('#edit-region').val(),
                nip: $('#edit-nip').val()
            };

            this.isSaving = true;
            $('.btn-save').prop('disabled', true).text(mythemeData.i18n.saving || 'Saving...');

            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: formData,
                success: this.onSaveSuccess.bind(this),
                error: this.onSaveError.bind(this)
            });
        },

        /**
         * Save success callback
         *
         * @param {Object} response AJAX response
         */
        onSaveSuccess: function (response) {
            this.isSaving = false;
            $('.btn-save').prop('disabled', false).text(mythemeData.i18n.save || 'Save');

            if (response.success && response.data) {
                // Update table row.
                const data = response.data.data;
                const $row = $('#post-' + data.id);
                
                $row.find('.places-col-name .places-name-link').text(data.title);
                $row.find('.places-col-street').text(data.street);
                $row.find('.places-col-number').text(data.number);
                $row.find('.places-col-region').text(data.region);
                $row.find('.places-col-nip').text(data.nip || '—');

                // Show success message.
                this.showMessage(response.data.message, 'success');
                
                // Close modal.
                this.closeModal();
            } else {
                this.showMessage(response.data.message || 'Error updating place', 'error');
            }
        },

        /**
         * Save error callback
         */
        onSaveError: function () {
            this.isSaving = false;
            $('.btn-save').prop('disabled', false).text(mythemeData.i18n.save || 'Save');
            this.showMessage('Error updating place', 'error');
        },

        /**
         * Show message
         *
         * @param {string} message Message text
         * @param {string} type Message type (success/error)
         */
        showMessage: function (message, type) {
            const $message = $('<div class="places-edit-message ' + type + '"></div>').text(message);
            $('.places-table-wrapper').before($message);
            
            setTimeout(function () {
                $message.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    // Initialize on document ready.
    $(document).ready(function () {
        PlacesLoadMore.init();
        PlacesEdit.init();
    });

    // Make globally accessible for debugging.
    window.PlacesLoadMore = PlacesLoadMore;
    window.PlacesEdit = PlacesEdit;

})(jQuery);


/**
 * Main theme JavaScript file
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * Theme main object
     */
    const MyTheme = {
        /**
         * Initialize theme
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            $(document).ready(this.onDocumentReady.bind(this));
            $(window).on('load', this.onWindowLoad.bind(this));
            $(window).on('resize', this.onWindowResize.bind(this));
        },

        /**
         * Document ready handler
         */
        onDocumentReady: function () {
            // Add your code here
        },

        /**
         * Window load handler
         */
        onWindowLoad: function () {
            // Add your code here
        },

        /**
         * Window resize handler
         */
        onWindowResize: function () {
            // Add your code here
        },

        /**
         * AJAX request helper
         *
         * @param {string} action Action name
         * @param {Object} data   Data object
         * @param {Function} callback Success callback
         */
        ajaxRequest: function (action, data, callback) {
            const ajaxData = {
                action: action,
                nonce: mythemeData.nonce,
                ...data
            };

            $.ajax({
                url: mythemeData.ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function (response) {
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    };

    // Initialize theme
    MyTheme.init();

    // Make MyTheme globally accessible
    window.MyTheme = MyTheme;

})(jQuery);


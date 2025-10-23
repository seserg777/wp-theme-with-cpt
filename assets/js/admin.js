/**
 * Admin JavaScript file
 *
 * @package MyTheme
 */

(function ($) {
    'use strict';

    /**
     * Admin object
     */
    const MyThemeAdmin = {
        /**
         * Initialize admin
         */
        init: function () {
            this.bindEvents();
            this.initMetaBoxes();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            $(document).ready(this.onDocumentReady.bind(this));
        },

        /**
         * Document ready handler
         */
        onDocumentReady: function () {
            console.log('Admin scripts loaded');
        },

        /**
         * Initialize meta boxes functionality
         */
        initMetaBoxes: function () {
            // Add custom functionality for meta boxes here
            $('.portfolio-meta-fields input').on('change', function () {
                // Handle input changes if needed
            });
        }
    };

    // Initialize admin
    MyThemeAdmin.init();

})(jQuery);


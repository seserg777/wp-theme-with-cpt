/**
 * Admin JavaScript file
 *
 * @package MyTheme
 * @author Serhii Soloviov <seserg777@gmail.com>
 * @version 1.0.0
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
            $('.places-meta-fields input').on('change', function () {
                // Handle input changes if needed
            });
        }
    };

    // Initialize admin
    MyThemeAdmin.init();

})(jQuery);


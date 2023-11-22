<?php

namespace {
    /**
     * WooGoship class
     *
     * @class WooGoship The class that holds the entire WooGoship plugin
     */
    final class WooGoship
    {
        /**
         * Plugin version
         *
         * @var string
         */
        public $version = '0.2.0';
        /**
         * Holds various class instances
         *
         * @var array
         */
        private $container = [];
        /**
         * Constructor for the WooGoship class
         *
         * Sets up all the appropriate hooks and actions
         * within our plugin.
         */
        public function __construct()
        {
        }
        /**
         * Initializes the WooGoship() class
         *
         * Checks for an existing WooGoship() instance
         * and if it doesn't find one, creates it.
         */
        public static function init()
        {
        }
        /**
         * Magic getter to bypass referencing plugin.
         *
         * @param $prop
         *
         * @return mixed
         */
        public function __get($prop)
        {
        }
        /**
         * Magic isset to bypass referencing plugin.
         *
         * @param $prop
         *
         * @return mixed
         */
        public function __isset($prop)
        {
        }
        /**
         * Define the constants
         *
         * @return void
         */
        public function define_constants()
        {
        }
        /**
         * Load the plugin after all plugis are loaded
         *
         * @return void
         */
        public function init_plugin()
        {
        }
        /**
         * Placeholder for activation function
         *
         * Nothing being called here yet.
         */
        public function activate()
        {
        }
        /**
         * Placeholder for deactivation function
         *
         * Nothing being called here yet.
         */
        public function deactivate()
        {
        }
        /**
         * Include the required files
         *
         * @return void
         */
        public function includes()
        {
        }
        /**
         * Initialize the hooks
         *
         * @return void
         */
        public function init_hooks()
        {
        }
        /**
         * Instantiate the required classes
         *
         * @return void
         */
        public function init_classes()
        {
        }
        /**
         * Initialize plugin for localization
         *
         * @uses load_plugin_textdomain()
         */
        public function localization_setup()
        {
        }
        /**
         * What type of request is this?
         *
         * @param  string $type admin, ajax, cron or frontend.
         *
         * @return bool
         */
        private function is_request($type)
        {
        }
    }
}
namespace App\Api {
    /**
     * REST_API Handler
     */
    class Example extends \WP_REST_Controller
    {
        /**
         * [__construct description]
         */
        public function __construct()
        {
        }
        /**
         * Register the routes
         *
         * @return void
         */
        public function register_routes()
        {
        }
        /**
         * Retrieves a collection of items.
         *
         * @param WP_REST_Request $request Full details about the request.
         *
         * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
         */
        public function get_items($request)
        {
        }
        /**
         * Checks if a given request has access to read the items.
         *
         * @param  WP_REST_Request $request Full details about the request.
         *
         * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
         */
        public function get_items_permissions_check($request)
        {
        }
        /**
         * Retrieves the query params for the items collection.
         *
         * @return array Collection parameters.
         */
        public function get_collection_params()
        {
        }
    }
}
namespace App {
    /**
     * REST_API Handler
     */
    class Api extends \WP_REST_Controller
    {
        /**
         * [__construct description]
         */
        public function __construct()
        {
        }
        /**
         * Include the controller classes
         *
         * @return void
         */
        private function includes()
        {
        }
        /**
         * Register the API routes
         *
         * @return void
         */
        public function register_routes()
        {
        }
    }
    /**
     * Admin Pages Handler
     */
    class Admin
    {
        public function __construct()
        {
        }
        /**
         * Register our menu page
         *
         * @return void
         */
        public function admin_menu()
        {
        }
        /**
         * Initialize our hooks for the admin page
         *
         * @return void
         */
        public function init_hooks()
        {
        }
        /**
         * Load scripts and styles for the app
         *
         * @return void
         */
        public function enqueue_scripts()
        {
        }
        /**
         * Render our admin page
         *
         * @return void
         */
        public function plugin_page()
        {
        }
    }
    /**
     * Scripts and Styles Class
     */
    class Assets
    {
        public function __construct()
        {
        }
        /**
         * Register our app scripts and styles
         *
         * @return void
         */
        public function register()
        {
        }
        /**
         * Register scripts
         *
         * @param  array $scripts
         *
         * @return void
         */
        private function register_scripts($scripts)
        {
        }
        /**
         * Register styles
         *
         * @param  array $styles
         *
         * @return void
         */
        public function register_styles($styles)
        {
        }
        /**
         * Get all registered scripts
         *
         * @return array
         */
        public function get_scripts()
        {
        }
        /**
         * Get registered styles
         *
         * @return array
         */
        public function get_styles()
        {
        }
    }
    /**
     * Frontend Pages Handler
     */
    class Frontend
    {
        public function __construct()
        {
        }
        /**
         * Render frontend app
         *
         * @param  array $atts
         * @param  string $content
         *
         * @return string
         */
        public function render_frontend($atts, $content = '')
        {
        }
    }
}

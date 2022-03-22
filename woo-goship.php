<?php
/*
Plugin Name: Goship Woocommerce
Plugin URI: https://goship.io/
Description: A Woocommerce plugin by Goship
Version: 0.1
Author: Nguyễn Trần Hoàn
Author URI: https://goship.io/
Text Domain: woogoship
Domain Path: /languages
License: GPLv2 or later
*/

/**
 * Copyright (c) YEAR Your Name (email: Email). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if (!defined('ABSPATH')) {
    exit;
}

function dd($arg)
{
    echo '<pre>';
    print_r($arg);
    echo '</pre>';
    die('^-^');
}

function randomString($length = 10)
{
    return substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)))), 1, $length);
}

/**
 * WooGoship class
 *
 * @class WooGoship The class that holds the entire WooGoship plugin
 */
final class WooGoship
{

    /**
     * Plugin name
     *
     * @var string
     */
    public $plugin_name = 'woo-goship';

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '0.1.0';

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
        $this->define_constants();

        register_activation_hook(__FILE__, [ $this, 'activate' ]);
        register_deactivation_hook(__FILE__, [ $this, 'deactivate' ]);

        add_action('plugins_loaded', [ $this, 'init_plugin' ]);
    }

    /**
     * Initializes the WooGoship() class
     *
     * Checks for an existing WooGoship() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {
        static $instance = false;

        if (! $instance) {
            $instance = new WooGoship();
        }

        return $instance;
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
        if (array_key_exists($prop, $this->container)) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
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
        return isset($this->{$prop}) || isset($this->container[ $prop ]);
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants()
    {
        define('WOOGOSHIP_VERSION', $this->version);
        define('WOOGOSHIP_FILE', __FILE__);
        define('WOOGOSHIP_PATH', dirname(WOOGOSHIP_FILE));
        define('WOOGOSHIP_INCLUDES', WOOGOSHIP_PATH . '/includes');
        define('WOOGOSHIP_URL', plugins_url('', WOOGOSHIP_FILE));
        define('WOOGOSHIP_ASSETS', WOOGOSHIP_URL . '/assets');
        define('WOOGOSHIP_SHIP_ID', 'woo_goship');
        define('WOOGOSHIP_TEXT_DOMAIN', 'woo-goship');
        define('WOOGOSHIP_API_URL', 'https://api.goship.io');
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin()
    {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate()
    {
        $installed = get_option('woogoship_installed');

        if (! $installed) {
            update_option('woogoship_installed', time());
        }

        $orderPrefix = get_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_order_prefix');
        if (!$orderPrefix) {
            update_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_order_prefix', randomString(10));
        }
        require_once WOOGOSHIP_INCLUDES . '/Model/GsPickup.php';

        \App\Model\GsPickup::initTable();
        update_option('WOOGOSHIP_VERSION', WOOGOSHIP_VERSION);
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
        require_once WOOGOSHIP_INCLUDES . '/Assets.php';
        require_once WOOGOSHIP_PATH . '/vendor/autoload.php';

        if ($this->is_request('admin')) {
            require_once WOOGOSHIP_INCLUDES . '/Admin.php';
        }

        if ($this->is_request('frontend')) {
            require_once WOOGOSHIP_INCLUDES . '/Frontend.php';
        }

        if ($this->is_request('ajax')) {
            // require_once WOOGOSHIP_INCLUDES . '/class-ajax.php';
        }

        require_once WOOGOSHIP_INCLUDES . '/Api.php';
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('init', [ $this, 'init_classes' ]);

        // Localize our plugin
        add_action('init', [ $this, 'localization_setup' ]);

        if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
            add_action('woocommerce_shipping_init', [$this, 'include_goship_shipping_method']);
            /*Add Settings link to the plugin*/
            $pluginBasename = $this->plugin_name . '/' . plugin_basename(plugin_dir_path(__DIR__) . $this->plugin_name . '.php');
            add_filter('plugin_action_links_' . $pluginBasename, [$this, 'add_goship_setting_links']);
            add_filter('woocommerce_shipping_methods', [$this, 'add_goship_shipping_method']);
        }
    }

    /**
    * Add settings link goship shipping
    */
    public function add_goship_setting_links($links)
    {
        $settings_link = [
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=shipping&section=' . WOOGOSHIP_SHIP_ID) . '">' . __('Settings', WOOGOSHIP_SHIP_ID) . '</a>',
       ];
        return array_merge($settings_link, $links);
    }

    public function include_goship_shipping_method()
    {
        require_once WOOGOSHIP_INCLUDES . '/GoshipShippingMethod.php';
    }

    /**
     * add tab goship shipping method
     */
    public function add_goship_shipping_method($methods)
    {
        $methods[WOOGOSHIP_SHIP_ID] = new App\GoshipShippingMethod();
        return $methods;
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes()
    {
        $this->container['api'] = new App\Api();
        $this->container['assets'] = new App\Assets();

        if ($this->is_request('admin')) {
            $this->container['admin'] = new App\Admin();
        }

        if ($this->is_request('frontend')) {
            $this->container['frontend'] = new App\Frontend();
        }

        if ($this->is_request('ajax')) {
            // $this->container['ajax'] =  new App\Ajax();
        }
    }
    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain('woogoship', false, dirname(plugin_basename(__FILE__)) . '/languages/');
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
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'rest':
                return defined('REST_REQUEST');

            case 'cron':
                return defined('DOING_CRON');

            case 'frontend':
                return (! is_admin() || defined('DOING_AJAX')) && ! defined('DOING_CRON');
        }
    }
}

$wooGoship = WooGoship::init();

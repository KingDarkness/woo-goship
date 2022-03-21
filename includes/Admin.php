<?php
namespace App;

/**
 * Admin Pages Handler
 */
class Admin
{
    public function __construct()
    {
        add_action('admin_menu', [ $this, 'admin_menu' ]);
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('woogoship-vendor');
            wp_enqueue_style('woogoship-admin');
        });

        add_filter('woocommerce_admin_order_actions', [$this, 'add_action_send_shipment'], 10, 2);
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu()
    {
        $capability = 'manage_woocommerce';
        $slug       = WOOGOSHIP_TEXT_DOMAIN;

        $hook = add_submenu_page('woocommerce', 'Goship', 'Goship', $capability, $slug, [ $this, 'plugin_page' ]);

        add_action('load-' . $hook, [ $this, 'init_hooks']);
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks()
    {
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_scripts' ]);
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style('wp-jquery-ui-dialog');
        wp_enqueue_script('woogoship-admin');
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function plugin_page()
    {
        echo '<div class="wrap"><div id="vue-admin-app"></div></div>';
    }

    public function add_action_send_shipment($actions, $order)
    {
        $actions['send-shipment'] = [
                'url'  => admin_url('/admin.php?page=woo-goship#/?search=' . $order->get_id()),
                'name' => 'Gửi sang Goship',
                'action' => 'send-shipment-goship'
            ];
        return $actions;
    }
}

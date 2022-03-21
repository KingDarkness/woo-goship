<?php
namespace App;

/**
 * Scripts and Styles Class
 */
class Assets
{
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [ $this, 'register' ]);
        } else {
            add_action('wp_enqueue_scripts', [ $this, 'register' ]);
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register()
    {
        $this->register_scripts($this->get_scripts());
        $this->register_styles($this->get_styles());
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
        foreach ($scripts as $handle => $script) {
            $deps      = isset($script['deps']) ? $script['deps'] : false;
            $in_footer = isset($script['in_footer']) ? $script['in_footer'] : false;
            $version   = isset($script['version']) ? $script['version'] : WOOGOSHIP_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, $in_footer);
            if ($handle == 'woogoship-admin') {
                $globalValue = ['res_url' => rest_url(), 'admin_url' => admin_url(), 'text_domain' => WOOGOSHIP_TEXT_DOMAIN, 'nonce' => wp_create_nonce('wp_rest')];
                wp_localize_script($handle, 'WEB_GLOBAL_JS_VARIABLES', $globalValue);
            }
            if ($handle == 'woogoship-frontend') {
                $globalValue = ['res_url' => rest_url(), 'text_domain' => WOOGOSHIP_TEXT_DOMAIN];
                wp_localize_script($handle, 'WEB_GLOBAL_JS_VARIABLES', $globalValue);
            }
        }
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
        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;

            wp_register_style($handle, $style['src'], $deps, WOOGOSHIP_VERSION);
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts()
    {
        $prefix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.min' : '';

        $scripts = [
            'woogoship-runtime' => [
                'src'       => WOOGOSHIP_ASSETS . '/js/runtime.js',
                'version'   => filemtime(WOOGOSHIP_PATH . '/assets/js/runtime.js'),
                'in_footer' => true
            ],
            'woogoship-vendor' => [
                'src'       => WOOGOSHIP_ASSETS . '/js/vendors.js',
                'version'   => filemtime(WOOGOSHIP_PATH . '/assets/js/vendors.js'),
                'in_footer' => true
            ],
            'woogoship-frontend' => [
                'src'       => WOOGOSHIP_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'woogoship-vendor', 'woogoship-runtime' ],
                'version'   => filemtime(WOOGOSHIP_PATH . '/assets/js/frontend.js'),
                'in_footer' => true
            ],
            'woogoship-admin' => [
                'src'       => WOOGOSHIP_ASSETS . '/js/admin.js',
                'deps'      => [ 'jquery', 'jquery-ui-dialog', 'woogoship-vendor', 'woogoship-runtime' ],
                'version'   => filemtime(WOOGOSHIP_PATH . '/assets/js/admin.js'),
                'in_footer' => true
            ]
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles()
    {
        $styles = [
            'woogoship-style' => [
                'src' =>  WOOGOSHIP_ASSETS . '/css/style.css'
            ],
            'woogoship-vendor' => [
                'src' =>  WOOGOSHIP_ASSETS . '/css/vendors.css'
            ],
            'woogoship-frontend' => [
                'src' =>  WOOGOSHIP_ASSETS . '/css/frontend.css'
            ],
            'woogoship-admin' => [
                'src' =>  WOOGOSHIP_ASSETS . '/css/admin.css',
            ],
        ];

        return $styles;
    }
}

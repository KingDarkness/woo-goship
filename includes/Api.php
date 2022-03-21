<?php
namespace App;

use App\Api\Order;
use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Api extends WP_REST_Controller
{

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->includes();

        add_action('rest_api_init', [ $this, 'register_routes' ]);
    }

    /**
     * Include the controller classes
     *
     * @return void
     */
    private function includes()
    {
        if (!class_exists(__NAMESPACE__ . '\Api\Goship')) {
            require_once __DIR__ . '/Api/Goship.php';
        }
        if (!class_exists(__NAMESPACE__ . '\Api\Pickup')) {
            require_once __DIR__ . '/Api/Pickup.php';
        }
        if (!class_exists(__NAMESPACE__ . '\Api\Order')) {
            require_once __DIR__ . '/Api/Order.php';
        }
    }

    /**
     * Register the API routes
     *
     * @return void
     */
    public function register_routes()
    {
        (new Api\Goship())->register_routes();
        (new Api\Pickup())->register_routes();
        (new Api\Order())->register_routes();
    }
}

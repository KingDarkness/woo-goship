<?php
namespace App\Api;

use App\Model\GsPickup;
use Kingdarkness\Goship\V2\Shipment;
use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Order extends WP_REST_Controller
{

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->namespace = 'goship/v1';
        $options = get_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_settings', null);
    }

    /**
     * Register the routes
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/orders/(?P<id>\d+)',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'send_order_info' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );
    }

    public function send_order_info($request)
    {
        try {
            $order = new \WC_Order($request->get_param('id'));

            $pickupId = $order->get_meta('_shipping_pickup') ? $order->get_meta('_shipping_pickup') : $order->get_meta('_billing_pickup');
            $pickup = (new GsPickup())->getByIdOrDefault($pickupId);

            $cod = 0;
            $weight = 0;
            $productName = [];

            $items = $order->get_items();
            foreach ($items as $product) {
                $quantity = $product['quantity'];
                $productId = $product['product_id'];
                $itemWeight = get_post_meta($productId, '_weight', true);

                $cod = $cod + $product['total'];
                $weight = $weight + ($itemWeight * $quantity);
                $weight = $weight > 50 ? $weight : 50;

                $productName[] = $quantity . ' x ' . $product->get_name();
            }

            if ($order->get_payment_method() != 'cod') {
                $cod = 0;
            }

            if ($order->get_meta('_gs_cod')) {
                $cod = $order->get_meta('_gs_cod');
            }
            if ($order->get_meta('_gs_weight')) {
                $weight = $order->get_meta('_gs_weight');
            }
            $amount = 0;

            if ($order->get_meta('_gs_amount')) {
                $amount = $order->get_meta('_gs_amount');
            }

            $shipment = [
                'rate' => $order->get_meta('_billing_gs_rate_id'),
                'from_city' => $pickup->city_code,
                'from_district' => $pickup->district_code,
                'from_ward' => $pickup->ward_id,
                'from_street' => $pickup->street,
                'sender_name' => $pickup->name,
                'sender_phone' => $pickup->phone,
                'to_city' => $order->shipping_city ? $order->shipping_city : $order->billing_city,
                'to_district'  => $order->get_meta('_shipping_district') ? $order->get_meta('_shipping_district') : $order->get_meta('_billing_district'),
                'to_ward' => $order->get_meta('_shipping_ward') ? $order->get_meta('_shipping_ward') : $order->get_meta('_billing_ward'),
                'to_street' => $order->get_shipping_address_1() ? $order->get_shipping_address_1() : $order->get_billing_address_1(),
                'receiver_name' => $order->get_shipping_first_name() ? $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() : $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
                'receiver_phone' => $order->get_shipping_phone() ? $order->get_shipping_phone() : $order->get_billing_phone(),
                'cod' => $cod,
                'amount' => $amount,
                'weight' => $weight,
                'payer' => $order->get_meta('_gs_payer') ? $order->get_meta('_gs_payer') : Shipment::CUSTOMER_PAY,
                'package_name' => $productName ? substr(implode(',', $productName), 0, 200) : 'Gói hàng #' . $order->get_id(),
                'metadata' => $order->get_customer_note(),
                'order_id' => $order->get_id(),
                'node_code' => '',
                'length' => 0,
                'width' => 0,
                'height' => 0,
                'pickup_id' => $pickup->id
            ];
            return rest_ensure_response([
                'shipment' => $shipment,
                'helpers' => ['statuses' => Shipment::getStatusText(), 'payers' => [Shipment::CUSTOMER_PAY => 'Người nhận trả', Shipment::SHOP_PAY => 'Người gửi trả']]
            ]);
        } catch (\Exception $th) {
            return new \WP_Error('invalid_data', $th->getMessage(), [ 'status' => 422, 'exception' => $th->getMessage() ]);
        }
    }

    /**
     * Checks if a given request has access to read the items.
     *
     * @param  WP_REST_Request $request Full details about the request.
     *
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function permissions_check($request)
    {
        return is_user_logged_in();
    }

    /**
     * Retrieves the query params for the items collection.
     *
     * @return array Collection parameters.
     */
    public function get_collection_params()
    {
        return ['foo' => 'baz'];
    }
}

<?php
namespace App\Api;

use Kingdarkness\Goship\Arr;
use Kingdarkness\Goship\Exceptions\ValidateException;
use Kingdarkness\Goship\Goship as GoshipSdk;
use Kingdarkness\Goship\V2\Shipment;
use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Goship extends WP_REST_Controller
{
    /**
     * @var GoshipSdk
     */
    public $goship;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->namespace = 'goship/v1';
        $options = get_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_settings', null);
        $this->goship = new GoshipSdk($options['client_id'], $options['client_secret'], $options['access_token'], null, null, $options['api_enpoint']);
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
            '/cities',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'cities'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/districts/(?P<cityCode>\w+)',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'districts'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/wards/(?P<districtCode>\w+)',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'wards'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/shipments',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'shipments'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/shipments',
            [
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'createShipment'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/shipments/(?P<shipmentCode>\w+)',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'shipment'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/shipments/(?P<shipmentCode>\w+)/print',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [$this, 'printShipment'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/shipments/(?P<orderId>\d+)',
            [
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => [$this, 'deleteShipment'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/rates',
            [
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'rates'],
                    'permission_callback' => [$this, 'permissions_check'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/webhook',
            [
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'listenWebhook'],
                ]
            ]
        );
    }

    public function cities($request)
    {
        $response = rest_ensure_response($this->goship->getCities());
        return $response;
    }

    public function districts($request)
    {
        $response = rest_ensure_response($this->goship->getDistricts($request->get_param('cityCode')));
        return $response;
    }

    public function wards($request)
    {
        $response = rest_ensure_response($this->goship->getWards($request->get_param('districtCode')));
        return $response;
    }

    public function shipments($request)
    {
        $response = rest_ensure_response($this->goship->getShipments());
        return $response;
    }

    public function shipment($request)
    {
        $response = rest_ensure_response($this->goship->getShipment($request->get_param('shipmentCode')));
        return $response;
    }

    public function printShipment($request)
    {
        $response = rest_ensure_response(['url' => $this->goship->getPrintUrl($request->get_param('shipmentCode'))]);
        return $response;
    }

    public function deleteShipment($request)
    {
        try {
            $order = new \WC_Order($request->get_param('orderId'));
            $gsCode = $order->get_meta('_gs_code');
            $this->goship->cancelShipment($gsCode);
            $order->update_meta_data('_gs_status', Shipment::STATUS_CANCEL);
            $order->update_meta_data('_gs_status_name', Shipment::getStatusText(Shipment::STATUS_CANCEL));
            $order->save();
            return rest_ensure_response(['message' => 'success']);
        } catch (ValidateException $e) {
            return new \WP_Error('invalid_data', __('Thiêu thông tin'), ['status' => 422, 'errors' => $e->errors]);
        } catch (\Exception $th) {
            return new \WP_Error('invalid_data', $th->getMessage(), ['status' => 422, 'exception' => $th->getMessage()]);
        }
    }

    public function createShipment($request)
    {
        $required = [
            'from_city' => 'Thành phố gửi',
            'from_district' => 'Quận huyện gửi',
            'from_ward' => 'Phường xã gửi',
            'from_street' => 'Địa chỉ gửi',
            'sender_name' => 'Tên người gửi',
            'sender_phone' => 'SĐT người gửi',
            'to_city' => 'Thành phố đến',
            'to_district' => 'Quận huyện đến',
            'to_ward' => 'Quận huyện đến',
            'to_street' => 'Địa chỉ đến',
            'receiver_name' => 'Tên người nhận',
            'receiver_phone' => 'SĐT người nhận',
            'rate' => 'Bảng giá',
            'pickup_id' => 'Địa điểm lấy hàng',
            'order_id' => 'Mã đơn hàng'
        ];
        foreach ($required as $key => $name) {
            if (!Arr::get($request, $key)) {
                return new \WP_Error('invalid_data', __($name . ' không được để trổng'), ['status' => 422, 'key' => $key]);
            }
        }

        try {
            $order = new \WC_Order($request->get_param('order_id'));
            $params = $request->get_params();
            $params['order_id'] = $this->generateOrderId($params['order_id']);
            $response = $this->goship->createShipment($params);

            $receiverName = $request['receiver_name'];
            $receiverName = explode(' ', $request['receiver_name']);
            $firstName =  array_shift($receiverName);
            $lastName = implode(' ', $receiverName);

            $order->update_meta_data('_shipping_pickup', $request['pickup_id']);
            $order->update_meta_data('_billing_gs_rate_id', $request['rate']);
            $order->update_meta_data('_shipping_city', $request['to_city']);
            $order->update_meta_data('_shipping_district', $request['to_district']);
            $order->update_meta_data('_shipping_ward', $request['to_ward']);
            $order->update_meta_data('_shipping_address_1', $request['to_street']);
            $order->update_meta_data('_shipping_first_name', $firstName);
            $order->update_meta_data('_shipping_last_name', $lastName);
            $order->update_meta_data('_shipping_phone', $request['receiver_phone']);
            $order->update_meta_data('_gs_weight', Arr::get($request, 'weight', 500));
            $order->update_meta_data('_gs_cod', Arr::get($request, 'cod', 0));
            $order->update_meta_data('_gs_amount', Arr::get($request, 'amount', 0));
            $order->update_meta_data('_gs_payer', Arr::get($request, 'payer', Shipment::CUSTOMER_PAY));
            $order->update_meta_data('_gs_code', $response['id']);
            $order->update_meta_data('_gs_tracking_number', $response['tracking_number']);
            $order->update_meta_data('_gs_carrier', $response['carrier']);
            $order->update_meta_data('_gs_send_at', $response['created_at']);
            $order->update_meta_data('_gs_fee', $response['fee']);
            $order->update_meta_data('_gs_status', Shipment::STATUS_NEW);
            $order->update_meta_data('_gs_status_name', Shipment::getStatusText(Shipment::STATUS_NEW));
            // if (Arr::get($request, 'from_city_name')) {
            //     $order->update_meta_data('_billing_city_name', $request['from_city_name']);
            // }
            // if (Arr::get($request, 'from_district_name')) {
            //     $order->update_meta_data('_billing_district_name', $request['from_district_name']);
            // }
            // if (Arr::get($request, 'from_ward_name')) {
            //     $order->update_meta_data('_billing_ward_name', $request['from_ward_name']);
            // }
            if (Arr::get($request, 'to_city_name')) {
                $order->update_meta_data('_billing_city_name', $request['to_city_name']);
            }
            if (Arr::get($request, 'to_district_name')) {
                $order->update_meta_data('_billing_district_name', $request['to_district_name']);
            }
            if (Arr::get($request, 'to_ward_name')) {
                $order->update_meta_data('_billing_ward_name', $request['to_ward_name']);
            }
            $order->save();
        } catch (ValidateException $e) {
            return new \WP_Error('invalid_data', __('Thiêu thông tin'), ['status' => 422, 'errors' => $e->errors]);
        } catch (\Exception $th) {
            return new \WP_Error('invalid_data', $th->getMessage(), ['status' => 422, 'exception' => $th->getMessage()]);
        }
        return rest_ensure_response($response);
    }

    public function rates($request)
    {
        if (! isset($request['from_city'])) {
            return new \WP_Error('invalid_data', __('Thành phố gửi không được để trổng'), ['status' => 422, 'key' => 'from_city']);
        }
        if (! isset($request['from_district'])) {
            return new \WP_Error('invalid_data', __('Quận huyện gửi không được để trổng'), ['status' => 422, 'key' => 'from_district']);
        }
        if (! isset($request['to_city'])) {
            return new \WP_Error('invalid_data', __('Thành phố nhận không được để trổng'), ['status' => 422, 'key' => 'to_city']);
        }
        if (! isset($request['to_district'])) {
            return new \WP_Error('invalid_data', __('Quận huyện nhận không được để trổng'), ['status' => 422, 'key' => 'to_district']);
        }

        $response = rest_ensure_response($this->goship->getRates($request->get_params()));
        return $response;
    }

    /**
     * Retrieves a collection of items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function test($request)
    {
        $options = get_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_settings', null);
        $items = [
            'foo' => 'bar',
            'url' => rest_url(),
            'config_options' => $options
        ];

        $response = rest_ensure_response($items);

        return $response;
    }

    public function listenWebhook($request)
    {
        try {
            $data = $this->goship->verifyWebhook();
            if (!Arr::get($data, 'order_id')) {
                return rest_ensure_response(['message' => 'missing_order_id']);
            }

            $id = $this->parseToOrderId($data['order_id']);

            $order = new \WC_Order($id);

            $order->update_meta_data('_gs_weight', $data['weight']);
            $order->update_meta_data('_gs_cod', $data['cod']);
            $order->update_meta_data('_gs_payer', $data['payer']);
            $order->update_meta_data('_gs_tracking_number', $data['code']);
            $order->update_meta_data('_gs_fee', $data['fee']);
            $order->update_meta_data('_gs_status', $data['status']);
            $order->update_meta_data('_gs_status_name', Shipment::getStatusText($data['status']));
            if (Arr::get($data, 'message')) {
                $order->update_meta_data('_gs_status_message', $data['message']);
            }
            if (Arr::get($data, 'tracking_url')) {
                $order->update_meta_data('_gs_tracking_url', $data['tracking_url']);
            }

            $order->save();

            return rest_ensure_response(['success' => true]);
        } catch (\Kingdarkness\Goship\Exceptions\UnverifiException $e) {
            return new \WP_Error('unverify', __('Xác thực không hợp lệ'), ['status' => 400]);
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

    private function generateOrderId($id)
    {
        $prefix = get_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_order_prefix');
        return $prefix . '-' . $id;
    }

    public function parseToOrderId($id)
    {
        return end(explode('-', $id));
    }
}

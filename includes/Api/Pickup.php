<?php
namespace App\Api;

use App\Model\GsPickup;
use Kingdarkness\Goship\Arr;
use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Pickup extends WP_REST_Controller
{
    /**
     * @var GsPickup
     */
    public $pickup;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->namespace = 'goship/v1';
        $this->pickup = new GsPickup();
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
            '/pickups',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'getByQuery' ],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/pickups',
            [
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/pickups/(?P<id>\d+)',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'detail' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/pickups/(?P<id>\d+)',
            [
                [
                    'methods'             => \WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/pickups/(?P<id>\d+)',
            [
                [
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ]
            ]
        );
    }

    public function getByQuery($request)
    {
        $response = rest_ensure_response($this->pickup->getByQuery($request->get_params()));
        return $response;
    }

    public function detail($request)
    {
        $pickup = $this->pickup->getById($request->get_param('id'));
        if ($pickup) {
            $response = rest_ensure_response($pickup);
            return $response;
        }
        return new \WP_Error('not_found', __('??i???m l???y h??ng kh??ng t???n t???i'), [ 'status' => 404 ]);
    }

    public function create($request)
    {
        if (! Arr::get($request, 'name')) {
            return new \WP_Error('invalid_data', __('T??n li??n h??? kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'name' ]);
        }

        if (! Arr::get($request, 'phone')) {
            return new \WP_Error('invalid_data', __('S??T kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'phone' ]);
        }

        if (! Arr::get($request, 'street')) {
            return new \WP_Error('invalid_data', __('?????a ch??? kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'street' ]);
        }

        if (! Arr::get($request, 'city_code')) {
            return new \WP_Error('invalid_data', __('Th??nh ph???  kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'city_code' ]);
        }

        if (! Arr::get($request, 'district_code')) {
            return new \WP_Error('invalid_data', __('Qu???n huy???n kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'district_code' ]);
        }

        if (! Arr::get($request, 'ward_id')) {
            return new \WP_Error('invalid_data', __('Ph?????ng x?? kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'ward_id' ]);
        }

        if (! Arr::get($request, 'full_street')) {
            return new \WP_Error('invalid_data', __('?????a ch??? chi ti???t kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'full_street' ]);
        }
        $response = $this->pickup->create($request->get_params());
        if ($response) {
            return rest_ensure_response($response);
        }
        return new \WP_Error('create_failed', __('T???o m???i ??i???m l???y h??ng th???t b???i'), [ 'status' => 422 ]);
    }

    public function update($request)
    {
        if (! Arr::get($request, 'name')) {
            return new \WP_Error('invalid_data', __('T??n li??n h??? kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'name' ]);
        }

        if (! Arr::get($request, 'phone')) {
            return new \WP_Error('invalid_data', __('S??T kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'phone' ]);
        }

        if (! Arr::get($request, 'street')) {
            return new \WP_Error('invalid_data', __('?????a ch??? kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'street' ]);
        }

        if (! Arr::get($request, 'city_code')) {
            return new \WP_Error('invalid_data', __('Th??nh ph???  kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'city_code' ]);
        }

        if (! Arr::get($request, 'district_code')) {
            return new \WP_Error('invalid_data', __('Qu???n huy???n kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'district_code' ]);
        }

        if (! Arr::get($request, 'ward_id')) {
            return new \WP_Error('invalid_data', __('Ph?????ng x?? kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'ward_id' ]);
        }

        if (! Arr::get($request, 'full_street')) {
            return new \WP_Error('invalid_data', __('?????a ch??? chi ti???t kh??ng ???????c ????? tr???ng'), [ 'status' => 422, 'key' => 'full_street' ]);
        }

        $response = $this->pickup->update($request->get_param('id'), $request->get_params());
        if ($response) {
            return rest_ensure_response($response);
        }
        return new \WP_Error('create_failed', __('C???p nh???t ??i???m l???y h??ng th???t b???i'), [ 'status' => 422 ]);
    }

    public function delete($request)
    {
        $pickup = $this->pickup->delete($request->get_param('id'));
        if ($pickup) {
            $response = rest_ensure_response(['message' => 'delete success']);
            return $response;
        }
        return new \WP_Error('not_found', __('??i???m l???y h??ng kh??ng t???n t???i'), [ 'status' => 404 ]);
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

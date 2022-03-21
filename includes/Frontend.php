<?php
namespace App;

use Kingdarkness\Goship\Arr;

/**
 * Frontend Pages Handler
 */
class Frontend
{
    public function __construct()
    {
        $settings = get_option('woocommerce_' . WOOGOSHIP_SHIP_ID . '_settings', null);
        if (Arr::get($settings, 'alow_customer', 'yes') == 'yes') {
            if (!session_id()) {
                session_start();
            }
            add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
            add_action('woocommerce_checkout_order_processed', [$this, 'customPlacedOrder']);
            add_filter('woocommerce_checkout_fields', [$this, 'customCheckoutFields']);
            add_filter('woocommerce_checkout_fields', [$this, 'sortCheckoutFields']);
            add_filter('woocommerce_cart_shipping_packages', [$this, 'customCartShippingPackages'], 10, 1);
            // add_filter('woocommerce_after_checkout_validation', [$this, 'customCheckoutProcess'], 10, 2);
        }
    }

    public function customCartShippingPackages($packages)
    {
        parse_str($_POST['post_data'], $postData);
        if (isset($postData['billing_district'])) {
            $_SESSION['gs_ship_destination'] = $postData;
        }
        foreach ($packages as $key=>$package) {
            $packages[$key]['destination']['billing_district'] = Arr::get($postData, 'billing_district');
            $packages[$key]['destination']['billing_city'] = Arr::get($postData, 'billing_city');
            $packages[$key]['destination']['shipping_district'] = Arr::get($postData, 'shipping_district');
            $packages[$key]['destination']['shipping_city'] = Arr::get($postData, 'shipping_city');
            $packages[$key]['destination']['ship_to_different_address'] = Arr::get($postData, 'ship_to_different_address', false);
            $packages[$key]['destination']['billing_pickup'] = Arr::get($postData, 'billing_pickup');
        }
        return $packages;
    }

    public function customPlacedOrder()
    {
        if (isset($_SESSION['gs_ship_destination'])) {
            unset($_SESSION['gs_ship_destination']);
        }
    }

    public function customCheckoutProcess($fields, $errors)
    {
        dd(\WC()->shipping()->get_packages()[0]['rates']);
    }

    public function enqueueScripts()
    {
        wp_enqueue_style('woogoship-frontend');
        wp_enqueue_script('woogoship-frontend');
    }

    public function customCheckoutFields($fields)
    {
        $fields['billing']['billing_country']['priority'] = 69;
        $fields['billing']['billing_address_1']['priority'] = 79;
        $fields['billing']['billing_address_2']['priority'] = 79;
        $fields['billing']['billing_email']['required'] = false;
        $fields['billing']['billing_pickup'] = [
            'label' => __('Lấy hàng tại', WOOGOSHIP_TEXT_DOMAIN),
            'type' => 'select',
            'required' => true,
            'default' => '',
            'class'     => ['form-row-wide', 'select2', 'address-field', 'update_totals_on_change'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'options' => ['' => 'Chọn cửa hàng nhận hàng'],
            'priority' => 69
        ];
        $fields['billing']['billing_city'] = [
            'label' => __('Tỉnh thành', WOOGOSHIP_TEXT_DOMAIN),
            'type' => 'select',
            'required' => true,
            'default' => '',
            'class'     => ['form-row-wide', 'select2'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'options' => ['' => 'Chọn tỉnh thành'],
            'priority' => 70
        ];

        $fields['billing']['billing_district'] = [
            'type'          => 'select',
            'label'         => __('Quận huyện', WOOGOSHIP_TEXT_DOMAIN),
            'required' => true,
            'class'     => ['form-row-wide', 'select2', 'address-field', 'update_totals_on_change'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'default' => '',
            'options' => ['' => 'Chọn quận huyện'],
            'priority' => 72
        ];

        $fields['billing']['billing_ward'] = [
            'type'          => 'select',
            'label'         => __('Phường xã', WOOGOSHIP_TEXT_DOMAIN),
            'required' => true,
            'class'     => ['form-row-wide', 'select2'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'default' => '',
            'options' => ['' => 'Chọn phường xã'],
            'priority' => 73
        ];

        $fields['shipping']['shipping_country']['priority'] = 69;
        $fields['shipping']['shipping_address_1']['priority'] = 79;
        $fields['shipping']['shipping_address_2']['priority'] = 79;
        $fields['shipping']['shipping_city'] = [
            'label' => __('Tỉnh thành', WOOGOSHIP_TEXT_DOMAIN),
            'type' => 'select',
            'required' => true,
            'default' => '',
            'class'     => ['form-row-wide', 'select2'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'options' => ['' => 'Chọn tỉnh thành'],
            'priority' => 70
        ];

        $fields['shipping']['shipping_district'] = [
            'type'          => 'select',
            'label'         => __('Quận huyện', WOOGOSHIP_TEXT_DOMAIN),
            'required' => true,
            'class'     => ['form-row-wide', 'select2', 'address-field', 'update_totals_on_change'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'default' => '',
            'options' => ['' => 'Chọn quận huyện'],
            'priority' => 72
        ];

        $fields['shipping']['shipping_ward'] = [
            'type'          => 'select',
            'label'         => __('Phường xã', WOOGOSHIP_TEXT_DOMAIN),
            'required' => true,
            'class'     => ['form-row-wide', 'select2'],
            'label_class' => ['infocounty'],
            'clear'     => true,
            'default' => '',
            'options' => ['' => 'Chọn phường xã'],
            'priority' => 73
        ];

        $fields['billing']['billing_city_name'] = [
            'type' => 'hidden',
            'default' => '',
            'id' => 'gs-city-name'
        ];

        $fields['billing']['billing_district_name'] = [
            'type' => 'hidden',
            'default' => '',
            'id' => 'gs-district-name'
        ];

        $fields['billing']['billing_ward_name'] = [
            'type' => 'hidden',
            'default' => '',
            'id' => 'gs-ward-name'
        ];
        $fields['billing']['billing_gs_rate_id'] = [
            'type' => 'hidden',
            'default' => '',
            'id' => 'gs-rate-id'
        ];

        return $fields;
    }

    public function sortCheckoutFields($fields)
    {
        $billingFields = [
                    'billing_company',
                    'billing_first_name',
                    'billing_last_name',
                    'billing_phone',
                    'billing_pickup',
                    'billing_city',
                    'billing_district',
                    'billing_ward',
                    'billing_address_1',
                    'billing_address_2',
                    'billing_postcode',
                    'billing_country',
                    'billing_email',

                ];

        foreach ($billingFields as $field) {
            $orderedBillingFields[$field] = $fields['billing'][$field];
        }
        foreach (array_keys($fields['billing']) as $field) {
            if (!isset($orderedBillingFields[$field])) {
                $orderedBillingFields[$field] = $fields['billing'][$field];
            }
        }

        $fields['billing'] = $orderedBillingFields;

        $shippingFields = [
                    'shipping_company',
                    'shipping_first_name',
                    'shipping_last_name',
                    'shipping_city',
                    'shipping_district',
                    'shipping_ward',
                    'shipping_address_1',
                    'shipping_address_2',

                ];
        foreach ($shippingFields as $field) {
            $orderedShippingFields[$field] = $fields['shipping'][$field];
        }
        foreach (array_keys($fields['shipping']) as $field) {
            if (!isset($orderedBillingFields[$field])) {
                $orderedShippingFields[$field] = $fields['shipping'][$field];
            }
        }

        $fields['shipping'] = $orderedShippingFields;

        return $fields;
    }
}

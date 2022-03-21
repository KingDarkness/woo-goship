<?php
namespace App;

use App\Model\GsPickup;
use Kingdarkness\Goship\Arr;
use Kingdarkness\Goship\Exceptions\ValidateException;
use Kingdarkness\Goship\Goship as GoshipSdk;
use WC_Shipping_Method;

class GoshipShippingMethod extends WC_Shipping_Method
{
    public $client_id;
    public $client_secret;
    public $access_token;
    public $api_enpoint;
    public $allow_customer;

    public function __construct()
    {
        $this->id                 = WOOGOSHIP_SHIP_ID;
        $this->method_title       = __('Goship Shipping', WOOGOSHIP_TEXT_DOMAIN);
        $this->method_description = __('Goship - Nền tảng vận chuyển cho thương mại điện tử', WOOGOSHIP_TEXT_DOMAIN);
        $this->init();

        $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'yes';
        $this->title = isset($this->settings['title']) ? $this->settings['title'] : __('Goship Shipping', WOOGOSHIP_TEXT_DOMAIN);
    }

    public function init()
    {
        // Load the settings API
        // $this->init_settings();
        $this->init_form_fields();

        $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : $this->enabled;
        $this->api_enpoint = isset($this->settings['api_enpoint']) ? $this->settings['api_enpoint'] : WOOGOSHIP_API_URL;
        $this->client_id = isset($this->settings['client_id']) ? $this->settings['client_id'] : '';
        $this->client_secret = isset($this->settings['client_secret']) ? $this->settings['client_secret'] : '';
        $this->access_token = isset($this->settings['access_token']) ? $this->settings['access_token'] : '';
        $this->allow_customer = isset($this->settings['allow_customer']) && $this->settings['allow_customer'] ? $this->settings['allow_customer'] : 1;

        // Save settings in admin if you have any defined
        add_action('woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ]);
    }

    /**
     * Define settings field for this shipping
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'api_enpoint' => [
                'title'       => __('Api enpoint', WOOGOSHIP_TEXT_DOMAIN),
                'type'        => 'text',
                'id'          => 'api_enpoint',
                'description' => __('Url api của goship', WOOGOSHIP_TEXT_DOMAIN),
                'default'     => WOOGOSHIP_API_URL
            ],
            'client_id' => [
                'title'       => __('Client ID', WOOGOSHIP_TEXT_DOMAIN),
                'type'        => 'text',
                'id'          => 'client_id',
                'description' => __('Client ID bạn đăng ký trên Goship', WOOGOSHIP_TEXT_DOMAIN),
                'default'     => ''
            ],
            'client_secret' => [
                'title'       => __('Client Secret', WOOGOSHIP_TEXT_DOMAIN),
                'type'        => 'text',
                'description' => __('Client Secret bạn đăng ký trên Goship', WOOGOSHIP_TEXT_DOMAIN),
                'default'     => ''
            ],
            'access_token' => [
                'title'       => __('AccessToken', WOOGOSHIP_TEXT_DOMAIN),
                'type'        => 'text',
                'description' => __('AccessToken trên Goship', WOOGOSHIP_TEXT_DOMAIN),
                'default'     => ''
            ],
            'alow_customer' => [
                'title'       => __('Cho phép khách hàng chọn HVC', WOOGOSHIP_TEXT_DOMAIN),
                'type'        => 'checkbox',
                'description' => __('Cho phép khách hàng chọn HVC khi thanh toán giỏ hàng', WOOGOSHIP_TEXT_DOMAIN),
                'default'     => 1
            ]
        ];
    }

    /**
     * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
     *
     * @access public
     * @param mixed $package
     * @return void
     */
    public function calculate_shipping($package = [])
    {
        // We will add the cost, rate and logics in here
        // dd(WC()->checkout->get_value('billing_city'));
        // $postData = $_SESSION['gs_ship_destination'];
        parse_str($_POST['post_data'], $postData);
        // $postData = $_SESSION['gs_ship_destination'];
        if (isset($_SESSION['gs_ship_destination']) && count($_SESSION['gs_ship_destination'])) {
            $postData = $_SESSION['gs_ship_destination'];
        }


        if ((Arr::get($postData, 'ship_to_different_address') && !Arr::get($postData, 'shipping_district')) || !Arr::get($postData, 'billing_district')) {
            return;
        }

        $goship = new GoshipSdk($this->client_id, $this->client_secret, $this->access_token, null, null, $this->api_enpoint);


        $cod = 0;
        $weight = 0;

        $payment_method = WC()->session->get('chosen_payment_method');
        foreach ($package['contents'] as $item => $values) {
            $quantity = $values['quantity'];
            $cod = $cod + $values['line_total'];
            $weight = $weight + ($values['data']->get_weight() * $quantity);
            $weight = $weight > 50 ? $weight : 50;
        }


        if ($payment_method != 'cod') {
            $cod = 0;
        }

        $pickup = (new GsPickup())->getByIdOrDefault(Arr::get($postData, 'billing_pickup'));
        if (!$pickup) {
            return;
        }

        $rates = [];
        $data = [
            'from_city'     => $pickup->city_code,
            'from_district' => $pickup->district_code,
            'to_city'       => Arr::get($postData, 'ship_to_different_address', false) ? $postData['shipping_city'] : $postData['billing_city'],
            'to_district'   => Arr::get($postData, 'ship_to_different_address', false) ? $postData['shipping_district'] : $postData['billing_district'],
            'cod'           => $cod,
            'amount'        => 0,
            'weight'        => $weight,
            'payer'         => \Kingdarkness\Goship\V2\Shipment::CUSTOMER_PAY,
        ];

        try {
            $rates = $goship->getRates($data);
            // dd($rates);
        } catch (ValidateException $e) {
            print_r($e->errors);
        }

        if (count($rates)) {
            foreach ($rates as $key => $value) {
                $rate = [
                        'id' => $value['id'],
                        'label' => '[' . $value['service'] . '] ' .$value['carrier_name'],
                        'cost' => $value['total_amount']
                    ];
                $this->add_rate($rate);
            }
        }
    }

    /**
     *
     * config init_form_fields function
     *
     * @access public
     * @return void
     */
    public function config_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title'       => __('Cho phép hoạt động', WOOGOSHIP_TEXT_DOMAIN),
                'type'        => 'checkbox',
                'description' => __('Cho phép Goship Shiping hoạt động trên website của bạn.', WOOGOSHIP_TEXT_DOMAIN),
                'default'     => 'yes'
            ]
        ];
    }

    public function admin_options()
    {
        ?>
            <div class="wf-banner updated below-h2">
            <p class="main">
                <ul>
                    <li><strong>Cấu hình để bắt đầu xử dụng goship.</strong></li>
                </ul>
            </p>
            <p><a href="https://doc.goship.io" target="_blank" class="button button-primary">Link tài liệu kết nối</a></p>
            <p class="main">
                <ul>
                    <li><strong>URL nhận webhook.</strong></li>
                </ul>
            </p>
            <p>
                <b><?php echo rest_url('/goship/v1/webhook'); ?></b>
                <br /><br /> <small>Hãy sử dụng URL trên vào cấu hình nhận tin webhook trên Goship</small>
            </p>

            <p class="main">
                <ul>
                    <li><strong>Hãy điền biểu mẫu bên dưới sau đó cài đặt điểm lấy hàng ở liên kết bên dưới</strong></li>
                </ul>
            </p>
            <p><a href="<?php echo admin_url('/admin.php?page=woo-goship#/pickups'); ?>" target="_blank" class="button button-default">Cài đặt điểm lấy hàng</a></p>
        </div>
        <?php
        parent::admin_options();
    }
}

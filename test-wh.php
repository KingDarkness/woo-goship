<?php

function generateWebhookToken($data, $clientSecret)
{
    $data = json_encode($data);
    return base64_encode(hash_hmac('sha256', $data, $clientSecret, true));
}

$dataTest = [
    'gcode'    => 'GS0001',
    'code'     => 'GS0001',
    'order_id' => '31',
    'weight'   => 500,
    'fee'      => 20000,
    'cod'      => 0,
    'payer'    => 0,
    'status'   => 900,
    'message'  => 'test',
    'tracking_url' => ''
];

$clientSecret = 'csfZHIwfJ9SJrEM9B82r6ZQbQiiwclCv2d16xOmf';
echo json_encode($dataTest, true);
echo PHP_EOL;
echo PHP_EOL;
echo generateWebhookToken($dataTest, $clientSecret);

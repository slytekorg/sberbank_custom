<?php

class SberbankHandler
{

    const OPTIONS = [
        'MODULE_ID' => '',
        'SBERBANK_PROD_URL' => '',
        'SBERBANK_TEST_URL' => '',
        'MODULE_VERSION' => '',
        'ISO' => '',
        'CMS_VERSION' => '',
        'TAX_DEFAULT' => '',
        'IGNORE_PRODUCT_TAX' => '',
        'RBS_ENABLE_CALLBACK' => '',
        'SBERBANK_OFD_TAX_SYSTEM' => '',
        'SBERBANK_OFD_RECIEPT' => '',
        'SBERBANK_FFD_VERSION' => '',
        'SBERBANK_FFD_PAYMENT_OBJECT' => '',
        'SBERBANK_FFD_PAYMENT_OBJECT_DELIVERY' => '',
        'SBERBANK_FFD_PAYMENT_METHOD' => '',
        'SBERBANK_FFD_PAYMENT_METHOD_DELIVERY' => '',
        'SBERBANK_GATE_TEST_MODE' => '',
        'SBERBANK_HANDLER_TWO_STAGE' => '',
        'SBERBANK_HANDLER_LOGGING' => '',
    ];

    protected $params = [];

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function initiatePay()
    {

        $RBS_Gateway = new \Sberbank\Payments\Gateway;

        // module settings
        $RBS_Gateway->setOptions(array(
            'module_id' => self::OPTIONS['MODULE_ID'],
            'gate_url_prod' => self::OPTIONS['SBERBANK_PROD_URL'],
            'gate_url_test' => self::OPTIONS['SBERBANK_TEST_URL'],
            'module_version' => self::OPTIONS['MODULE_VERSION'],
            'iso' => self::OPTIONS['ISO'],
            'cms_version' => self::OPTIONS['CMS_VERSION'],
            'language' => 'ru',
            'default_cartItem_tax' => self::OPTIONS['TAX_DEFAULT'],
            'ignore_product_tax' => self::OPTIONS['IGNORE_PRODUCT_TAX'],
            'callback_mode' => self::OPTIONS['RBS_ENABLE_CALLBACK'],
            'test_mode' => self::OPTIONS['SBERBANK_GATE_TEST_MODE'],
            'handler_two_stage' => self::OPTIONS['SBERBANK_HANDLER_TWO_STAGE'],
            'handler_logging' => self::OPTIONS['SBERBANK_HANDLER_LOGGING']

            // 'ofd_tax' => self::OPTIONS['SBERBANK_OFD_TAX_SYSTEM'],
            // 'ofd_enabled' => self::OPTIONS['SBERBANK_OFD_RECIEPT'],
            // 'ffd_version' => self::OPTIONS['SBERBANK_FFD_VERSION'],
            // 'ffd_payment_object' => self::OPTIONS['SBERBANK_FFD_PAYMENT_OBJECT'],
            // 'ffd_payment_object_delivery' => self::OPTIONS['SBERBANK_FFD_PAYMENT_OBJECT_DELIVERY'],
            // 'ffd_payment_method' => self::OPTIONS['SBERBANK_FFD_PAYMENT_METHOD'],
            // 'ffd_payment_method_delivery' => self::OPTIONS['SBERBANK_FFD_PAYMENT_METHOD_DELIVERY'],

            // 'additionalOfdParams' => array(
            // 'agent_info.type' => 6,
            // 'agent_info.paying.operation' => '',
            // 'agent_info.paying.phones' => '',
            // 'agent_info.paymentsOperator.phones' => '',
            // 'agent_info.MTOperator.address' => '',
            // 'agent_info.MTOperator.inn' => '',
            // 'agent_info.MTOperator.name' => '',
            // 'agent_info.MTOperator.phones' => '',
            // 'supplier_info.inn' => '',
            // 'supplier_info.name' => '',
            // 'supplier_info.phones' => '',
            // 'cashier' => '',
            // 'additional_check_props' => '',
            // 'additional_user_props.name' => '',
            // 'additional_user_props.value' => '',
            // ),
        ));


        $RBS_Gateway->buildData(array(
            'orderNumber' => $this->params['ORDER_NUMBER'],
            'amount' => $this->params['ORDER_AMOUNT'],
            'userName' => $this->params['SBERBANK_GATE_LOGIN'],
            'password' => $this->params['SBERBANK_GATE_PASSWORD'],
            'description' => $this->params['ORDER_DESCRIPTION']
        ));

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== "off" ? 'https://' : 'http://';
        $domain_name = strtok($_SERVER['HTTP_HOST'], ":");

        if (strlen($domain_name) < 3)
        {
            $domain_name = $_SERVER['SERVER_NAME'];
        }

        if (strlen($domain_name) > 3)
        {
            $RBS_Gateway->setOptions(
                array(
                    'domain_finded' => true,
                    'callback_url' => html_entity_decode($protocol . $domain_name . '/sberbank_result.php')
                )
            );
        }

        $RBS_Gateway->buildData(array(
            'returnUrl' => $protocol . $domain_name . '/sberbank_result.php' . '?ORDER_ID=' . $this->params['ORDER_ID']
        ));

        $RBS_Gateway->setOptions(array(
            'customer_name' => $this->params['NAME'],
            'customer_email' => $this->params['EMAIL'],
            'customer_phone' => $this->params['PHONE']
        ));

        $gateResponse = $RBS_Gateway->registerOrder();

        $result = array(
            'sberbank_result' => $gateResponse,
            'payment_link' => $RBS_Gateway->getPaymentLink(),
            'currency' => $this->params['CURRENCY'],
        );
        return $result;
    }

    public function processRequest()
    {
        $RBS_Gateway = new \Sberbank\Payments\Gateway;
        $RBS_Gateway->setOptions(array(
            // module settings
            'gate_url_prod' => self::OPTIONS['SBERBANK_PROD_URL'],
            'gate_url_test' => self::OPTIONS['SBERBANK_TEST_URL'],
            'test_mode' => self::OPTIONS['SBERBANK_GATE_TEST_MODE'],
            'callback_redirect' => self::OPTIONS['CALLBACK_REDIRECT']
        ));

        $RBS_Gateway->buildData(array(
            'userName' => self::OPTIONS['SBERBANK_GATE_LOGIN'],
            'password' => self::OPTIONS['SBERBANK_GATE_PASSWORD'],
            'orderId' => $_REQUEST['orderId'],
        ));

        $gateResponse = $RBS_Gateway->checkOrder();

        $resultId = $gateResponse['orderNumber'];

        $successPayment = true;

        if ($resultId != $this->params['SBERBANK_ORDER_NUMBER'])
        {
            $resultId = false;
        }

        if ($gateResponse['errorCode'] != 0 || ($gateResponse['orderStatus'] != 1 && $gateResponse['orderStatus'] != 2))
        {
            $resultId = false;
        }

        return $resultId;
    }
}

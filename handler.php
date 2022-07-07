<?php

class SberbankHandler
{

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
            'language' => 'ru',
            'default_cartItem_tax' => $this->params['TAX_DEFAULT'],
            'test_mode' => $this->params['TEST_MODE'],
            'handler_logging' => $this->params['LOGGING']
        ));


        $RBS_Gateway->buildData(array(
            'orderNumber' => $this->params['ORDER_NUMBER'],
            'amount' => $this->params['ORDER_AMOUNT'],
            'userName' => $this->params['LOGIN'],
            'password' => $this->params['PASSWORD'],
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
            'test_mode' => $this->params['TEST_MODE'],
            'callback_redirect' => $_REQUEST['CALLBACK_REDIRECT']=='1'
        ));

        $RBS_Gateway->buildData(array(
            'userName' => $this->params['LOGIN'],
            'password' => $this->params['PASSWORD'],
            'orderId' => $_REQUEST['CALLBACK_REDIRECT'] == 1 ? $_REQUEST['mdOrder'] : $_REQUEST['orderId']
        ));

        $gateResponse = $RBS_Gateway->checkOrder();

        $resultId = $gateResponse['orderNumber'];

        $successPayment = true;

        if ($resultId != $this->params['ORDER_NUMBER'])
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

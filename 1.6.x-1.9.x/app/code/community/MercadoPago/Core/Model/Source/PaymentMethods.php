<?php

/**
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL).
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category       Payment Gateway
 * @package        MercadoPago
 * @author         Gabriel Matsuoka (gabriel.matsuoka@gmail.com)
 * @copyright      Copyright (c) MercadoPago [http://www.mercadopago.com]
 * @license        http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MercadoPago_Core_Model_Source_PaymentMethods
    extends Mage_Payment_Model_Method_Abstract
{
    const XML_PATH_ACCESS_TOKEN = 'payment/mercadopago_custom_checkout/access_token';

    public function toOptionArray()
    {
        $methods = array();

        //adiciona um valor vazio caso nao queria excluir nada
        $methods[] = array("value" => "", "label" => "");

        $access_token = Mage::getStoreConfig(self::XML_PATH_ACCESS_TOKEN);
        $clientId = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_ID);
        $clientSecret = Mage::getStoreConfig(MercadoPago_Core_Helper_Data::XML_PATH_CLIENT_SECRET);
        if (empty($access_token) && (empty($clientId) || empty($clientSecret))) {
            return $methods;
        }

        //verifico se as credenciais não são vazias, caso sejam não é possível obte-los
        if (empty($access_token)) {
            $access_token = Mage::helper('mercadopago')->getApiInstance($clientId, $clientSecret)->get_access_token();
        }

        Mage::helper('mercadopago')->log("Get payment methods by country... ", 'mercadopago.log');
        Mage::helper('mercadopago')->log("API payment methods: " . "/v1/payment_methods?access_token=" . $access_token, 'mercadopago.log');
        $response = MercadoPago_Lib_RestClient::get("/v1/payment_methods?access_token=" . $access_token);

        Mage::helper('mercadopago')->log("API payment methods", 'mercadopago.log', $response);

        $response = $response['response'];

        foreach ($response as $m) {
            if ($m['id'] != 'account_money') {
                $methods[] = array(
                    'value' => $m['id'],
                    'label' => Mage::helper('mercadopago')->__($m['name'])
                );
            }
        }

        return $methods;
    }
}

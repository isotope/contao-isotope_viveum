<?php

/**
 * Extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 terminal42 gmbh
 *
 * @package    isotope_viveum
 * @link       http://www.terminal42.ch
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 */


class PaymentViveum extends IsotopePayment
{
    /**
     * SHA-OUT relevant fields
     * @var array
     */
    private static $arrShaOut = array
    (
        'AAVADDRESS',
        'AAVCHECK',
        'AAVZIP',
        'ACCEPTANCE',
        'ALIAS',
        'AMOUNT',
        'BIN',
        'BRAND',
        'CARDNO',
        'CCCTY',
        'CN',
        'COMPLUS',
        'CREATION_STATUS',
        'CURRENCY',
        'CVCCHECK',
        'DCC_COMMPERCENTAGE',
        'DCC_CONVAMOUNT',
        'DCC_CONVCCY',
        'DCC_EXCHRATE',
        'DCC_EXCHRATESOURCE',
        'DCC_EXCHRATETS',
        'DCC_INDICATOR',
        'DCC_MARGINPERCENTAGE',
        'DCC_VALIDHOURS',
        'DIGESTCARDNO',
        'ECI',
        'ED',
        'ENCCARDNO',
        'IP',
        'IPCTY',
        'NBREMAILUSAGE',
        'NBRIPUSAGE',
        'NBRIPUSAGE_ALLTX',
        'NBRUSAGE',
        'NCERROR',
        'ORDERID',
        'PAYID',
        'PM',
        'SCO_CATEGORY',
        'SCORING',
        'STATUS',
        'SUBBRAND',
        'SUBSCRIPTION_ID',
        'TRXDATE',
        'VC'
    );

    /**
     * Process payment on confirmation page.
     */
    public function processPayment()
    {
        if ($this->Input->get('NCERROR') > 0) {
            $this->log('Order ID "' . $this->Input->get('orderID') . '" has NCERROR ' . $this->Input->get('NCERROR'), __METHOD__, TL_ERROR);
            return false;
        }

        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('id', $this->Input->get('orderID'))) {
            $this->log('Order ID "' . $this->Input->get('orderID') . '" not found', __METHOD__, TL_ERROR);
            return false;
        }

        if (!$this->validateSHASign()) {
            $this->log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);
            return false;
        }

        // Validate payment data
        if ($objOrder->currency != $this->Input->post('currency') || $objOrder->grandTotal != $this->Input->post('amount')) {
            $this->log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);
            $this->redirect($this->addToUrl('step=failed', true));
        }

        $objOrder->date_paid = time();
        $objOrder->save();

        return true;
    }


    /**
     * Process post-sale request from the VIVEUM payment server.
     */
    public function processPostSale()
    {
        if ($this->Input->post('NCERROR') > 0) {
            $this->log('Order ID "' . $this->Input->post('orderID') . '" has NCERROR ' . $this->Input->post('NCERROR'), __METHOD__, TL_ERROR);
            return;
        }

        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('id', $this->Input->post('orderID'))) {
            $this->log('Order ID "' . $this->Input->post('orderID') . '" not found', __METHOD__, TL_ERROR);
            return;
        }

        if (!$this->validateSHASign()) {
            $this->log('Received invalid postsale data for order ID "' . $objOrder->id . '"', __METHOD__, TL_ERROR);
            return;
        }

        // Validate payment data
        if ($objOrder->currency != $this->Input->post('currency') || $objOrder->grandTotal != $this->Input->post('amount')) {
            $this->log('Postsale checkout manipulation in payment for Order ID ' . $objOrder->id . '!', __METHOD__, TL_ERROR);
            return;
        }

        if (!$objOrder->checkout()) {
            $this->log('Post-Sale checkout for Order ID "' . $objOrder->id . '" failed', __METHOD__, TL_ERROR);
            return;
        }

        $objOrder->date_paid = time();
        $objOrder->updateOrderStatus($this->new_order_status);

        $objOrder->save();
    }


    /**
     * Return the payment form.
     * @return  string
     */
    public function checkoutForm()
    {
        $objOrder = new IsotopeOrder();

        if (!$objOrder->findBy('cart_id', $this->Isotope->Cart->id)) {
            $this->redirect($this->addToUrl('step=failed', true));
        }

        $objAddress = $this->Isotope->Cart->billingAddress;
        $strFailedUrl = $this->Environment->base . $this->addToUrl('step=failed', true);

        $arrParam = array
        (
            'PSPID' => $this->viveum_pspid,
            'ORDERID' => $objOrder->id,
            'AMOUNT' => round(($this->Isotope->Cart->grandTotal * 100)),
            'CURRENCY' => $this->Isotope->Config->currency,
            'LANGUAGE' => $GLOBALS['TL_LANGUAGE'] . '_' . strtoupper($GLOBALS['TL_LANGUAGE']),
            'CN' => $objAddress->firstname . ' ' . $objAddress->lastname,
            'EMAIL' => $objAddress->email,
            'OWNERZIP' => $objAddress->postal,
            'OWNERADDRESS' => $objAddress->street_1,
            'OWNERADDRESS2' => $objAddress->street_2,
            'OWNERCTY' => $objAddress->country,
            'OWNERTOWN' => $objAddress->city,
            'OWNERTELNO' => $objAddress->phone,
            'ACCEPTURL' => $this->Environment->base . IsotopeFrontend::addQueryStringToUrl('uid=' . $objOrder->uniqid, $this->addToUrl('step=complete', true)),
            'DECLINEURL' => $strFailedUrl,
            'EXCEPTIONURL' => $strFailedUrl,
            'TP' => $this->viveum_dynamic_template ? $this->viveum_dynamic_template : '',
            'PARAMPLUS' => 'mod=pay&amp;id=' . $this->id,
        );

        // SHA-1 must be generated on alphabetically sorted keys.
        ksort($arrParam);

        $strSHASign = '';
        foreach ($arrParam as $k => $v) {
            if ($v == '')
                continue;

            $strSHASign .= $k . '=' . htmlspecialchars_decode($v) . $this->viveum_hash_in;
        }

        $arrParam['SHASIGN'] = strtoupper(sha1($strSHASign));

        $objTemplate = new FrontendTemplate('iso_payment_viveum');

        $objTemplate->action = 'https://viveum.v-psp.com/ncol/' . ($this->debug ? 'test' : 'prod') . '/orderstandard_utf8.asp';
        $objTemplate->params = $arrParam;
        $objTemplate->slabel = $GLOBALS['TL_LANG']['MSC']['pay_with_cc'][2];
        $objTemplate->id = $this->id;

        return $objTemplate->parse();
    }

    /**
     * Validate SHA-OUT signature
     */
    private function validateSHASign()
    {
        $strSHASign = '';
        $arrParam = array();

        foreach (array_keys($_POST) as $key) {
            if (in_array(strtoupper($key), self::$arrShaOut)) {
                $arrParam[$key] = $this->Input->post($key);
            }
        }

        uksort($arrParam, 'strcasecmp');

        foreach ($arrParam as $k => $v) {
            if ($v == '')
                continue;

            $strSHASign .= strtoupper($k) . '=' . $v . $this->viveum_hash_out;
        }

        if ($this->Input->post('SHASIGN') == strtoupper(sha1($strSHASign))) {
            return true;
        }

        return false;
    }
}
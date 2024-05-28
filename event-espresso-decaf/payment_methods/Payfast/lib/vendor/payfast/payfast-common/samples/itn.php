<?php

require_once __DIR__ . '../../src/PayfastCommon.php';

use Payfast\PayfastCommon\PayfastCommon;

$pfError       = false;
$pfErrMsg      = '';
$pfData        = array();
$pfParamString = '';
$pfHost        = "sandbox.payfast.co.za";
$pfPassphrase  = "";

define("PF_DEBUG", true);
define("PF_SOFTWARE_NAME", "PayFast Software CO");
define("PF_SOFTWARE_VER", 1.0);
define("PF_MODULE_NAME", "PayFast Testing Module");
define("PF_MODULE_VER", 1.0);

PayfastCommon::pflog('Payfast ITN call received');

//// Notify PayFast that information has been received
header('HTTP/1.0 200 OK');
flush();

//// Get data sent by PayFast
PayfastCommon::pflog('Get posted data');

// Posted variables from ITN
$pfData = PayfastCommon::pfGetData();

PayfastCommon::pflog('PayFast Data: ' . json_encode($pfData));

if ($pfData === false) {
    $pfError  = true;
    $pfErrMsg = PayfastCommon::PF_ERR_BAD_ACCESS;
}

/**
 * Validate callback authenticity.
 *
 */

// Verify security signature
if (!$pfError) {
    PayfastCommon::pflog('Verify security signature');

    $passphrase = null;

    // If signature different, log for debugging
    if (!PayfastCommon::pfValidSignature($pfData, $pfParamString, $passphrase)) {
        $pfError  = true;
        $pfErrMsg = PayfastCommon::PF_ERR_INVALID_SIGNATURE;
    }
}

// Verify data received
if (!$pfError) {
    PayfastCommon::pflog('Verify data received');

    $pfValid = PayfastCommon::pfValidData($pfHost, $pfParamString);

    if (!$pfValid) {
        $pfError  = true;
        $pfErrMsg = PayfastCommon::PF_ERR_BAD_ACCESS;
    }
}

//// Check data against internal order & Check order amount
if (!$pfError && (!PayfastCommon::pfAmountsEqual($pfData['amount_gross'], 10.00))) {
    $pfError  = true;
    $pfErrMsg = PayfastCommon::PF_ERR_AMOUNT_MISMATCH;
}

//// Check status and update order
if (!$pfError) {
    PayfastCommon::pflog('Check status and update order');

    $transaction_id = $pfData['pf_payment_id'];

    switch ($pfData['payment_status']) {
        case 'COMPLETE':
            PayfastCommon::pflog('- Complete');
            break;

        case 'FAILED':
            PayfastCommon::pflog('- Failed');
            break;

        case 'PENDING':
            PayfastCommon::pflog('- Pending');
            break;

        default:
            // If unknown status, do nothing (the safest course of action)
            break;
    }
}

//// Create order
if (!$pfError && $pfData['payment_status'] == "COMPLETE") {
    PayfastCommon::pflog("ORDER COMPLETE");
}


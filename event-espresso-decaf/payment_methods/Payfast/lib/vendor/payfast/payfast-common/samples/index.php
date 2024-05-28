<?php

require_once __DIR__ . '../../src/PayfastCommon.php';

use Payfast\PayfastCommon\PayfastCommon;

$year = date('Y');
const HTTP_LITERAL = "https://";

$pathInfo = pathinfo(__FILE__);

$return_url = HTTP_LITERAL . $_SERVER['HTTP_HOST'] . "/index.php";
$cancel_url = $return_url;
$notify_url = HTTP_LITERAL . $_SERVER['HTTP_HOST'] . "/itn.php";

/**
 * Statement Containing
 */
if (array_key_exists('payfastMagicButton', $_POST)) {
    $merchantID  = $_POST['merchantID'] ?? 0;
    $merchantKey = $_POST['merchantKey'] ?? '';
    $passphrase  = $_POST['passphrase'] ?? '';

    $examplePfPaymentId = $_POST["pfPaymentID"] ?? 0;

    $subscriptionToken = $_POST["subscriptionToken"] ?? '';

    $selectedTest       = $_POST['testSelect'];
    $subscriptionAction = null;
    $subscriptionData   = [];

    $refundAction = null;
    $refundData   = [];

    switch ($selectedTest) {
        case "Sale Transaction":
            $salePayArray = array(
                'merchant_id'  => $merchantID ?? false,
                'merchant_key' => $merchantKey ?? false,
                'return_url'   => $return_url,
                'cancel_url'   => $cancel_url,
                'notify_url'   => $notify_url,
                'amount'       => 10.00,
                'item_name'    => 'test item',
            );
            PayfastCommon::createTransaction($salePayArray, $passphrase, true);
            break;
        case "Subscription Transaction":
            $subscriptionPayArray = array(
                'merchant_id'       => $merchantID ?? false,
                'merchant_key'      => $merchantKey ?? false,
                'return_url'        => $return_url,
                'cancel_url'        => $cancel_url,
                'notify_url'        => $notify_url,
                'amount'            => 10.00,
                'item_name'         => 'test item',
                'subscription_type' => 1,
                'frequency'         => 3,
                'cycles'            => 0,
            );
            PayfastCommon::createTransaction($subscriptionPayArray, $passphrase, true);
            break;
        case "Ping PayFast":
            echo filter_var(PayfastCommon::pingPayfast($merchantID, $passphrase), FILTER_SANITIZE_STRING);
            break;
        case "Refund Query":
            $refundAction = "query";
            break;
        case "Create Refund":
            $refundAction = "create";
            $refundData   = [
                "amount"          => "1000",
                "notify_buyer"    => true,
                "notify_merchant" => false,
                "reason"          => "Product out of stock",
            ];
            break;
        case "Retrieve Refund":
            $refundAction = "retrieve";
            break;
        case "Retrieve Subscription":
            $subscriptionAction = "fetch";
            break;
        case "Pause Subscription":
            $subscriptionData   = [
                "cycles" => 2
            ];
            $subscriptionAction = "pause";
            break;
        case "Unpause Subscription":
            $subscriptionAction = "unpause";
            break;
        case "Cancel Subscription":
            $subscriptionAction = "cancel";
            break;
        case "Update Subscription":
            $subscriptionData   = [
                "amount"    => 2000,
                "cycles"    => 2,
                "frequency" => 2,
                "run_date"  => "2025-05-15"
            ];
            $subscriptionAction = "update";
            break;
        case "Charge Tokenization Payment":
            $subscriptionAction = "adhoc";
            $subscriptionData   = [
                "amount"    => 150,
                "item_name" => "adhocTestProduct"
            ];
            break;
        default:
            break;
    }
    if ($subscriptionAction !== null) {
        echo filter_var(
            PayfastCommon::subscriptionAction(
                $merchantID,
                $subscriptionToken,
                $subscriptionAction,
                $subscriptionData,
                $passphrase,
                true
            ),
            FILTER_SANITIZE_STRING
        );
    } elseif ($refundAction !== null) {
        echo filter_var(
            PayfastCommon::refundAction(
                $merchantID,
                $passphrase,
                $examplePfPaymentId,
                $refundAction,
                $refundData,
                FILTER_SANITIZE_STRING
            )
        );
    }
}
echo <<<HTML
<!DOCTYPE html>
<html lang="en" id="checkout__test">

<head>
    <title>Payfast Testing Tool</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">
    <meta name="description" content="A tool for testing purposes only">
    <meta name="robots" content="noindex">
    <link rel="icon" href="assets/images/favicon.png" sizes="32x32"/>
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body class="body">
<nav>
    <div class="navbar--lighter">
        <a href="index.php"><img src="assets/images/logo.svg" alt="PayFast logo" title="PayFast logo"></a>
    </div>
    <div class="navbar">
        <ul>
            <li><a href="index.php">Payfast testing tool</a></li>
        </ul>
    </div>
</nav>
<div class="page__header">
    <div class="page__heading">
        <h1>Payfast testing tool</h1>
    </div>
</div>
<div class="wrapper">
    <div class="main__container">
        <form method="post">
            <label>
                <span>Merchant ID*:</span>
                <div class="input-wrapper">
                    <input name="merchantID" type="text" value="">
                </div>
            </label>
            <label>
                <span>Merchant Key*:</span>
                <div class="input-wrapper">
                    <input name="merchantKey" type="text" value="">
                </div>
            </label>
            <label>
                <span>Passphrase:</span>
                <div class="input-wrapper">
                    <input name="passphrase" type="text" value="">
                </div>
            </label>
            <label>
                            <span>PF Payment ID:
                            </span>
                <div class="input-wrapper">
                    <input name="pfPaymentID" type="text" value="">
                </div>
            </label>
            <label>
                <span>Subscription Token:</span>
                <div class="input-wrapper">
                    <input name="subscriptionToken" type="text" value="">
                </div>
            </label>
            <label>
                <span>Request:</span>
                <div class="input-wrapper">
                    <select name="testSelect">
                        <option>Sale Transaction</option>
                        <option>Subscription Transaction</option>
                        <option>Ping PayFast</option>
                        <option>Refund Query</option>
                        <option>Create Refund</option>
                        <option>Retrieve Refund</option>
                        <option>Retrieve Subscription</option>
                        <option>Pause Subscription</option>
                        <option>Unpause Subscription</option>
                        <option>Cancel Subscription</option>
                        <option>Update Subscription</option>
                        <option>Charge Tokenization Payment</option>
                    </select>
                </div>
            </label>
            <label>
                <div class="input-wrapper">
                    <input type="submit" name="payfastMagicButton" class="btn" value="Submit test"/>
                </div>
            </label>
        </form>
    </div>
</div>
<footer class="">
    <div class="footer__reserved">
        © $year - Payfast | All rights reserved
    </div>
</footer>
</body>

</html>

HTML;

<?php

require_once('../src/library/Siru.php');
require_once('../src/library/Demoshop.php');

$merchantSecret = 'xooxoo';

$siru = new Siru($merchantSecret);

if (isset($_GET['siru_signature']) && !$siru->responseSignatureIsValid($_GET)) {
    die('Signature does not match!');
}

if (isset($_GET['notify'])) {
    $requestBody = file_get_contents('php://input');

    file_put_contents(
        '../data/logs/notifications.log',
        (new DateTime())->format('d.m.Y H:i:s') . " - RECEIVED POST:\n$requestBody\n",
        FILE_APPEND
    );

    $requestJson = json_decode($requestBody, true);

    if ($siru->responseSignatureIsValid($requestJson)) {
        $demoshop = new Demoshop();

        $event = $requestJson['siru_event'];

        switch ($event) {
            case 'success':

                $demoshop->confirmAndLogPurchase($requestJson);
                break;

            case 'cancel':

                // TODO: cancel purchase
                break;

            case 'failure':

                // TODO: fail purchase
                break;

            default:
                throw new Exception("Unknown event: '$event'");
        }

        header("HTTP/1.1 200 OK");
    }

    die;
}

$baseUrl = 'https://' . $_SERVER['SERVER_NAME'];

$fields = [
    'variant' => 'variant1',
    'merchantId' => '1',
    'purchaseCountry' => 'FI',
    'basePrice' => '5.00',
    'taxClass' => 3,
    'serviceGroup' => 4,
    'customerNumber' => '358xxxxxxx',
    'purchaseReference' => 'P1234567',
    'customerReference' => 'john.doe@tunk.io',
    'notifyAfterSuccess' => $baseUrl . '/?notify=success',
    'notifyAfterFailure' => $baseUrl . '/?notify=failure',
    'notifyAfterCancel' => $baseUrl . '/?notify=cancel',
];

$statusNotSuccess = !isset($_GET['status']) || (isset($_GET['status']) && $_GET['status'] !== 'success');

$signature = $siru->createRequestSignature($fields);

?>

<html>
    <head>
        <style>
            body { font-family: Helvetica, Arial, sans-serif; }
            .status { padding: 10px; border: 1px solid black; }
            label { display: block; width: 100px; margin: 10px; }
            .success { background-color: green; color: white; }
            .failure { background-color: red;   color: white; }
            .cancelled { background-color: yellow; color: black; }
        </style>
        <title>SiruMobile Reference DemoShop</title>
    </head>
    <body>
        <img src="https://payment.siru.tunk.io/images/sirumobile-logo_sirumobile-logo_1.png">

        <h1>SiruMobile Reference DemoShop</h1>

        <?php if ($statusNotSuccess): ?>
            <h2>Variant1</h2>

            <form action="https://payment.siru.tunk.io/payment.html" method="post">
                <input type="hidden" name="variant" value="variant1">
                <input type="hidden" name="merchantId" value="1">
                <input type="hidden" name="purchaseCountry" value="FI">
                <input type="hidden" name="redirectAfterSuccess" value="<?= $baseUrl; ?>/?status=success">
                <input type="hidden" name="redirectAfterFailure" value="<?= $baseUrl; ?>/?status=failure">
                <input type="hidden" name="redirectAfterCancel" value="<?= $baseUrl; ?>/?status=cancel">
                <?php foreach (['notifyAfterSuccess', 'notifyAfterFailure', 'notifyAfterCancel'] as $status): ?>
                    <input type="hidden" name="<?= $status; ?>" value="<?= $fields[$status]; ?>">
                <?php endforeach; ?>
                <input type="hidden" name="purchaseReference" value="P1234567">
                <input type="hidden" name="customerReference" value="john.doe@tunk.io">
                <input type="hidden" name="signature" value="<?= $signature; ?>">

                <label>
                    basePrice
                    <input type="text" name="basePrice" value="5.00">
                </label>

                <label>
                    customerNumber
                    <input type="text" name="customerNumber" value="358503485508">
                </label>

                <label>
                    taxClass
                    <input type="text" name="taxClass" value="3">
                </label>

                <label>
                    serviceGroup
                    <input type="text" name="serviceGroup" value="4">
                </label>

                <button>Make a purchase</button>
            </form>
        <?php else: ?>
            <div class="status success">Successful purchase</div>
            <br/>
            <a href="<?= $baseUrl; ?>">Try again!</a>
        <?php endif; ?>

        <?php if (isset($_GET['status']) && $_GET['status'] === 'failure'): ?>
            <div class="status failure">Failed purchase</div>
        <?php elseif (isset($_GET['status']) && $_GET['status'] === 'cancel'): ?>
            <div class="status cancelled">Cancelled purchase</div>
        <?php endif; ?>
    </body>
</html>
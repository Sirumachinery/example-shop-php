<?php

require_once('Siru.php');

$siru = new Siru();

$merchantSecret = 'merchantSecret';

if (isset($_GET['siru_signature'])) {

    $calculatedSignature = $siru->calculateResponseSignature($_GET, $merchantSecret);

    $signatureFromRequest = $_GET['siru_signature'];

    var_dump(($signatureFromRequest === $calculatedSignature ? 'Signature matches' : 'Signature does not match!'));
}

if (count($_POST)) {
    file_put_contents('post.log', $_POST, FILE_APPEND);
}

$fields = [
    'variant' => 'variant1',
    'merchantId' => '1',
    'purchaseCountry' => 'FI',
    'basePrice' => '5.00',
    'taxClass' => 3,
    'serviceGroup' => 4,
    'customerNumber' => '358xxxxxxxx',
    'purchaseReference' => 'P1234567',
    'customerReference' => 'john.doe@tunk.io',
];

$baseUrl = 'https://' . $_SERVER['SERVER_NAME'];

$statusNotSuccess = !isset($_GET['status']) || (isset($_GET['status']) && $_GET['status'] !== 'success');

$signature = $siru->calculateRequestSignature($fields, $merchantSecret);

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
                <input type="hidden" name="redirectAfterSuccess" value="<?= $baseUrl; ?>/demoshop.php?status=success">
                <input type="hidden" name="redirectAfterFailure" value="<?= $baseUrl; ?>/demoshop.php?status=failure">
                <input type="hidden" name="redirectAfterCancel" value="<?= $baseUrl; ?>/demoshop.php?status=cancel">
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
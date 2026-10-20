<?php

declare(strict_types=1);

require_once __DIR__ . "/Products.php";
require_once __DIR__ . "/Cart.php";
require_once __DIR__ . "/function.php";

$result = processCart($cart, $products);

$subtotal = $result["subtotal"];

$discount = calculateDiscount(
    $subtotal,
    $customer["member"]
);

$discountedTotal = $subtotal - $discount;

$shipping = empty($result["items"])
    ? 0
    : calculateShippingCost($discountedTotal);

$grandTotal = $discountedTotal + $shipping;




echo "===== SHOPPING CART =====\n\n";

echo "Customer: " . $customer["name"] . "\n";

echo "Membership: " . $customer["member"] . "\n\n";


foreach ($result["items"] as $item) {

    echo $item["product"]
        . " x "
        . $item["quantity"]
        . " = "
        . formatCurrency($item["total_item"])
        . "\n";
}


echo "\nSubtotal: " . formatCurrency($subtotal);

echo "\nDiscount: " . formatCurrency($discount);

echo "\nShipping: " . formatCurrency($shipping);

echo "\nGrand Total: " . formatCurrency($grandTotal);

echo "\n";


if (!empty($result["rejected_items"])) {

    echo "\nRejected Items:\n";

    foreach ($result["rejected_items"] as $error) {
        echo "- " . $error . "\n";
    }
}
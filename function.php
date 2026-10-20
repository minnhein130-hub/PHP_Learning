<?php
function searchProduct(int $productId,array $products): ?array 
{
   
        return $products[$productId]?? null;

}

function validateStock(int $productId,int $quantity,array $products): bool 
{
    $product = searchProduct($productId,$products);
    if($product===null){
        return false;
    }
    if($quantity <=0){
        return false;
    }
    if($quantity > $product['stock']){
        return false;
    }
    return true;
}

function calculateTotalItem( int $product,int $quantity )
{
    $TotalItem = $product * $quantity;
    return $TotalItem;
}

// function calculateDiscount(string $memberType, float $totalAmount):float{
    
//     $discount = 0;
//     if($memberType === "gold"){
     
//         $discount = $totalAmount * 0.2;
//     }
//     return $discount;
// }
function calculateDiscount($totalAmount, $memberType): int
{
    $dicount = discountRate($memberType);
    return (int)round($totalAmount * $dicount);
}
function discountRate(string $memberType): float
{
    return match(strtolower($memberType)){
        'gold' => 0.2,
        'silver' => 0.1,
        default => 0
    };
}
function calculateShippingCost(int $totalAmount): int
{
    if($totalAmount < 100){
        return 10;
    }elseif($totalAmount >= 100 && $totalAmount <= 200){
        return 5;
    }else{
        return 0;
    }
}
function formatCurrency(float $amount): string
{
    return '$' . number_format($amount, 2);
}
function validateStockError(int $productId,int $quantity,array $products): string 
{
    $product = searchProduct($productId,$products);
    if($product===null){
        return "Product not found";
    }
    if($quantity <=0){
        return "Quantity cannot be zero or negative";
    }
    if($quantity > $product['stock']){
        return "Insufficient stock";
    }
    return "";
}
function processCart(
    array $cart,
    array $products
): array 
{

    $cartItems = [];

    $rejectedItems = [];

    $quantities = [];

    $subtotal = 0;

    foreach ($cart as $item) {

        $productId = $item["product_id"] ?? null;
        $quantity = $item["quantity"] ?? null;

        if (!is_int($productId) || !is_int($quantity)) {
            $rejectedItems[] = "Invalid cart item";
            continue;
        }

        if ($quantity <= 0) {
            $rejectedItems[] = "Invalid quantity for product $productId";
            continue;
        }

        $quantities[$productId] =
            ($quantities[$productId] ?? 0) + $quantity;
    }

    // Step 2: Process each product

    foreach ($quantities as $productId => $quantity) {

        $product = searchProduct($productId, $products);

        if (!validateStock($productId, $quantity, $products)) {

            $rejectedItems[] = validateStockError(
                $productId,
                $quantity,
                $products
            );

            continue;
        }

        $itemTotal = calculateTotalItem(
            $product["price"],
            $quantity
        );

        $subtotal += $itemTotal;

        $cartItems[] = [
            "product_id" => $productId,
            "product" => $product["name"],
            "quantity" => $quantity,
            "unit_price" => $product["price"],
            "total_item" => $itemTotal
        ];
    }

    return [
        "items" => $cartItems,
        "subtotal" => $subtotal,
        "rejected_items" => $rejectedItems
    ];
}
?>


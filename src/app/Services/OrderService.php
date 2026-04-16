<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\Setting;
use Core\Database;
use RuntimeException;

class OrderService
{
    /**
     * Create an order with its line items in a single transaction.
     *
     * $data = [
     *   'customer_name'     => string,
     *   'customer_phone'    => string,
     *   'customer_address'  => string,
     *   'customer_city'     => string,
     *   'customer_province' => string,
     *   'notes'             => string|null,
     *   'items'             => [
     *     ['product_id' => int, 'quantity' => int, 'selling_price' => string],
     *     ...
     *   ]
     * ]
     */
    public function create(array $data, int $userId, ?int $parentSellerId = null): int
    {
        $pdo = Database::getInstance();

        $deliveryCharge = Setting::deliveryCharge();
        $totalBase      = '0.00';
        $totalWholesale = '0.00';
        $totalSelling   = '0.00';
        $resolvedItems  = [];

        // Validate and resolve each item
        foreach ($data['items'] as $item) {
            $product = Product::find((int) $item['product_id']);

            if (!$product || !$product['is_active']) {
                throw new RuntimeException("Product #{$item['product_id']} is unavailable.");
            }

            $qty = max(1, (int) $item['quantity']);

            // Calculate wholesale price if this is a Store order
            $wholesalePrice = $product['base_price'];
            if ($parentSellerId) {
                $markup = $pdo->prepare(
                    "SELECT markup_type, markup_value FROM seller_category_markups 
                     WHERE seller_id = ? AND category_id = ? LIMIT 1"
                );
                $markup->execute([$parentSellerId, $product['category_id']]);
                $m = $markup->fetch();
                
                $wholesalePrice = Product::calculateWholesalePrice(
                    (float)$product['base_price'],
                    $m['markup_type'] ?? null,
                    (float)($m['markup_value'] ?? 0)
                );
                $wholesalePrice = (string)$wholesalePrice;
            }

            $resolvedItems[] = [
                'product_id'               => $product['id'],
                'product_title'            => $product['title'],
                'base_price_snapshot'      => $product['base_price'],
                'wholesale_price_snapshot' => $wholesalePrice,
                'selling_price'            => $item['selling_price'],
                'quantity'                 => $qty,
            ];

            $totalBase      = bcadd($totalBase,      bcmul($product['base_price'], (string)$qty, 2), 2);
            $totalWholesale = bcadd($totalWholesale, bcmul($wholesalePrice,        (string)$qty, 2), 2);
            $totalSelling   = bcadd($totalSelling,   bcmul($item['selling_price'], (string)$qty, 2), 2);
        }

        $orderNumber = $this->generateOrderNumber();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare(
                'INSERT INTO orders
                 (user_id, parent_seller_id, order_number, customer_name, customer_phone,
                  customer_address, customer_city, customer_province, notes,
                  total_selling_price, total_wholesale_price, total_base_price, delivery_charge, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $userId,
                $parentSellerId,
                $orderNumber,
                $data['customer_name'],
                $data['customer_phone'],
                $data['customer_address'],
                $data['customer_city'],
                $data['customer_province'],
                $data['notes'] ?? null,
                $totalSelling,
                $totalWholesale,
                $totalBase,
                $deliveryCharge,
                'pending',
            ]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare(
                'INSERT INTO order_items
                 (order_id, product_id, product_title, base_price_snapshot, wholesale_price_snapshot, selling_price, quantity)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            foreach ($resolvedItems as $item) {
                $itemStmt->execute([
                    $orderId,
                    $item['product_id'],
                    $item['product_title'],
                    $item['base_price_snapshot'],
                    $item['wholesale_price_snapshot'],
                    $item['selling_price'],
                    $item['quantity'],
                ]);
            }

            $pdo->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private function generateOrderNumber(): string
    {
        $pdo    = Database::getInstance();
        $prefix = \App\Models\Setting::get('order_number_prefix', 'EMG');
        $date   = date('Ymd');

        // Count today's orders to derive sequence number
        $count = (int) $pdo->query(
            "SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()"
        )->fetchColumn();

        return sprintf('%s-%s-%05d', $prefix, $date, $count + 1);
    }
}

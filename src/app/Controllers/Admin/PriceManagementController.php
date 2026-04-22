<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Models\Category;
use Core\Database;
use Core\Request;
use Core\Response;
use Core\Session;
use Core\View;

class PriceManagementController
{
    public function index(Request $request): void
    {
        $pdo = Database::getInstance();
        $rules = $pdo->query("SELECT pr.*, c.name as category_name 
                              FROM price_rules pr 
                              LEFT JOIN categories c ON c.id = pr.category_id 
                              ORDER BY rule_type ASC, priority DESC")->fetchAll();

        View::render('admin/settings/price_management', [
            'rules'      => $rules,
            'categories' => Category::allActive(),
            'success'    => Session::getFlash('success'),
            'errors'     => Session::errors()
        ], 'admin');
    }

    public function storeRule(Request $request): void
    {
        $type       = $request->input('rule_type');
        $categoryId = $request->input('category_id') ? (int)$request->input('category_id') : null;
        $minPrice   = $request->input('min_price') !== '' ? (float)$request->input('min_price') : null;
        $maxPrice   = $request->input('max_price') !== '' ? (float)$request->input('max_price') : null;
        $mType      = $request->input('margin_type');
        $mValue     = (float)$request->input('margin_value');
        $maxCap     = $request->input('max_cap') !== '' ? (float)$request->input('max_cap') : null;

        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO price_rules 
            (rule_type, category_id, min_price, max_price, margin_type, margin_value, max_cap) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([$type, $categoryId, $minPrice, $maxPrice, $mType, $mValue, $maxCap]);

        Session::flash('success', 'Price rule added successfully.');
        Response::redirect('/admin/settings/price-management');
    }

    public function deleteRule(Request $request): void
    {
        $id = (int)$request->param('id');
        $pdo = Database::getInstance();
        $pdo->prepare("DELETE FROM price_rules WHERE id = ?")->execute([$id]);

        Session::flash('success', 'Price rule deleted.');
        Response::redirect('/admin/settings/price-management');
    }

    public function syncPrices(Request $request): void
    {
        $pdo = Database::getInstance();
        $products = $pdo->query("SELECT id, category_id, buy_price FROM products")->fetchAll();
        $rules    = $pdo->query("SELECT * FROM price_rules ORDER BY priority DESC, id DESC")->fetchAll();

        $updated = 0;
        foreach ($products as $product) {
            $buyPrice = (float)$product['buy_price'];
            $newPrice = $this->calculatePrice($buyPrice, (int)$product['category_id'], $rules);

            $stmt = $pdo->prepare("UPDATE products SET base_price = ? WHERE id = ?");
            $stmt->execute([$newPrice, $product['id']]);
            $updated++;
        }

        Session::flash('success', "Price sync complete. Updated {$updated} products.");
        Response::redirect('/admin/settings/price-management');
    }

    private function calculatePrice(float $buyPrice, int $categoryId, array $rules): float
    {
        // Find best matching rule
        // Priority: Range > Category > Overall
        $matchedRule = null;

        // 1. Check Range Rules
        foreach ($rules as $rule) {
            if ($rule['rule_type'] === 'range') {
                if ($buyPrice >= (float)$rule['min_price'] && $buyPrice <= (float)$rule['max_price']) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        // 2. Check Category Rules if no range match
        if (!$matchedRule) {
            foreach ($rules as $rule) {
                if ($rule['rule_type'] === 'category' && (int)$rule['category_id'] === $categoryId) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        // 3. Check Overall Rule if still no match
        if (!$matchedRule) {
            foreach ($rules as $rule) {
                if ($rule['rule_type'] === 'overall') {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        if (!$matchedRule) return $buyPrice;

        $margin = 0.0;
        if ($matchedRule['margin_type'] === 'fixed') {
            $margin = (float)$matchedRule['margin_value'];
        } elseif ($matchedRule['margin_type'] === 'percent') {
            $margin = $buyPrice * ((float)$matchedRule['margin_value'] / 100);
        } elseif ($matchedRule['margin_type'] === 'percent_cap') {
            $margin = $buyPrice * ((float)$matchedRule['margin_value'] / 100);
            if ($matchedRule['max_cap'] && $margin > (float)$matchedRule['max_cap']) {
                $margin = (float)$matchedRule['max_cap'];
            }
        }

        return $buyPrice + $margin;
    }
}

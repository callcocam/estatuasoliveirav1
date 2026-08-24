<?php

namespace App\Services\LegacyImport;

use App\Enums\QuoteStatus;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;

class QuoteImporter extends LegacyImporter
{
    public function handle(): void
    {
        $orders = $this->reader->rows('orders');
        $items = collect($this->reader->rows('items'))->groupBy('order_id');

        $this->report->source('orders', count($orders));
        $this->report->source('items', $items->flatten(1)->count());

        foreach ($orders as $row) {
            if (! $this->isRealCompany($row)) {
                $this->report->skip('orders', $row['id'], 'outra empresa');

                continue;
            }

            $ulid = $this->ids->ulid('orders', (string) $row['id']);

            if (Quote::withTrashed()->whereKey($ulid)->exists()) {
                $this->report->skip('orders', $row['id'], 'já importado');

                continue;
            }

            $quote = new Quote([
                'user_id' => $this->existingUser($row['user_id']),
                'status' => self::quoteStatus($row['status']),
                'notes' => $row['description'],
                'total' => 0,
            ]);

            $quote->id = $ulid;

            $this->persistWithTimestamps($quote, $row);
            $this->report->imported('orders');

            $total = 0.0;

            foreach ($items->get($row['id'], collect()) as $item) {
                $total += $this->importItem($ulid, $item);
            }

            Quote::withTrashed()->whereKey($ulid)->update(['total' => round($total, 2)]);

            $legacyTotal = is_numeric($row['total']) ? round((float) $row['total'], 2) : null;

            if ($legacyTotal !== null && abs($legacyTotal - round($total, 2)) >= 0.01) {
                $this->report->note(
                    "orders {$row['id']}: total legado {$legacyTotal} difere do recalculado ".round($total, 2)
                );
            }
        }
    }

    /**
     * Import a single legacy item; returns the item total.
     *
     * @param  array<string, string|null>  $row
     */
    private function importItem(string $quoteUlid, array $row): float
    {
        $ulid = $this->ids->ulid('items', (string) $row['id']);

        if (($row['deleted_at'] ?? null) !== null) {
            $this->report->skip('items', $row['id'], 'deletado no legado');

            return 0.0;
        }

        if (QuoteItem::query()->whereKey($ulid)->exists()) {
            $this->report->skip('items', $row['id'], 'já importado');

            return 0.0;
        }

        $productId = $this->ids->existing('products', $row['product_id']);
        $product = $productId ? Product::withTrashed()->find($productId) : null;

        $quantity = max(1, (int) ($row['qty'] ?? 1));
        $unitPrice = is_numeric($row['price']) ? round((float) $row['price'], 2) : 0.0;
        $total = is_numeric($row['total']) ? round((float) $row['total'], 2) : round($quantity * $unitPrice, 2);

        $item = new QuoteItem([
            'quote_id' => $quoteUlid,
            'product_id' => $product?->id,
            'name' => $product?->name ?? trim((string) ($row['description'] ?? '')) ?: 'Produto removido',
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $total,
        ]);

        $item->id = $ulid;

        $this->persistWithTimestamps($item, $row);
        $this->report->imported('items');

        return $total;
    }

    /**
     * Get the mapped user ULID when the user was imported.
     */
    private function existingUser(?string $uuid): ?string
    {
        $ulid = $this->ids->existing('users', $uuid);

        if ($ulid === null || ! User::withTrashed()->whereKey($ulid)->exists()) {
            return null;
        }

        return $ulid;
    }

    /**
     * Convert a legacy order status into the quote status enum.
     */
    private static function quoteStatus(?string $status): QuoteStatus
    {
        return match ($status) {
            'featured', 'published' => QuoteStatus::Answered,
            default => QuoteStatus::Pending,
        };
    }
}

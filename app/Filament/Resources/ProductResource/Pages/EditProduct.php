<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ProductPricing;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected ?array $pricingData = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete Record')
                ->modalDescription('Are you sure you want to delete this record? This action cannot be undone.'),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->pricingData = $data['pricing'] ?? [];
        unset($data['pricing']);

        return $data;
    }

    protected function afterSave(): void
    {
        $product = $this->record;
        $existingPricingIds = [];

        foreach ($this->pricingData as $item) {
            $pricingId = $item['pricing_id'] ?? null;

            if ($pricingId) {
                $pricingRecord = $product->pricing()->where('id', $pricingId)->first();
                if ($pricingRecord) {
                    $pricingRecord->update([
                        'name' => $item['name'] ?? '',
                        'type' => $item['type'] ?? 'recurring',
                        'interval' => $item['interval'] ?? 'month',
                        'billing_period' => $item['billing_period'] ?? 1,
                    ]);
                    $existingPricingIds[] = $pricingId;
                } else {
                    $pricingRecord = $this->createPricing($product, $item);
                }
            } else {
                $pricingRecord = $this->createPricing($product, $item);
            }

            $this->syncCurrencies($pricingRecord, $item['currencies_data'] ?? []);
        }

        $product->pricing()
            ->whereNotIn('id', $existingPricingIds)
            ->delete();
    }

    private function createPricing($product, array $item): ProductPricing
    {
        return $product->pricing()->create([
            'name' => $item['name'] ?? '',
            'type' => $item['type'] ?? 'recurring',
            'interval' => $item['interval'] ?? 'month',
            'billing_period' => $item['billing_period'] ?? 1,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function syncCurrencies(ProductPricing $pricing, array $currenciesData): void
    {
        $syncData = [];
        foreach ($currenciesData as $entry) {
            $currencyId = $entry['currency_id'] ?? null;
            if (! $currencyId) {
                continue;
            }
            $syncData[$currencyId] = [
                'amount' => $entry['amount'] ?? 0,
                'setup_fee' => $entry['setup_fee'] ?? 0,
            ];
        }
        $pricing->currencies()->sync($syncData);
    }
}

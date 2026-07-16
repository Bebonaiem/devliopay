<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected ?array $pricingData = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->pricingData = $data['pricing'] ?? [];
        unset($data['pricing']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $product = $this->record;

        foreach ($this->pricingData as $item) {
            $pricingRecord = $product->pricing()->create([
                'name' => $item['name'] ?? '',
                'type' => $item['type'] ?? 'recurring',
                'interval' => $item['interval'] ?? 'month',
                'billing_period' => $item['billing_period'] ?? 1,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            $currenciesData = $item['currencies_data'] ?? [];
            if (! empty($currenciesData)) {
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
                $pricingRecord->currencies()->sync($syncData);
            }
        }
    }
}

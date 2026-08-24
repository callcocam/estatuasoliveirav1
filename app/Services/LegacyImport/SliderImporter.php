<?php

namespace App\Services\LegacyImport;

use App\Models\Slider;

class SliderImporter extends LegacyImporter
{
    public function handle(): void
    {
        $rows = $this->reader->rows('sliders');
        $this->report->source('sliders', count($rows));

        foreach ($rows as $row) {
            if (! $this->isRealCompany($row)) {
                $this->report->skip('sliders', $row['id'], 'outra empresa');

                continue;
            }

            $ulid = $this->ids->ulid('sliders', (string) $row['id']);

            if (Slider::withTrashed()->whereKey($ulid)->exists()) {
                $this->report->skip('sliders', $row['id'], 'já importado');

                continue;
            }

            $slider = new Slider([
                'title' => trim((string) $row['name']),
                'subtitle' => $row['description'],
                'status' => $this->publishStatus($row['status']),
                'sort_order' => 0,
            ]);

            $slider->id = $ulid;

            $this->persistWithTimestamps($slider, $row);
            $this->report->imported('sliders');
        }
    }
}

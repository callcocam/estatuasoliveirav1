<?php

use App\Models\Media;
use App\Models\Product;
use App\Models\Setting;
use App\Services\LegacyImport\MediaFileMigrator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
    Http::preventStrayRequests();

    $this->source = sys_get_temp_dir().'/legacy-media-'.uniqid();
    mkdir($this->source.'/products/202005', 0777, true);
});

afterEach(function () {
    exec('rm -rf '.escapeshellarg($this->source));
});

/** A valid 1x1 PNG file. */
function fixturePng(): string
{
    return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
}

function makeLegacyMedia(string $path): Media
{
    $product = Product::factory()->create();

    return $product->media()->create([
        'collection' => 'default',
        'disk' => 'public',
        'path' => $path,
        'file_name' => basename($path),
        'mime_type' => 'image/png',
        'size' => null,
        'sort_order' => 0,
    ]);
}

test('copies the legacy file and updates size and dimensions', function () {
    file_put_contents($this->source.'/products/202005/foto.png', fixturePng());
    $media = makeLegacyMedia('products/202005/foto.png');

    $this->artisan('legacy:import-media', ['--source' => $this->source])
        ->assertSuccessful();

    Storage::disk('public')->assertExists('products/202005/foto.png');

    $media->refresh();

    expect($media->size)->toBe(strlen(fixturePng()))
        ->and($media->custom_properties['width'])->toBe(1)
        ->and($media->custom_properties['height'])->toBe(1)
        ->and($media->custom_properties['migrated_at'])->not->toBeNull();
});

test('dry run changes nothing', function () {
    file_put_contents($this->source.'/products/202005/foto.png', fixturePng());
    $media = makeLegacyMedia('products/202005/foto.png');

    $this->artisan('legacy:import-media', ['--source' => $this->source, '--dry-run' => true])
        ->expectsOutputToContain('[copy] products/202005/foto.png')
        ->assertSuccessful();

    Storage::disk('public')->assertMissing('products/202005/foto.png');
    Storage::disk('local')->assertMissing(MediaFileMigrator::REPORT_PATH);

    expect($media->refresh()->size)->toBeNull();
});

test('downloads from production when the local mirror lacks the file', function () {
    Http::fake(['estatuasoliveira.com.br/storage/products/202005/remota.png' => Http::response(fixturePng())]);

    $media = makeLegacyMedia('products/202005/remota.png');

    $this->artisan('legacy:import-media', ['--source' => $this->source])->assertSuccessful();

    Storage::disk('public')->assertExists('products/202005/remota.png');
    expect($media->refresh()->custom_properties['migrated_at'])->not->toBeNull();
});

test('reports missing files without failing and keeps the record by default', function () {
    Http::fake(['estatuasoliveira.com.br/*' => Http::response('', 404)]);

    $media = makeLegacyMedia('products/202005/sumida.png');

    $this->artisan('legacy:import-media', ['--source' => $this->source])
        ->expectsOutputToContain('[ausente] products/202005/sumida.png')
        ->assertSuccessful();

    expect($media->refresh()->trashed())->toBeFalse();

    $report = json_decode((string) Storage::disk('local')->get(MediaFileMigrator::REPORT_PATH), true);

    expect($report['counts']['missing'])->toBe(1)
        ->and($report['missing'][0]['path'])->toBe('products/202005/sumida.png');
});

test('prune soft deletes orphan media records', function () {
    Http::fake(['estatuasoliveira.com.br/*' => Http::response('', 404)]);

    $media = makeLegacyMedia('products/202005/sumida.png');

    $this->artisan('legacy:import-media', ['--source' => $this->source, '--prune' => true])
        ->assertSuccessful();

    expect($media->refresh()->trashed())->toBeTrue();
});

test('is idempotent: a second run skips already migrated files', function () {
    file_put_contents($this->source.'/products/202005/foto.png', fixturePng());
    makeLegacyMedia('products/202005/foto.png');

    $this->artisan('legacy:import-media', ['--source' => $this->source])->assertSuccessful();

    $this->artisan('legacy:import-media', ['--source' => $this->source])
        ->expectsOutputToContain('Copiados: 0 | Baixados: 0 | Já migrados: 1')
        ->assertSuccessful();
});

test('uses the legacy path from the migrator fallback map for renamed rows', function () {
    file_put_contents($this->source.'/products/202005/original-legado.png', fixturePng());
    $media = makeLegacyMedia('products/buda-ref-001.png');

    (new MediaFileMigrator(
        sourcePath: $this->source,
        legacyPaths: [$media->id => 'products/202005/original-legado.png'],
    ))->handle();

    // O arquivo é gravado no path novo (slug), lido do path legado.
    Storage::disk('public')->assertExists('products/buda-ref-001.png');
    expect($media->refresh()->custom_properties['migrated_at'])->not->toBeNull();
});

test('copies the branding logo referenced by the setting', function () {
    mkdir($this->source.'/companies/202006', 0777, true);
    file_put_contents($this->source.'/companies/202006/logo.png', fixturePng());

    Setting::set('branding_logo_path', 'companies/202006/logo.png', 'branding');

    $this->artisan('legacy:import-media', ['--source' => $this->source])->assertSuccessful();

    Storage::disk('public')->assertExists('companies/202006/logo.png');
});

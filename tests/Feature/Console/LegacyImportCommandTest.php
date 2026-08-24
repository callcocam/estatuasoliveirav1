<?php

use App\Enums\PublishStatus;
use App\Enums\QuoteStatus;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function runLegacyImport(): void
{
    test()->artisan('legacy:import', ['--path' => 'tests/Fixtures/legacy-sample.sql'])->assertSuccessful();
}

test('importa categorias convertendo status e preservando soft delete', function () {
    runLegacyImport();

    // No dump há duas categorias "budas": uma ativa e uma soft-deletada.
    $active = Category::query()->where('slug', 'budas')->first();

    expect(Category::withTrashed()->count())->toBeGreaterThanOrEqual(2)
        ->and($active)->not->toBeNull()
        ->and($active->status)->toBe(PublishStatus::Published)
        ->and($active->trashed())->toBeFalse()
        ->and(Category::onlyTrashed()->where('slug', 'like', 'budas%')->exists())->toBeTrue();
});

test('converte featured, medida legada e estoque dos produtos', function () {
    runLegacyImport();

    $featured = Product::withTrashed()->where('name', 'Buda Ref: 016')->firstOrFail();

    expect($featured->featured)->toBeTrue()
        ->and($featured->height_cm)->toBe(53) // "Altura 53cm"
        ->and($featured->custom_properties)->toMatchArray(['legacy_size' => 'Altura 53cm'])
        ->and($featured->stock)->toBe(0)
        ->and($featured->category)->not->toBeNull();

    $junkStock = Product::withTrashed()->where('name', 'like', 'Buda Pensador%')->firstOrFail();
    $numericStock = Product::withTrashed()->where('name', 'like', '%OVELHA DEITADA%')->firstOrFail();

    expect($junkStock->stock)->toBe(0) // stoke legado "65cm" não é numérico
        ->and($numericStock->stock)->toBe(15);
});

test('migra files como media com morph novo e logo da empresa como setting', function () {
    runLegacyImport();

    $cover = Media::query()->where('collection', 'cover')->firstOrFail();

    expect($cover->mediable_type)->toBe(Product::class)
        ->and($cover->mediable)->toBeInstanceOf(Product::class)
        ->and($cover->path)->toStartWith('products/')
        ->and($cover->path)->not->toContain('storage/')
        ->and($cover->mime_type)->toBe('image/jpeg');

    expect(Media::query()->where('mediable_type', Slider::class)->exists())->toBeTrue();

    // Logo da empresa não vira media: vira setting de branding.
    expect(Setting::query()->where('key', 'branding_logo_path')->value('value'))
        ->toBe('companies/202006/83144932-1591191622-logo-new.png');
});

test('migra slider com name como title e description como subtitle', function () {
    runLegacyImport();

    $slider = Slider::query()->firstOrFail();

    expect($slider->title)->toBe('Estátuas Oliveira')
        ->and($slider->subtitle)->toContain('Há mais de 25 anos');
});

test('migra orders como quotes recalculando o total', function () {
    runLegacyImport();

    // As duas orders da fixture são soft-deletadas no legado (deleted_at preservado).
    $answered = Quote::withTrashed()->where('status', QuoteStatus::Answered)->firstOrFail();

    // O item deletado no legado (deleted_at preenchido) não é importado: quote_items não tem soft delete.
    expect($answered->items)->toHaveCount(2)
        ->and((float) $answered->total)->toBe(5.0) // 2.00 + 3.00; ignora o item deletado de 8.00
        ->and($answered->trashed())->toBeTrue()
        ->and($answered->user_id)->not->toBeNull()
        ->and($answered->items->every(fn (QuoteItem $item) => $item->product_id !== null))->toBeTrue();

    expect(Quote::withTrashed()->where('status', QuoteStatus::Pending)->exists())->toBeTrue(); // order "draft"
});

test('order de usuário não importado vira quote com user_id nulo', function () {
    runLegacyImport();

    // O dono da order foi pulado (deletado no legado), então a quote fica sem usuário.
    $orphan = Quote::withTrashed()->where('notes', 'Pedido de usuário deletado no legado')->firstOrFail();

    expect($orphan->user_id)->toBeNull()
        ->and(User::withTrashed()->where('email', 'deletado@legado.test')->exists())->toBeFalse();
});

test('migra usuários preservando o hash bcrypt legado e mapeando roles', function () {
    runLegacyImport();

    $admin = User::query()->where('email', 'callcocam@gmail.com')->firstOrFail();

    expect($admin->role)->toBe(UserRole::Admin) // role legada Super Admin (all-access)
        ->and($admin->password)->toBe('$2y$10$WF06nsqUPAOEi92Yo821O.mRqEsw3W3il1CuCk8xV39lDdbAaRYua')
        ->and($admin->created_at->toDateTimeString())->toBe('2020-06-05 15:22:09');

    $customer = User::query()->where('email', 'senha.conhecida@legado.test')->firstOrFail();

    expect($customer->role)->toBe(UserRole::Customer);

    // Usuário da outra empresa não é migrado.
    expect(User::query()->where('email', 'alfredgennick@outlook.com')->exists())->toBeFalse();
});

test('login funciona com senha legada de usuário migrado', function () {
    runLegacyImport();

    expect(Auth::validate(['email' => 'senha.conhecida@legado.test', 'password' => 'legado123']))->toBeTrue()
        ->and(Auth::validate(['email' => 'senha.conhecida@legado.test', 'password' => 'errada']))->toBeFalse();
});

test('é idempotente: rodar duas vezes não duplica registros', function () {
    runLegacyImport();

    $counts = [
        Category::withTrashed()->count(),
        Product::withTrashed()->count(),
        Media::query()->count(),
        Slider::withTrashed()->count(),
        Quote::withTrashed()->count(),
        QuoteItem::query()->count(),
        User::withTrashed()->count(),
    ];

    runLegacyImport();

    expect([
        Category::withTrashed()->count(),
        Product::withTrashed()->count(),
        Media::query()->count(),
        Slider::withTrashed()->count(),
        Quote::withTrashed()->count(),
        QuoteItem::query()->count(),
        User::withTrashed()->count(),
    ])->toBe($counts);
});

test('persiste o mapa uuid→ulid e o relatório em storage', function () {
    runLegacyImport();

    Storage::disk('local')->assertExists('legacy-id-map.json');
    Storage::disk('local')->assertExists('legacy-import-report.json');

    $map = json_decode(Storage::disk('local')->get('legacy-id-map.json'), true);

    expect($map)->toHaveKeys(['categories', 'products', 'files', 'users', 'orders'])
        ->and($map['products']['0d332bf1-5627-4ba8-9f11-c9d87fdcebcb'] ?? null)
        ->toBe(Product::withTrashed()->where('name', 'Buda Ref: 016')->value('id'));
});

test('importa dados da empresa e endereço como settings', function () {
    runLegacyImport();

    expect(Setting::query()->where('key', 'company_document')->value('value'))->not->toBeNull()
        ->and(Setting::query()->where('key', 'contact_email')->value('value'))->toBe('contato@estatuasoliveira.com.br')
        ->and(Setting::query()->where('key', 'address_city')->value('value'))->not->toBeNull();
});

test('a opção fresh apaga somente os dados importados e reimporta', function () {
    runLegacyImport();

    $seeded = User::factory()->create(); // usuário local não pode ser apagado pelo --fresh

    test()->artisan('legacy:import', ['--path' => 'tests/Fixtures/legacy-sample.sql', '--fresh' => true])
        ->assertSuccessful();

    expect(User::query()->whereKey($seeded->id)->exists())->toBeTrue()
        ->and(User::query()->where('email', 'callcocam@gmail.com')->count())->toBe(1)
        ->and(Product::withTrashed()->where('name', 'Buda Ref: 016')->count())->toBe(1);
});

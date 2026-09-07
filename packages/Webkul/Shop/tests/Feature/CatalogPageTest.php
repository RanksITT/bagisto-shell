<?php

use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Category\Models\Category;
use Webkul\Faker\Helpers\Product as ProductFaker;
use Webkul\Product\Helpers\Toolbar;
use Webkul\Product\Repositories\ProductRepository;

use function Pest\Laravel\get;

it('returns a successful response', function () {
    // Act and Assert.
    get(route('shop.products.index'))
        ->assertOk();
});

it('draws the whole catalogue behind one grid and one filter drawer', function () {
    // Act.
    $response = get(route('shop.products.index'));

    // Assert.
    $response->assertOk();

    expect(Str::contains($response->content(), '<v-catalog>'))
        ->toBeTruthy();

    expect(Str::contains($response->content(), 'v-filters'))
        ->toBeTruthy();

    expect(Str::contains($response->content(), 'v-drawer'))
        ->toBeTruthy();
});

it('keeps browsing inside the drawer rather than down the page', function () {
    // Act.
    $response = get(route('shop.products.index'));

    // Assert.
    $response->assertOk();

    $body = Str::before($response->content(), '<script');

    expect(Str::contains($body, trans('shop::app.products.catalog.browse-by-category')))
        ->toBeFalsy();

    expect(Str::contains($response->content(), trans('shop::app.products.catalog.browse-by-category')))
        ->toBeTruthy();

    expect(Str::contains($response->content(), trans('shop::app.products.catalog.browse-by-spec')))
        ->toBeTruthy();
});

it('wires every category tile and specification chip to the filter state', function () {
    // Arrange.
    $category = Category::factory()->create();

    $attribute = Attribute::query()
        ->where('is_filterable', 1)
        ->where('type', '!=', 'price')
        ->whereHas('options')
        ->firstOrFail();

    $counts = app(ProductRepository::class)->getFilterableOptionCounts([$attribute->id])
        ->get($attribute->id, collect());

    // Act.
    $response = get(route('shop.products.index'));

    // Assert.
    $response->assertOk();

    expect(Str::contains($response->content(), 'toggleCategory('.$category->id.')'))
        ->toBeTruthy();

    if ($counts->isNotEmpty()) {
        expect(Str::contains($response->content(), "toggleSpec('{$attribute->code}', {$counts->keys()->first()})"))
            ->toBeTruthy();
    }
});

it('asks the product endpoint for the largest page the toolbar allows', function () {
    // Arrange.
    $maxLimit = app(Toolbar::class)->getAvailableLimits()->max();

    // Act.
    $response = get(route('shop.products.index'));

    // Assert.
    $response->assertOk();

    expect(Str::contains($response->content(), 'listingParams.limit = '.$maxLimit))
        ->toBeTruthy();
});

it('offers a chip only for the options something is stocked against', function () {
    // Arrange.
    $attribute = Attribute::query()
        ->where('is_filterable', 1)
        ->where('type', '!=', 'price')
        ->whereHas('options')
        ->firstOrFail();

    $counts = app(ProductRepository::class)->getFilterableOptionCounts([$attribute->id])
        ->get($attribute->id, collect());

    $emptyOption = $attribute->options->firstWhere(fn ($option) => ! $counts->has($option->id));

    if (! $emptyOption) {
        expect($counts)->not->toBeEmpty();

        return;
    }

    // Act.
    $response = get(route('shop.products.index'));

    // Assert.
    $response->assertOk();

    expect(Str::contains($response->content(), "toggleSpec('{$attribute->code}', {$emptyOption->id})"))
        ->toBeFalsy();
});

it('counts only enabled, individually visible products behind each option', function () {
    // Arrange.
    $attribute = Attribute::query()
        ->where('is_filterable', 1)
        ->where('type', '!=', 'price')
        ->whereHas('options')
        ->firstOrFail();

    $option = $attribute->options()->firstOrFail();

    $repository = app(ProductRepository::class);

    $countFor = fn () => $repository->getFilterableOptionCounts([$attribute->id])
        ->get($attribute->id)
        ?->get($option->id) ?? 0;

    $baseline = $countFor();

    $product = (new ProductFaker)->getSimpleProductFactory()->create();

    $product->attribute_values()->create([
        'attribute_id' => $attribute->id,
        'integer_value' => $option->id,
        'channel' => null,
        'locale' => null,
        'unique_id' => $product->id.'|'.$attribute->id,
    ]);

    // Act and Assert. The new product is now stocked against the option.
    expect($countFor())->toBe($baseline + 1);

    // Act and Assert. Disabling it takes it back out of the count.
    $product->attribute_values()
        ->where('attribute_id', Attribute::query()->where('code', 'status')->value('id'))
        ->update(['boolean_value' => 0]);

    expect($countFor())->toBe($baseline);
});

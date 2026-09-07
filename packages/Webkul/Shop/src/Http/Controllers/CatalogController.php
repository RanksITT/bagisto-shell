<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Webkul\Attribute\Enums\AttributeTypeEnum;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Helpers\Toolbar;
use Webkul\Product\Repositories\ProductRepository;

class CatalogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected Toolbar $toolbar
    ) {}

    /**
     * The whole catalogue on one page.
     *
     * Every tile and chip the page draws is an ordinary link carrying a query parameter,
     * because the filter drawer this page shares with the category and search listings already
     * seeds itself from the query string. That leaves one source of filter truth, and means
     * a shopper can be linked straight to any slice of the catalogue.
     */
    public function index(): View
    {
        return view('shop::products.catalog', [
            'categories' => $this->getCategories(),
            'specs' => $this->getSpecs($this->attributeRepository->getFilterableAttributes()),
            'orders' => $this->toolbar->getAvailableOrders()->values(),
            'maxLimit' => $this->toolbar->getAvailableLimits()->max(),
            'params' => [
                'sort' => request()->query('sort', $this->toolbar->getDefaultOrder()['value']),
                'mode' => request()->query('mode', $this->toolbar->getAvailableModes()->first()),
            ],
        ]);
    }

    /**
     * The categories the browse-by-category tiles are drawn from.
     *
     * The tree is flattened because the tiles are one flat rail: a shopper reaching for
     * "Brake Fluid" does not care that it hangs off "Car Care & Fluids".
     */
    protected function getCategories(): Collection
    {
        return $this->flatten(
            $this->categoryRepository->getVisibleCategoryTree(
                core()->getCurrentChannel()->root_category_id
            )
        );
    }

    /**
     * Flatten a category tree, keeping the position order the tree was built in.
     */
    protected function flatten(Collection $categories): Collection
    {
        return $categories->reduce(function ($flattened, $category) {
            return $flattened
                ->push($category)
                ->merge($this->flatten($category->children));
        }, collect());
    }

    /**
     * The attributes the browse-by-spec rails are drawn from.
     *
     * Price is filterable but has no options to make chips out of, and an option nothing is
     * stocked against would only lead a shopper to an empty grid, so both are dropped here
     * rather than in the view. A rail left holding one option says nothing worth a row.
     */
    protected function getSpecs(Collection $attributes): Collection
    {
        $attributes = $attributes->where('type', '!=', AttributeTypeEnum::PRICE->value);

        if ($attributes->isEmpty()) {
            return collect();
        }

        $counts = $this->productRepository->getFilterableOptionCounts(
            $attributes->pluck('id')->all()
        );

        return $attributes
            ->map(function ($attribute) use ($counts) {
                $totals = $counts->get($attribute->id, collect());

                return [
                    'code' => $attribute->code,
                    'name' => $attribute->name ?: $attribute->admin_name,
                    'options' => $attribute->options
                        ->filter(fn ($option) => $totals->has($option->id))
                        ->map(fn ($option) => [
                            'id' => $option->id,
                            'label' => $option->label ?: $option->admin_name,
                            'total' => $totals->get($option->id),
                        ])
                        ->values(),
                ];
            })
            ->filter(fn ($spec) => $spec['options']->count() > 1)
            ->values();
    }
}

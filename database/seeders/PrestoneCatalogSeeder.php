<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Product\Repositories\ProductRepository;

class PrestoneCatalogSeeder extends Seeder
{
    /**
     * Directory the seeder sources its product artwork from.
     */
    public const MEDIA_DIRECTORY = __DIR__.'/media/prestone';

    /**
     * Attribute options the Prestone range needs on top of the ones the store already has.
     */
    public const ATTRIBUTE_OPTIONS = [
        'brand' => ['Prestone'],
        'pack_size' => ['1L', '4L'],
    ];

    /**
     * Filters offered on every Prestone category.
     */
    public const FILTERABLE_ATTRIBUTES = ['brand', 'price', 'pack_size', 'vehicle_type'];

    /**
     * Attribute values shared by the whole range.
     */
    public const SHARED_ATTRIBUTES = [
        'brand' => 'Prestone',
        'vehicle_type' => 'Car',
        'status' => 1,
        'visible_individually' => 1,
        'guest_checkout' => 1,
        'manage_stock' => 0,
        'allow_rma' => 0,
    ];

    /**
     * Quantity stocked into the default inventory source for every product.
     */
    public const INVENTORY_QUANTITY = 100;

    /**
     * Ids of the attribute options resolved by label, keyed by attribute code.
     */
    protected array $optionIds = [];

    /**
     * Ids of the seeded categories, keyed by slug.
     */
    protected array $categoryIds = [];

    /**
     * Create a new seeder instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected AttributeFamilyRepository $attributeFamilyRepository,
        protected AttributeOptionRepository $attributeOptionRepository,
        protected CategoryRepository $categoryRepository,
        protected InventorySourceRepository $inventorySourceRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Seed the Prestone brand, its categories and its products, guarding the models
     * again first because Artisan unguards every one of them while seeding.
     */
    public function run(): void
    {
        Model::reguard();

        $this->seedAttributeOptions();

        $this->seedCategories();

        $this->seedProducts();
    }

    /**
     * The category tree the range is filed under, parents before their children.
     */
    protected function categories(): array
    {
        return [
            [
                'slug' => 'car-care-fluids',
                'parent' => null,
                'name' => 'Car Care & Fluids',
                'description' => 'Coolants, brake fluids, steering fluids and screen wash for everyday motoring.',
                'image' => 'prestone-antifreeze-coolant-all-vehicles.jpg',
            ], [
                'slug' => 'antifreeze-coolant',
                'parent' => 'car-care-fluids',
                'name' => 'Antifreeze & Coolant',
                'description' => 'Ready to use 50/50 antifreeze and coolant matched to your engine.',
                'image' => 'prestone-max-european-audi-vw-porsche-mercedes.jpg',
            ], [
                'slug' => 'brake-fluid',
                'parent' => 'car-care-fluids',
                'name' => 'Brake Fluid',
                'description' => 'DOT 3 and DOT 4 brake fluids for hydraulic brake and clutch systems.',
                'image' => 'prestone-max-asian-toyota-lexus-scion.jpg',
            ], [
                'slug' => 'power-steering-fluid',
                'parent' => 'car-care-fluids',
                'name' => 'Power Steering Fluid',
                'description' => 'Power steering fluids that keep the pump quiet and the seals conditioned.',
                'image' => 'prestone-max-asian-honda-acura-nissan.jpg',
            ], [
                'slug' => 'windshield-washer-fluid',
                'parent' => 'car-care-fluids',
                'name' => 'Windshield Washer Fluid',
                'description' => 'Screen wash that clears bugs, road film and grime without streaking.',
                'image' => 'prestone-max-asian-hyundai-kia-mazda.jpg',
            ],
        ];
    }

    /**
     * The Radiator Cool line MotorMart stocks. The brand has not supplied artwork
     * for it yet, so each entry carries the nearest MAX jug as a stand-in.
     */
    protected function products(): array
    {
        return [
            [
                'sku' => 'PRS-RC-GRN-1L',
                'product_number' => 'FIMT-000253',
                'name' => 'Prestone Radiator Cool Green Fluid 1L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '1L',
                'price' => 550,
                'weight' => 1.1,
                'featured' => 1,
                'new' => 1,
                'image' => 'prestone-max-asian-hyundai-kia-mazda.jpg',
                'short_description' => 'Ready to use green radiator coolant for petrol and diesel engines.',
                'description' => '<p>Prestone Radiator Cool in green fluid is a ready to use coolant for the petrol and diesel engines of everyday cars, vans and light commercials. It pours straight into the radiator or expansion tank with no water to add.</p><p>The inhibitor package guards the radiator, water pump, hoses and cylinder head against rust, scale and corrosion, and raises the boiling point so the engine holds its temperature in traffic.</p>',
            ], [
                'sku' => 'PRS-RC-PNK-1L',
                'product_number' => 'FIMT-000254',
                'name' => 'Prestone Radiator Cool Pink Fluid 1L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '1L',
                'price' => 550,
                'weight' => 1.1,
                'new' => 1,
                'image' => 'prestone-max-asian-toyota-lexus-scion.jpg',
                'short_description' => 'Ready to use pink long life radiator coolant for modern engines.',
                'description' => '<p>Prestone Radiator Cool in pink fluid is the long life coolant for engines that left the factory on a pink or red organic acid coolant, common across Japanese and Korean models. It pours straight into the radiator or expansion tank with no water to add.</p><p>The organic acid inhibitors stay active far longer than a conventional coolant, protecting the radiator, water pump, hoses and cylinder head against rust, scale and corrosion.</p>',
            ], [
                'sku' => 'PRS-RC-GRN-4L',
                'product_number' => 'FIMT-000255',
                'name' => 'Prestone Radiator Cool Green Fluid 4L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '4L',
                'price' => 1850,
                'weight' => 4.3,
                'featured' => 1,
                'image' => 'prestone-max-asian-hyundai-kia-mazda.jpg',
                'short_description' => 'Full flush and fill of ready to use green radiator coolant.',
                'description' => '<p>The 4 L jug of Prestone Radiator Cool green fluid is sized for a complete drain and refill rather than a top up, and suits the petrol and diesel engines of everyday cars, vans and light commercials.</p><p>The inhibitor package guards the radiator, water pump, hoses and cylinder head against rust, scale and corrosion, and raises the boiling point so the engine holds its temperature in traffic. Ready to use with no water to add.</p>',
            ], [
                'sku' => 'PRS-RC-PNK-4L',
                'product_number' => 'FIMT-000256',
                'name' => 'Prestone Radiator Cool Pink Fluid 4L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '4L',
                'price' => 1850,
                'weight' => 4.3,
                'image' => 'prestone-max-asian-toyota-lexus-scion.jpg',
                'short_description' => 'Full flush and fill of ready to use pink long life radiator coolant.',
                'description' => '<p>The 4 L jug of Prestone Radiator Cool pink fluid is sized for a complete drain and refill of an engine that runs a pink or red organic acid coolant, common across Japanese and Korean models.</p><p>The organic acid inhibitors stay active far longer than a conventional coolant, protecting the radiator, water pump, hoses and cylinder head against rust, scale and corrosion. Ready to use with no water to add.</p>',
            ],
        ];
    }

    /**
     * Add the attribute options the range needs, leaving any that already exist alone.
     */
    protected function seedAttributeOptions(): void
    {
        foreach (self::ATTRIBUTE_OPTIONS as $attributeCode => $labels) {
            $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

            $sortOrder = $attribute->options()->max('sort_order') ?? 0;

            foreach ($labels as $label) {
                if ($attribute->options()->where('admin_name', $label)->exists()) {
                    continue;
                }

                $this->attributeOptionRepository->create([
                    'attribute_id' => $attribute->id,
                    'admin_name' => $label,
                    'sort_order' => ++$sortOrder,
                    'en' => ['label' => $label],
                ]);
            }
        }

        foreach (['brand', 'pack_size', 'vehicle_type'] as $attributeCode) {
            $this->optionIds[$attributeCode] = $this->attributeRepository
                ->findOneByField('code', $attributeCode)
                ->options()
                ->pluck('id', 'admin_name')
                ->all();
        }
    }

    /**
     * Create the category tree, or refresh the categories that are already there.
     */
    protected function seedCategories(): void
    {
        $rootId = core()->getDefaultChannel()->root_category_id;

        $filterableAttributeIds = $this->attributeRepository
            ->findWhereIn('code', self::FILTERABLE_ATTRIBUTES)
            ->pluck('id')
            ->all();

        $position = $this->categoryRepository->getModel()->max('position');

        foreach ($this->categories() as $category) {
            $existing = $this->categoryRepository->getModel()
                ->whereTranslation('slug', $category['slug'])
                ->first();

            $data = $this->categoryTranslations($category) + [
                'locale' => core()->getRequestedLocaleCode(),
                'parent_id' => $category['parent'] ? $this->categoryIds[$category['parent']] : $rootId,
                'position' => ++$position,
                'status' => 1,
                'display_mode' => 'products_and_description',
                'attributes' => $filterableAttributeIds,
                'logo_path' => ['image_0' => $this->uploadedFile($category['image'])],
                'logo_meta' => [['file_name' => $category['slug'], 'alt_text' => $category['name']]],
            ];

            $model = $this->withUploadedRequestFiles($data['logo_path'], fn () => $existing
                ? $this->categoryRepository->update($data, $existing->id)
                : $this->categoryRepository->create($data));

            $this->categoryIds[$category['slug']] = $model->id;
        }
    }

    /**
     * Create the products, or refresh the ones that are already there.
     */
    protected function seedProducts(): void
    {
        $familyId = $this->attributeFamilyRepository->findOneByField('code', 'default')->id;

        foreach ($this->products() as $product) {
            $existing = $this->productRepository->findOneByField('sku', $product['sku']);

            if (! $existing) {
                $existing = $this->productRepository->create([
                    'type' => 'simple',
                    'attribute_family_id' => $familyId,
                    'sku' => $product['sku'],
                ]);
            }

            $this->productRepository->update($this->productData($product), $existing->id);
        }
    }

    /**
     * Build the payload the product repository expects for a single product.
     */
    protected function productData(array $product): array
    {
        $urlKey = str($product['name'])->replace(['+', '.'], '-')->slug()->value();

        $data = self::SHARED_ATTRIBUTES + [
            'sku' => $product['sku'],
            'product_number' => $product['product_number'],
            'name' => $product['name'],
            'url_key' => $urlKey,
            'price' => $product['price'],
            'weight' => $product['weight'],
            'short_description' => $product['short_description'],
            'description' => $product['description'],
            'meta_title' => $product['name'].' | MotorMart',
            'meta_description' => $product['short_description'],
            'featured' => $product['featured'] ?? 0,
            'new' => $product['new'] ?? 0,
            'channels' => [core()->getDefaultChannel()->id],
            'categories' => [
                $this->categoryIds['car-care-fluids'],
                $this->categoryIds[$product['category']],
            ],
            'inventories' => [
                $this->inventorySourceRepository->findOneByField('code', 'default')->id => self::INVENTORY_QUANTITY,
            ],
        ];

        $data['brand'] = $this->optionIds['brand'][$data['brand']];

        $data['vehicle_type'] = $this->optionIds['vehicle_type'][$data['vehicle_type']];

        $data['pack_size'] = $this->optionIds['pack_size'][$product['pack_size']];

        if (isset($product['image'])) {
            $data['images'] = [
                'files' => [$this->uploadedFile($product['image'])],
                'meta' => [['file_name' => $urlKey, 'alt_text' => $product['name']]],
            ];
        }

        return $data;
    }

    /**
     * Spell a category's translated fields out under every locale code, which is how
     * the repository reads them on both create and update.
     */
    protected function categoryTranslations(array $category): array
    {
        $translations = [];

        foreach (core()->getAllLocales() as $locale) {
            $translations[$locale->code] = [
                'locale_id' => $locale->id,
                'name' => $category['name'],
                'slug' => $category['slug'],
                'description' => $category['description'],
                'meta_title' => $category['name'].' | MotorMart',
                'meta_description' => $category['description'],
            ];
        }

        return $translations;
    }

    /**
     * Run the callback against a request carrying the given logo files, which is where
     * the category repository reads its images from rather than off the payload.
     */
    protected function withUploadedRequestFiles(array $files, callable $callback): mixed
    {
        $original = request();

        app()->instance('request', Request::create('/', 'POST', [], [], ['logo_path' => $files]));

        try {
            return $callback();
        } finally {
            app()->instance('request', $original);
        }
    }

    /**
     * Wrap one of the bundled artwork files so the media repositories can store it.
     */
    protected function uploadedFile(string $fileName): UploadedFile
    {
        return new UploadedFile(
            self::MEDIA_DIRECTORY.'/'.$fileName,
            $fileName,
            'image/jpeg',
            null,
            true
        );
    }
}

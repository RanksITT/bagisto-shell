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
        'pack_size' => ['355ml', '946ml', '3.78L', '9.46L'],
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
     * Seed the Prestone brand, its categories and its products.
     *
     * The catalog repositories hand the whole payload to the models and lean on their
     * fillable lists to strip the keys that are not columns, so the models have to be
     * guarded again for the length of the run. Artisan unguards them while seeding.
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
     * The Prestone range.
     *
     * The five 3.78 L coolants carry the artwork supplied by the brand. The remaining
     * entries are placeholder listings and are deliberately left without an image.
     */
    protected function products(): array
    {
        return [
            [
                'sku' => 'PRS-AF-ALL-3.78L',
                'name' => 'Prestone Antifreeze+Coolant All Vehicles 3.78L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '3.78L',
                'price' => 2450,
                'weight' => 4.1,
                'featured' => 1,
                'new' => 1,
                'image' => 'prestone-antifreeze-coolant-all-vehicles.jpg',
                'short_description' => 'Ready to use 50/50 antifreeze and coolant for every make, model and year.',
                'description' => '<p>Prestone Antifreeze+Coolant is guaranteed for all makes, models and years, so a single jug covers the whole driveway. It arrives prediluted 50/50 and is ready to pour straight into the radiator or reservoir with no water to add.</p><p>The formula protects against rust and corrosion across the entire cooling system and carries a 10 year, 300,000 mile guarantee when used as directed.</p>',
            ], [
                'sku' => 'PRS-AF-ALL-946ML',
                'name' => 'Prestone Antifreeze+Coolant All Vehicles 946ml',
                'category' => 'antifreeze-coolant',
                'pack_size' => '946ml',
                'price' => 750,
                'weight' => 1.05,
                'image' => 'prestone-antifreeze-coolant-all-vehicles.jpg',
                'short_description' => 'Top-up bottle of ready to use 50/50 antifreeze and coolant for all vehicles.',
                'description' => '<p>The 946 ml bottle of Prestone Antifreeze+Coolant is sized for topping up between services and for keeping in the boot. Guaranteed for all makes, models and years, prediluted 50/50 and ready to use.</p><p>Protects against rust and corrosion across the whole cooling system, with a 10 year, 300,000 mile guarantee when used as directed.</p>',
            ], [
                'sku' => 'PRS-AF-ALL-9.46L',
                'name' => 'Prestone Antifreeze+Coolant All Vehicles 9.46L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '9.46L',
                'price' => 5650,
                'weight' => 10.2,
                'image' => 'prestone-antifreeze-coolant-all-vehicles.jpg',
                'short_description' => 'Workshop sized ready to use 50/50 antifreeze and coolant for all vehicles.',
                'description' => '<p>The 9.46 L jug of Prestone Antifreeze+Coolant suits workshops and fleets that flush and fill more than one cooling system at a time. Guaranteed for all makes, models and years and prediluted 50/50, so it pours straight in.</p><p>Protects against rust and corrosion across the whole cooling system, with a 10 year, 300,000 mile guarantee when used as directed.</p>',
            ], [
                'sku' => 'PRS-MAX-ASN-RED-3.78L',
                'name' => 'Prestone MAX Asian Vehicles Red Antifreeze+Coolant 3.78L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '3.78L',
                'price' => 2950,
                'weight' => 4.1,
                'featured' => 1,
                'new' => 1,
                'image' => 'prestone-max-asian-toyota-lexus-scion.jpg',
                'short_description' => 'Red fluid coolant guaranteed for Toyota, Lexus and Scion, all years.',
                'description' => '<p>Prestone MAX in red fluid is guaranteed for Toyota, Lexus and Scion vehicles of all years, matching the original equipment coolant those engines are built around.</p><p>Advanced Original Equipment Technology delivers industry-leading protection from engine-damaging rust and corrosion, with a 15 year, 350,000 mile fluid life. Prediluted 50/50 and ready to use, so no water is added.</p>',
            ], [
                'sku' => 'PRS-MAX-ASN-RED-946ML',
                'name' => 'Prestone MAX Asian Vehicles Red Antifreeze+Coolant 946ml',
                'category' => 'antifreeze-coolant',
                'pack_size' => '946ml',
                'price' => 890,
                'weight' => 1.05,
                'image' => 'prestone-max-asian-toyota-lexus-scion.jpg',
                'short_description' => 'Top-up bottle of red fluid coolant for Toyota, Lexus and Scion.',
                'description' => '<p>A 946 ml top-up bottle of Prestone MAX red fluid, guaranteed for Toyota, Lexus and Scion vehicles of all years and matched to the coolant those engines left the factory with.</p><p>Advanced Original Equipment Technology protects against rust and corrosion for up to 15 years or 350,000 miles. Prediluted 50/50 and ready to use.</p>',
            ], [
                'sku' => 'PRS-MAX-ASN-BLU-3.78L',
                'name' => 'Prestone MAX Asian Vehicles Blue Antifreeze+Coolant 3.78L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '3.78L',
                'price' => 2950,
                'weight' => 4.1,
                'featured' => 1,
                'new' => 1,
                'image' => 'prestone-max-asian-honda-acura-nissan.jpg',
                'short_description' => 'Blue fluid coolant for Honda and Acura, plus Nissan, Infiniti and Subaru from 2009.',
                'description' => '<p>Prestone MAX in blue fluid is guaranteed for Honda and Acura vehicles of all years, and for Nissan, Infiniti and Subaru vehicles from 2009 onwards.</p><p>Advanced Original Equipment Technology delivers industry-leading protection from engine-damaging rust and corrosion, with a 15 year, 350,000 mile fluid life. Prediluted 50/50 and ready to use, so no water is added.</p>',
            ], [
                'sku' => 'PRS-MAX-ASN-BLU-946ML',
                'name' => 'Prestone MAX Asian Vehicles Blue Antifreeze+Coolant 946ml',
                'category' => 'antifreeze-coolant',
                'pack_size' => '946ml',
                'price' => 890,
                'weight' => 1.05,
                'image' => 'prestone-max-asian-honda-acura-nissan.jpg',
                'short_description' => 'Top-up bottle of blue fluid coolant for Honda, Acura, Nissan, Infiniti and Subaru.',
                'description' => '<p>A 946 ml top-up bottle of Prestone MAX blue fluid, guaranteed for Honda and Acura vehicles of all years and for Nissan, Infiniti and Subaru vehicles from 2009 onwards.</p><p>Advanced Original Equipment Technology protects against rust and corrosion for up to 15 years or 350,000 miles. Prediluted 50/50 and ready to use.</p>',
            ], [
                'sku' => 'PRS-MAX-ASN-GRN-3.78L',
                'name' => 'Prestone MAX Asian Vehicles Green Antifreeze+Coolant 3.78L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '3.78L',
                'price' => 2950,
                'weight' => 4.1,
                'new' => 1,
                'image' => 'prestone-max-asian-hyundai-kia-mazda.jpg',
                'short_description' => 'Green fluid coolant for Hyundai, Kia, Mazda and Mitsubishi, all years.',
                'description' => '<p>Prestone MAX in green fluid is guaranteed for Hyundai, Kia, Mazda and Mitsubishi vehicles of all years, for Suzuki up to 2010 and for Nissan, Infiniti and Subaru up to 2009.</p><p>Advanced Original Equipment Technology delivers industry-leading protection from engine-damaging rust and corrosion, with a 15 year, 350,000 mile fluid life. Prediluted 50/50 and ready to use, so no water is added.</p>',
            ], [
                'sku' => 'PRS-MAX-EUR-VLT-3.78L',
                'name' => 'Prestone MAX European Vehicles Violet Antifreeze+Coolant 3.78L',
                'category' => 'antifreeze-coolant',
                'pack_size' => '3.78L',
                'price' => 3250,
                'weight' => 4.1,
                'featured' => 1,
                'new' => 1,
                'image' => 'prestone-max-european-audi-vw-porsche-mercedes.jpg',
                'short_description' => 'Violet fluid coolant for Audi, Volkswagen, Porsche and Mercedes.',
                'description' => '<p>Prestone MAX in violet fluid is guaranteed for Audi from 2000, Volkswagen from 2009, Porsche from 2010 and Mercedes from 2014, matching the original equipment coolant those engines are built around.</p><p>Advanced Original Equipment Technology delivers maximum engine protection from rust and corrosion, with a 5 year, 150,000 mile fluid life. Prediluted 50/50 and ready to use, so no water is added.</p>',
            ], [
                'sku' => 'PRS-MAX-EUR-VLT-946ML',
                'name' => 'Prestone MAX European Vehicles Violet Antifreeze+Coolant 946ml',
                'category' => 'antifreeze-coolant',
                'pack_size' => '946ml',
                'price' => 980,
                'weight' => 1.05,
                'image' => 'prestone-max-european-audi-vw-porsche-mercedes.jpg',
                'short_description' => 'Top-up bottle of violet fluid coolant for Audi, Volkswagen, Porsche and Mercedes.',
                'description' => '<p>A 946 ml top-up bottle of Prestone MAX violet fluid, guaranteed for Audi from 2000, Volkswagen from 2009, Porsche from 2010 and Mercedes from 2014.</p><p>Advanced Original Equipment Technology protects against rust and corrosion for up to 5 years or 150,000 miles. Prediluted 50/50 and ready to use.</p>',
            ], [
                'sku' => 'PRS-BF-DOT3-355ML',
                'name' => 'Prestone DOT 3 Brake Fluid 355ml',
                'category' => 'brake-fluid',
                'pack_size' => '355ml',
                'price' => 480,
                'weight' => 0.42,
                'short_description' => 'DOT 3 brake fluid for hydraulic brake and clutch systems.',
                'description' => '<p>Prestone DOT 3 Brake Fluid meets the FMVSS 116 DOT 3 specification and suits the hydraulic brake and clutch systems of most passenger cars and light trucks.</p><p>A high boiling point resists vapour lock under hard braking, and the corrosion inhibitors protect the metal and rubber parts of the system. Use only from a sealed container, as brake fluid absorbs moisture from the air.</p>',
            ], [
                'sku' => 'PRS-BF-DOT3-946ML',
                'name' => 'Prestone DOT 3 Brake Fluid 946ml',
                'category' => 'brake-fluid',
                'pack_size' => '946ml',
                'price' => 1150,
                'weight' => 1.08,
                'short_description' => 'Workshop bottle of DOT 3 brake fluid for hydraulic brake and clutch systems.',
                'description' => '<p>The 946 ml bottle of Prestone DOT 3 Brake Fluid is sized for a full brake bleed. It meets the FMVSS 116 DOT 3 specification and suits the hydraulic brake and clutch systems of most passenger cars and light trucks.</p><p>A high boiling point resists vapour lock under hard braking, and the corrosion inhibitors protect the metal and rubber parts of the system.</p>',
            ], [
                'sku' => 'PRS-BF-DOT4-355ML',
                'name' => 'Prestone DOT 4 Brake Fluid 355ml',
                'category' => 'brake-fluid',
                'pack_size' => '355ml',
                'price' => 620,
                'weight' => 0.42,
                'new' => 1,
                'short_description' => 'DOT 4 brake fluid with a higher boiling point for ABS equipped vehicles.',
                'description' => '<p>Prestone DOT 4 Brake Fluid meets the FMVSS 116 DOT 4 specification and carries a higher dry and wet boiling point than DOT 3, which suits vehicles fitted with ABS and those driven hard or towing.</p><p>Compatible with DOT 3 systems where the manufacturer permits it. Use only from a sealed container, as brake fluid absorbs moisture from the air.</p>',
            ], [
                'sku' => 'PRS-PSF-946ML',
                'name' => 'Prestone Power Steering Fluid 946ml',
                'category' => 'power-steering-fluid',
                'pack_size' => '946ml',
                'price' => 890,
                'weight' => 0.95,
                'short_description' => 'Power steering fluid that quietens the pump and conditions the seals.',
                'description' => '<p>Prestone Power Steering Fluid restores smooth, quiet steering in most domestic and imported vehicles that call for a conventional power steering fluid.</p><p>Seal conditioners keep the hoses and seals pliable to help stop weeping, and the anti-wear additives protect the pump. Stays fluid in cold weather so assistance is there from the first turn of the wheel.</p>',
            ], [
                'sku' => 'PRS-PSF-ASN-946ML',
                'name' => 'Prestone Power Steering Fluid for Asian Vehicles 946ml',
                'category' => 'power-steering-fluid',
                'pack_size' => '946ml',
                'price' => 1050,
                'weight' => 0.95,
                'short_description' => 'Power steering fluid formulated for Japanese and Korean steering systems.',
                'description' => '<p>Prestone Power Steering Fluid for Asian Vehicles is formulated for the tighter tolerances and lower viscosity requirements of Japanese and Korean power steering systems.</p><p>Seal conditioners keep the hoses and seals pliable, and the anti-wear additives protect the pump. Check the handbook before use, as some vehicles specify an automatic transmission fluid instead.</p>',
            ], [
                'sku' => 'PRS-WW-BUGWASH-3.78L',
                'name' => 'Prestone Bug Wash Windshield Washer Fluid 3.78L',
                'category' => 'windshield-washer-fluid',
                'pack_size' => '3.78L',
                'price' => 620,
                'weight' => 3.9,
                'short_description' => 'Screen wash that lifts bugs and road film without streaking.',
                'description' => '<p>Prestone Bug Wash cuts through insect residue, road film and grime that ordinary water leaves behind, clearing the screen on the first pass of the wipers.</p><p>Ready to use with no dilution needed, and safe for painted surfaces, glass and wiper rubber. Fills the reservoir straight from the jug.</p>',
            ], [
                'sku' => 'PRS-WW-ALLSEASON-3.78L',
                'name' => 'Prestone All Season Windshield Washer Fluid 3.78L',
                'category' => 'windshield-washer-fluid',
                'pack_size' => '3.78L',
                'price' => 540,
                'weight' => 3.9,
                'short_description' => 'Year round screen wash for everyday road grime.',
                'description' => '<p>Prestone All Season Windshield Washer Fluid handles the dust, road film and light grime of everyday driving, whatever the month.</p><p>Ready to use with no dilution needed, and safe for painted surfaces, glass and wiper rubber. Fills the reservoir straight from the jug.</p>',
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
            'name' => $product['name'],
            'url_key' => $urlKey,
            'price' => $product['price'],
            'weight' => $product['weight'],
            'short_description' => $product['short_description'],
            'description' => $product['description'],
            'meta_title' => $product['name'].' | Rancon LubMart',
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
     * Spell a category's translated fields out for every locale the store runs.
     *
     * The repository reads translations off locale keyed sub arrays on both create and
     * update, so the same category text is repeated under each locale code.
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
                'meta_title' => $category['name'].' | Rancon LubMart',
                'meta_description' => $category['description'],
            ];
        }

        return $translations;
    }

    /**
     * Run the callback against a request carrying the given logo files.
     *
     * The category repository reads its images off the current request rather than off
     * the payload, so seeding one has to put the files where it looks for them.
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

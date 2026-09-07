<!-- SEO Meta Content -->
@push('meta')
    <meta
        name="description"
        content="{{ trans('shop::app.products.catalog.meta-description') }}"
    />
@endPush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        @lang('shop::app.products.catalog.title')
    </x-slot>

    <!-- Catalogue Hero -->
    <div class="container mt-10 px-[60px] max-lg:px-8 max-md:mt-6 max-sm:px-4">
        <h1
            class="font-dmserif text-5xl max-md:text-3xl max-sm:text-2xl"
            data-reveal="up"
        >
            @lang('shop::app.products.catalog.heading')
        </h1>

        <p
            class="mt-4 max-w-[640px] text-mutedBlue max-md:mt-3 max-md:text-sm max-sm:text-xs"
            data-reveal="up"
        >
            @lang('shop::app.products.catalog.subtitle')
        </p>
    </div>

    <!-- Catalogue Vue Component -->
    <v-catalog>
        <!-- Catalogue Shimmer Effect -->
        <div class="container mt-16 px-[60px] max-lg:px-8 max-md:mt-10 max-sm:px-4">
            <div class="grid grid-cols-4 gap-8 max-1180:grid-cols-3 max-1060:grid-cols-2 max-md:gap-x-4">
                <x-shop::shimmer.products.cards.grid count="12" />
            </div>
        </div>
    </v-catalog>

    {{-- The filter drawer presents the same `v-filters` component the category and search rails use. --}}
    @include('shop::categories.filter-component')

    @pushOnce('scripts')
        <script
            type="text/x-template"
            id="v-catalog-template"
        >
            <div class="container mt-16 px-[60px] max-lg:px-8 max-md:mt-10 max-sm:px-4">
                <!-- Catalogue Toolbar -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-navyBorder pb-5">
                    <div class="flex flex-wrap items-center gap-4">
                        <!-- Filters Drawer -->
                        <x-shop::drawer
                            position="left"
                            width="420px"
                            ::is-active="isDrawerActive.filter"
                        >
                            <!-- Drawer Toggler -->
                            <x-slot:toggle>
                                <button
                                    class="flex cursor-pointer items-center gap-x-2.5 rounded-full border border-navyBorder bg-navySurface px-5 py-2.5 text-sm font-medium uppercase transition-colors duration-200 hover:border-darkBlue hover:bg-navySurfaceHover max-sm:px-4 max-sm:py-2 max-sm:text-xs"
                                    type="button"
                                    @click="isDrawerActive.filter = true"
                                >
                                    <span class="icon-filter-1 text-xl"></span>

                                    @lang('shop::app.categories.filters.filter')

                                    <span
                                        class="rounded-full bg-darkBlue px-2 text-xs text-navyBlue"
                                        v-if="appliedFilterCount"
                                        v-text="appliedFilterCount"
                                    ></span>
                                </button>
                            </x-slot>

                            {{--
                                The drawer carries only its close control. `v-filters` heads its
                                own panel with a title and a Clear All that also unticks the
                                boxes, which a second header here would duplicate and undercut.
                            --}}
                            <x-slot:header class="!pb-0">
                                <div></div>
                            </x-slot>

                            <!-- Drawer Content -->
                            <x-slot:content>
                                <div class="grid gap-5">
                                    <!-- Browse By Category -->
                                    <x-shop::products.catalog.category-tiles :categories="$categories" />

                                    <!-- Browse By Specification -->
                                    <x-shop::products.catalog.spec-rails :specs="$specs" />

                                    <v-filters
                                        ref="filterPanel"
                                        @filter-applied="setFilters('filter', $event)"
                                        @filter-clear="clearFilters('filter', $event)"
                                    >
                                        <x-shop::shimmer.categories.filters />
                                    </v-filters>
                                </div>
                            </x-slot>
                        </x-shop::drawer>

                        <!-- Result Count -->
                        <p
                            class="text-sm text-mutedBlue max-sm:text-xs"
                            v-if="! isLoading"
                            v-text="resultSummary"
                        ></p>
                    </div>

                    <div class="flex items-center gap-4">
                        <!-- Sort -->
                        <x-shop::dropdown position="bottom-{{ core()->getCurrentLocale()->direction === 'rtl' ? 'left' : 'right' }}">
                            <x-slot:toggle>
                                <button
                                    class="flex cursor-pointer items-center gap-x-2.5 rounded-full border border-navyBorder bg-navySurface px-5 py-2.5 text-sm font-medium transition-colors duration-200 hover:border-darkBlue hover:bg-navySurfaceHover max-sm:px-4 max-sm:py-2 max-sm:text-xs"
                                    type="button"
                                >
                                    <span class="icon-sort-1 text-xl"></span>

                                    @{{ appliedSortLabel }}
                                </button>
                            </x-slot>

                            <x-slot:menu>
                                <x-shop::dropdown.menu.item
                                    v-for="order in orders"
                                    ::key="order.value"
                                    @click="applySort(order.value)"
                                >
                                    <span
                                        :class="order.value === appliedSort ? 'text-darkBlue' : ''"
                                        v-text="order.title"
                                    ></span>
                                </x-shop::dropdown.menu.item>
                            </x-slot>
                        </x-shop::dropdown>

                        <!-- Display Mode -->
                        <div class="flex items-center gap-2">
                            <span
                                class="cursor-pointer text-2xl"
                                :class="appliedMode === 'list' ? 'icon-listing-fill text-darkBlue' : 'icon-listing'"
                                role="button"
                                tabindex="0"
                                aria-label="@lang('shop::app.categories.toolbar.list')"
                                @click="applyMode('list')"
                            ></span>

                            <span
                                class="cursor-pointer text-2xl"
                                :class="appliedMode !== 'list' ? 'icon-grid-view-fill text-darkBlue' : 'icon-grid-view'"
                                role="button"
                                tabindex="0"
                                aria-label="@lang('shop::app.categories.toolbar.grid')"
                                @click="applyMode('grid')"
                            ></span>
                        </div>
                    </div>
                </div>

                <!-- Product List Card Container -->
                <div
                    class="mt-8 grid grid-cols-1 gap-6"
                    data-reveal-group
                    v-if="appliedMode === 'list'"
                >
                    <template v-if="isLoading">
                        <x-shop::shimmer.products.cards.list count="12" />
                    </template>

                    <template v-else>
                        <template v-if="products.length">
                            <x-shop::products.card
                                ::mode="'list'"
                                v-for="product in products"
                            />
                        </template>

                        <template v-else>
                            @include('shop::products.catalog.empty')
                        </template>
                    </template>
                </div>

                <!-- Product Grid Card Container -->
                <div v-else class="mt-8 max-md:mt-5">
                    <template v-if="isLoading">
                        <div class="grid grid-cols-4 gap-8 max-1180:grid-cols-3 max-1060:grid-cols-2 max-md:justify-items-center max-md:gap-x-4">
                            <x-shop::shimmer.products.cards.grid count="12" />
                        </div>
                    </template>

                    <template v-else>
                        <template v-if="products.length">
                            <div
                                class="grid grid-cols-4 gap-8 max-1180:grid-cols-3 max-1060:grid-cols-2 max-md:justify-items-center max-md:gap-x-4"
                                data-reveal-group
                            >
                                <x-shop::products.card
                                    ::mode="'grid'"
                                    v-for="product in products"
                                />
                            </div>
                        </template>

                        <template v-else>
                            @include('shop::products.catalog.empty')
                        </template>
                    </template>
                </div>

                <!-- Still Fetching The Remainder -->
                <div
                    class="mt-10 flex justify-center"
                    v-if="loader"
                >
                    <img
                        class="h-6 w-6 animate-spin"
                        src="{{ bagisto_asset('images/spinner.svg') }}"
                        alt="Loading"
                    />
                </div>
            </div>
        </script>

        <script type="module">
            app.component('v-catalog', {
                template: '#v-catalog-template',

                data() {
                    return {
                        isLoading: true,

                        isDrawerActive: {
                            filter: false,
                        },

                        filters: {
                            filter: this.filtersFromUrl(),
                        },

                        selectedCategoryId: new URLSearchParams(window.location.search).get('category_id'),

                        orders: @json($orders),

                        appliedSort: "{{ $params['sort'] }}",

                        appliedMode: "{{ $params['mode'] }}",

                        products: [],

                        total: 0,

                        loader: false,
                    }
                },

                computed: {
                    /**
                     * `view` and `mode` describe how this page is drawn rather than what it
                     * holds, so they stay in the address bar and out of the product request.
                     */
                    listingParams() {
                        let { view, mode, ...listingParams } = Object.assign({}, this.filters.filter);

                        listingParams.sort = this.appliedSort;

                        listingParams.limit = {{ $maxLimit }};

                        return this.removeJsonEmptyValues(listingParams);
                    },

                    queryString() {
                        let params = Object.assign({}, this.removeJsonEmptyValues(Object.assign({}, this.filters.filter)), {
                            sort: this.appliedSort,
                            mode: this.appliedMode,
                        });

                        delete params.view;

                        return this.jsonToQueryString(this.removeJsonEmptyValues(params));
                    },

                    appliedFilterCount() {
                        return Object.keys(this.removeJsonEmptyValues(Object.assign({}, this.filters.filter)))
                            .filter(key => ! ['view', 'mode', 'sort', 'limit'].includes(key))
                            .length;
                    },

                    appliedSortLabel() {
                        return this.orders.find(order => order.value === this.appliedSort)?.title
                            ?? "@lang('shop::app.products.sort-by.title')";
                    },

                    resultSummary() {
                        return "@lang('shop::app.products.catalog.showing', ['count' => ':count', 'total' => ':total'])"
                            .replace(':count', this.products.length)
                            .replace(':total', this.total);
                    },
                },

                watch: {
                    listingParams() {
                        this.getProducts();
                    },

                    queryString() {
                        window.history.pushState({}, '', '?' + this.queryString);
                    },
                },

                mounted() {
                    this.getProducts();
                },

                methods: {
                    /**
                     * The catalogue is entered by link as often as by control: a category tile,
                     * a specification chip, or a shared address all arrive as query parameters.
                     * They are read here rather than waited on from the filter drawer, which is
                     * closed on arrival and is only one of the ways a filter gets set.
                     */
                    filtersFromUrl() {
                        let filters = {};

                        new URLSearchParams(window.location.search).forEach((value, key) => {
                            if (['sort', 'limit', 'mode', 'view'].includes(key)) {
                                return;
                            }

                            filters[key] = value.split(',');
                        });

                        return filters;
                    },

                    /**
                     * `v-filters` owns every attribute filter and re-emits the whole set, which
                     * is why the chosen category is carried alongside rather than inside it.
                     */
                    setFilters(type, filters) {
                        if (type !== 'filter') {
                            this.filters[type] = filters;

                            return;
                        }

                        let applied = Object.assign({}, filters);

                        if (this.selectedCategoryId) {
                            applied.category_id = [this.selectedCategoryId];
                        } else {
                            delete applied.category_id;
                        }

                        this.filters.filter = applied;
                    },

                    clearFilters(type, filters) {
                        this.selectedCategoryId = null;

                        this.filters[type] = {};
                    },

                    isCategorySelected(categoryId) {
                        return String(this.selectedCategoryId) === String(categoryId);
                    },

                    toggleCategory(categoryId) {
                        this.selectedCategoryId = this.isCategorySelected(categoryId)
                            ? null
                            : String(categoryId);

                        this.setFilters('filter', this.withoutCategory(this.filters.filter));
                    },

                    withoutCategory(filters) {
                        let { category_id, ...rest } = filters;

                        return rest;
                    },

                    isSpecSelected(code, optionId) {
                        return (this.filters.filter[code] ?? [])
                            .some(value => String(value) === String(optionId));
                    },

                    /**
                     * A chip is a shortcut to the tick box the panel below already draws for the
                     * same option, so it moves that box rather than keeping a second opinion:
                     * one source of truth, and the panel stays in step with what the chips show.
                     */
                    toggleSpec(code, optionId) {
                        let item = (this.$refs.filterPanel?.$refs?.filterItemComponent ?? [])
                            .find(filterItem => filterItem.filter?.code === code);

                        if (! item) {
                            return;
                        }

                        let values = (item.appliedValues ?? [])
                            .filter(value => String(value) !== String(optionId));

                        if (values.length === (item.appliedValues ?? []).length) {
                            values.push(optionId);
                        }

                        item.appliedValues = values;

                        item.applyValue();
                    },

                    applySort(value) {
                        this.appliedSort = value;
                    },

                    applyMode(value) {
                        this.appliedMode = value;
                    },

                    /**
                     * The page promises the whole catalogue, and the endpoint only ever hands
                     * back a page of it, so the rest is drawn in behind the first screenful
                     * rather than left behind a button.
                     */
                    /**
                     * The drawer is left open: it is where the catalogue is browsed as well as
                     * filtered, and a shopper stacking a category onto a grade should not have
                     * to reopen it between clicks. The grid updates behind it.
                     */
                    getProducts() {
                        this.isLoading = true;

                        this.$axios.get("{{ route('shop.api.products.index') }}", {
                            params: this.listingParams
                        })
                            .then(response => {
                                this.isLoading = false;

                                this.products = response.data.data;

                                this.total = response.data.meta?.total ?? this.products.length;

                                this.loadRemaining(response.data.links?.next);
                            }).catch(error => {
                                this.isLoading = false;

                                console.log(error);
                            });
                    },

                    loadRemaining(next) {
                        if (! next) {
                            this.loader = false;

                            return;
                        }

                        this.loader = true;

                        this.$axios.get(next)
                            .then(response => {
                                this.products = [...this.products, ...response.data.data];

                                this.loadRemaining(response.data.links?.next);
                            }).catch(error => {
                                this.loader = false;

                                console.log(error);
                            });
                    },

                    removeJsonEmptyValues(params) {
                        Object.keys(params).forEach(function (key) {
                            if ((! params[key] && params[key] !== undefined)) {
                                delete params[key];
                            }

                            if (Array.isArray(params[key])) {
                                params[key] = params[key].join(',');
                            }
                        });

                        return params;
                    },

                    jsonToQueryString(params) {
                        let parameters = new URLSearchParams();

                        for (const key in params) {
                            parameters.append(key, params[key]);
                        }

                        return parameters.toString();
                    }
                },
            });
        </script>
    @endPushOnce
</x-shop::layouts>

{!! view_render_event('bagisto.shop.categories.view.filters.before') !!}

<!-- Desktop Filters Navigation -->
<div v-if="! isMobile">
    <!-- Filters Vue Component -->
    <v-filters
        @filter-applied="setFilters('filter', $event)"
        @filter-clear="clearFilters('filter', $event)"
    >
        <!-- Category Filter Shimmer Effect -->
        <x-shop::shimmer.categories.filters />
    </v-filters>
</div>

<!-- Mobile Filters Navigation -->
<div
    class="fixed bottom-0 z-10 grid w-full max-w-full grid-cols-[1fr_auto_1fr] items-center justify-items-center border-t border-navyBorder bg-navySurface px-5 ltr:left-0 rtl:right-0"
    v-if="isMobile"
>
    <!-- Filter Drawer -->
    <x-shop::drawer
        position="left"
        width="100%"
        ::is-active="isDrawerActive.filter"
    >
        <!-- Drawer Toggler -->
        <x-slot:toggle>
            <div
                class="flex cursor-pointer items-center gap-x-2.5 px-2.5 py-3.5 text-base font-medium uppercase max-md:py-3"
                @click="isDrawerActive.filter = true"
            >
                <span class="icon-filter-1 text-2xl"></span>

                @lang('shop::app.categories.filters.filter')
            </div>
        </x-slot>

        <!-- Drawer Header -->
        <x-slot:header>
            <div class="flex items-center justify-between">
                <p class="text-lg font-semibold">
                    @lang('shop::app.categories.filters.filters')
                </p>

                <p
                    class="cursor-pointer text-sm font-medium ltr:mr-[50px] rtl:ml-[50px]"
                    @click="clearFilters('filter', '')"
                >
                    @lang('shop::app.categories.filters.clear-all')
                </p>
            </div>
        </x-slot>

        <!-- Drawer Content -->
        <x-slot:content>
            <!-- Filters Vue Component -->
            <v-filters
                @filter-applied="setFilters('filter', $event)"
                @filter-clear="clearFilters('filter', $event)"
            >
                <!-- Category Filter Shimmer Effect -->
                <x-shop::shimmer.categories.filters />
            </v-filters>
        </x-slot>
    </x-shop::drawer>

    <!-- Separator -->
    <span class="h-5 w-0.5 bg-navySurfaceHover"></span>

    <!-- Sort Drawer -->
    <x-shop::drawer
        position="bottom"
        width="100%"
        ::is-active="isDrawerActive.toolbar"
    >
        <!-- Drawer Toggler -->
        <x-slot:toggle>
            <div
                class="flex cursor-pointer items-center gap-x-2.5 px-2.5 py-3.5 text-base font-medium uppercase max-md:py-3"
                @click="isDrawerActive.toolbar = true"
            >
                <span class="icon-sort-1 text-2xl"></span>

                @lang('shop::app.categories.filters.sort')
            </div>
        </x-slot>

        <!-- Drawer Header -->
        <x-slot:header>
            <div class="flex items-center justify-between">
                <p class="text-lg font-semibold">
                    @lang('shop::app.categories.filters.sort')
                </p>
            </div>
        </x-slot>

        <!-- Drawer Content -->
        <x-slot:content class="!px-0">
            @include('shop::categories.toolbar')
        </x-slot>
    </x-shop::drawer>
</div>

{!! view_render_event('bagisto.shop.categories.view.filters.after') !!}

@include('shop::categories.filter-component')


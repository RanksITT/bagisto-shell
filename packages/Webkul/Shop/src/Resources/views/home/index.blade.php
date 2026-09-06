@php
    $channel = core()->getCurrentChannel();
@endphp

<!-- SEO Meta Content -->
@push ('meta')
    <meta
        name="title"
        content="{{ $channel->home_seo['meta_title'] ?? '' }}"
    />

    <meta
        name="description"
        content="{{ $channel->home_seo['meta_description'] ?? '' }}"
    />

    <meta
        name="keywords"
        content="{{ $channel->home_seo['meta_keywords'] ?? '' }}"
    />
@endPush

@push('scripts')
    @if(! empty($categories))
        <script>
            localStorage.setItem('categories', JSON.stringify(@json($categories)));
        </script>
    @endif
@endpush

<x-shop::layouts>
    <!-- Page Title -->
    <x-slot:title>
        {{  $channel->home_seo['meta_title'] ?? '' }}
    </x-slot>

    <!-- Loop over the storefront sections -->
    @foreach ($sections as $section)
        @php ($data = $section->options) @endphp

        {{-- Only the types this page renders; the layout marks the ones it draws. --}}
        @php ($marks = ($preview ?? false) && in_array($section->type, [
            $section::IMAGE_CAROUSEL,
            $section::STATIC_CONTENT,
            $section::CATEGORY_CAROUSEL,
            $section::PRODUCT_CAROUSEL,
        ]))

        @if ($marks)
            <div
                data-section-id="{{ $section->id }}"
                data-section-name="{{ $section->name }}"
            >
        @endif

        <!-- Static Content -->
        @switch ($section->type)
            @case ($section::IMAGE_CAROUSEL)
                <!-- Image Carousel -->
                <x-shop::carousel
                    :options="$data"
                    aria-label="{{ trans('shop::app.home.index.image-carousel') }}"
                />

                @break
            @case ($section::STATIC_CONTENT)
                <!-- Push Style -->
                @if (! empty($data['css']))
                    @push ('styles')
                        <style>
                            {!! $data['css'] !!}
                        </style>
                    @endpush
                @endif

                {{--
                    Admin authored markup is purified, which drops any `data-*`
                    hook it was written with. Wrapping it in a scrubbed element
                    publishes the section's scroll progress as a custom property
                    that the `rl-` classes inside can inherit and animate against.
                --}}
                @if (! empty($data['html']))
                    <div
                        class="static-chapter"
                        data-scrub="section"
                    >
                        {!! $data['html'] !!}
                    </div>
                @endif

                @break
            @case ($section::CATEGORY_CAROUSEL)
                <!-- Categories scroller -->
                <x-shop::categories.scroller
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.categories.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.home.index')"
                    aria-label="{{ trans('shop::app.home.index.categories-carousel') }}"
                />

                <!-- Engine Chapter, with the oil poured in and back out again -->
                <x-shop::story.pour />

                <x-shop::story.engine />

                <x-shop::story.pour />

                @break
            @case ($section::PRODUCT_CAROUSEL)
                <!-- Product Carousel -->
                <x-shop::products.carousel
                    :title="$data['title'] ?? ''"
                    :src="route('shop.api.products.index', $data['filters'] ?? [])"
                    :navigation-link="route('shop.search.index', $data['filters'] ?? [])"
                    aria-label="{{ trans('shop::app.home.index.product-carousel') }}"
                />

                {{--
                    The remaining chapters hang off the first and second product
                    carousels, so each sits between two blocks of catalogue rather
                    than stacking against one another.
                --}}
                @if (! ($storyGradeFinder ?? false))
                    @php ($storyGradeFinder = true)

                    <x-shop::story.viscosity />

                    <x-shop::story.protection />
                @elseif (! ($storyFigures ?? false))
                    @php ($storyFigures = true)

                    <x-shop::story.figures />
                @endif

                @break
        @endswitch

        @if ($marks)
            </div>
        @endif
    @endforeach

    @if ($preview ?? false)
        @include('shop::home.preview-bridge')
    @endif
</x-shop::layouts>

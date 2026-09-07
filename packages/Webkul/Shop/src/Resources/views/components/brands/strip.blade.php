@props([
    'title' => '',
    'brands' => [],
])

@php
    $brands = collect($brands)
        ->map(fn ($brand) => [
            'name' => $brand['name'] ?? '',
            'logo' => $brand['logo'] ?? '',
            'link' => $brand['link'] ?? '',
        ])
        ->filter(fn ($brand) => filled($brand['name']) || filled($brand['logo']));
@endphp

@if ($brands->isNotEmpty())
    <section
        class="container mt-20 max-lg:px-8 max-md:mt-10 max-sm:!px-4"
        data-scrub="section"
    >
        @if ($title)
            <h2
                class="font-dmserif text-4xl max-md:text-2xl max-sm:text-xl"
                data-reveal="up"
            >
                {{ $title }}
            </h2>
        @endif

        <div class="brand-strip">
            @foreach ($brands as $brand)
                {{--
                    An entry with nowhere to go is still an anchor, only without an href, so
                    the row keeps one shape whether or not the brand has been given a link.
                --}}
                <a
                    class="brand-strip__item"
                    data-reveal="up"
                    @if ($brand['link'])
                        href="{{ $brand['link'] }}"
                    @endif
                >
                    @if ($brand['logo'])
                        <span class="brand-strip__plate">
                            <img
                                class="brand-strip__logo"
                                src="{{ $brand['logo'] }}"
                                alt="{{ $brand['name'] }}"
                                loading="lazy"
                                decoding="async"
                            >
                        </span>
                    @endif

                    @if ($brand['name'])
                        <span class="brand-strip__name">{{ $brand['name'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
    </section>
@endif

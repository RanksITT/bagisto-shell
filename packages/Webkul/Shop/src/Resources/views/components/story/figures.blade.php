@inject('productRepository', 'Webkul\Product\Repositories\ProductRepository')
@inject('attributeRepository', 'Webkul\Attribute\Repositories\AttributeRepository')

@php
    $optionsFor = function ($code) use ($attributeRepository) {
        $attribute = $attributeRepository->findOneByField('code', $code);

        return $attribute ? $attribute->options->count() : 0;
    };

    $figures = [
        ['value' => $productRepository->count(), 'label' => trans('shop::app.components.story.figures.products')],
        ['value' => $optionsFor('oil_grade'), 'label' => trans('shop::app.components.story.figures.grades')],
        ['value' => $optionsFor('vehicle_type'), 'label' => trans('shop::app.components.story.figures.vehicles')],
    ];
@endphp

<section
    class="container mt-24 max-lg:px-8 max-md:mt-14 max-sm:!px-4"
    data-scrub="figures"
>
    <h2
        class="font-dmserif text-4xl text-offWhite max-md:text-3xl max-sm:text-2xl"
        data-reveal="up"
    >
        @lang('shop::app.components.story.figures.title')
    </h2>

    <div
        class="mt-10 grid grid-cols-3 gap-8 max-md:grid-cols-1 max-md:gap-6"
        data-reveal-group
    >
        @foreach ($figures as $figure)
            <div
                class="odo-card"
                data-reveal="up"
            >
                {{--
                    The reels are decoration: a screen reader is given the settled
                    figure in the sibling below, never a spinning one.
                --}}
                <div
                    class="odo"
                    dir="ltr"
                    aria-hidden="true"
                >
                    @foreach (str_split((string) $figure['value']) as $index => $digit)
                        <span class="odo__window">
                            <span
                                class="odo__reel"
                                style="--digit: {{ $digit }}; --reel-delay: {{ $index * 0.08 }}"
                            >
                                @for ($n = 0; $n <= 9; $n++)
                                    <span class="odo__digit">{{ $n }}</span>
                                @endfor
                            </span>
                        </span>
                    @endforeach
                </div>

                <p class="mt-3 text-base text-mutedBlue max-sm:text-sm">
                    {{ $figure['label'] }}
                </p>

                <p class="sr-only">{{ $figure['value'] }} {{ $figure['label'] }}</p>
            </div>
        @endforeach
    </div>
</section>

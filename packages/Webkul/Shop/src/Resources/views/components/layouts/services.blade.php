{!! view_render_event('bagisto.shop.layout.features.before') !!}

@inject('sectionRepository', 'Webkul\Theme\Repositories\SectionRepository')

@php
    $channel = core()->getCurrentChannel();

    $sections = $sectionRepository->findAllOfType(
        'services_content',
        $channel->id,
        $channel->theme,
        app()->getLocale()
    );
@endphp

<!-- Features -->
@foreach ($sections as $section)
    @continue (empty($section->options['services']))

    <div
        class="container mt-20 max-lg:px-8 max-md:mt-10 max-md:px-4"
        v-pre
        @if ($sectionRepository->isPreviewing())
            data-section-id="{{ $section->id }}"
            data-section-name="{{ $section->name }}"
        @endif
    >
        <div class="grid grid-cols-4 gap-5 max-lg:grid-cols-2 max-sm:gap-2.5">
            @foreach ($section->options['services'] as $service)
                <div class="group rounded-xl border border-navyBorder bg-navySurface p-6 transition-all duration-300 hover:-translate-y-1 hover:border-darkBlue max-lg:p-5 max-sm:p-4">
                    <span
                        class="{{ $service['service_icon'] }} mb-5 flex h-12 w-12 items-center justify-center rounded-lg bg-darkBlue/10 text-3xl text-darkBlue transition-colors duration-300 group-hover:bg-darkBlue group-hover:text-navyBlue max-sm:mb-3 max-sm:h-10 max-sm:w-10 max-sm:text-2xl"
                        role="presentation"
                    >
                    </span>

                    <!-- Service Title -->
                    <p class="font-dmserif text-lg font-medium leading-tight text-offWhite max-sm:text-base">
                        {{ $service['title'] }}
                    </p>

                    <!-- Service Description -->
                    <p class="mt-2 text-sm font-normal leading-relaxed text-mutedBlue max-sm:text-xs">
                        {{ $service['description'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

{!! view_render_event('bagisto.shop.layout.features.after') !!}

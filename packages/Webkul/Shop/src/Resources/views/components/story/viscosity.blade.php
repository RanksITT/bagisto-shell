@inject('attributeRepository', 'Webkul\Attribute\Repositories\AttributeRepository')

@php
    $attribute = $attributeRepository->findOneByField('code', 'oil_grade');

    $grades = $attribute ? $attribute->options : collect();
@endphp

@if ($grades->count())
    <section
        class="container mt-24 max-lg:px-8 max-md:mt-14 max-sm:!px-4"
        data-scrub="viscosity"
    >
        <div class="grid grid-cols-2 items-center gap-14 max-lg:grid-cols-1 max-lg:gap-8">
            <div>
                <h2
                    class="font-dmserif text-4xl text-offWhite max-md:text-3xl max-sm:text-2xl"
                    data-reveal="up"
                >
                    @lang('shop::app.components.story.viscosity.title')
                </h2>

                <p
                    class="mt-4 max-w-[48ch] text-lg text-mutedBlue max-sm:text-base"
                    data-reveal="up"
                >
                    @lang('shop::app.components.story.viscosity.text')
                </p>

                {{--
                    The dial face is decoration; the grades themselves are a real
                    list of links so the section is navigable by keyboard and
                    readable by a screen reader.
                --}}
                <ul
                    class="mt-8 flex flex-wrap gap-2.5"
                    data-reveal-group
                >
                    @foreach ($grades as $grade)
                        <li data-reveal="up">
                            <a
                                class="grade-chip"
                                href="{{ route('shop.search.index', ['oil_grade' => $grade->id]) }}"
                            >
                                {{ $grade->admin_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div
                class="visc"
                data-reveal="scale"
            >
                <svg
                    class="visc__svg"
                    viewBox="0 0 200 150"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                    focusable="false"
                >
                    <path
                        class="visc__track"
                        d="M24 124 A76 76 0 0 1 176 124"
                    />

                    <path
                        class="visc__fill"
                        pathLength="1"
                        d="M24 124 A76 76 0 0 1 176 124"
                    />

                    <g class="visc__needle">
                        <path d="M100 124 L100 58"/>

                        <circle
                            cx="100"
                            cy="124"
                            r="8"
                        />
                    </g>
                </svg>

                <div class="visc__scale">
                    <span>@lang('shop::app.components.story.viscosity.cold')</span>

                    <span>@lang('shop::app.components.story.viscosity.hot')</span>
                </div>
            </div>
        </div>
    </section>
@endif

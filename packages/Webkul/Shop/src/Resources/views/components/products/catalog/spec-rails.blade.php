@props(['specs'])

@if ($specs->isNotEmpty())
    <div class="border-b border-navyBorder pb-5">
        <p class="text-lg font-semibold max-sm:font-medium">
            @lang('shop::app.products.catalog.browse-by-spec')
        </p>

        <div class="mt-4 grid gap-4">
            @foreach ($specs as $spec)
                <div>
                    <p class="text-sm text-mutedBlue max-sm:text-xs">
                        {{ $spec['name'] }}
                    </p>

                    <div class="mt-2.5 flex flex-wrap gap-2">
                        @foreach ($spec['options'] as $option)
                            <button
                                class="flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs transition-colors duration-200"
                                type="button"
                                :class="isSpecSelected('{{ $spec['code'] }}', {{ $option['id'] }})
                                    ? 'border-darkBlue bg-darkBlue text-navyBlue'
                                    : 'border-navyBorder text-offWhite hover:border-darkBlue hover:bg-navySurfaceHover'"
                                :aria-pressed="isSpecSelected('{{ $spec['code'] }}', {{ $option['id'] }})"
                                @click="toggleSpec('{{ $spec['code'] }}', {{ $option['id'] }})"
                            >
                                {{ $option['label'] }}

                                <span :class="isSpecSelected('{{ $spec['code'] }}', {{ $option['id'] }}) ? 'text-navyBlue/70' : 'text-mutedBlue'">
                                    {{ $option['total'] }}
                                </span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

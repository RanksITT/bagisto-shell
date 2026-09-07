@props(['categories'])

@if ($categories->isNotEmpty())
    <div class="border-b border-navyBorder pb-5">
        <p class="text-lg font-semibold max-sm:font-medium">
            @lang('shop::app.products.catalog.browse-by-category')
        </p>

        <div class="mt-4 grid grid-cols-2 gap-2.5 max-sm:gap-2">
            @foreach ($categories as $category)
                <button
                    class="flex items-center gap-2.5 rounded-lg border p-2 text-start transition-colors duration-200"
                    type="button"
                    :class="isCategorySelected({{ $category->id }})
                        ? 'border-darkBlue bg-navySurfaceHover'
                        : 'border-navyBorder hover:border-darkBlue hover:bg-navySurfaceHover'"
                    :aria-pressed="isCategorySelected({{ $category->id }})"
                    @click="toggleCategory({{ $category->id }})"
                >
                    @if ($category->logo_url)
                        <x-shop::media.images.lazy
                            class="h-10 w-10 flex-shrink-0 rounded bg-photoBackdrop object-contain"
                            src="{{ $category->logo_url }}"
                            alt="{{ $category->logo_alt ?: $category->name }}"
                            width="40"
                            height="40"
                        />
                    @else
                        <span
                            class="icon-category flex h-10 w-10 flex-shrink-0 items-center justify-center rounded bg-navySurfaceHover text-xl text-mutedBlue"
                            role="presentation"
                        ></span>
                    @endif

                    <span
                        class="text-xs leading-tight"
                        :class="isCategorySelected({{ $category->id }}) ? 'text-darkBlue' : 'text-offWhite'"
                    >
                        {{ $category->name }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>
@endif

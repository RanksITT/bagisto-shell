@if ($paginator->hasPages())
    <div class="flex items-center justify-between p-6">
        <p class="text-xs font-medium">
            @lang('shop::app.partials.pagination.pagination-showing', [
                'firstItem' => $paginator->firstItem(),
                'lastItem' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ])
        </p>

        <nav aria-label="Page Navigation">
            <ul class="inline-flex items-center -space-x-px">
                <!-- Previous Page Link -->
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="icon-arrow-left rtl:icon-arrow-right flex h-[37px] w-[35px] items-center justify-center border border-navyBorder text-2xl font-medium leading-normal ltr:rounded-l-lg rtl:rounded-r-lg"></span>
                    @else
                        <a 
                            href="{{ urldecode($paginator->previousPageUrl()) }}" 
                            class="flex h-[37px] w-[35px] items-center justify-center border border-navyBorder font-medium leading-normal hover:bg-navySurfaceHover ltr:rounded-l-lg rtl:rounded-r-lg" 
                            aria-label="{{ trans('shop::app.partials.pagination.prev-page') }}"
                        >
                            <span class="icon-arrow-left rtl:icon-arrow-right text-2xl"></span>
                        </a>
                    @endif
                </li>
    
                <!-- Pagination Elements -->
                @foreach ($elements as $element) 
                    @if (is_string($element)) 
                        <li>
                            <span 
                                class="disabled flex h-[37px] w-[35px] items-center justify-center border border-navyBorder text-center font-medium leading-normal text-offWhite"
                            >
                                {{ $element }}
                            </span>
                        </li>
                    @endif 

                    @if (is_array($element)) 
                        @foreach ($element as $page => $url) 
                            <li>
                                <a 
                                    href="{{ $paginator->currentPage() ? $url : '#' }}"
                                    class="flex h-[37px] w-[35px] items-center justify-center border border-navyBorder text-center font-medium leading-normal text-offWhite hover:bg-navySurfaceHover {{ $paginator->currentPage() ? 'bg-navySurface' : '' }}"
                                >
                                    {{ $page }}
                                </a>
                            </li>
                        @endforeach 
                    @endif 
                @endforeach

                <!-- Next Page Link -->
                <li>
                    @if ($paginator->hasMorePages())
                        <a 
                            href="{{ urldecode($paginator->nextPageUrl()) }}" 
                            class="flex h-[37px] w-[35px] items-center justify-center border border-navyBorder font-medium leading-normal hover:bg-navySurfaceHover ltr:rounded-r-lg rtl:rounded-l-lg" 
                            aria-label="{{ trans('shop::app.partials.pagination.next-page') }}"
                        >
                            <span class="icon-arrow-right rtl:icon-arrow-left text-2xl"></span>
                        </a>
                    @else
                        <span class="icon-arrow-right rtl:icon-arrow-left flex h-[37px] w-[35px] items-center justify-center border border-navyBorder text-2xl font-medium leading-normal ltr:rounded-r-lg rtl:rounded-l-lg"></span>
                    @endif
                </li>
            </ul>
        </nav>
    </div>
@endif

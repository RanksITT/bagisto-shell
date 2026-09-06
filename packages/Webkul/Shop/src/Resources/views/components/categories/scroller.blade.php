<v-categories-scroller
    src="{{ $src }}"
    title="{{ $title }}"
    navigation-link="{{ $navigationLink ?? '' }}"
>
    <x-shop::shimmer.categories.carousel
        :count="8"
        :navigation-link="$navigationLink ?? false"
    />
</v-categories-scroller>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-categories-scroller-template"
    >
        <section
            ref="stage"
            class="relative mt-20 max-md:mt-10"
            v-if="! isLoading && categories?.length"
        >
            <div :class="isPinned ? 'sticky top-0 flex h-screen flex-col justify-center overflow-hidden' : ''">
                <div class="container max-lg:px-8 max-sm:!px-4">
                    <div class="flex items-end justify-between gap-6">
                        <h2
                            class="font-dmserif text-4xl max-md:text-2xl max-sm:text-xl"
                            data-reveal="up"
                            v-text="title"
                        >
                        </h2>

                        <p
                            class="text-sm text-mutedBlue max-md:hidden"
                            data-reveal="up"
                            v-if="isPinned"
                        >
                            @lang('shop::app.components.categories.scroller.scroll-hint')
                        </p>
                    </div>

                    <div
                        class="mt-6 h-px w-full bg-navyBorder max-md:hidden"
                        v-if="isPinned"
                    >
                        <div
                            class="h-px bg-darkBlue"
                            :style="{ width: (progress * 100) + '%' }"
                        >
                        </div>
                    </div>
                </div>

                <div
                    :class="[
                        'mt-10 max-md:mt-6',
                        isPinned
                            ? 'overflow-hidden'
                            : 'range-scroller-swipe scrollbar-hide overflow-x-auto scroll-smooth',
                    ]"
                >
                    <div
                        ref="track"
                        :class="[
                            'flex w-max gap-8 px-[90px] max-lg:px-8 max-sm:gap-4 max-sm:px-4',
                            isPinned
                                ? 'range-scroller-track'
                                : 'lg:grid lg:w-auto lg:grid-cols-3',
                        ]"
                        :style="isPinned ? { transform: 'translate3d(-' + offset + 'px, 0, 0)' } : null"
                        data-reveal-group
                    >
                        <a
                            class="group grid w-[400px] shrink-0 content-start overflow-hidden rounded-2xl border border-navyBorder bg-navySurface transition-colors duration-300 hover:border-darkBlue max-lg:w-[320px] max-md:w-[240px] max-sm:w-[200px]"
                            :class="isPinned ? '' : 'lg:w-auto'"
                            :href="category.slug"
                            :aria-label="category.name"
                            data-reveal="up"
                            v-for="(category, index) in categories"
                        >
                            <div class="relative overflow-hidden bg-photoBackdrop">
                                <x-shop::media.images.lazy
                                    class="w-full transition-transform duration-500 group-hover:scale-105"
                                    ::src="category.logo?.medium_image_url || fallback"
                                    ::srcset="`
                                        ${(category.logo?.small_image_url || fallback)} 150w,
                                        ${(category.logo?.medium_image_url || fallback)} 320w,
                                        ${(category.logo?.large_image_url || fallback)} 640w
                                    `"
                                    sizes="(max-width: 640px) 200px, 320px"
                                    width="320"
                                    height="320"
                                    ::alt="category.name"
                                />

                                <span
                                    class="absolute top-4 font-dmserif text-lg text-darkBlue ltr:left-4 rtl:right-4"
                                    v-text="String(index + 1).padStart(2, '0')"
                                >
                                </span>
                            </div>

                            <div class="grid gap-2 p-6 max-sm:p-4">
                                <p
                                    class="font-dmserif text-2xl text-offWhite max-sm:text-lg"
                                    v-text="category.name"
                                >
                                </p>

                                <p class="flex items-center gap-1.5 text-sm text-mutedBlue transition-colors duration-300 group-hover:text-darkBlue">
                                    @lang('shop::app.components.categories.scroller.view-range')

                                    <span class="icon-arrow-right rtl:icon-arrow-left text-lg transition-transform duration-300 ltr:group-hover:translate-x-1 rtl:group-hover:-translate-x-1"></span>
                                </p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Scroller Shimmer -->
        <template v-if="isLoading">
            <x-shop::shimmer.categories.carousel
                :count="8"
                :navigation-link="$navigationLink ?? false"
            />
        </template>
    </script>

    <script type="module">
        app.component('v-categories-scroller', {
            template: '#v-categories-scroller-template',

            props: [
                'src',
                'title',
                'navigationLink',
            ],

            data() {
                return {
                    isLoading: true,

                    isPinned: false,

                    categories: [],

                    distance: 0,

                    offset: 0,

                    progress: 0,

                    frame: null,

                    fallback: "{{ bagisto_asset('images/medium-product-placeholder.webp') }}",
                };
            },

            mounted() {
                this.getCategories();
            },

            unmounted() {
                this.teardown();
            },

            methods: {
                getCategories() {
                    this.$axios.get(this.src)
                        .then((response) => {
                            this.isLoading = false;

                            this.categories = response.data.data;

                            this.$nextTick(this.setup);
                        }).catch((error) => {
                            this.isLoading = false;

                            console.log(error);
                        });
                },

                /**
                 * Pinning is a desktop only enhancement, skipped whenever the visitor
                 * asked for reduced motion. A track that only just overflows is also
                 * left alone, since holding the page still for a few dozen pixels of
                 * travel reads as a stutter rather than as a deliberate chapter.
                 */
                canPin() {
                    return window.matchMedia('(min-width: 1024px)').matches
                        && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                },

                /**
                 * Ride the shared scroll engine where it is running, so the page keeps
                 * a single scroll loop, and fall back to a private listener when motion
                 * is reduced and the engine never started.
                 */
                setup() {
                    if (window.scrollEngine) {
                        window.scrollEngine.subscribe(this.update);
                    } else {
                        window.addEventListener('scroll', this.onScroll, { passive: true });
                    }

                    window.addEventListener('resize', this.measure, { passive: true });

                    this.measure();
                },

                teardown() {
                    if (window.scrollEngine) {
                        window.scrollEngine.unsubscribe(this.update);
                    }

                    window.removeEventListener('scroll', this.onScroll);

                    window.removeEventListener('resize', this.measure);

                    if (this.frame) {
                        window.cancelAnimationFrame(this.frame);
                    }
                },

                measure() {
                    const stage = this.$refs.stage;

                    const track = this.$refs.track;

                    if (! stage || ! track) {
                        return;
                    }

                    const distance = track.scrollWidth - window.innerWidth;

                    if (! this.canPin() || distance < window.innerWidth * 0.25) {
                        this.isPinned = false;

                        this.distance = 0;

                        this.offset = 0;

                        this.progress = 0;

                        stage.style.height = '';

                        return;
                    }

                    this.isPinned = true;

                    this.distance = distance;

                    stage.style.height = `${distance + window.innerHeight}px`;

                    this.update();
                },

                onScroll() {
                    if (! this.isPinned || this.frame) {
                        return;
                    }

                    this.frame = window.requestAnimationFrame(() => {
                        this.frame = null;

                        this.update();
                    });
                },

                update() {
                    const stage = this.$refs.stage;

                    if (! stage || ! this.isPinned) {
                        return;
                    }

                    const travel = stage.offsetHeight - window.innerHeight;

                    if (travel <= 0) {
                        return;
                    }

                    const progress = Math.min(Math.max(-stage.getBoundingClientRect().top / travel, 0), 1);

                    this.progress = progress;

                    this.offset = progress * this.distance;
                },
            },
        });
    </script>
@endPushOnce

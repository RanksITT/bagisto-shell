@props(['options'])

@php
    $carouselImages = $options['images'] ?? [];

    $firstImage = data_get($carouselImages, '0.image');

    $firstImageTitle = data_get($carouselImages, '0.title');
@endphp

@if ($firstImage)
    {{--
        Preload the LCP image in <head> so the browser starts fetching it
        before HTML parse reaches the <img> tag. Directly targets the LCP
        "resource load delay" subpart Lighthouse reports as the biggest
        contributor on this page.
    --}}
    @push('meta')
        <link
            rel="preload"
            as="image"
            href="{{ str_replace('storage', 'cache/small', $firstImage) }}"
            imagesrcset="{{ $firstImage }} 1920w, {{ str_replace('storage', 'cache/large', $firstImage) }} 1280w, {{ str_replace('storage', 'cache/medium', $firstImage) }} 1024w, {{ str_replace('storage', 'cache/small', $firstImage) }} 768w"
            imagesizes="100vw"
            fetchpriority="high"
        >
    @endpush
@endif

<v-carousel :images="{{ json_encode($carouselImages) }}">
    <div class="relative overflow-hidden">
        @if ($firstImage)
            {{--
                Server-rendered first slide so the browser can discover and
                fetch the LCP image immediately, before Vue mounts the
                carousel. `sizes="100vw"` declares the actual rendered width
                (the img has `w-screen`) so the browser picks the smallest
                srcset variant that satisfies viewport_px × DPR — mobile
                412 × 1.75 ≈ 721 → 768w small variant. The inline `style`
                supplies width/aspect-ratio so the LCP element can paint
                before the Tailwind CSS bundle finishes parsing on slow
                mobile CPU.
            --}}
            <img
                src="{{ $firstImage }}"
                srcset="{{ $firstImage }} 1920w, {{ str_replace('storage', 'cache/large', $firstImage) }} 1280w, {{ str_replace('storage', 'cache/medium', $firstImage) }} 1024w, {{ str_replace('storage', 'cache/small', $firstImage) }} 768w"
                sizes="100vw"
                class="aspect-[2.2/1] max-h-screen w-screen select-none object-cover"
                style="width:100vw;aspect-ratio:2.2/1;max-height:100vh;object-fit:cover;display:block"
                alt="{{ $firstImageTitle ?: trans('shop::app.home.index.image-carousel') }}"
                fetchpriority="high"
                decoding="sync"
            >

            @if (! $firstImageTitle)
                <div class="hero-vignette"></div>
            @endif
        @else
            <div class="shimmer aspect-[2.2/1] max-h-screen w-screen"></div>
        @endif
    </div>
</v-carousel>

@pushOnce('scripts')
    <script
        type="text/x-template"
        id="v-carousel-template"
    >
        <div class="relative m-auto flex w-full overflow-hidden">
            <!-- Slider -->
            <div
                class="inline-flex translate-x-0 cursor-pointer transition-transform duration-700 ease-out will-change-transform"
                ref="sliderContainer"
            >
                <div
                    class="relative max-h-screen w-screen bg-cover bg-no-repeat"
                    v-for="(image, index) in images"
                    :key="index"
                    @click="visitLink(image)"
                    data-scrub="hero"
                    ref="slide"
                >
                    <div class="hero-parallax">
                        <x-shop::media.images.lazy
                            class="aspect-[2.2/1] max-h-full w-full max-w-full select-none transition-transform duration-300 ease-in-out will-change-transform"
                            ::lazy="index === 0 ? false : true"
                            ::src="image.image"
                            ::srcset="image.image + ' 1920w, ' + image.image.replace('storage', 'cache/large') + ' 1280w,' + image.image.replace('storage', 'cache/medium') + ' 1024w, ' + image.image.replace('storage', 'cache/small') + ' 768w'"
                            sizes="100vw"
                            ::alt="image?.title || 'Carousel Image ' + (index + 1)"
                            tabindex="0"
                            ::fetchpriority="index === 0 ? 'high' : 'low'"
                            ::decoding="index === 0 ? 'sync' : 'async'"
                        />
                    </div>

                    <!-- Slide Headline -->
                    <div
                        class="hero-overlay"
                        v-if="image.title"
                    >
                        <div class="container max-lg:px-8 max-sm:!px-4">
                            <h2
                                class="hero-overlay__title font-dmserif"
                                data-reveal="clip"
                                v-text="image.title"
                            >
                            </h2>

                            <span
                                class="hero-overlay__rule"
                                data-reveal="scale"
                                role="presentation"
                            >
                            </span>

                            <a
                                class="primary-button hero-overlay__cta"
                                :href="image.link"
                                data-reveal="up"
                                @click.stop
                                v-if="image.link"
                            >
                                @lang('shop::app.components.carousel.explore-range')
                            </a>
                        </div>
                    </div>

                    {{--
                        A banner that carries its own artwork gets no scrim over it, only
                        the vignette, so the edges settle against the page instead of
                        stopping at a hard line.
                    --}}
                    <div
                        class="hero-vignette"
                        v-else
                    >
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <button
                class="hero-nav icon-arrow-left left-6"
                type="button"
                aria-label="@lang('shop::app.components.carousel.previous')"
                v-if="images?.length >= 2"
                @click="navigate('prev')"
            >
            </button>

            <button
                class="hero-nav icon-arrow-right right-6"
                type="button"
                aria-label="@lang('shop::app.components.carousel.next')"
                v-if="images?.length >= 2"
                @click="navigate('next')"
            >
            </button>

            <!-- Pagination -->
            <div
                class="hero-progress"
                v-if="images?.length >= 2"
            >
                <div class="container max-lg:px-8 max-sm:!px-4">
                    <div class="hero-progress__inner">
                        <p class="hero-progress__count">
                            <span v-text="padded(activeIndex + 1)"></span>

                            <i>/</i>

                            <span
                                class="hero-progress__total"
                                v-text="padded(images.length)"
                            >
                            </span>
                        </p>

                        <div class="hero-progress__rail">
                            <span
                                class="hero-progress__bar"
                                :style="barStyle"
                            >
                            </span>

                            <div class="hero-progress__segs">
                                <button
                                    class="hero-progress__seg"
                                    type="button"
                                    v-for="(image, index) in images"
                                    :key="index"
                                    :aria-label="slideLabel(index)"
                                    :aria-current="index === activeIndex"
                                    @click="navigateByPagination(index)"
                                >
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </script>

    <script type="module">
        app.component("v-carousel", {
            template: '#v-carousel-template',

            props: ['images'],

            data() {
                return {
                    isDragging: false,
                    startPos: 0,
                    currentTranslate: 0,
                    prevTranslate: 0,
                    animationID: 0,
                    currentIndex: 0,
                    slider: '',
                    slides: [],
                    autoPlayInterval: null,
                    direction: 'ltr',
                    startFrom: 1,
                };
            },

            computed: {
                activeIndex() {
                    return Math.abs(this.currentIndex);
                },

                barStyle() {
                    const span = 100 / (this.images?.length || 1);

                    return {
                        width: span + '%',
                        transform: `translateX(${this.direction == 'rtl' ? -this.activeIndex * 100 : this.activeIndex * 100}%)`,
                    };
                },
            },

            mounted() {
                this.slider = this.$refs.sliderContainer;

                if (
                    this.$refs.slide
                    && typeof this.$refs.slide[Symbol.iterator] === 'function'
                ) {
                    this.slides = Array.from(this.$refs.slide);
                }

                // Use requestIdleCallback for non-critical initialization
                if ('requestIdleCallback' in window) {
                    requestIdleCallback(() => {
                        this.init();
                        setTimeout(() => {
                            this.play();
                        }, 4000);
                    });
                } else {
                    setTimeout(() => {
                        this.init();
                        setTimeout(() => {
                            this.play();
                        }, 4000);
                    });
                }
            },

            beforeUnmount() {
                this.cleanup();
            },

            methods: {
                init() {
                    this.direction = document.dir;

                    if (this.direction == 'rtl') {
                        this.startFrom = -1;
                    }

                    this.slides.forEach((slide, index) => {
                        slide.querySelector('img')?.addEventListener('dragstart', (e) => e.preventDefault());

                        slide.addEventListener('mousedown', this.handleDragStart);

                        slide.addEventListener('touchstart', this.handleDragStart, { passive: true });

                        slide.addEventListener('mouseup', this.handleDragEnd);

                        slide.addEventListener('mouseleave', this.handleDragEnd);

                        slide.addEventListener('touchend', this.handleDragEnd, { passive: true });

                        slide.addEventListener('mousemove', this.handleDrag);

                        slide.addEventListener('touchmove', this.handleDrag, { passive: true });
                    });

                    window.addEventListener('resize', this.setPositionByIndex);
                },

                handleDragStart(event) {
                    this.startPos = event.type === 'mousedown' ? event.clientX : event.touches[0].clientX;

                    this.isDragging = true;

                    this.animationID = requestAnimationFrame(this.animation);
                },

                handleDrag(event) {
                    if (! this.isDragging) {
                        return;
                    }

                    const currentPosition = event.type === 'mousemove' ? event.clientX : event.touches[0].clientX;

                    this.currentTranslate = this.prevTranslate + currentPosition - this.startPos;
                },

                handleDragEnd(event) {
                    clearInterval(this.autoPlayInterval);

                    cancelAnimationFrame(this.animationID);

                    this.isDragging = false;

                    const movedBy = this.currentTranslate - this.prevTranslate;

                    if (this.direction == 'ltr') {
                        if (
                            movedBy < -100
                            && this.currentIndex < this.slides.length - 1
                        ) {
                            this.currentIndex += 1;
                        }

                        if (
                            movedBy > 100
                            && this.currentIndex > 0
                        ) {
                            this.currentIndex -= 1;
                        }
                    } else {
                        if (
                            movedBy > 100
                            && this.currentIndex < this.slides.length - 1
                        ) {
                            if (Math.abs(this.currentIndex) != this.slides.length - 1) {
                                this.currentIndex -= 1;
                            }
                        }

                        if (
                            movedBy < -100
                            && this.currentIndex < 0
                        ) {
                            this.currentIndex += 1;
                        }
                    }

                    this.setPositionByIndex();

                    this.play();
                },

                animation() {
                    this.setSliderPosition();

                    if (this.isDragging) {
                        requestAnimationFrame(this.animation);
                    }
                },

                setPositionByIndex() {
                    this.currentTranslate = this.currentIndex * -window.innerWidth;

                    this.prevTranslate = this.currentTranslate;

                    this.setSliderPosition();
                },

                setSliderPosition() {
                    if (this.slider) {
                        this.slider.style.transform = `translateX(${this.currentTranslate}px)`;
                    }
                },

                padded(number) {
                    return String(number).padStart(2, '0');
                },

                slideLabel(index) {
                    return @json(trans('shop::app.components.carousel.go-to-slide')).replace(':number', index + 1);
                },

                visitLink(image) {
                    if (image.link) {
                        window.location.href = image.link;
                    }
                },

                navigate(type) {
                    clearInterval(this.autoPlayInterval);

                    if (this.direction === 'rtl') {
                        type === 'next' ? this.prev() : this.next();
                    } else {
                        type === 'next' ? this.next() : this.prev();
                    }

                    this.setPositionByIndex();

                    this.play();
                },

                next() {
                    this.currentIndex = (this.currentIndex + this.startFrom) % this.images.length;
                },

                prev() {
                    this.currentIndex = this.direction == 'ltr'
                        ? this.currentIndex > 0 ? this.currentIndex - 1 : 0
                        : this.currentIndex < 0 ? this.currentIndex + 1 : 0;
                },

                navigateByPagination(index) {
                    this.direction == 'rtl' ? index = -index : '';

                    clearInterval(this.autoPlayInterval);

                    this.currentIndex = index;

                    this.setPositionByIndex();

                    this.play();
                },

                play() {
                    clearInterval(this.autoPlayInterval);

                    this.autoPlayInterval = setInterval(() => {
                        this.currentIndex = (this.currentIndex + this.startFrom) % this.images.length;

                        this.setPositionByIndex();
                    }, 5000);
                },

                cleanup() {
                    // Clear intervals and animation frames
                    clearInterval(this.autoPlayInterval);
                    cancelAnimationFrame(this.animationID);

                    // Remove event listeners
                    if (this.slides) {
                        this.slides.forEach(slide => {
                            slide.removeEventListener('mousedown', this.handleDragStart);
                            slide.removeEventListener('touchstart', this.handleDragStart);
                            slide.removeEventListener('mouseup', this.handleDragEnd);
                            slide.removeEventListener('mouseleave', this.handleDragEnd);
                            slide.removeEventListener('touchend', this.handleDragEnd);
                            slide.removeEventListener('mousemove', this.handleDrag);
                            slide.removeEventListener('touchmove', this.handleDrag);
                        });
                    }

                    window.removeEventListener('resize', this.setPositionByIndex);
                },
            },
        });
    </script>
@endpushOnce

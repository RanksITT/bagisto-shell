<section
    class="container mt-24 max-lg:px-8 max-md:mt-14 max-sm:!px-4"
    data-scrub="protection"
>
    <h2
        class="font-dmserif text-4xl text-offWhite max-md:text-3xl max-sm:text-2xl"
        data-reveal="up"
    >
        @lang('shop::app.components.story.protection.title')
    </h2>

    <p
        class="mt-4 max-w-[52ch] text-lg text-mutedBlue max-sm:text-base"
        data-reveal="up"
    >
        @lang('shop::app.components.story.protection.text')
    </p>

    {{--
        Two drawings of the same bearing surface stacked on top of each other. The
        protected one is clipped back as the section crosses the viewport, so the
        wipe reveals the worn metal underneath it. Clipping the wrapping element
        rather than the SVG keeps the inset resolving against a predictable box.
    --}}
    <div
        class="wipe mt-10"
        data-reveal="scale"
    >
        <div class="wipe__layer">
            <svg
                class="wipe__svg"
                viewBox="0 0 800 260"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
                focusable="false"
            >
                <rect
                    x="0"
                    y="60"
                    width="800"
                    height="140"
                    fill="#242427"
                />

                <path
                    class="wipe__scar"
                    d="M40 96 H760 M40 118 H520 M180 140 H760 M40 162 H660 M300 184 H760"
                />

                <circle class="wipe__pit" cx="210" cy="128" r="7"/>

                <circle class="wipe__pit" cx="430" cy="172" r="5"/>

                <circle class="wipe__pit" cx="596" cy="110" r="9"/>

                <circle class="wipe__pit" cx="694" cy="156" r="6"/>
            </svg>
        </div>

        <div class="wipe__layer wipe__layer--protected">
            <svg
                class="wipe__svg"
                viewBox="0 0 800 260"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
                focusable="false"
            >
                <rect
                    x="0"
                    y="60"
                    width="800"
                    height="140"
                    fill="#3A342B"
                />

                <rect
                    class="wipe__film"
                    x="0"
                    y="60"
                    width="800"
                    height="18"
                />

                <path
                    class="wipe__sheen"
                    d="M0 92 H800 M0 168 H800"
                />
            </svg>
        </div>

        <span class="wipe__edge"></span>
    </div>

    <div
        class="mt-5 flex justify-between text-sm uppercase tracking-[.18em]"
        data-reveal="up"
    >
        <span class="wipe__label wipe__label--worn">
            @lang('shop::app.components.story.protection.worn')
        </span>

        <span class="wipe__label wipe__label--kept">
            @lang('shop::app.components.story.protection.kept')
        </span>
    </div>
</section>

<section
    class="pin-stage engine-stage"
    data-scrub="engine"
    data-engine-scene
>
    <div class="pin-stage__inner">
        <div class="container grid grid-cols-2 items-center gap-16 max-lg:grid-cols-1 max-lg:gap-8 max-lg:px-8 max-sm:!px-4">
            <div class="engine-copy">
                <p
                    class="text-sm uppercase tracking-[.2em] text-darkBlue"
                    data-reveal="up"
                >
                    @lang('shop::app.components.story.engine.eyebrow')
                </p>

                <h2
                    class="mt-4 font-dmserif text-5xl leading-tight text-offWhite max-lg:text-4xl max-sm:text-3xl"
                    data-reveal="up"
                >
                    @lang('shop::app.components.story.engine.title')
                </h2>

                <p
                    class="mt-5 max-w-[46ch] text-lg text-mutedBlue max-sm:text-base"
                    data-reveal="up"
                >
                    @lang('shop::app.components.story.engine.text')
                </p>

                <ol class="engine-steps mt-8 grid gap-3" data-reveal-group>
                    <li class="engine-step" data-reveal="up">
                        <span class="engine-step__dot"></span>
                        @lang('shop::app.components.story.engine.step-sump')
                    </li>

                    <li class="engine-step" data-reveal="up">
                        <span class="engine-step__dot"></span>
                        @lang('shop::app.components.story.engine.step-gallery')
                    </li>

                    <li class="engine-step" data-reveal="up">
                        <span class="engine-step__dot"></span>
                        @lang('shop::app.components.story.engine.step-ring')
                    </li>
                </ol>
            </div>

            <div class="engine-figure" data-reveal="scale">
                <svg
                    class="engine-svg"
                    viewBox="0 0 360 470"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    aria-hidden="true"
                    focusable="false"
                >
                    <defs>
                        <linearGradient id="rlmEngineOil" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#F0B85C"/>
                            <stop offset="1" stop-color="#C67F1E"/>
                        </linearGradient>

                        <clipPath id="rlmEngineOilClip">
                            <rect class="engine-oil-clip" x="0" y="0" width="360" height="470"/>
                        </clipPath>
                    </defs>

                    <g clip-path="url(#rlmEngineOilClip)">
                        <path
                            class="engine-oil"
                            d="M84 296 H276 L250 438 A10 10 0 0 1 240 446 H120 A10 10 0 0 1 110 438 Z"
                        />

                        <rect class="engine-oil" x="91" y="96" width="10" height="204" rx="5"/>

                        <rect class="engine-oil" x="259" y="96" width="10" height="204" rx="5"/>

                        <rect class="engine-oil" x="91" y="88" width="178" height="10" rx="5"/>

                        <rect class="engine-oil engine-oil--film" x="112" y="70" width="6" height="196"/>

                        <rect class="engine-oil engine-oil--film" x="242" y="70" width="6" height="196"/>

                        <circle class="engine-bubble engine-bubble--a" cx="150" cy="410" r="7"/>

                        <circle class="engine-bubble engine-bubble--b" cx="196" cy="410" r="5"/>

                        <circle class="engine-bubble engine-bubble--c" cx="228" cy="410" r="6"/>
                    </g>

                    <g class="engine-lines">
                        <rect x="96" y="26" width="168" height="44" rx="8"/>

                        <path d="M104 70 V296"/>

                        <path d="M256 70 V296"/>

                        <path d="M84 296 H276 L250 438 A10 10 0 0 1 240 446 H120 A10 10 0 0 1 110 438 Z"/>

                        <path d="M118 70 V266"/>

                        <path d="M242 70 V266"/>
                    </g>

                    <rect
                        class="engine-flash"
                        x="120"
                        y="46"
                        width="120"
                        height="34"
                        rx="6"
                    />

                    <g class="engine-piston">
                        <rect x="120" y="80" width="120" height="62" rx="7"/>

                        <path d="M126 96 H234"/>

                        <path d="M126 108 H234"/>

                        <path d="M126 120 H234"/>

                        <circle cx="180" cy="132" r="9"/>
                    </g>

                    {{--
                        The rod is a sibling of the piston, not a child, so it can
                        carry its own rotation about the wrist pin and keep its foot
                        on the crank pin through the whole stroke.
                    --}}
                    <g class="engine-rod">
                        <path d="M173 132 L168 300 H192 L187 132 Z"/>
                    </g>

                    <g class="engine-crank">
                        <circle cx="180" cy="342" r="62"/>

                        <circle class="engine-crank__pin" cx="180" cy="294" r="11"/>
                    </g>
                </svg>
            </div>
        </div>
    </div>
</section>

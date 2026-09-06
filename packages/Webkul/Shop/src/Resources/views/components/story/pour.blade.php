{{--
    A pour sits in the vertical gap between two chapters and draws itself as that
    gap crosses the viewport, so the page reads as one continuous stream of oil
    handed from one section to the next. It is purely decorative, occupies its own
    empty block in normal flow, and therefore never overlaps or blocks anything.
--}}
<div
    class="pour"
    data-scrub="pour"
    role="presentation"
    aria-hidden="true"
>
    <svg
        class="pour__svg"
        viewBox="0 0 120 240"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        focusable="false"
    >
        <ellipse
            class="pour__lip"
            cx="60"
            cy="4"
            rx="9"
            ry="3"
        />

        <path
            class="pour__stream"
            pathLength="1"
            d="M60 2 C60 48 44 64 44 94 C44 126 76 132 76 164 C76 198 60 208 60 238"
        />

        <ellipse
            class="pour__pool"
            cx="60"
            cy="236"
            rx="16"
            ry="4"
        />
    </svg>
</div>

/**
 * Shared scroll engine.
 *
 * Every scroll linked effect on the storefront runs from this one loop. A single
 * passive `scroll` listener drives a single animation frame, and each frame reads
 * all of its geometry before it writes any style, so a page carrying several
 * pinned or scrubbed sections still costs one layout pass rather than one per
 * effect.
 *
 * Elements opt in declaratively with `data-scrub`, which publishes their progress
 * through the viewport as a `--scrub` custom property between 0 and 1. What that
 * progress means is left to CSS. Components that need the raw number instead
 * subscribe with `subscribe()`.
 *
 * The engine stays asleep while the visitor has asked for reduced motion.
 */

import { afterMount } from './scroll-reveal';

const SCRUB_SELECTOR = '[data-scrub]';

/**
 * Elements currently holding a promoted compositor layer.
 *
 * Held off the DOM for the same reason the reveal primitive holds its bind state
 * off the DOM: the root application turns the markup inside `#app` into its own
 * template, and anything written there before the mount is captured into it.
 */
const promoted = new WeakSet();

const subscribers = new Set();

let scrubbed = [];

let frame = null;

let refreshTimer = null;

let progressBar = null;

let lastScrollY = 0;

let started = false;

/**
 * Whether the visitor has asked the interface to hold still.
 */
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Clamp to the 0 to 1 range the custom properties are defined over.
 */
function clamp(value) {
    return Math.min(Math.max(value, 0), 1);
}

/**
 * How far an element has travelled through the viewport, from 0 as its top edge
 * enters the bottom to 1 as its bottom edge leaves the top.
 */
function travelProgress(rect, viewportHeight) {
    const travel = rect.height + viewportHeight;

    if (travel <= 0) {
        return 0;
    }

    return clamp((viewportHeight - rect.top) / travel);
}

/**
 * How far an element has scrolled past the top of the viewport, as a fraction of
 * its own height. This is the useful measure for a full height hero, which starts
 * already in view and should read as 0 until the visitor actually scrolls.
 */
function exitProgress(rect) {
    if (rect.height <= 0) {
        return 0;
    }

    return clamp(-rect.top / rect.height);
}

/**
 * Run one frame: measure everything, then apply everything.
 */
function tick() {
    frame = null;

    const viewportHeight = window.innerHeight;

    const measurements = scrubbed.map((element) => ({
        element,
        rect: element.getBoundingClientRect(),
    }));

    const context = {
        scrollY: window.scrollY,
        viewportHeight,
    };

    measurements.forEach(({ element, rect }) => {
        const onScreen = rect.top < viewportHeight && rect.bottom > 0;

        if (! onScreen) {
            if (promoted.has(element)) {
                promoted.delete(element);

                element.style.willChange = '';
            }

            return;
        }

        if (! promoted.has(element)) {
            promoted.add(element);

            element.style.willChange = 'transform, opacity';
        }

        element.style.setProperty('--scrub', travelProgress(rect, viewportHeight).toFixed(4));

        element.style.setProperty('--scrub-exit', exitProgress(rect).toFixed(4));
    });

    updateChrome(context);

    subscribers.forEach((subscriber) => subscriber(context));
}

/**
 * Drive the reading progress bar and the auto hiding header.
 *
 * The header only hides once the visitor is clear of the first viewport, so a
 * small scroll near the top of the page never takes the navigation away.
 */
function updateChrome({ scrollY, viewportHeight }) {
    const scrollable = document.documentElement.scrollHeight - viewportHeight;

    if (progressBar && scrollable > 0) {
        progressBar.style.setProperty('--progress', clamp(scrollY / scrollable).toFixed(4));
    }

    const goingDown = scrollY > lastScrollY;

    if (Math.abs(scrollY - lastScrollY) > 6) {
        document.documentElement.classList.toggle(
            'header-hidden',
            goingDown && scrollY > viewportHeight
        );

        lastScrollY = scrollY;
    }
}

/**
 * Queue a frame, collapsing every scroll event that arrives before it runs.
 */
function request() {
    if (frame) {
        return;
    }

    frame = window.requestAnimationFrame(tick);
}

/**
 * Rebuild the list of scrubbed elements, for markup rendered after boot.
 */
export function refresh() {
    scrubbed = Array.prototype.slice.call(document.querySelectorAll(SCRUB_SELECTOR));

    request();
}

/**
 * Add a callback invoked once per scroll frame with the current scroll context.
 */
export function subscribe(subscriber) {
    subscribers.add(subscriber);

    request();
}

/**
 * Remove a previously added callback.
 */
export function unsubscribe(subscriber) {
    subscribers.delete(subscriber);
}

/**
 * Boot the engine.
 */
export default function initScrollEngine() {
    if (started || prefersReducedMotion()) {
        return;
    }

    started = true;

    window.scrollEngine = {
        subscribe,
        unsubscribe,
        refresh,
    };

    lastScrollY = window.scrollY;

    window.addEventListener('scroll', request, { passive: true });

    window.addEventListener('resize', request, { passive: true });

    afterMount(() => {
        progressBar = document.querySelector('.scroll-progress');

        refresh();

        const root = document.getElementById('app');

        if (root && window.MutationObserver) {
            new MutationObserver(() => {
                window.clearTimeout(refreshTimer);

                refreshTimer = window.setTimeout(refresh, 120);
            }).observe(root, {
                childList: true,
                subtree: true,
            });
        }
    });
}

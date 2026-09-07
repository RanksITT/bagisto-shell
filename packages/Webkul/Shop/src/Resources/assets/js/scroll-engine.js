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
 * as custom properties between 0 and 1: `--scrub` through the viewport,
 * `--scrub-exit` past the top of it, and, for a stage taller than the viewport,
 * `--scrub-pin` through its own pinned window. Elements marked `data-progress`
 * receive `--progress`, reading progress for the document as a whole. What any of
 * those numbers mean is left to CSS; components that need the raw value instead
 * subscribe with `subscribe()`.
 *
 * The engine stays asleep while the visitor has asked for reduced motion.
 */

import { afterMount } from './scroll-reveal';

const SCRUB_SELECTOR = '[data-scrub]';

/**
 * Elements that want document reading progress.
 *
 * Progress is written to these rather than to the root element on purpose. A
 * custom property is inherited, so setting one on the root invalidates style for
 * every element in the document on every scroll frame; writing to the handful of
 * elements that actually read it keeps the invalidation where it belongs.
 */
const READER_SELECTOR = '[data-progress]';

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

let readers = [];

let frame = null;

let refreshTimer = null;

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
 * How far a pinned stage has travelled through its own pinned window, from 0 as
 * its top reaches the top of the viewport to 1 as its bottom reaches the bottom.
 *
 * A tall stage that sticks its contents needs this rather than the travel measure,
 * whose range is compressed towards the middle by the height of the stage itself.
 */
function pinProgress(rect, viewportHeight) {
    const travel = rect.height - viewportHeight;

    if (travel <= 0) {
        return 0;
    }

    return clamp(-rect.top / travel);
}

/**
 * Run one frame: read all geometry, then write all styles.
 *
 * The chrome update and the subscribers both read layout, so they run inside the
 * read phase alongside the measurement pass. Moving either of them after the
 * write loop would force a synchronous layout on every frame.
 */
function tick() {
    frame = null;

    const viewportHeight = window.innerHeight;

    const measurements = scrubbed.map((element) => ({
        element,
        rect: element.getBoundingClientRect(),
    }));

    const scrollable = document.documentElement.scrollHeight - viewportHeight;

    const context = {
        scrollY: window.scrollY,
        viewportHeight,
        measurements,
    };

    subscribers.forEach((subscriber) => subscriber(context));

    updateChrome(context, scrollable);

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

        if (rect.height > viewportHeight) {
            element.style.setProperty('--scrub-pin', pinProgress(rect, viewportHeight).toFixed(4));
        }
    });
}

/**
 * Publish reading progress and drive the auto hiding header.
 *
 * The header only hides once the visitor is clear of the first viewport, so a
 * small scroll near the top of the page never takes the navigation away.
 */
function updateChrome({ scrollY, viewportHeight }, scrollable) {
    if (scrollable > 0 && readers.length) {
        const progress = clamp(scrollY / scrollable).toFixed(4);

        readers.forEach((reader) => reader.style.setProperty('--progress', progress));
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

    readers = Array.prototype.slice.call(document.querySelectorAll(READER_SELECTOR));

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

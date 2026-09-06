/**
 * Scroll driven reveal primitive.
 *
 * Any element carrying a `data-reveal` attribute starts hidden and transitions
 * into place the first time it enters the viewport. A container carrying
 * `data-reveal-group` staggers the revealable elements inside it, so a product
 * grid fills in card by card instead of snapping in as one block.
 *
 * The hidden state lives behind the `js-motion` class that this module owns,
 * so a failed bundle or a reduced motion preference leaves every element
 * painted normally.
 */

const MOTION_CLASS = 'js-motion';

const VISIBLE_CLASS = 'is-visible';

const STAGGER_STEP = 70;

const STAGGER_LIMIT = 6;

const SAFETY_DELAY = 2500;

/**
 * Reveal styles for markup this theme does not author.
 *
 * Storefront sections of type `static_content` are written in the admin and run
 * through HTMLPurifier, which drops any `data-reveal` attribute. Their markup does
 * keep its `rl-` class namespace, so the theme maps those classes onto the same
 * primitive instead. Keys are selectors, values are either a reveal style or the
 * literal `group` to mark a stagger container.
 */
const CLASS_MAP = {
    '.rl-section__title': 'up',
    '.rl-section__sub': 'up',
    '.rl-cats': 'group',
    '.rl-cat': 'up',
    '.rl-offer': 'scale',
    '.rl-offer__title': 'clip',
    '.rl-brands': 'group',
    '.rl-brand': 'up',
};

/**
 * Elements already handed to the observer.
 *
 * The root application is created without a template, so Vue takes the markup
 * inside `#app` as one at mount time. Any attribute or inline style written onto
 * that markup beforehand is captured into the template string and stamped back
 * onto the freshly created elements, which would make bookkeeping written to the
 * DOM look like work that had already been done. Bind state is therefore held
 * here rather than on the elements.
 */
const bound = new WeakSet();

let observer = null;

/**
 * Whether the visitor has asked the interface to hold still.
 */
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Run a callback once the root application has taken over `#app`.
 *
 * The layout mounts on `DOMContentLoaded` and registers that listener while the
 * document is still parsing, which is before any deferred module runs, so a
 * listener added here is always called after the mount has replaced the markup.
 *
 * The readiness test is against `complete` rather than `loading` on purpose: a
 * deferred module runs while the state is already `interactive`, which is after
 * parsing but still before `DOMContentLoaded` and therefore before the mount.
 * Treating `interactive` as ready would hand the callback the markup that is
 * about to be thrown away.
 */
export function afterMount(callback) {
    if (document.readyState === 'complete') {
        callback();

        return;
    }

    document.addEventListener('DOMContentLoaded', callback);
}

/**
 * Give an element the delay it inherits from its stagger group.
 */
function applyStagger(element) {
    const group = element.closest('[data-reveal-group]');

    if (! group) {
        return;
    }

    const members = Array.prototype.slice.call(group.querySelectorAll('[data-reveal]'));

    const index = members.indexOf(element);

    if (index <= 0) {
        return;
    }

    element.style.setProperty(
        '--reveal-delay',
        `${Math.min(index, STAGGER_LIMIT) * STAGGER_STEP}ms`
    );
}

/**
 * Stamp `data-reveal` onto admin authored markup that matches the class map.
 */
function applyClassMap(scope) {
    Object.keys(CLASS_MAP).forEach((selector) => {
        const style = CLASS_MAP[selector];

        const matches = Array.prototype.slice.call(scope.querySelectorAll(selector));

        if (scope.matches && scope.matches(selector)) {
            matches.unshift(scope);
        }

        matches.forEach((element) => {
            if (style === 'group') {
                element.setAttribute('data-reveal-group', '');

                return;
            }

            if (! element.hasAttribute('data-reveal')) {
                element.setAttribute('data-reveal', style);
            }
        });
    });
}

/**
 * Start watching every element inside the given root that is still hidden.
 */
function scan(root) {
    if (! observer) {
        return;
    }

    const scope = root || document;

    if (scope.querySelectorAll) {
        applyClassMap(scope);
    }

    const candidates = Array.prototype.slice.call(
        scope.querySelectorAll ? scope.querySelectorAll('[data-reveal]') : []
    );

    if (scope.matches && scope.matches('[data-reveal]')) {
        candidates.unshift(scope);
    }

    candidates.forEach((element) => {
        if (bound.has(element)) {
            return;
        }

        bound.add(element);

        observer.observe(element);
    });
}

/**
 * Reveal everything at once and stop observing, used when motion is unwanted.
 */
function revealAll() {
    document.documentElement.classList.remove(MOTION_CLASS);

    document.querySelectorAll('[data-reveal]').forEach((element) => {
        element.classList.add(VISIBLE_CLASS);
    });
}

/**
 * Reveal anything already sitting in the viewport, by measurement rather than by
 * observation.
 *
 * A browser only runs the rendering steps for a page it is actually painting, so
 * a tab restored in the background delivers no intersection records at all and its
 * revealable content would stay at `opacity: 0` until the visitor scrolled. This
 * sweep runs once the page is on screen and settles anything the observer has not
 * reported yet.
 */
function revealInViewport() {
    if (document.visibilityState !== 'visible') {
        return;
    }

    document.querySelectorAll('[data-reveal]:not(.is-visible)').forEach((element) => {
        if (! element.offsetWidth) {
            return;
        }

        const rect = element.getBoundingClientRect();

        const onScreen = rect.top < window.innerHeight
            && rect.bottom > 0
            && rect.left < window.innerWidth
            && rect.right > 0;

        if (! onScreen) {
            return;
        }

        applyStagger(element);

        element.classList.add(VISIBLE_CLASS);

        if (observer) {
            observer.unobserve(element);
        }
    });
}

/**
 * Watch the application root so Vue rendered markup is picked up after mount.
 */
function watchMutations() {
    const root = document.getElementById('app');

    if (! root || ! window.MutationObserver) {
        return;
    }

    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    scan(node);
                }
            });
        });
    }).observe(root, {
        childList: true,
        subtree: true,
    });
}

/**
 * Boot the reveal primitive.
 */
export default function initScrollReveal() {
    if (! ('IntersectionObserver' in window) || prefersReducedMotion()) {
        revealAll();

        return;
    }

    document.documentElement.classList.add(MOTION_CLASS);

    document.documentElement.dataset.revealReady = '1';

    observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (! entry.isIntersecting) {
                return;
            }

            applyStagger(entry.target);

            entry.target.classList.add(VISIBLE_CLASS);

            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -8% 0px',
        threshold: 0,
    });

    afterMount(() => {
        scan(document);

        watchMutations();
    });

    window.setTimeout(revealInViewport, SAFETY_DELAY);

    document.addEventListener('visibilitychange', () => {
        window.setTimeout(revealInViewport, SAFETY_DELAY);
    });

    window.matchMedia('(prefers-reduced-motion: reduce)').addEventListener('change', (event) => {
        if (event.matches) {
            revealAll();
        }
    });
}

/**
 * Engine chapter kinematics.
 *
 * Every other scroll effect on the storefront is expressed purely in CSS, driven
 * by a custom property the scroll engine publishes. The engine chapter's linkage
 * is the one exception, because a connecting rod only reads as a mechanism if its
 * foot genuinely stays on the crank pin, and that closure is the exact slider
 * crank solution rather than anything `calc()` can approximate.
 *
 * The cost is nil: the stage is already a `[data-scrub]` element, so its geometry
 * arrives with the frame and this module adds no layout read of its own. It
 * writes three properties and lets CSS do the rest.
 */

import { afterMount } from './scroll-reveal';

const STAGE_SELECTOR = '[data-engine-scene]';

const CRANK_RADIUS = 48;

const ROD_LENGTH = 162;

/**
 * Crank centre to wrist pin distance with the piston at top dead centre, which is
 * the position the artwork is drawn in.
 */
const REST_OFFSET = CRANK_RADIUS + ROD_LENGTH;

/**
 * Full crank revolutions across the pinned stage. Matches the four stroke cycles
 * the CSS keyframes run over the same range, so the two halves cannot drift.
 */
const REVOLUTIONS = 4;

let stage = null;

/**
 * Whether the visitor has asked the interface to hold still.
 */
function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

/**
 * Pinned progress for the stage, from the rect the engine already measured.
 */
function progressFrom(rect, viewportHeight) {
    const travel = rect.height - viewportHeight;

    if (travel <= 0) {
        return 0;
    }

    return Math.min(Math.max(-rect.top / travel, 0), 1);
}

/**
 * Solve the linkage for one crank angle and publish it.
 *
 * `piston` is how far the wrist pin sits below its top dead centre position, and
 * `rod` is the angle the rod leans from vertical to keep its foot on the pin.
 */
function solve(angle) {
    const theta = angle * Math.PI / 180;

    const sine = Math.sin(theta);

    const reach = Math.sqrt(ROD_LENGTH * ROD_LENGTH - CRANK_RADIUS * CRANK_RADIUS * sine * sine);

    const pistonY = REST_OFFSET - (CRANK_RADIUS * Math.cos(theta) + reach);

    const rodAngle = Math.asin(CRANK_RADIUS * sine / ROD_LENGTH) * 180 / Math.PI;

    return { pistonY, rodAngle };
}

/**
 * Recompute the linkage for the current frame.
 */
function update({ viewportHeight, measurements }) {
    if (! stage || ! stage.isConnected) {
        stage = document.querySelector(STAGE_SELECTOR);
    }

    if (! stage) {
        return;
    }

    const measurement = measurements.find((entry) => entry.element === stage);

    if (! measurement) {
        return;
    }

    const angle = progressFrom(measurement.rect, viewportHeight) * REVOLUTIONS * 360;

    const { pistonY, rodAngle } = solve(angle);

    stage.style.setProperty('--crank-angle', angle.toFixed(2));

    stage.style.setProperty('--piston-y', pistonY.toFixed(2));

    stage.style.setProperty('--rod-angle', rodAngle.toFixed(2));
}

/**
 * Boot the engine chapter, where one is on the page and motion is wanted.
 */
export default function initEngineScene() {
    if (prefersReducedMotion() || ! window.scrollEngine) {
        return;
    }

    afterMount(() => {
        if (! document.querySelector(STAGE_SELECTOR)) {
            return;
        }

        window.scrollEngine.subscribe(update);
    });
}

/**
 * Shared motion variants — Premium 3D scroll-based storytelling system.
 *
 * Reduced-motion is handled globally by the
 * <MotionConfig reduced-motion="user"> wrapper in PublicLayout.
 */

/** Snappy but soft easing used for entrances. */
export const EASE_OUT = [0.16, 1, 0.3, 1] as const;

/** Cinematic slow ease for dramatic reveals. */
export const EASE_CINEMATIC = [0.33, 0, 0.1, 1] as const;

export const fadeUp = {
    hidden: { opacity: 0, y: 32 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.7, ease: EASE_OUT },
    },
};

export const fadeIn = {
    hidden: { opacity: 0 },
    visible: { opacity: 1, transition: { duration: 0.6, ease: EASE_OUT } },
};

export const scaleIn = {
    hidden: { opacity: 0, scale: 0.92 },
    visible: {
        opacity: 1,
        scale: 1,
        transition: { duration: 0.6, ease: EASE_OUT },
    },
};

/**
 * Parent variant that walks its children in one after another.
 * Pair with `fadeUp` (or similar) on each child.
 */
export function stagger(staggerChildren = 0.08, delayChildren = 0) {
    return {
        hidden: {},
        visible: {
            transition: { staggerChildren, delayChildren },
        },
    };
}

/** Standard `whileInView` config: animate once, slightly before fully visible. */
export const inViewOnce = {
    once: true,
    margin: '0px 0px -80px 0px',
} as const;

/** Soft spring used for pointer-driven motion (magnetic buttons, tilt, spotlight). */
export const SPRING_SOFT = {
    type: 'spring',
    stiffness: 150,
    damping: 18,
    mass: 0.6,
} as const;

/** Snappier spring for elements that must feel like they track the cursor. */
export const SPRING_SNAPPY = {
    type: 'spring',
    stiffness: 320,
    damping: 26,
    mass: 0.4,
} as const;

/**
 * Like `fadeUp`, but defocuses on the way in. Reads as more "expensive" than a
 * plain translate, so it is reserved for headline and hero-level elements.
 */
export const blurUp = {
    hidden: { opacity: 0, y: 40, filter: 'blur(12px)' },
    visible: {
        opacity: 1,
        y: 0,
        filter: 'blur(0px)',
        transition: { duration: 0.8, ease: EASE_OUT },
    },
};

/**
 * Horizontal entrance. `direction` is the side the element travels *from*.
 */
export function slideInX(direction: 'left' | 'right' = 'left', distance = 40) {
    const offset = direction === 'left' ? -distance : distance;

    return {
        hidden: { opacity: 0, x: offset },
        visible: {
            opacity: 1,
            x: 0,
            transition: { duration: 0.6, ease: EASE_OUT },
        },
    };
}

/**
 * Wipe a line of text into view from behind its own baseline. Pair with a
 * parent that has `overflow: hidden` so the mask edge is clean.
 */
export const maskReveal = {
    hidden: { y: '110%' },
    visible: {
        y: '0%',
        transition: { duration: 0.75, ease: EASE_OUT },
    },
};

/** Per-character variant used by KineticHeading. */
export const characterReveal = {
    hidden: { opacity: 0, y: '0.6em', filter: 'blur(6px)' },
    visible: {
        opacity: 1,
        y: '0em',
        filter: 'blur(0px)',
        transition: { duration: 0.5, ease: EASE_OUT },
    },
};

/**
 * Spring-based scale entrance for cards and interactive elements.
 * Feels more alive than a plain opacity transition.
 */
export const springScale = {
    hidden: { opacity: 0, scale: 0.85 },
    visible: {
        opacity: 1,
        scale: 1,
        transition: { type: 'spring', stiffness: 260, damping: 20, mass: 0.8 },
    },
};

/**
 * Rotating entrance — element spins in from a slight angle.
 * Good for badges, icons, or accent elements.
 */
export const rotateIn = {
    hidden: { opacity: 0, rotate: -8, scale: 0.9 },
    visible: {
        opacity: 1,
        rotate: 0,
        scale: 1,
        transition: { duration: 0.6, ease: EASE_OUT },
    },
};

/**
 * 3D flip entrance — element rotates on the X axis.
 * Use sparingly for "wow" moments (hero badge, section reveal).
 */
export const flipIn = {
    hidden: { opacity: 0, rotateX: 90 },
    visible: {
        opacity: 1,
        rotateX: 0,
        transition: { duration: 0.7, ease: EASE_OUT },
    },
};

/**
 * Stagger with faster children for denser lists (skill bars, tag lists).
 */
export function staggerFast(staggerChildren = 0.04, delayChildren = 0) {
    return {
        hidden: {},
        visible: {
            transition: { staggerChildren, delayChildren },
        },
    };
}

/** Slightly larger fade-up for hero-level content. */
export const heroFadeUp = {
    hidden: { opacity: 0, y: 48 },
    visible: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.9, ease: EASE_OUT },
    },
};

/** Scale + fade from center — good for floating elements. */
export const floatIn = {
    hidden: { opacity: 0, scale: 0.8, y: 20 },
    visible: {
        opacity: 1,
        scale: 1,
        y: 0,
        transition: { duration: 0.7, ease: EASE_OUT },
    },
};

/* ═══════════════════════════════════════════════════════════
   PREMIUM 3D / SCROLL STORYTELLING VARIANTS
   ═══════════════════════════════════════════════════════════ */

/**
 * Cinematic section reveal — rises from below with a blur + subtle scale.
 * Used on major section wrappers for dramatic scroll-triggered entrances.
 */
export const sectionReveal = {
    hidden: { opacity: 0, y: 60, scale: 0.97, filter: 'blur(6px)' },
    visible: {
        opacity: 1,
        y: 0,
        scale: 1,
        filter: 'blur(0px)',
        transition: { duration: 1, ease: EASE_CINEMATIC },
    },
};

/**
 * 3D perspective card — rises from depth with subtle rotation.
 * Cards appear to emerge from a 3D space as you scroll.
 */
export const perspectiveCard = {
    hidden: {
        opacity: 0,
        y: 50,
        rotateX: 8,
        rotateY: -4,
        scale: 0.95,
    },
    visible: {
        opacity: 1,
        y: 0,
        rotateX: 0,
        rotateY: 0,
        scale: 1,
        transition: { duration: 0.8, ease: EASE_OUT },
    },
};

/**
 * Depth layer — for elements at different z-depths in parallax compositions.
 * `depth` controls how much Y translation is applied (0 = static, 1 = deep).
 */
export function depthLayer(depth: number, direction: 'up' | 'down' = 'up') {
    const y = direction === 'up' ? -40 * depth : 40 * depth;

    return {
        hidden: { opacity: 0, y: y * 0.5 },
        visible: {
            opacity: 1,
            y: 0,
            transition: { duration: 0.8 + depth * 0.2, ease: EASE_OUT },
        },
    };
}

/**
 * Parallax float — subtle continuous float for background decorative elements.
 * Use with `animate` instead of `whileInView`.
 */
export const parallaxFloat = {
    y: [0, -12, 0, 8, 0],
    x: [0, 4, 0, -3, 0],
    rotate: [0, 1, 0, -0.5, 0],
};

/**
 * Cinematic text reveal — word-by-word entrance with stagger and blur.
 */
export const cinematicReveal = {
    hidden: {},
    visible: {
        transition: {
            staggerChildren: 0.06,
            delayChildren: 0.2,
        },
    },
};

export const cinematicWord = {
    hidden: { opacity: 0, y: 20, filter: 'blur(4px)' },
    visible: {
        opacity: 1,
        y: 0,
        filter: 'blur(0px)',
        transition: { duration: 0.5, ease: EASE_OUT },
    },
};

/**
 * Scroll-driven transform variants for use with `useTransform`.
 * These are plain value maps, not animation configs.
 */
export const scrollParallax = {
    /** Standard Y parallax: [scrollProgress] → Y translate */
    y: [0, -80],
    /** Scale that contracts slightly on scroll */
    scale: [1, 0.96],
    /** Opacity that fades out on scroll */
    opacity: [1, 0],
} as const;

/**
 * Staggered container for landing page sections.
 */
export function landingStagger(staggerChildren = 0.1, delayChildren = 0.1) {
    return {
        hidden: {},
        visible: {
            transition: { staggerChildren, delayChildren },
        },
    };
}

/**
 * Premium card hover — subtle lift + glow for interactive cards.
 * Pair with CSS `depth-card` class.
 */
export const premiumHover = {
    rest: {
        y: 0,
        scale: 1,
        rotateX: 0,
        rotateY: 0,
    },
    hover: {
        y: -6,
        scale: 1.01,
        rotateX: -2,
        rotateY: 2,
        transition: { duration: 0.4, ease: EASE_OUT },
    },
};

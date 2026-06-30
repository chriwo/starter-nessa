/**
 * Main navigation behaviour (vanilla JS, no jQuery).
 *
 * Desktop dropdowns open via CSS (:hover / :focus-within); this module only keeps
 * the ARIA state in sync, closes dropdowns on Escape, and flips the level-3 panel
 * when it would overflow the viewport. The sticky header gains a shadow on scroll.
 *
 * The mobile offcanvas and its collapse sub-menus are handled natively by
 * Bootstrap 5 (data-bs-* attributes) — no JavaScript required here.
 */
export default function menuMain() {
    initStickyShadow();
    initDropdownAria();
    initEscapeToClose();
    initLevel3Flip();
    initOffcanvasAria();
}

/**
 * Add a shadow to the sticky header once the page is scrolled.
 */
function initStickyShadow() {
    const header = document.getElementById('site-header');
    if (!header || !header.classList.contains('is-header-sticky')) {
        return;
    }

    const onScroll = () => header.classList.toggle('has-shadow', window.scrollY > 10);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
}

/**
 * Keep dropdowns in sync with hover and keyboard focus.
 *
 * Mouse hover opens dropdowns purely via CSS (works without JS). For keyboard
 * users this adds an .is-open class on focus so the dropdown is revealed, and
 * keeps aria-expanded in sync. The class (rather than :focus-within) lets Escape
 * close the dropdown while focus remains on the toggle.
 */
function initDropdownAria() {
    const parents = document.querySelectorAll(
        '.c-mainnav__item--has-children, .c-mainnav__subitem--has-children',
    );

    parents.forEach((parent) => {
        const toggle = parent.querySelector(':scope > .c-mainnav__toggle, :scope > .c-mainnav__subtoggle');
        if (!toggle) {
            return;
        }

        const setExpanded = (expanded) => toggle.setAttribute('aria-expanded', String(expanded));
        const open = () => {
            parent.classList.add('is-open');
            setExpanded(true);
        };
        const close = () => {
            parent.classList.remove('is-open');
            setExpanded(false);
        };

        // Mouse: visibility is handled by CSS :hover, only mirror the ARIA state.
        parent.addEventListener('mouseenter', () => setExpanded(true));
        parent.addEventListener('mouseleave', () => {
            if (!parent.contains(document.activeElement)) {
                close();
            }
        });

        // Keyboard: open on focus, close when focus leaves the item entirely.
        parent.addEventListener('focusin', open);
        parent.addEventListener('focusout', (event) => {
            if (!parent.contains(event.relatedTarget)) {
                close();
            }
        });
    });
}

/**
 * Close open desktop dropdowns on Escape and return focus to the outermost
 * open toggle that currently holds focus.
 */
function initEscapeToClose() {
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const active = document.activeElement;
        let focusTarget = null;

        document
            .querySelectorAll('.c-mainnav__item--has-children.is-open, .c-mainnav__subitem--has-children.is-open')
            .forEach((parent) => {
                const toggle = parent.querySelector(
                    ':scope > .c-mainnav__toggle, :scope > .c-mainnav__subtoggle',
                );

                // Return focus to the outermost (level-1) toggle that held focus.
                if (toggle && parent.contains(active) && !focusTarget) {
                    focusTarget = toggle;
                }

                parent.classList.remove('is-open');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });

        if (focusTarget) {
            focusTarget.focus();
        }
    });
}

/**
 * Flip the level-3 dropdown to the left when it would overflow the right edge.
 */
function initLevel3Flip() {
    const edgeGap = 16;

    document.querySelectorAll('.c-mainnav__dropdown--l3').forEach((dropdown) => {
        const parent = dropdown.closest('.c-mainnav__subitem--has-children');
        if (!parent) {
            return;
        }

        const evaluate = () => {
            dropdown.classList.remove('flip-left');
            const rect = dropdown.getBoundingClientRect();
            if (rect.right > window.innerWidth - edgeGap) {
                dropdown.classList.add('flip-left');
            }
        };

        parent.addEventListener('mouseenter', evaluate);
        parent.addEventListener('focusin', evaluate);
    });
}

/**
 * Mirror the offcanvas open state onto the hamburger toggler's aria-expanded.
 * Bootstrap manages the offcanvas itself but does not update the trigger.
 */
function initOffcanvasAria() {
    const offcanvas = document.getElementById('offcanvasNav');
    const toggler = document.querySelector('[data-bs-target="#offcanvasNav"]');
    if (!offcanvas || !toggler) {
        return;
    }

    offcanvas.addEventListener('show.bs.offcanvas', () => toggler.setAttribute('aria-expanded', 'true'));
    offcanvas.addEventListener('hide.bs.offcanvas', () => toggler.setAttribute('aria-expanded', 'false'));
}

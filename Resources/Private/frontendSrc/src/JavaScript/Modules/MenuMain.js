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
 * Keep aria-expanded in sync with the hover/focus state of dropdown parents.
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

        parent.addEventListener('mouseenter', () => setExpanded(true));
        parent.addEventListener('mouseleave', () => setExpanded(false));
        parent.addEventListener('focusin', () => setExpanded(true));
        parent.addEventListener('focusout', (event) => {
            if (!parent.contains(event.relatedTarget)) {
                setExpanded(false);
            }
        });
    });
}

/**
 * Close all open desktop dropdowns on Escape and return focus to the toggle.
 */
function initEscapeToClose() {
    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        const openToggles = document.querySelectorAll(
            '.c-mainnav__toggle[aria-expanded="true"], .c-mainnav__subtoggle[aria-expanded="true"]',
        );

        openToggles.forEach((toggle) => {
            toggle.setAttribute('aria-expanded', 'false');
            // Move focus out of the dropdown so :focus-within releases it.
            toggle.focus();
        });
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

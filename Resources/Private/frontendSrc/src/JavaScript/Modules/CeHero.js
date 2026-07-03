import { Carousel } from 'bootstrap';

const AUTOPLAY_INTERVAL = 6000;

export default function ceHeroCarousel() {
    const $carousel = $('#heroCarousel');
    const $indicators = $('#hero-carousel-indicators');
    const slides = $carousel.find('.carousel-inner').children('.carousel-item');

    // A single (static) hero renders no carousel markup — nothing to initialise.
    if (!$carousel.length || slides.length <= 1) {
        return;
    }

    // Indicators with accessible names.
    slides.each(function (index) {
        const activeAttr = index === 0 ? " class='active' aria-current='true'" : '';
        $indicators.append(
            "<button type='button' data-bs-target='#heroCarousel' data-bs-slide-to='" +
                index +
                "'" +
                activeAttr +
                " aria-label='Slide " +
                (index + 1) +
                "'></button>",
        );
    });

    // Drive autoplay from JS (data-bs-ride removed in the template) so the pause
    // button is authoritative and hover pausing never fights an explicit pause.
    const carousel = Carousel.getOrCreateInstance($carousel[0], {
        ride: false,
        pause: false,
        interval: false,
    });

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const $pauseButton = $carousel.find('.hero-carousel-pause');

    let timer = null;
    // Respect reduced-motion: start paused, no auto-rotation.
    let userPaused = reduceMotion;

    const startTimer = function () {
        if (timer || userPaused) {
            return;
        }
        timer = window.setInterval(() => carousel.next(), AUTOPLAY_INTERVAL);
    };

    const stopTimer = function () {
        window.clearInterval(timer);
        timer = null;
    };

    const renderButton = function () {
        $pauseButton.find('use').attr('href', userPaused ? '#bi-play-fill' : '#bi-pause-fill');
        $pauseButton.attr(
            'aria-label',
            userPaused ? 'Start slide rotation' : 'Pause slide rotation',
        );
        $pauseButton.attr('aria-pressed', userPaused ? 'true' : 'false');
    };

    $pauseButton.on('click', function () {
        userPaused = !userPaused;
        userPaused ? stopTimer() : startTimer();
        renderButton();
    });

    // Pause on hover / keyboard focus; resume unless the user paused explicitly.
    $carousel.on('mouseenter focusin', stopTimer);
    $carousel.on('mouseleave focusout', startTimer);

    // Entrance animations — skipped entirely for reduced-motion users.
    if (!reduceMotion) {
        $carousel.on('slid.bs.carousel', function () {
            $(this).find('h2').addClass('animate__animated animate__fadeInDown');
            $(this).find('p, .btn-get-started').addClass('animate__animated animate__fadeInUp');
        });
    }

    renderButton();
    startTimer();
}

export default function ceHeroCarousel() {
    let heroCarousel = $('#heroCarousel');
    let heroCarouselIndicators = $('#hero-carousel-indicators');
    heroCarousel
        .find('.carousel-inner')
        .children('.carousel-item')
        .each(function (index) {
            index === 0
                ? heroCarouselIndicators.append(
                      "<button type='button' data-bs-target='#heroCarousel' data-bs-slide-to='" +
                          index +
                          "' class='active' aria-current='true'></button>",
                  )
                : heroCarouselIndicators.append(
                      "<button type='button' data-bs-target='#heroCarousel' data-bs-slide-to='" + index + "'></button>",
                  );
        });

    heroCarousel.on('slid.bs.carousel', function () {
        $(this).find('h2').addClass('animate__animated animate__fadeInDown');
        $(this).find('p, .btn-get-started').addClass('animate__animated animate__fadeInUp');
    });
}

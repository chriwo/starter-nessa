import 'jquery.easing';

export default function backToTop() {
    // Back to top button
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('#js-back-to-top').fadeIn('slow');
        } else {
            $('#js-back-to-top').fadeOut('slow');
        }
    });

    $('#js-back-to-top').click(function () {
        $('html, body').animate(
            {
                scrollTop: 0,
            },
            1500,
            'easeInOutExpo',
        );
        return false;
    });
}

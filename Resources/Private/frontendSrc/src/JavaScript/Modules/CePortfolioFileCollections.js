import 'venobox';

export default function cePortfolioFileCollections() {
    const portfolioContainer = document.querySelector('.js-portfolio-file-collections');

    if (portfolioContainer) {
        $(document).ready(function () {
            $('.js-portfolio__collections-gallery').venobox({
                titleattr: 'data-title',
                titlePosition: 'bottom',
            });
        });
    }
}

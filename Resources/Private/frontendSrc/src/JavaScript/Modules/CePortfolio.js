import 'venobox';

import Isotope from 'isotope-layout';

export default function cePortfolio() {
    const portfolioContainer = document.querySelector('.portfolio-container');

    function radioButtonGroup(buttonGroup) {
        buttonGroup.addEventListener('click', function (event) {
            buttonGroup.querySelector('.is-checked').classList.remove('is-checked');
            event.target.classList.add('is-checked');
        });
    }

    if (portfolioContainer) {
        var portfolioIsotope = new Isotope(portfolioContainer, {
            itemSelector: '.portfolio-item',
        });

        // filter functions
        var filterFns = {
            // show if number is greater than 50
            numberGreaterThan50: function (itemElem) {
                var number = itemElem.querySelector('.number').textContent;
                return parseInt(number, 10) > 50;
            },
            // show if name ends with -ium
            ium: function (itemElem) {
                var name = itemElem.querySelector('.name').textContent;
                return name.match(/ium$/);
            },
        };

        // bind filter button click
        var filtersElem = document.querySelector('.filters-button-group');
        filtersElem.addEventListener('click', function (event) {
            var filterValue = event.target.getAttribute('data-filter');
            // use matching filter function
            filterValue = filterFns[filterValue] || filterValue;
            portfolioIsotope.arrange({ filter: filterValue });
        });

        // change is-checked class on buttons
        var buttonGroups = document.querySelectorAll('.button-group');
        for (var i = 0, len = buttonGroups.length; i < len; i++) {
            var buttonGroup = buttonGroups[i];
            radioButtonGroup(buttonGroup);
        }

        $(document).ready(function () {
            $('.js-portfolio__gallery').venobox({
                titlePosition: 'bottom',
            });
        });
    }
}

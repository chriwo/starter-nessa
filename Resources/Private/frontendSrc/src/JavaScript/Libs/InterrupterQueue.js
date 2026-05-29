'use strict';

import $ from 'jquery/dist/jquery.js';
import Interrupter from '../Modules/Interrupter.js';

class InterrupterQueue {
    constructor() {
        this.options = {
            selector: '.js-interrupter',
            timeBetween: 15 * 1000
        };
        this.$interrupters = null;
        this.queue = [];
    }

    init() {
        let _ = this;

        _.$interrupters = $(_.options.selector);

        if (_.$interrupters.length) {
            $.each(_.$interrupters, function () {
                _.queue.push(new Interrupter($(this)));
            });
        }

        _.showNextItem();

        document.addEventListener('interrupter-dismissed', function () {
            setTimeout(function () {
                _.showNextItem();
            }, _.options.timeBetween);
        });
    }

    removeDismissedItems() {
        let _ = this;

        _.queue.forEach(function (item) {
            const i = _.queue.indexOf(item);

            if (item.isDismissed()) {
                _.queue.splice(i, 1);
            }
        });
    }

    showCurrentItem() {
        let _ = this;
        let currentItem = _.queue[0];

        if (typeof currentItem !== 'undefined') {
            currentItem.init();
        }
    }

    showNextItem() {
        let _ = this;

        _.removeDismissedItems();
        _.showCurrentItem();
    }
}

export default InterrupterQueue;

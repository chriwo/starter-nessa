'use strict';

import $ from 'jquery/dist/jquery.js';
import Cookies from 'js-cookie';

class Interrupter {
    constructor($interrupter) {
        this.$interrupter = $interrupter;
        this.options = {
            closeSelector: '.js-interrupter-close',
            dismissSelector: '.js-interrupter-dismiss',
            cookieLifeTime: undefined
        };
        this.$overlay = $('<div class="c-interrupter-overlay"></div>');
        this.$body = $('body');
        this.cookieId = 'steripharm_teaser_' + this.$interrupter.attr('id');
        this.enableOverlay = true;

        /* Interval/cookieLifeTime is to be defined in hours */
        this.options.cookieLifeTime =
            this.$interrupter.data('interval') || this.options.cookieLifeTime;
    }

    init() {
        var _ = this;

        if (_.$interrupter.length) {
            _.$closeButton = _.$interrupter.find(_.options.closeSelector);
            _.$dismissButton = _.$interrupter.find(_.options.dismissSelector);

            /* If there’s another interrupter that has been initialized before
               we can just use that interrupter’s overlay. */
            if ($('.c-interrupter-overlay').length === 0) {
                _.$body.append(_.$overlay);
            } else {
                _.$overlay = $('.c-interrupter-overlay');
            }

            _.show();

            _.$closeButton.on('click', function (e) {
                var $button = $(this);

                e.preventDefault();
                _.dismiss();
                $button.blur();

                if ($button.attr('href') !== null && $button.attr('href') !== undefined) {
                    window.location = $button.attr('href');
                }

                _.fireDismissalEvent();
            });

            _.$dismissButton.on('click', function () {
                _.setDismissedCookie();
                _.fireDismissalEvent();
            });

            _.initWindowResize();
        }
    }

    show() {
        var _ = this;
        //console.log('interrupter:show', _.cookieId);

        if (!_.isDismissed()) {
            setTimeout(function () {
                _.$interrupter.addClass('is-active');
                _.$interrupter.css('z-index', 5);

                if (_.enableOverlay) {
                    _.showOverlay();
                }
            }, 2000);
        }
    }

    hide() {
        var _ = this;
        //console.log('interrupter:hide', _.cookieId);

        _.$interrupter.removeClass('is-active');

        if (_.enableOverlay) {
            _.hideOverlay();
        }

        setTimeout(function () {
            _.$interrupter.css('z-index', -1);
        }, 1000);
    }

    showOverlay() {
        var _ = this;

        _.$overlay.addClass('is-visible');
        _.$body.addClass('interrupter-is-open');
    }

    hideOverlay() {
        var _ = this;

        _.$overlay.removeClass('is-visible');
        _.$body.removeClass('interrupter-is-open');
    }

    dismiss() {
        var _ = this;
        //console.log('interrupter:dismiss');

        _.hide();
        _.setDismissedCookie();
    }

    isDismissed() {
        var _ = this;
        var cookieContent = Cookies.get(_.cookieId);

        //console.log('interrupter:isDismissed', cookieContent || 0, _.cookieId);

        if (cookieContent === '1') {
            return true;
        }

        return false;
    }

    setDismissedCookie() {
        var _ = this;
        var expires = new Date(new Date().getTime() + _.options.cookieLifeTime * (60 * 60 * 1000));

        Cookies.set(_.cookieId, '1', {
            expires: expires,
            secure: true
        });
        //console.log('interrupter:setDismissedCookie', Cookies.get());
    }

    fireDismissalEvent() {
        var _ = this;
        var dismissalEvent = new CustomEvent('interrupter-dismissed', {
            bubbles: false,
            detail: _
        });

        document.dispatchEvent(dismissalEvent);
    }

    resetCookies() {
        var _ = this;

        Cookies.remove(_.cookieId);
        //console.log('interrupter:resetCookies', Cookies.get());
    }

    initWindowResize() {
        var _ = this;
        var w1 = $(window).width();

        _.enableOverlay = !window.matchMedia('(min-width: 570px)').matches;

        $(window).resize(function () {
            var w2 = $(window).width();

            _.enableOverlay = !window.matchMedia('(min-width: 570px)').matches;

            // Check if viewport width has changed
            if (w1 !== w2) {
                if (!_.enableOverlay) {
                    _.hideOverlay();
                } else {
                    if (!_.isDismissed()) {
                        _.showOverlay();
                    }
                }

                w1 = w2;
            }
        });
    }
}

export default Interrupter;

define(["jquery", "SableSoft_Phone/js/jquery.mask"], function( $ ) {
    "use strict";
    $.widget('sendCode.js', {
        _create: function() {
            let options = this.options;
            let button = this.element;
            let phone = jQuery( options.selector );
            let numberLength = 9;
            let countryCode = options.countryCode;
            phone.mask('+' + countryCode + ' (00) 000-00-00');
            button.on('click', function( e ) {
                let disabled = button.attr('disabled');
                if( disabled !== undefined && disabled !== false ) return false;
                let number = phone.cleanVal();
                if( number.length !== numberLength ) return;
                button.prop('disabled', true );
                jQuery.post( options.url, { number : number }, function( res ) {
                    console.info( res );
                    setTimeout( function() {
                        button.removeAttr('disabled');
                    }, options.freezeTime );
                });
            });
        }
    });

    return $.sendCode.js;
});

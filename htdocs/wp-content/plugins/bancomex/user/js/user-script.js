(function ($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */


    $(function () {
        if(document.getElementById('subscribe_user')!==null){
            document.getElementById('subscribe_user').addEventListener('submit', function (event) {

                // Deshabilita el botón de envío
                var submitButton = this.querySelector('input[type="submit"]');
                submitButton.disabled = true;

                // Reactivar el botón después de 1 segundo
                setTimeout(function () {

                    // Reactivar el botón de envío
                    submitButton.disabled = false;

                }, 1000); // 1000 milisegundos (1 segundo)

                // Desactivar la casilla de Aviso de Privacidad
                var elemento_1 = document.getElementById('agreeEsId');
                var elemento_2 = document.getElementById('agreeEnId');

                if (elemento_1 !== null) {
                    // El elemento con el ID 'agreeEsId' existe
                    elemento_1.checked = false;
                }

                if (elemento_2 !== null) {
                    // El elemento con el ID 'agreeEnId' existe
                    elemento_2.checked = false;
                }

            });
        }

    });

})(jQuery);

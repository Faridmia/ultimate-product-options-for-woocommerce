(function (window, document, $, undefined) {
    'use strict';

    
    $('#term-color_type').wpColorPicker();

    $(document).ready(function($) {
        var mediaUploader;

        $('#upow-swatch-term-upload-img-btn').on('click', function(e) {
            e.preventDefault();

            if (mediaUploader) {
                mediaUploader.open();
                return;
            }

            mediaUploader = wp.media.frames.file_frame = wp.media({
                title: 'Choose Image',
                button: {
                    text: 'Choose Image'
                },
                multiple: false
            });

            mediaUploader.on('select', function() {
                var attachment = mediaUploader.state().get('selection').first().toJSON();
                $('#upow-swatch-term-img-input').val(attachment.url);
                $('#upow-term-image-preview').attr('src', attachment.url).show();
                $('#upow-swatch-term-img-remove-btn').removeClass('upow-d-none');
            });

            mediaUploader.open();
        });

        $('#upow-swatch-term-img-remove-btn').on('click', function(e) {
            e.preventDefault();
            $('#upow-swatch-term-img-input').val('');
            $('#upow-term-image-preview').hide();
            $(this).addClass('upow-d-none');
        });
    });

})(window, document, jQuery);
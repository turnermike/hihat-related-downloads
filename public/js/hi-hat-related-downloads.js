jQuery(document).ready(function($) {

    imageWidget = {

        // Call this from the upload button to initiate the upload frame.
        uploader : function(widget_id, widget_id_string) {

            // console.log('widget_id', widget_id);
            // console.log('widget_id_string', widget_id_string);

            var frame = wp.media({
                title : imageWidget.frame_title,
                multiple : false,
                library : { type : 'application/pdf, application/msword' },
                button : { text : imageWidget.button_title }
            });

            // Handle results from media manager.
            frame.on('close', function() {
                var attachments = frame.state().get('selection').toJSON();
                imageWidget.render(widget_id, widget_id_string, attachments[0]);
            });

            frame.open();
            return false;
        },

        // Output Image preview and populate widget form.
        render : function(widget_id, widget_id_string, attachment) {
        // render : function(widget_id, widget_id_string, attachment) {

            // console.log('attachment', attachment);

            if (attachment) {
                $('#hihat-attachment-id').val(attachment.id);
                $('.hihat-attachment-title').val(attachment.title);
                $('.hihat-attachment-title').html(attachment.title);
                $('.hihat-attachment-url').val(attachment.url);
                $('.hihat-attachment-url').html(attachment.url);

            }

        },

        // Update input fields if it is empty
        updateInputIfEmpty : function(widget_id_string, name, value) {
            var field = $("#" + widget_id_string + name);
            if (field.val() === '') {
                field.val(value);
            }
        }

        // // Render html for the image.
        // imgHTML : function( attachment, widget_id ) {

        //     // console.log('attachement', attachment);
        //     // console.log('widget id,', widget_id);

        //     var img_html = '<img src="' + attachment.url + '" ';
        //     img_html += 'width="100%"';
        //     img_html += 'height="auto"';
        //     // img_html += 'width="' + attachment.width + '" ';
        //     // img_html += 'height="' + attachment.height + '" ';
        //     if ( attachment.alt != '' ) {
        //         img_html += 'alt="' + attachment.alt + '" ';
        //     }
        //     img_html += '/>';

        //     return img_html;
        // }

    };

});
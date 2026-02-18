(function( $ ) {
	'use strict';

	$(document).ready( function() {

		// Place the Replace Media section after the side metaboxes
		$('#media-replace-div').appendTo('#postbox-container-1');
		
	});

})( jQuery );

// let asenhaMRVars = asenhaMR;

// Open media modal on clicking Select New Media File button. 
// This is set with onclick="replaceMedia('mime/type')" on the button HTML
function replaceMedia(originalAttachmentId,oldImageMimeType) {
	if ( oldImageMimeType ) {
		// There's a mime type defined. Do nothing.
	} else {
		// We're in the grid view of an image. Get the mime type form the file info in DOM.
		if ( jQuery('.details .file-type').length ) {
			var oldImageMimeTypeFromDom = jQuery('.details .file-type').html();		
		}
		// Sometimes .file-type div is not there, and instead, a second .filename div is used to display file type info
		else if ( jQuery('.details .filename:nth-child(2)').length ) {
			var oldImageMimeTypeFromDom = jQuery('.details .filename:nth-child(2)').html();		
		}
		// Replace '<strong>File type:</strong>' in any language with empty string
		oldImageMimeTypeFromDom = oldImageMimeTypeFromDom.replace(/<strong>(.*?)<\/strong>/, '');
		// Replace one blank spacing with an empty space / no space
		oldImageMimeType = oldImageMimeTypeFromDom.replace(' ', '');
	}

	// https://codex.wordpress.org/Javascript_Reference/wp.media
	// https://github.com/ericandrewlewis/wp-media-javascript-guide

	// Instantiate the media frame
	mediaFrame = wp.media({
		title: mediaReplace.selectMediaText,
		button: {
			text: mediaReplace.performReplacementText
		},
		multiple: false // Enable/disable multiple select
	});
	

	// Open the media dialog and store it in a variable
	var mediaFrameEl = jQuery(mediaFrame.open().el);

	// Open the "Upload files" tab on load
	mediaFrameEl.find('#menu-item-upload').click();
	
	// When an image is selected
	mediaFrameEl.on('click', 'li.attachment', function(e) {
		var mimeTypeWarning = '<div class="mime-type-warning">The selected image is of a different type than the image to replace. Please choose an image with the same type.</div>';
		var selectedAttachment = mediaFrame.state().get('selection').first().toJSON();
		var selectedAttachmentMimeType = selectedAttachment.mime;

		if ( oldImageMimeType != selectedAttachmentMimeType ) {
			jQuery('.media-frame-toolbar .media-toolbar-primary .mime-type-warning').remove();
			jQuery('.media-frame-toolbar .media-toolbar-primary').prepend(mimeTypeWarning);
			jQuery('.media-frame-toolbar .media-toolbar-primary .media-button-select').prop('disabled', true);
		} else {
			jQuery('.media-frame-toolbar .media-toolbar-primary .mime-type-warning').remove();
			jQuery('.media-frame-toolbar .media-toolbar-primary .media-button-select').prop('disabled', false);
		}
	});
	
	// Make sure the "Drop files to upload" blue overlay is closed after dropping one or more files
	jQuery('.supports-drag-drop:not(.upload.php)').on( 'drop', function() {
		jQuery('.uploader-window').hide();
	});

	// When Perform Replacement button is clicked in the media frame...
	mediaFrame.on('select', function() {

		// Get media attachment details from the frame state
		var attachment = mediaFrame.state().get('selection').first().toJSON();
		var newImageMimeType = attachment.mime;
		
		if ( oldImageMimeType == newImageMimeType ) {

			// Send the attachment id of the replacement / new image to our hidden input
			jQuery('#new-attachment-id-'+originalAttachmentId).val(attachment.id);

			if (jQuery('#new-attachment-id-'+originalAttachmentId).closest('.media-modal').length) {
			    
			} else {
				// For media library list view
				// "Perform Replacement" button has been clicked. Submit the edit form, which includes 'new-attachment-id'
				jQuery('#new-attachment-id-'+originalAttachmentId).closest("form").submit();
				jQuery(mediaFrame.close());
			}			
		}

	});

}
<?php

/**
 * Add option to the Bulk Actions form using Javascript.
 *
 * @link        https://wordpress.org/plugins/
 * @since       1.0.0
 *
 * @package     Better_Comments_Reply_Manager_Pro
 * @subpackage  Better_Comments_Reply_Manager_Pro/partials
 */

?>
<script type="text/javascript">
	jQuery( document ).ready( function() {
		jQuery( '<option>' ).val( 'bcrm_bulk_mark_as_does_not_need_reply' ).text( '<?php _e( "Mark as Does Not Need Reply", 'better-comments-reply-manager-pro' ) ?>' ).appendTo( "select[name^='action']" );
	} );
</script>

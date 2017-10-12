<?php
/*
Plugin Name: Product Types Options
Plugin URI: https://campustrading.com.au/
Description: Product types options for woocommerce multisite.
Version: v1.0
Author: Ryan Bobotioc.
Author URI: https://campustrading.com.au/
License: GPL2
*/

/* Product Types Options adding inside the post as post meta  */
/*Add Feature Custom Post Type*/
/**
 * Adds a box to the main column on the Post and Page edit screens.
 */
function campus_shipping_options_add_meta_box() {

	$screens = array( 'product' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'myplugin_sectionid',
			__( 'Types of Product', 'myplugin_textdomain' ),
			'campus_shipping_meta_box_callback',
			$screen
		);
	}
}
add_action( 'add_meta_boxes', 'campus_shipping_options_add_meta_box' );
add_action('admin_enqueue_scripts', 'add_my_js');
   
function add_my_js(){    
  wp_enqueue_script('my_validate', get_template_directory_uri() . '/js/jquery.validate.min.js', array('jquery'));
  wp_enqueue_script('my_script_js', get_template_directory_uri() . '/js/shipping_type_validation.js');
}

/**
 * Prints the box content.
 * 
 * @param WP_Post $post The object for the current post/page.
 */
function campus_shipping_meta_box_callback( $post ) {

	// Add an nonce field so we can check for it later.
	wp_nonce_field( 'campus_shipping_meta_box', 'campus_shipping_meta_box_nonce' );

	/*
	* Use get_post_meta() to retrieve an existing value
	* from the database and use the value for the form.	 
	*/
	
	

	/*echo '<label for="myplugin_new_field">';
	_e( '', 'myplugin_textdomain' );
	echo '</label>';	*/
	//hand_delivered
	$shipping_type = get_post_meta( $post->ID, 'shipping_type', true );
	echo '<input type="radio" name="shipping_type" value="0" class="required" style="margin-bottom: -39px;"  />';
	echo '<br/>';
	if($shipping_type==1){ $hand_delivered_check = 'checked'; 	}
	echo '<input type="radio" '.$hand_delivered_check.' name="shipping_type" value="1"  />';
	echo 'Local<br/>';
	//locally_shipped
	if($shipping_type==2){ $locally_shipped_check = 'checked'; 	}
	echo '<input type="radio" '.$locally_shipped_check.'  name="shipping_type" value="2"  />';	
	echo 'National<br/>';	
	//virtual_downloadable_products
	//if($shipping_type==3){ $both_check = 'checked'; 	}
	//echo '<input type="radio" '.$both_check.'  name="shipping_type" value="3"  />';		
	//echo 'Both<br/>';			
	
}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function campus_shipping_save_meta_box_data( $post_id ) {

	/*
	* We need to verify this came from our screen and with proper authorization,
	* because the save_post action can be triggered at other times.
	*/


	// Check if our nonce is set.
	if ( ! isset( $_POST['campus_shipping_meta_box_nonce'] ) ) {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['campus_shipping_meta_box_nonce'], 'campus_shipping_meta_box' ) ) {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'product' == $_POST['post_type'] ) {

		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

	} else {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}

	/* OK, it's safe for us to save the data now. */

	// Make sure that it is set.
	if(isset($_POST['shipping_type'])) {
		$shipping_type = sanitize_text_field( $_POST['shipping_type'] );
	}
	else{	
		$shipping_type = 1;
	}
	update_post_meta( $post_id, 'shipping_type', $shipping_type );
	
}
add_action( 'save_post', 'campus_shipping_save_meta_box_data' );

//Run Create categories function when a new blog created
if(function_exists('ms_taxonomy_sync')) {
    add_action('wpmu_new_blog', 'ms_taxonomy_sync');
}

/*Abacies Team End */
/*
add_action('admin_init', 'update_products_shipping_options');

function update_products_shipping_options(){

    // The Query
    $args = array ( 'posts_per_page' => -1,'post_type'=>'product' );
    $the_query = new WP_Query( $args );

    // The Loop
    while ( $the_query->have_posts() ) :
        $the_query->the_post();
		update_post_meta(get_the_ID(), 'shipping_type', 2);
    endwhile;

}*/
/*Quick edit Shipping options**/

add_action( 'quick_edit_custom_box', 'campus_shipping_bulk_quick_edit_custom_box', 10, 2 );
function campus_shipping_bulk_quick_edit_custom_box( $column_name, $post_type ) {
    switch ( $post_type ) {
        case 'product':
        switch( $column_name ) {
            case 'product_tags': global $post;
            ?> <fieldset class="inline-edit-col-right">
            <div class="inline-edit-group">
            <label>
            <span class="title">Shipping options</span><br>
			</label>
            <input type="radio" name="shipping_type" id="" value="1" />Local<br>
			<input type="radio" name="shipping_type" id="" value="2" />National <br>
			<input type="radio" name="shipping_type" id="" value="3" />Both <br>

	
            </div>
            </fieldset><?php
        break;
    }
    break;
}
}

add_action('admin_footer', 'campus_shipping_quick_edit_javascript');
function campus_shipping_quick_edit_javascript() 
{
    global $current_screen,$post;
    if (($current_screen->post_type != 'product')) return;
    ?>
    <script type="text/javascript">
        function set_custom_value(fieldValue, nonce) 
        { 
		
            // refresh the quick menu properly
            inlineEditPost.revert();
			jQuery('[name=shipping_type][value="'+fieldValue+'"]').prop('checked',true);
           /* if(fieldValue=='0'){
                jQuery('#shipping_type').prop('checked',true);
                
            }else if(fieldValue=='0'){
                jQuery('#shipping_type').removeAttr('checked');
                
            }*/
            //jQuery('#shipping_type').val(fieldValue);
        }
        /*jQuery('#product_checkbox').click (
            function (){
                if (jQuery('#product_checkbox').is (':checked'))
                    {
                        jQuery('#product_checkbox').val('yes');
                    }else{ jQuery('#product_checkbox').val('no');}
            });
        jQuery(document).ready(function() {
                jQuery('a.editinline').click (function (){
                var id = inlineEditPost.getId(this);
                var val = parseInt(jQuery('#inline_' + id + 'product_checkbox').text());
                jQuery('#product_checkbox').attr('checked', !!val);
   
        // edit by ajax
        var val = '<?php echo get_post_meta( $post->ID, 'shipping_type', TRUE);?>';
        var data = {
                    action: 'ajaxDataSubmit',
                    id: val
                };
        ajax_params = '<?php echo admin_url('admin-ajax.php');?>';
        jQuery.ajax({
            type:"POST",
            url: ajax_params, // our PHP handler file
            data: data,
            //loadin image or text beforeload ajax code },
            success:function(results){
                // do something with returned data
                if(results.trim()=='yes'){
                    jQuery('#product_checkbox').attr("checked","checked");
                }else{
                    jQuery('#product_checkbox').removeAttr("checked");
                }
            return results;
        }
    });
  });
});*/
</script>
<?php
}

add_action('wp_ajax_ajaxDataSubmit', 'campus_shipping_ajaxDataSubmit');//Logged in users
add_action('wp_ajax_nopriv_ajaxDataSubmit', 'campus_shipping_ajaxDataSubmit'); // For not logged in users
function campus_shipping_ajaxDataSubmit(){
    global $wpdb;
    $id = $_POST['id'];
    echo $id;
    die;
}

add_filter('post_row_actions', 'campus_shipping_expand_quick_edit_link', 10, 2);
function campus_shipping_expand_quick_edit_link($actions, $post) {
    global $current_screen;
    if (($current_screen->post_type != 'product'))
    return $actions;
    $nonce = wp_create_nonce( 'shipping_type'.$post->ID);
    $shipping_type = get_post_meta( $post->ID, 'shipping_type', TRUE);
    $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" title="';
    $actions['inline hide-if-no-js'] .= esc_attr( __( 'Edit this item inline' ) ) . '"';
    $actions['inline hide-if-no-js'] .= " onclick=\"set_custom_value('{$shipping_type}')\" >";
    $actions['inline hide-if-no-js'] .= __( 'Quick Edit' );
    $actions['inline hide-if-no-js'] .= '</a>';
    return $actions;
}

add_action('save_post', 'campus_shipping_save_quick_edit_data');
function campus_shipping_save_quick_edit_data($post_id) { // print_r($_REQUEST);
    // verify if this is an auto save routine.
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
    return $post_id;
    // Authentication passed now we save the data
    if ('product' == $_POST['post_type']) {
        $my_fieldvalue = esc_attr($_REQUEST['shipping_type']);
        if (isset($_REQUEST['shipping_type']) && $my_fieldvalue != '')
        {
            update_post_meta( $post_id, 'shipping_type',intval($_REQUEST['shipping_type']));
        }
    }
    return $my_fieldvalue;
}
?>

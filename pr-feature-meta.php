<?php
/*
** Meta boxes for features
*/


function pr_feature_meta_add()
{
	add_meta_box( 'feature-box', 'Featured site link', 'pr_feature_meta_cb', 'pr_feature', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'pr_feature_meta_add' );

function pr_feature_meta_cb( $post )
{

	wp_nonce_field( 'pr_feature_inner', 'pr_feature_meta_nonce' );
	$values = get_post_custom( $post->ID );
	$linkedto = isset( $values['pr_feature_link'] ) ? esc_url( $values['pr_feature_link'][0] ) : '';
  $order = isset( $values['pr_feature_order'] ) ? sanitize_text_field( $values['pr_feature_order'][0] ) : '';

	echo 	'<p><label for="pr_feature_link">' 
      . __('Feature links to: ', 'xo')
      . '</label>' 
      . '<input type="text" name="pr_feature_link" id="pr_feature_link" value="'
      .  $linkedto 
      . '" /></p>';

 // order selector
  echo '<p><label for="pr_feature_order">' . __('Importance: ', 'xo') . '</label>';
  echo '<select name="pr_feature_order" id="pr_feature_order">';
  for($i = 1; $i <= 10; $i++ ){
    $selected = "";
    if($i == intval($order)) {
      $selected = 'selected';

    }
    echo '<option value="' . $i . '"' . $selected . '>' . $i . '</option>';
  }
  echo '</select>';
  echo '<span class="help-text">  <i>' . __('Importance 1 items are shown first in the site', 'xo') . '</i></span>';

}

/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 * @package xo
 */
function pr_feature_box_save( $post_id ) 
{

  /*
   * We need to verify this came from the our screen and with proper authorization,
   * because save_post can be triggered at other times.
   */
  
  // Check if our nonce is set.
  if ( ! isset( $_POST['pr_feature_meta_nonce'] ) )
    return $post_id;

  $nonce = $_POST['pr_feature_meta_nonce'];

  // Verify that the nonce is valid.
  if ( ! wp_verify_nonce( $nonce, 'pr_feature_inner' ) )
      return $post_id;
 
  // If this is an autosave, our form has not been submitted, so we don't want to do anything.
  if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
      return $post_id;

  // Check the user's permissions.
  if ( 'page' == $_POST['post_type'] ) {

    if ( ! current_user_can( 'edit_page', $post_id ) )
        return $post_id;
  
  } else {

    if ( ! current_user_can( 'edit_post', $post_id ) )
        return $post_id;
  }

  /* OK, its safe for us to save the data now. */
  $data = esc_url( $_POST['pr_feature_link']);

	if( isset( $_POST['pr_feature_link'] ) ) {
		update_post_meta( $post_id, 'pr_feature_link', $data  );
	}

  if( isset( $_POST['pr_feature_order'] ) ) {
    update_post_meta( $post_id, 'pr_feature_order', sanitize_text_field( $_POST['pr_feature_order'] )  );
  }

}

add_action( 'save_post', 'pr_feature_box_save' );
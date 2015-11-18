<?php
/**  Flatsome-Child functions and definitions
 *
 *
 */


add_action( 'woocommerce_email_after_order_table', 'add_link_back_to_order', 10, 2 );
function add_link_back_to_order( $order, $is_admin ) {

	//print_r($order);
	//echo $order->get_status();

    // Only for admin emails
    if ( ! $is_admin ) {
        return;
    }

    // Open the section with a paragraph so it is separated from the other content
    $link = '<p>';

    // Add the anchor link with the admin path to the order page
    $link .= '<a href="'. admin_url( 'post.php?post=' . absint( $order->id ) . '&action=edit' ) .'" >';

    // Clickable text
    $link .= __( 'Click here to go to the order page', 'your_domain' );

    // Close the link
    $link .= '</a>';

    // Close the paragraph
    $link .= '</p>';

    // Return the link into the email
    echo $link;

}
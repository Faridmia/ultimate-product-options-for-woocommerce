<?php
/**
 * Filter the allowed HTML tags for a specific context.
 *
 * This function extends the list of allowed HTML tags and attributes for specific contexts
 * using the `wp_kses` function. The contexts can be 'upow_kses' for general HTML content
 * or 'upow_img' for image-specific tags.
 *
 * @param array  $upow_tags    The default allowed HTML tags and attributes.
 * @param string $upow_context The context in which the HTML is being filtered.
 * @return array The modified list of allowed HTML tags and attributes.
 *
 * @since 1.0.0
 */
function upow_kses_allowed_html($upow_tags, $upow_context)
{
    switch ($upow_context) {
        case 'upow_kses':
            $upow_tags = array(
                'div'    => array(
                    'class' => array(),
                ),
                'ul'     => array(
                    'class' => array(),
                ),
                'li'     => array(),
                'span'   => array(
                    'class' => array(),
                ),
                'a'      => array(
                    'href'  => array(),
                    'class' => array(),
                ),
                'i'      => array(
                    'class' => array(),
                ),
                'p'      => array(),
                'em'     => array(),
                'br'     => array(),
                'strong' => array(),
                'h1'     => array(),
                'h2'     => array(),
                'h3'     => array(),
                'h4'     => array(),
                'h5'     => array(),
                'h6'     => array(),
                'del'    => array(),
                'ins'    => array(),
            );
            return $upow_tags;
        case 'upow_img':
            $upow_tags = array(
                'img' => array(
                    'class'  => array(),
                    'height' => array(),
                    'width'  => array(),
                    'src'    => array(),
                    'alt'    => array(),
                ),
            );
            return $upow_tags;
        default:
            return $upow_tags;
    }
}

function sanitize_upow_custom_field_items_data( $field ){
    if( is_array( $field ) ) {
        $value = array_map( 'sanitize_text_field', $field );
    } else {
        $value = sanitize_text_field( $field );
    }
    return $value;
}

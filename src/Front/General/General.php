<?php
namespace Ultimate\Upow\Front\General;
use Ultimate\Upow\Traitval\Traitval;

/**
 * Class Front
 * 
 * Handles the front-end functionality for the Ultimate Product Options For WooCommerce plugin.
 */
class General
{
    use Traitval;

    /**
     * Add custom CSS to the front-end
     */
    public function add_custom_css()
    {
        $upow_custom_css = get_option_upow('upow_custom_css', false);
        if ($upow_custom_css) {
            wp_register_style('upow_custom_css', false);
            wp_enqueue_style('upow_custom_css');
            wp_add_inline_style('upow_custom_css', $upow_custom_css);
        }
    }
    /**
     * Add custom js to the front-end
     */
    public function add_custom_js()
    {
        $upow_custom_js = get_option_upow('upow_custom_js', false);
        if ($upow_custom_js) {
            echo "<script>(function ($) { 'use strict';" . wp_unslash($upow_custom_js) . "})(jQuery);</script>";
        }
    }

     /**
     * Add google analytics code
     */
    public function add_google_analytics()
    {
        $upow_google_analytics = get_option_upow('upow_google_analytics', false);
        if ($upow_google_analytics) {
            echo wp_unslash($upow_google_analytics);
        }
    }

    /**
     * Add custom CSS to the front-end
     */
    public function add_generate_custom_css()
    {
        $styles = [
            ".upow-flash-sale-offer-title" => [
                'background-color' => get_option_upow('offer_title_bg_color', '#222'),
                'color'            => get_option_upow('offer_title_text_color', '#fff')
            ],
            ".upow-flash-sale-inner-item .upow-flash-sale-timer-item-number" => [
                'background-color' => get_option_upow('offer_countdown_bg_color', '#222'),
                'color'            => get_option_upow('offer_countdown_text_color', '#fff')
            ],
            ".upow-flash-sale-inner-item .upow-flash-sale-timer-item-title" => [
                'color' => get_option_upow('offer_countdown_item_title_color', '#444')
            ],
            ".product-details-content .product-details .upow-extra-wrap-title" => [
                'color' => get_option_upow('upow_addon_title_text_color', '#444'),
                'font-size' => get_option_upow('upow_addon_title_text_size') ? get_option_upow('upow_addon_title_text_size') . 'px' : ''
            ],
            ".upow-extra-acc .upow-extra-acc-item .upow-extra-title-tab" => [
                'background-color' => get_option_upow('addon_item_title_bg_color', '#efecec69'),
                'color'            => get_option_upow('addon_item_title_text_color', '#777'),
                'padding'          => get_option_upow('upow_addon_title_padding')
            ],
            ".upow-extra-options" => [
                'color' => get_option_upow('addon_item_label_text_color', '#777')
            ],
            ".upow-extra-addons-pricing-info" => [
                'background-color' => get_option_upow('addon_price_info_bg_color', '#efecec69')
            ],
            ".upow-extra-addons-pricing-info p" => [
                'color'            => get_option_upow('addon_price_info_text_price_color', '#777'),
            ],
        ];

        // Conditionally set the border-bottom style
        $border_color = get_option_upow('addon_price_info_border_bottom_color', '#ebebeb');
        if (!empty($border_color)) {
            $styles[".upow-extra-addons-pricing-info p"]['border-bottom'] = '1px solid ' . $border_color;
        }

        $custom_style = $this->generate_css($styles);

        if (!empty($custom_style)) {
            wp_register_style('upow_custom_css_global_options', false);
            wp_enqueue_style('upow_custom_css_global_options');
            wp_add_inline_style('upow_custom_css_global_options', $custom_style);
        }
    }

    /**
     * Generate custom CSS from styles array
     *
     * @param array $styles Array of CSS rules and values.
     * @return string Generated CSS.
     */
    private function generate_css(array $styles)
    {
        $css = '';

        foreach ( $styles as $selector => $properties ) {
            $css .= $selector . ' {';

            foreach ( $properties as $property => $value ) {
                if ( $value !== '' ) {
                    $css .= "{$property}: {$value}; ";
                }
            }

            $css .= '} ';
        }

        return $css;
    }
}
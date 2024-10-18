<?php
namespace Ultimate\Upow\Front\FlashSale;
use Ultimate\Upow\Traitval\Traitval;

class FlashSale
{
    use Traitval;

    public $flash_sale;

    public function __construct()
    {
        $sales_flash_pos = get_option('upow_countdown_position');
        add_action( $sales_flash_pos, [$this, 'render_countdown'] );
        add_action('wp_enqueue_scripts', [$this, 'enqueue_flash_sale_assets']);
        $this->flash_sale = new PriceOverride();
    }

    public function enqueue_flash_sale_assets()
    {
        wp_enqueue_style('upow-flashsale-css', UPOW_CORE_ASSETS . 'src/Front/FlashSale/css/flash-sale.css', [], UPOW_VERSION);
        wp_enqueue_script('jquery-countdown-min-js', UPOW_CORE_ASSETS . 'src/Front/FlashSale/js/jquery.countdown.js', ['jquery'], UPOW_VERSION, true);
        wp_enqueue_script('upow-flashsale-script', UPOW_CORE_ASSETS . 'src/Front/FlashSale/js/countdown.js', ['jquery'], UPOW_VERSION, true);
    }

    public function render_countdown()
    {
        $current_product_id = get_the_ID();
        if ( !$this->is_flash_sale_enabled() || !$this->is_product_in_flash_sale( $current_product_id ) ) {
            return;
        }

        $schedule = $this->get_flash_sale_schedule( $current_product_id );
        if ( $this->should_render_countdown( $schedule, $current_product_id ) ) {
            $this->output_countdown( $current_product_id, $schedule['start'], $schedule['end'] );
        }
    }

    private function is_flash_sale_enabled()
    {
        return get_option('upow_enable_flash_sale_here', true) == 1;
    }

    private function get_flash_sale_settings()
    {
        return get_option('upow_flash_sale_settings', []);
    }


    private function is_product_in_flash_sale( $product_id )
    {
        $settings = $this->get_flash_sale_settings();

        foreach ( $settings as $product_data ) {

            foreach ( $product_data['upow_flashsale_product'] as $data ) {
                if ( in_array($product_id, $data['select_product'] ?? []) || $data['apply_all_product'] == 1 ) {
                    return true;
                }
            }

        }

        return false;
    }

    private function get_flash_sale_schedule( $product_id )
    {
        $settings = $this->get_flash_sale_settings();
        $schedule = ['start' => '', 'end' => ''];

        foreach ( $settings as $product_data ) {

            foreach ( $product_data['upow_flashsale_product'] as $data ) {

                if ( $data['apply_all_product'] == 1 || in_array( $product_id, $data['select_product'] ?? [] ) ) {
                    $schedule['start'] = $data['flashsale_start_date'] ?? '';
                    $schedule['end'] = $data['flashsale_end_date'] ?? '';
                    return $schedule;
                }

            }

        }

        return $schedule;
    }

    private function should_render_countdown( $schedule, $product_id )
    {
        $end_date       = strtotime($schedule['end']);
        $start_date     = strtotime($schedule['start']);
        $today          = strtotime('today');

        if ( $end_date >= $today && $start_date <= $today ) {

            $exclude_products = $this->get_exclude_products();
            return !in_array($product_id, $exclude_products);

        }

        return false;
    }

    private function get_exclude_products()
    {
        $settings         = $this->get_flash_sale_settings();
        $exclude_products = [];

        foreach ( $settings as $product_data ) {
            foreach ( $product_data['upow_flashsale_product'] as $data ) {
                if (!empty( $data['exclude_product'] ) ) {
                    $exclude_products = array_merge( $exclude_products, $data['exclude_product'] );
                }
            }
        }

        return $exclude_products;
    }

    private function output_countdown( $product_id, $start_date, $end_date )
    {
        date_default_timezone_set('Asia/Dhaka');
        $layout_style = get_option('upow_countdown_style');
        $timer_title = get_option('upow_countdown_timer_title');
        $heading = !empty($timer_title) ? "<p class='upow-flash-sale-offer-title'>{$timer_title}</p>" : '';
        $rand_id = rand(10, 1000);
        $output = '';

        ob_start();
        if ($layout_style == 'layout_style_1') {
            $output .= "<div class='upow-flash-sale-wrapper' data-startdate='" . date('m-d-Y', strtotime($start_date)) . "'>"
                . wp_kses_post($heading)
                . "<div class='upow-flash-sale-item' data-id='upow-flash-sale-item-{$rand_id}' data-date='" . date('m-d-Y', strtotime($end_date)) . "'>"
                . "<div id='upow-flash-sale-item-{$rand_id}' class='upow-flash-sale-inner-item'></div>"
                . "</div></div>";
        } elseif ($layout_style == 'layout_style_2') {
            $output .= "<div class='upow-flash-sale-banner-text' data-startdate='" . date('m-d-Y', strtotime($start_date)) . "'>"
                . wp_kses_post($heading)
                . "<div id='countdown' data-cdtdate='" . date('m-d-Y', strtotime($end_date)) . "' class='upow-flash-sale-countdown'></div>"
                . "</div>";
        }

        $output .= ob_get_clean();
        printf('%s', do_shortcode( $output ) );
    }
}

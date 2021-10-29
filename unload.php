<?php

/**
 * Plugin Name: unload
 * Description: Плагин выгрузки товаров из woocommerce
 * Version: 1.0.0
 * Author: Roman
 */

defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_submenu_page('woocommerce', 'Выгрузка каталога', 'Выгрузка', 'manage_options', 'unload', 'page_view');
});

add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script('unload', plugins_url('unload/unload.js'), '', '1.0', true);
    wp_enqueue_style( 'unload', plugins_url('unload/unload.css') );
}, 99);

function page_view()
{
?>

    <h2><?php echo get_admin_page_title() ?></h2>

    <form method="post" id="form">

        <div>
            <select name="exist">
                <option disabled selected>Наличие товара</option>
                <option>В наличии</option>
            </select>
        </div>

        <div>
            <span>Цена</span>
            <input type="text" name="pricefrom"> - <input type="text" name="pticeto">
        </div>

        <? submit_button('Получить xml') ?>
    </form>

<?
}

function unload_func()
{
    global $wpdb;

    class Product {
        public $id;
        public $title;
        public $available;
        public $price;
        public $order;

        function __construct($id, $title) {
            $this->id = $id;
            $this->title = $title;
            $this->available = (get_post_meta($id, '_stock_status', true) === 'instock') ? 'true' : 'false';
            $this->price = get_post_meta($id, '_price', true);
            $this->order = get_post_meta($id, 'total_sales', true);
        }
    }

    $products = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='product'");

    $xml = new DOMDocument('1.0', 'UTF-8');

    $xml->formatOutput = true;

    $catalog = $xml->createElement("Каталог");

    $catalog = $xml->appendChild($catalog);

    foreach ($products as $p) {

        $prod = new Product($p->ID, $p->post_title);

        $product = $xml->createElement("Товар");

        $product = $catalog->appendChild($product);

        $product->setAttribute("ИД", $prod->id);

        $product_name = $xml->createElement("Наименование", $prod->title);

        $product_name = $product->appendChild($product_name);

        $product_availability = $xml->createElement("Наличие", $prod->available);

        $product_availability = $product->appendChild($product_availability);

        $product_price = $xml->createElement("Стоимость", $prod->price);

        $product_price = $product->appendChild($product_price);

        $product_order = $xml->createElement("Заказы", $prod->order);

        $product_order = $product->appendChild($product_order);
    }

    echo $xml->save('php://output');

    wp_die();
}

add_action('wp_ajax_nopriv_unload_func', 'unload_func');
add_action('wp_ajax_unload_func', 'unload_func');

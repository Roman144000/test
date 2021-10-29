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
}, 99);

function page_view()
{
?>

    <h2><?php echo get_admin_page_title() ?></h2>

    <form method="post" id="form">
        <input type="hidden" name="xml" value="1">

        <div style="margin-bottom: 30px;">
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

    $products = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='product'");

    $xml = new DOMDocument('1.0', 'UTF-8');

    $xml->formatOutput = true;

    $catalog = $xml->createElement("Каталог");

    $catalog = $xml->appendChild($catalog);

    foreach ($products as $p) {

        $id = $p->ID;

        $availability = (get_post_meta($id, '_stock_status', true) === 'instock') ? 'true' : 'false';

        $product = $xml->createElement("Товар");

        $product = $catalog->appendChild($product);

        $product->setAttribute("ИД", $id);

        $product_name = $xml->createElement("Наименование", $p->post_title);

        $product_name = $product->appendChild($product_name);

        $product_availability = $xml->createElement("Наличие", $availability);

        $product_availability = $product->appendChild($product_availability);

        $product_price = $xml->createElement("Стоимость", get_post_meta($id, '_price', true));

        $product_price = $product->appendChild($product_price);

        $product_order = $xml->createElement("Заказы", get_post_meta($id, 'total_sales', true));

        $product_order = $product->appendChild($product_order);
    }

    header('Content-Type: text/xml');

    header('Content-Disposition: attachment; filename="catalog.xml"');

    $xml->save('php://output');

    wp_die();
}

add_action('wp_ajax_nopriv_unload_func', 'unload_func');
add_action('wp_ajax_unload_func', 'unload_func');

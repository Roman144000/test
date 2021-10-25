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

function page_view()
{
?>

    <h2><?php echo get_admin_page_title() ?></h2>

    <form method="post">
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

if (isset($_POST['xml'])) {
    global $wpdb;

    $products = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='product'");

    $xml = new DOMDocument('1.0', 'UTF-8');

    $xml->formatOutput = true;

    $catalog = $xml->createElement("каталог");

    $catalog = $xml->appendChild($catalog);

    foreach ($products as $p) {

        $product = $xml->createElement("товар");

        $product = $catalog->appendChild($product);

        $product_id = $xml->createElement("ид", $p->ID);

        $product_id = $product->appendChild($product_id);

        $product_name = $xml->createElement("название", $p->post_title);

        $product_name = $product->appendChild($product_name);
    }

    header('Content-Type: text/xml');

    header('Content-Disposition: attachment; filename="catalog.xml"');

    $xml->save('php://output');
}

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
    wp_enqueue_style('unload', plugins_url('unload/unload.css'));
}, 99);

//class Product
require_once('un_Product.php');

//function create page for plugin
function page_view()
{
?>
    <h2><?php echo get_admin_page_title() ?></h2>
    <form id="form">
        <div>
            <select name="exist">
                <option value="all" disabled selected>Наличие товара</option>
                <option value="instock">В наличии</option>
                <option value="onbackorder">Предзаказ</option>
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

// function create xml file
function unload_func()
{
    if (!empty($_POST['exist'])) {
        $current_exist = $_POST['exist'];
    }
    if (!empty($_POST['pricefrom'])) {
        $current_min_price = $_POST['pricefrom'];
    }
    if (!empty($_POST['pticeto'])) {
        $current_max_price = $_POST['pticeto'];
    }
    global $wpdb;
    $products = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='product'");
    if ($products) {
        header("Content-type: text/xml");
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $xml->preserveWhiteSpace = FALSE;
        $catalog = $xml->createElement("Каталог");
        $catalog = $xml->appendChild($catalog);
        foreach ($products as $p) {
            $prod = new Product($p->ID, $p->post_title);
            if ($prod->available === $current_exist || !isset($current_exist)) {
                if ($prod->price > $current_min_price || !isset($current_min_price)) {
                    if ($prod->price < $current_max_price || !isset($current_max_price)) {
                        $product = $xml->createElement("Товар");
                        $product = $catalog->appendChild($product);
                        $product->setAttribute("ИД", $prod->id);
                        $product_name = $xml->createElement("Наименование", $prod->title);
                        $product_name = $product->appendChild($product_name);
                        $product_availability = $xml->createElement("Наличие", ($prod->available === 'instock') ? 'true' : 'false');
                        $product_availability = $product->appendChild($product_availability);
                        $product_price = $xml->createElement("Стоимость", $prod->price);
                        $product_price = $product->appendChild($product_price);
                        $product_order = $xml->createElement("Заказы", $prod->order);
                        $product_order = $product->appendChild($product_order);
                    }
                }
            }
        }
        echo $xml->save('php://output');
    }
    wp_die();
}

add_action('wp_ajax_nopriv_unload_func', 'unload_func');
add_action('wp_ajax_unload_func', 'unload_func');

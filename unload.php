<?php
/**
* Plugin Name: unload
* Description: Плагин выгрузки товаров из woocommerce
* Version: 1.0.0
* Author: Roman
*/

defined( 'ABSPATH' ) || exit;

add_action('admin_menu', function(){
    add_menu_page( 'Выгрузка каталога', 'Выгрузка', 'manage_options', 'unload', 'page_view', '', 6 );
} );

function page_view() {
?>

	<h2><?php echo get_admin_page_title() ?></h2>

	<form method="post">
		<input type="hidden" name="xml" value="1">

		<?submit_button('Получить xml')?>
	</form>

<?
}

if(isset($_POST['xml'])) {
	global $wpdb;

	$products = $wpdb->get_results("SELECT product_id FROM info_wc_product_meta_lookup");

    $xml = new DOMDocument('1.0', 'UTF-8');

	$xml->formatOutput = true;

    $catalog = $xml->createElement("каталог");
	
    $catalog = $xml->appendChild($catalog);

	foreach($products as $product => $value) {

		$id = $value->product_id;

		$name = $wpdb->get_var($wpdb->prepare("SELECT post_title FROM info_posts WHERE ID = %s", $id));

		$product = $xml->createElement("товар");

		$product = $catalog->appendChild($product);

		$product_id = $xml->createElement("ид", $id);

		$product_id = $product->appendChild($product_id);

		$product_name = $xml->createElement("название", $name);

		$product_name = $product->appendChild($product_name);

	}

    $xml->save(ABSPATH . '/catalog.xml');
}

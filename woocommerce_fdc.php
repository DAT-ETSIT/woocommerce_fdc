<?php
/*
Plugin Name: WooCommerce FDC
Description: Plugin que envía los detalles del pedido a valiDAT cuando el pedido contiene uno o más vasos.
Version: 1.0
Author: Álvaro Rosado González
Author URI: https://github.com/aLVaRoZz01/
*/

// Hook para ejecutar la función cuando se completa el pago
add_action('woocommerce_thankyou', 'enviar_detalles_pedido');
function enviar_detalles_pedido($order_id) {
    // Definir las IDs específicas de los productos que quieres verificar
    $ids_productos_especificos = array(9642, 9703);

    // Obtener el objeto del pedido
    $order = wc_get_order($order_id);
    
	// Obtener el email del usuario actual
    $user = wp_get_current_user();
    $user_email = $user->user_email;

    $product_found = false;

    $cart = [];
    
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id(); // ID del producto
        $product_quantity = $item->get_quantity(); // Cantidad del producto
        
        // Verificar si el ID del producto coincide con alguna de las IDs específicas que buscamos
        if (in_array($product_id, $ids_productos_especificos)) {
            $product_found = true;
        }

        $cart[] = [
            'product_id' => $product_id,
            'quantity' => $product_quantity
        ];
    }

    if ($product_found) {
        $data = [
            'email' => $user_email,
            'cart' => $cart
        ];

        $data_json = json_encode($data);

        //URL de la API externa
        $url = '';

        $token = ''; 

        $response = wp_remote_post($url, array(
            'method'    => 'POST',
            'body'      => $data_json,
            'headers'   => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ),
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('Error al enviar los detalles del pedido: ' . $error_message);
        } else {
            error_log('Detalles del pedido enviados exitosamente.');
        }
    }
}

?>

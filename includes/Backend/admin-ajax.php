<?php

/**
 * Dokan Custom Fields Admin Ajax
 *
 * @since 2.4.0
 */

 add_action('wp_ajax_vendor_excel_upload', 'handle_vendor_excel_upload');

function handle_vendor_excel_upload() {
    // Verifique o nonce
    if ( !isset($_POST['vendor_excel_upload_nonce']) || !wp_verify_nonce($_POST['vendor_excel_upload_nonce'], 'vendor_excel_upload_action') ) {
        wp_send_json_error(array('message' => 'Erro de segurança: nonce inválido.'));
    }

    // Agora pode processar o arquivo Excel
    if (!empty($_FILES['excel_file']['name'])) {
        $file = $_FILES['excel_file'];
        // Lógica para processar o arquivo Excel e importar os vendedores
        // ...
    } else {
        wp_send_json_error(array('message' => 'Nenhum arquivo enviado.'));
    }

    wp_send_json_success(array('message' => 'Vendedores importados com sucesso.'));
}

?>
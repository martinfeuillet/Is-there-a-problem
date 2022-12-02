<?php



require_once plugin_dir_path(__FILE__) . 'inc/code-error.php';

class ItapPageArchive {
    function __construct() {
        $this->itap_partial_archive();
    }

    function itap_archiveDisplayTab($error) {
        $allowed_html = array(
            'div' => array(
                'class' => array()
            ),
            'span' => array(
                'class' => array()
            )
        );
?>
        <tr>
            <td><?php echo esc_html($error['productId']); ?></td>
            <td><?php echo esc_html($error['product_title']); ?></td>
            <td><a target="_blank" href="<?php echo esc_url($error['product_url']); ?>">click</a></td>
            <td><?php echo wp_kses($error['problem'], $allowed_html); ?></td>
            <td><?php echo esc_html($error['product_author']); ?></td>
            <td><input type="checkbox" class="itap_checkbox" checked="true" name="archiver" class="archiver" value="<?php echo esc_attr($error['uniqId']); ?>"></td>
        </tr>
<?php
    }

    function itap_get_archive_from_database() {
        global $wpdb;
        include plugin_dir_path(__FILE__) . 'inc/code-error.php';
        $table_name = $wpdb->prefix . 'itap_archive';
        $query = "SELECT * FROM $table_name ";
        $results = $wpdb->get_results($query, ARRAY_A);
        $infoProduct = array();
        foreach ($results as $result => $value) {
            $uniqId = $value['uniqId'];
            $codeError = substr($uniqId, -4);
            $productId = substr($uniqId, 0, -4);
            $product = wc_get_product($productId);
            if ($product) {
                array_push($infoProduct, array(
                    'uniqId' => $uniqId,
                    'productId' => $product->get_id(),
                    'product_title' => $product->get_title(),
                    'product_url' => get_edit_post_link($productId),
                    'product_author' => get_the_author_meta('display_name', get_post_field('post_author', $product->get_id())),
                    'problem' => $codeErrorFile[$codeError]
                ));
            }
        }
        return $infoProduct;
    }


    function itap_partial_archive() {
        if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem_archive') {
            require_once plugin_dir_path(__FILE__) . 'partials/itap-archive-display.php';
        }
    }
}

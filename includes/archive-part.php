<?php
include './code-error.php';

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
    include 'code-error.php';
    $table_name = $wpdb->prefix . 'itap_archive';
    $query = "SELECT * FROM $table_name ";
    $results = $wpdb->get_results($query, ARRAY_A);
    $infoProduct = array();
    foreach ($results as $result => $value) {
        $uniqId = $value['uniqId'];
        $codeError = substr($uniqId, -4);
        $productId = substr($uniqId, 0, -4);
        $product = wc_get_product($productId);
        array_push($infoProduct, array(
            'uniqId' => $uniqId,
            'productId' => $productId,
            'product_title' => $product->get_title(),
            'product_url' => get_edit_post_link($productId),
            'product_author' => get_the_author_meta('display_name', $product->post->post_author),
            'problem' => $codeErrorFile[$codeError]
        ));
    }
    return $infoProduct;
}



if (isset($_GET['page']) && $_GET['page'] == 'is_there_a_problem_archive') :
?>
    <div class="wrap is-there-a-problem-container">
        <p>Problèmes archivés</p>
        <table class="table-plugin">
            <thead>
                <tr class="thead-plugin">
                    <th class="thead-plugin-little">Id Produit</th>
                    <th class="thead-plugin-middle">Nom Produit</th>
                    <th class="thead-plugin-little">Url Produit</th>
                    <th class="thead-plugin-big">Problème remonté</th>
                    <th class="thead-plugin-little">Nom intégrateur</th>
                    <th class="thead-plugin-little">archiver</th>
                </tr>
            </thead>
            <tbody class="tbody-plugin">
                <?php
                $archive = itap_get_archive_from_database();
                foreach ($archive as $error) {
                    itap_archiveDisplayTab($error);
                }
                ?>
            </tbody>
        </table>
    </div>
<?php
endif;
?>
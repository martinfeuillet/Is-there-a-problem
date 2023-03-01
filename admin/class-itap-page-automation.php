<?php

class ItapPageAutomation
{
    // method to change Rank Math primary category on product
    function itap_change_primary_category(): void
    {
        $product_id = $_POST['product_id'];
        $new_cat_id = $_POST['new_cat_id'];
        $change_or_ignore = $_POST['change_or_ignore'];
        if ($change_or_ignore != 'ignore') {
            update_post_meta($product_id, 'rank_math_primary_product_cat', $new_cat_id);
        }
        update_post_meta($product_id, 'itap_ignore_primary_cat', true);
        wp_send_json_success();

    }

    public function itap_partials_automation(): void
    {
        ?>
        <div class="wrap">
            <h1>Automation</h1>
            <?php
            if ($this->itap_fix_primary_cat()) { ?>
                <h3>Produit appartenant à plusieurs catégories dont la catégorie primaire n'est pas la derniere
                    enfant</h3>
                <div class="select_primary_textarea">
                    <table class="select_primary_cat_table">
                        <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Catégorie primaire</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <?php
                        foreach ($this->itap_fix_primary_cat() as $product) {
                            ?>
                            <tr class="row">
                                <td>
                                    <a target="_blank"
                                       href="<?php echo $product['product_link'] ?>"><?php echo $product['product_name']; ?></a>
                                </td>
                                <td>
                                    <select name="select_primary_cat" id="select_primary_cat">
                                        <?php
                                        foreach ($product['primary_product_cat_name'] as $cat_id => $cat_name) {
                                            $selected = ($cat_id == $product['primary_product_cat']) ? 'selected' : '';
                                            echo '<option value="' . $cat_id . '" ' . $selected . '>' . $cat_name . '</option>';
                                        }
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <button data-choice="validate"
                                            data-product-id="<?php echo $product['product_id'] ?>"
                                            class="button-primary button_choice">Valider
                                    </button>
                                    <button data-choice="ignore" data-product-id="<?php echo $product['product_id'] ?>"
                                            class="button button_choice ">ignorer
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }

                        ?>
                    </table>
                </div>
                <?php
            } // end if fix primary cat
            ?>

        </div>
        <?php
    }

    function itap_fix_primary_cat(): array
    {
        $maybe_product_problem = array();
        $product_id_and_cat_names = array();
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        );
        $products = new WP_Query($args);
        $products = $products->posts;
        foreach ($products as $product) {
            $product_id_and_cat_names = array();
            $product_categories_ids = wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'ids'));
            // make an associative array with cat_id => cat_name
            foreach ($product_categories_ids as $product_cat_id) {
                $product_id_and_cat_names[$product_cat_id] = get_term($product_cat_id)->name;
            }

            if (count($product_categories_ids) < 2) {
                continue;
            }
            $primary_product_cat = get_post_meta($product->ID, 'rank_math_primary_product_cat', true);
            $primary_product_cat_parents = get_ancestors($primary_product_cat, 'product_cat');
            $primary_product_cat_children = get_term_children($primary_product_cat, 'product_cat');
            if (!empty($primary_product_cat_children) && !get_post_meta($product->ID, 'itap_ignore_primary_cat', true)) {
                $maybe_product_problem[] = array(
                    'product_id' => $product->ID,
                    "product_link" => get_edit_post_link($product->ID),
                    'product_name' => $product->post_title,
                    'primary_product_cat' => $primary_product_cat,
                    'primary_product_cat_name' => $product_id_and_cat_names,
                );
            }

        }
        return $maybe_product_problem;
    }

}


<div class="wrap">
    <h1>Automation</h1>
    <?php
    if ( $this->itap_fix_primary_cat() ) : ?>
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
                foreach ( $this->itap_fix_primary_cat() as $product ) {
                    ?>
                    <tr class="row">
                        <td>
                            <a target="_blank"
                               href="<?php echo $product['product_link'] ?>"><?php echo $product['product_name']; ?></a>
                        </td>
                        <td>
                            <select name="select_primary_cat" id="select_primary_cat">
                                <?php
                                foreach ( $product['primary_product_cat_name'] as $cat_id => $cat_name ) {
                                    $selected = ( $cat_id == $product['primary_product_cat'] ) ? 'selected' : '';
                                    printf( "<option value='%s' %s>%s</option>" , $cat_id , $selected , $cat_name );
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
                <?php } ?>
            </table>
        </div>
    <?php endif; ?> <!--    end if fix primary cat-->
</div>
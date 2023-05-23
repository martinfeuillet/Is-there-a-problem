<?php
$itap_settings = get_option( 'itap_settings' );
?>
<div class="wrap">
    <h1>Reglages</h1>
    <div class="itap-settings">
        <h3>Champs pris en compte dans le compte des mots par produit</h3>
        <div>
            <input type="checkbox" name="short_desc"
                   id="short_desc" <?php echo $itap_settings['short_desc'] ? 'checked' : '' ?>>
            <label for="desc1">short description</label>
        </div>
        <div>
            <input type="checkbox" name="desc1" id="desc1" <?php echo $itap_settings['desc1'] ? 'checked' : '' ?>>
            <label for="desc1">Description 1</label>
        </div>
        <div>
            <input type="checkbox" name="desc2" id="desc2" <?php echo $itap_settings['desc2'] ? 'checked' : '' ?>>
            <label for="desc2">Description 2</label>
        </div>
        <div>
            <input type="checkbox" name="desc3" id="desc3" <?php echo $itap_settings['desc3'] ? 'checked' : '' ?>>
            <label for="desc3">Description 3</label>
        </div>
        <div>
            <input type="checkbox" name="desc_seo"
                   id="desc_seo" <?php echo $itap_settings['desc_seo'] ? 'checked' : '' ?>>
            <label for="desc_seo">Description SEO</label>
        </div>
        <div>
            <input type="checkbox" name="custom_field"
                   id="itap_custom_field" <?php echo $itap_settings['custom_field'] ? 'checked' : '' ?>>
            <label for="custom_field">Champs personnalisés</label>
            <input type="text"
                   style="<?php echo $itap_settings['custom_field'] ? 'display: block;' : 'display: none;' ?> margin-top: 1rem;"
                   class="custom_field_input" id="custom_field_input_1" name="custom_field_input_1"
                   placeholder="custom-1" value="<?php echo $itap_settings['custom_field_input_1'] ?? null ?>">

            <input type="text"
                   style="<?php echo $itap_settings['custom_field'] ? 'display: block;' : 'display: none;' ?>"
                   class="custom_field_input" id="custom_field_input_2" name="custom_field_input_2"
                   placeholder="custom-2" value="<?php echo $itap_settings['custom_field_input_2'] ?? null ?>">

            <input type="text"
                   style="<?php echo $itap_settings['custom_field'] ? 'display: block;' : 'display: none;' ?>"
                   class="custom_field_input" id="custom_field_input_3" name="custom_field_input_3"
                   placeholder="custom-3" value="<?php echo $itap_settings['custom_field_input_3'] ?? null ?>">
        </div>
        <div>
            <h3>Champs pris en compte au niveau des images</h3>
            <div>
                <input type="checkbox" name="itap_img_1"
                       id="itap_img_1" <?php echo $itap_settings['itap_img_1'] ? 'checked' : '' ?>>
                <label for="itap_img_1">Image-1</label>
                <div class="itap_img_1_label"
                     style="display: <?php echo $itap_settings['itap_img_1_label'] ? "block" : "none" ?>">
                    <label for="itap_img_1_label">Nom du meta-field </label>
                    <input type="text" name="itap_img_1_label" id="itap_img_1_label"
                           value="<?php echo $itap_settings['itap_img_1_label'] ?: "" ?>">
                </div>
            </div>
            <div>
                <input type="checkbox" name="itap_img_2"
                       id="itap_img_2" <?php echo $itap_settings['itap_img_2'] ? 'checked' : '' ?>>
                <label for="itap_img_2">Image-2</label>
                <div class="itap_img_2_label"
                     style="display: <?php echo $itap_settings['itap_img_2_label'] ? "block" : "none" ?>">
                    <label for="itap_img_1_label">Nom du meta-field </label>
                    <input type="text" name="itap_img_2_label" id="itap_img_2_label"
                           value="<?php echo $itap_settings['itap_img_2_label'] ?: "" ?>">
                </div>
                <div>
                    <input type="checkbox" name="itap_img_3"
                           id="itap_img_3" <?php echo $itap_settings['itap_img_3'] ? 'checked' : '' ?>>
                    <label for="itap_img_3">Image-3</label>
                    <div class="itap_img_3_label"
                         style="display: <?php echo $itap_settings['itap_img_3_label'] ? "block" : "none" ?>">
                        <label for="itap_img_1_label">Nom du meta-field </label>
                        <input type="text" name="itap_img_3_label" id="itap_img_3_label"
                               value="<?php echo $itap_settings['itap_img_3_label'] ?: "" ?>">
                    </div>

                </div>
                <div>
                    <label for="desc1">Total des mots minimum par page :</label>
                    <div><input type="text" value="<?php echo $itap_settings['total_words_min_page'] ?? '200' ?>"
                                id="total_words_min_page"></div>
                </div>
                <div>
                    <label for="desc1">Total des mots minimum par bloc :</label>
                    <div><input type="text" value="<?php echo $itap_settings['total_words_min_block'] ?? '60' ?>"
                                id="total_words_min_block"></div>
                </div>
                <div>
                    <label for="desc1">Total des mots minimum par categorie :</label>
                    <div><input type="text" value="<?php echo $itap_settings['total_words_min_by_cat'] ?? '800' ?>"
                                id="total_words_min_by_cat"></div>
                </div>
                <button id="itap_submit">Enregistrer les paramètres</button>
            </div>
        </div>
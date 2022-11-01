<?php
// get Schema Markup info

// echo '<pre>';
// print_r($schema);
// echo '</pre>';
// die();
?>

<div class="wrap is-there-a-problem-container">
    <p>Problèmes d'intégration</p>
    <div class="form-tri">
        <p>Trier par intégrateur</p>
        <form action="?page=is_there_a_problem" method="GET">
            <div class="bloc-form">
                <div>
                    <input type="hidden" name="page" value="is_there_a_problem">
                    <select name="author_name" id="author_name">
                        <option value="">choisissez votre nom d'utilisateur</option>
                        <?php
                        //get all admin and shop manager
                        foreach ($admins as $user) {
                        ?>
                            <option value="<?php echo esc_attr($user->display_name); ?>" name=""><?php echo esc_attr($user->display_name); ?></option>
                        <?php
                        }
                        ?>
                    </select>
                    <button type="submit" class="itap_button" value="Rechercher">Rechercher</button>
                </div>
            </div>
        </form>
    </div>
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
            // filter data by integrator
            $this->itap_getErrors('itap_getErrorsFromLinks', $results, '#DC3444');
            // if ($this->lines < 290) $this->itap_getErrors('itap_no_schema_product', $results, '#DC3444');
            if ($this->lines < 290) $this->itap_getErrors('itap_getErrorFromBaliseAlt', $results);
            if ($this->lines < 290) $this->itap_getErrors('itap_getErrorFromVariableProducts', $results);
            if ($this->lines < 290) $this->itap_getErrors('itap_getErrorsFromImages', $results);
            if ($this->lines < 290) $this->itap_getErrors('itap_getErrorsFromRankMath', $results);
            if ($this->lines < 290) $this->itap_getErrors('itap_getErrorsFromDescriptions', $results);

            if (!$total_integration_errors) {
                echo wp_kses("<tr><td colspan='5' class='congrats-plugin'>Aucune erreur détéctée , félicitations</td></tr>", array('td' => array('colspan' => array()), 'tr' => array('class' => array())));
            }
            ?>

        </tbody>
    </table>
</div>
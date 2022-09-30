<div class="wrap seo-quantum-part">
    <h1>Seo Quantum</h1>


    <div class="quantum_form_submit" style="<?php echo get_option('itap_seo_quantum_api_key') != '' ? 'display: none;' : '' ?>">
        <label for="api_key_seo_quantum">Clé Api</label>
        <input type="text" id="api_key_seo_quantum" placeholder="Merci de rentrer votre clé api pour accéder à l'Api SEO Quantum">
        <button id="btn" class="save_api_key">
            <p id="btnText">Enregistrer</p>
            <div class="check-box">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
                    <path fill="transparent" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
                </svg>
            </div>
        </button>
    </div>
    <button id="reset_api_key" style="<?php echo empty(get_option('itap_seo_quantum_api_key')) ? 'display: none;' : '' ?>">Reinitialiser la clé Api</button>
    <table class="table-plugin">
        <thead>
            <tr class="thead-plugin">
                <th>Id</th>
                <th>Categorie</th>
                <th>Note</th>
                <th>Note concurrent</th>
                <th>Points d'améliorations</th>
                <th>Requête</th>
            </tr>
        </thead>
        <tbody class="tbody-plugin">
            <?php foreach ($this->display_all_cate_product() as $key) : ?>
                <tr>
                    <td><?php echo $key['id'] ?></td>
                    <td><?php echo '<a href="' . $key['link'] . '">' . $key['name'] . '</a>' ?></td>
                    <td>10</td>
                    <td>10</td>
                    <td>10</td>
                    <td><button value="<?php echo $key['name'] ?>" class="seo-quantum-request">Requete</button></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
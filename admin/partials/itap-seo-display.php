<div class="wrap is-there-a-problem-container">
    <p>Problèmes liés au référencement naturel</p>
    <!-- <p class="problems_number">nombre de problèmes : </p> -->

    <table class="table-plugin">
        <thead>
            <tr class="thead-plugin">
                <th>Id </th>
                <th>Nom</th>
                <th>Url</th>
                <th>Problème remonté</th>
            </tr>
        </thead>
        <tbody class="tbody-plugin">
            <?php
            $noDescription = $this->itap_get_errors_no_categories_description();
            if (count($noDescription) > 0) {
                foreach ($noDescription as $category) {
                    $this->itap_seoDisplayTab($category);
                }
            }
            $noBelowContent = $this->itap_get_errors_below_category_content();
            if (count($noBelowContent) > 0) {
                foreach ($noBelowContent as $category) {
                    $this->itap_seoDisplayTab($category);
                }
            }
            $noAttributesDescription = $this->itap_get_errors_no_tags_description();
            if (count($noAttributesDescription) > 0) {
                foreach ($noAttributesDescription as $category) {
                    $this->itap_seoDisplayTab($category);
                }
            }
            ?>
        </tbody>
    </table>
</div>
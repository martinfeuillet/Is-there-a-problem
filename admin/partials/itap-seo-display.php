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
            $this->get_errors_from_seo(array($this, 'itap_get_errors_from_meta_title'), array($this, 'itap_seoDisplayTab'));
            $this->get_errors_from_seo(array($this, 'itap_get_errors_nofollow_link'), array($this, 'itap_seoDisplayTabLinks'));
            $this->get_errors_from_seo(array($this, 'itap_get_errors_no_categories_description'), array($this, 'itap_seoDisplayTab'));
            $this->get_errors_from_seo(array($this, 'itap_get_errors_below_category_content'), array($this, 'itap_seoDisplayTab'));
            $this->get_errors_from_seo(array($this, 'itap_get_errors_no_tags_description'), array($this, 'itap_seoDisplayTab'));
            ?>
        </tbody>
    </table>
</div>
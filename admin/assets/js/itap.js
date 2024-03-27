(function ($) {

    $(function () {

        function deleteLineToArchive() {
            // checkboxes are generated in js
            $(document).on("change", ".itap_checkbox", (val) => {
                if (val.target.checked) {
                    let seo = $(val.target).data('archive') === 'seo' ? "true" : "false";
                    $(val.target).closest("tr").fadeOut();
                    $.ajax({
                        type: "POST",
                        url: my_ajax_object.ajaxurl,
                        data: {
                            action: "get_checkbox_value",
                            uniqId: val.target.value,
                            seo
                        },
                        success: () => {
                            console.log("success");
                        },
                        error: () => {
                            console.log("error");
                        },
                    });
                } else {
                    $(val.target).closest("tr").fadeOut();
                    $.ajax({
                        type: "POST",
                        url: my_ajax_object.ajaxurl,
                        data: {
                            action: "delete_checkbox_value",
                            uniqId: val.target.value,
                        },
                        success: () => {
                            console.log("success");
                        },
                        error: () => {
                            console.log("error");
                        },
                    });
                }
            });
        }

        function saveApiKey() {
            const btn = $(".save_api_key");
            const btnText = $("#btnText");
            const apiKey = $("#api_key_seo_quantum");
            btn.on("click", () => {
                btnText.text("Merci");
                btn.addClass("active");
                $.ajax({
                    type: "POST",
                    url: my_ajax_object.ajaxurl,
                    data: {
                        action: "save_seo_quantum_api_key",
                        apiKey: apiKey.val(),
                    },
                    success: () => {
                        apiKey.val("");
                        console.log("success");
                    },
                    error: () => {
                        console.log("error");
                    },
                });
            });
        }


        // function that display the input text when we check the "champ personnalisé" checkbox
        // also work on img1, img2, img3
        function displayInputText() {
            const btnCustom = $("#itap_custom_field");
            const btnImg1 = $("#itap_img_1");
            const btnImg2 = $("#itap_img_2");
            const btnImg3 = $("#itap_img_3");
            btnCustom.on("change", () => {
                if (btnCustom.prop("checked")) {
                    $(".custom_field_input").css("display", "block");
                } else {
                    $(".custom_field_input").css("display", "none");
                }
            });

            btnImg1.on("change", () => {
                if (btnImg1.prop("checked")) {
                    $(".itap_img_1_label").css("display", "block");
                } else {
                    $(".itap_img_1_label").css("display", "none");
                }
            })
            btnImg2.on("change", () => {
                if (btnImg2.prop("checked")) {
                    $(".itap_img_2_label").css("display", "block");
                } else {
                    $(".itap_img_2_label").css("display", "none");
                }
            })
            btnImg3.on("change", () => {
                if (btnImg3.prop("checked")) {
                    $(".itap_img_3_label").css("display", "block");
                } else {
                    $(".itap_img_3_label").css("display", "none");
                }
            })
        }

        // function that save the settings of the plugin
        function saveSettings() {
            const btnSubmit = $("#itap_submit");
            btnSubmit.on("click", () => {
                console.log(data)
                $.ajax({
                    type: "POST",
                    url: my_ajax_object.ajaxurl,
                    data: {
                        action: "itap_save_settings",
                        short_desc: $("#short_desc").prop("checked") ? 1 : 0,
                        desc1: $("#desc1").prop("checked") ? 1 : 0,
                        desc2: $("#desc2").is(":checked") ? 1 : 0,
                        desc3: $("#desc3").is(":checked") ? 1 : 0,
                        itap_img_1: $("#itap_img_1").prop("checked") ? "image-description-1" : 0,
                        itap_img_2: $("#itap_img_2").is(":checked") ? "image-description-2" : 0,
                        itap_img_3: $("#itap_img_3").is(":checked") ? "image-description-3" : 0,
                        itap_img_1_label: $("#itap_img_1_label").val(),
                        itap_img_2_label: $("#itap_img_2_label").val(),
                        itap_img_3_label: $("#itap_img_3_label").val(),
                        desc_seo: $("#desc_seo").is(":checked") ? 1 : 0,
                        custom_field: $("#itap_custom_field").is(":checked") ? 1 : 0,
                        custom_field_input_1: $("#custom_field_input_1").val(),
                        custom_field_input_2: $("#custom_field_input_2").val(),
                        custom_field_input_3: $("#custom_field_input_3").val(),
                        total_words_min_page: $("#total_words_min_page").val(),
                        total_words_min_block: $("#total_words_min_block").val(),
                        total_words_min_by_cat: $("#total_words_min_by_cat").val(),
                        total_words_min_short_desc: $("#total_words_min_short_desc").val(),
                        total_words_min_principal_desc: $("#total_words_min_principal_desc").val(),
                        colors: $("#itap_colors").val(),
                    },
                    success: () => {
                        alert("Vos paramètres ont bien été enregistrés");
                    },
                    error: () => {
                        alert("Une erreur est survenue, merci de reessayer plus tard");
                    },
                });
            });
        }

        // all the function for automation page
        // function for change primary category on automation page
        function changePrimaryCategory() {
            const buttonChoice = $('.button_choice')
            buttonChoice.on('click', function () {
                console.log()
                $.ajax({
                    type: "POST",
                    url: my_ajax_object.ajaxurl,
                    data: {
                        action: "change_primary_category",
                        change_or_ignore: $(this).data('choice'),
                        product_id: $(this).data('product-id'),
                        new_cat_id: $(this).parent().parent().find('select').val()
                    },
                    success: (res) => {
                        console.log(res);
                        $(this).parent().parent().hide()
                    },
                    error: () => {
                        alert("Une erreur est survenue, merci de reessayer plus tard");
                    },
                });
            })
        }

        // function for change primary category on automation page
        function fix_variation_color() {
            const buttonVariation = $('.fix_variation_color')
            buttonVariation.on('click', function () {
                buttonVariation.text('correction en cours...')
                $.ajax({
                    type: "GET",
                    url: my_ajax_object.ajaxurl,
                    data: {
                        action: "fix_variation_color",
                    },
                    success: () => {
                        buttonVariation.text('corrigé')
                        alert('correction effectuée')

                    },
                    error: () => {
                        alert("Une erreur est survenue, merci de reessayer plus tard");
                    },
                });
            })
        }

        function putAllTdWithBackgroundColorInTopOfTheTable() {
            const table = $('.table-plugin')
            const trs = table.find('tr')
            trs.each(tr => {
                //if tr has class bm_animation or mistake, we put it in top of the table
                if (trs[tr].classList.contains('bm_animation') || trs[tr].classList.contains('mistake')) {
                    table.prepend(trs[tr])
                }
            })
        }

        function getDataInPageIsThereAProblem(pageNumber = 1) {
            // get the param page in the url
            const url = new URL(window.location.href);
            const paramPage = url.searchParams.get("page");
            const authorName = url.searchParams.get("author_name") ? url.searchParams.get("author_name") : ''
            if (paramPage === "is_there_a_problem") {
                $.ajax({
                    type: "POST",
                    url: my_ajax_object.ajaxurl,
                    data: {
                        action: "get_data_in_page_is_there_a_problem",
                        pageNumber,
                        authorName
                    },
                    success: (res) => {
                        console.log(res);
                        const data = res.data
                        if (res.success && data.continue) {
                            console.log('continue')
                            let nbr = $('.nbr')
                            let nbrError = Number(nbr.text())
                            data.errors.forEach((error) => {
                                $('#display-errors').append(error)
                            })
                            nbr.text(nbrError + data.errors.length)

                            getDataInPageIsThereAProblem(pageNumber + 1)
                        } else if (res.success && !data.continue) {
                            $('.search-error').text('Analyse terminée')
                            $('.loader').hide()
                            putAllTdWithBackgroundColorInTopOfTheTable()
                        } else {
                            alert('Une erreur est survenue, merci de reessayer plus tard.Si cette erreur persiste, merci de contacter un administateur')
                        }
                    },
                    error: () => {
                        alert("Une erreur est survenue, merci de contacter un administrateur");
                    },
                });
            }
        }

        getDataInPageIsThereAProblem()

        fix_variation_color()

        changePrimaryCategory()

        saveSettings()

        displayInputText()

        saveApiKey()

        deleteLineToArchive()


    })


})(jQuery);







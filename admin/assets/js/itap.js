// archive the differents problems when we check the boxes
(function ($) {
    $(function () {
        let checkboxes = $(".itap_checkbox");
        checkboxes.on("change", (val) => {
            // Get the value of the checkbox
            if (val.target.checked) {
                $(val.target).closest("tr").fadeOut();
                $.ajax({
                    type: "POST",
                    url: my_ajax_object.ajaxurl,
                    data: {
                        action: "get_checkbox_value",
                        uniqId: val.target.value,
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
    })

    $(function () {
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
    })

    // function trigger every time we click on the button "requete"
    // send a request to the Seo Quantum API
    $(function () {
        let buttonrequest = $(".seo-quantum-request");
        buttonrequest.on("click", (val) => {
            const cat_name = val.target.value;
            $.ajax({
                type: "POST",
                url: my_ajax_object.ajaxurl,
                data: {
                    action: "send_request_to_seo_quantum",
                    cat_name,
                },
                beforeSend: () => {
                    buttonrequest.text("Requête en cours...");
                    buttonrequest.attr("disabled", true);
                },
                success: (res) => {
                    console.log(res);
                    window.location.reload();
                },
                error: (er) => {
                    console.log(er);
                },
            });
        });
    })

    // function that send a request to the Seo Quantum API and get all the possible optimization from our text
    $(function () {
        const btn = $(".seo-quantum-analysis");
        // get data-analysis_id of btn
        const data_analysis_id = btn.attr("data-analysis_id");
        const data_cat_id = btn.attr("data-cat_id");
        const data_url = btn.attr("data-url");
        data = [data_analysis_id, data_cat_id, data_url];
        btn.on("click", () => {
            console.log(data);
            btn.text("Analyse en cours");
            btn.prop("disabled", true);
            $.ajax({
                type: "POST",
                url: my_ajax_object.ajaxurl,
                data: {
                    action: "analysis_text_seo_quantum",
                    data_analysis_id,
                    data_cat_id,
                    data_url,
                },
                success: (res) => {
                    btn.prop("disabled", false);
                    btn.text("Analyse terminée");
                    console.log(res);
                },
                error: (err) => {
                    console.log(err);
                },
            });
        });
    })

    // function that reset the input api key to enter a new one
    $(function () {
        const btn = $("#reset_api_key");
        btn.on("click", () => {
            btn.css("display", "none");
            $(".quantum_form_submit").css("display", "block");
        });
    })

    // function that display the input text when we check the "champ personnalisé" checkbox
    // also work on img1, img2, img3
    $(function () {
        const btn = $("#itap_custom_field");
        const btnImg1 = $("#itap_img_1");
        const btnImg2 = $("#itap_img_2");
        const btnImg3 = $("#itap_img_3");
        btn.on("change", () => {
            if (btn.prop("checked")) {
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
    })

    // function that save the settings of the plugin
    $(function () {
        const btn = $("#itap_submit");
        btn.on("click", () => {
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
                },
                success: () => {
                    alert("Vos paramètres ont bien été enregistrés");
                },
                error: () => {
                    alert("Une erreur est survenue, merci de reessayer plus tard");
                },
            });
        });
    })

    // all the function for automation page
    // function for change primary category on automation page
    $(function () {
        const button = $('.button_choice')
        button.on('click', function () {
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
    })

    $(function () {
        const button = $('.fix_variation_color')
        button.on('click', function () {
            button.text('correction en cours...')
            $.ajax({
                type: "GET",
                url: my_ajax_object.ajaxurl,
                data: {
                    action: "fix_variation_color",
                },
                success: () => {
                    button.text('corrigé')
                    alert('correction effectuée')

                },
                error: () => {
                    alert("Une erreur est survenue, merci de reessayer plus tard");
                },
            });
        })
    })


})(jQuery);






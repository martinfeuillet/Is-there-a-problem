
// archive the differents problems when we check the boxes
(function ($) {
    let checkboxes = $(".itap_checkbox");
    checkboxes.on("change", (val) => {
        // Get the value of the checkbox
        if (val.target.checked) {
            $(val.target).closest('tr').fadeOut();
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
                }
            });
        } else {
            $(val.target).closest('tr').fadeOut();
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
                }
            });
        }
    });
})(jQuery);

// function trigger every time we click on the button "requete"
// send a request to the Seo Quantum API
(function($){
    let buttonrequest = $(".seo-quantum-request");
    buttonrequest.on("click", (val) => {
        const cat_name = val.target.value;
        $.ajax({
            type: "POST",
            url: my_ajax_object.ajaxurl,
            data: {
                action: "send_request_to_seo_quantum",
                cat_name
            },
            success: (res) => {
                console.log(res);
            },
            error: () => {
                console.log("error");
            }
        });
    });
})(jQuery);

// function that save api key in the database
(function($){
    const btn = $(".save_api_key");
    const btnText = $("#btnText")
    const apiKey = $("#api_key_seo_quantum")
    btn.on("click", () => {
        btnText.text("Merci");
        btn.addClass("active")
        $.ajax({
            type: "POST",
            url: my_ajax_object.ajaxurl,
            data: {
                action: "save_seo_quantum_api_key",
                apiKey : apiKey.val()
            },
            success: (res) => {
                apiKey.val("");
                console.log("success");
            },
            error: () => {
                console.log("error");
            }
        });
    });
})(jQuery);

// function that send a request to the Seo Quantum API and get all the possible optimization from our text
(function($){
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
            data : {
                action : "analysis_text_seo_quantum",
                data_analysis_id,
                data_cat_id,
                data_url
            },
            success: (res) => {
                btn.prop("disabled", false);
                btn.text("Analyse terminée");
                console.log(res);
            },
            error: (err) => {
                console.log(err);
            }
        });
    });
})(jQuery);

// function that reset the input api key to enter a new one
(function($){
    const btn = $("#reset_api_key")
    btn.on("click", () => {
        btn.css('display', 'none')
        $('.quantum_form_submit').css('display', 'block')
    });
})(jQuery);

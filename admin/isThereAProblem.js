(function ($) {
    let checkboxes = $(".itap_checkbox");
    checkboxes.on("change", (val) => {
        // Get the value of the checkbox
        if (val.target.checked) {
            $.ajax({
                type: "POST",
                url: my_ajax_object.ajaxurl,
                data: {
                    action: "get_checkbox_value",
                    uniqId: val.target.value,
                },
                success: (data) => {
                    console.log(data);
                },
                error: () => {
                    console.log("error");
                },
            });
        } else {
            $.ajax({
                type: "POST",
                url: my_ajax_object.ajaxurl,
                data: {
                    action: "delete_checkbox_value",
                    uniqId: val.target.value,
                },
                success: (data) => {
                    console.log(data);
                },
                error: () => {
                    console.log("error");
                },
            });
        }
    });
})(jQuery);

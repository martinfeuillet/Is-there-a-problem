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

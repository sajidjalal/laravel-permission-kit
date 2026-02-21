if ($("#addRoleForm").length) {
    const form = $("#addRoleForm");

    form.validate({
        ignore: ".ignore",
        onkeyup: false,
        rules: {
            name: "required",
            status: {
                required: true,
                minlength: 1,
            },
        },
        messages: {
            // status: "Please select status",
        },
        highlight: handleHighlightUnhighlight,
        unhighlight: handleHighlightUnhighlight,
        errorPlacement: handleErrorPlacement,
        submitHandler: handleSubmit,
    });
}

function resetModel() {
    $("#id").val("");
    $("#name").val("");
    $("#is_admin").val("").trigger("change");
    $("#description").val("");
    $("#status").val("").trigger("change");
    $("#reporting_role_id").val("").trigger("change");
}

$("table").on("click", ".edit_role_class", function () {
    const routes = window.rolePermissionRoutes || {};
    const getRolesUrl = routes.getRoles || "/get-roles";
    var formData = new FormData();

    var role_id = $(this).attr("data-role_id"); // will return the string "123"

    formData.append("role_id", role_id);

    $.ajax({
        url: getRolesUrl,
        data: formData,
        processData: false,
        contentType: false,
        type: "POST",
        dataType: "json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            // Call the existing handleSuccess function
            handleSuccess(response);
            // Additional code to execute after success
            let role_details = response.data[0];
            $("#id").val(role_details.id);
            $("#name").val(role_details.name);
            $("#description").val(role_details.description);
            $("#role_prefix").val(role_details.role_prefix);
            $("#is_admin").val(role_details.is_admin).trigger("change");
            $("#status").val(role_details.status).trigger("change");
            $("#reporting_role_id").val(role_details.reporting_role_id).trigger("change");
            $("#addRoleModal").modal("toggle");
        },
        error: function (xhr, status, error) {
            handleError(xhr, status, error, form); // Pass the form parameter
        },
    });
});

$("table").on("click", ".delete_record", function () {
    const routes = window.rolePermissionRoutes || {};
    const roleDeleteUrl = routes.roleDelete || "/role-delete";
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-warning m-r-5",
        },
        buttonsStyling: false,
    });

    swalWithBootstrapButtons
        .fire({
            title: "Are you sure?",
            text: "Do you want to delete this role!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, cancel!",
            reverseButtons: true,
        })
        .then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                var formData = new FormData();

                var role_id = $(this).attr("data-role_id"); // will return the string "123"

                formData.append("role_id", role_id);

                // var submit_id = document.activeElement.id;
                var submit_id = "temp";

                var formData = new FormData();

                var role_id = $(this).attr("data-role_id"); // will return the string "123"

                formData.append("role_id", role_id);

                $.ajax({
                    url: roleDeleteUrl,
                    data: formData,
                    processData: false,
                    contentType: false,
                    type: "POST",
                    dataType: "json",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content"
                        ),
                    },
                    success: function (response) {
                        // Call the existing handleSuccess function
                        handleSuccess(response);
                        $("#roleDataTable").DataTable().ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        handleError(xhr, status, error, form); // Pass the form parameter
                    },
                });
            }
        });
});

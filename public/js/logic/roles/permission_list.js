$(document).ready(function () {
    // Select All - Top Level
    $("#selectAllCheckbox").on("change", function () {
        var isChecked = $(this).is(":checked");

        $('#allMenus input[type="checkbox"]').each(function () {
            var $checkbox = $(this);
            var isFrozen = $checkbox.closest("span").hasClass("freezeCheckBox");

            if (isChecked) {
                // Check only if not frozen and not disabled
                if (!isFrozen && !$checkbox.is(":disabled")) {
                    $checkbox.prop("checked", true);
                }
            } else {
                // For frozen (disabled) checkboxes: temporarily enable -> uncheck -> disable again
                if ($checkbox.is(":disabled")) {
                    $checkbox.prop("disabled", false).prop("checked", false).prop("disabled", true);
                } else {
                    $checkbox.prop("checked", false);
                }
            }
        });

        updateAllRowSelects();
    });

    // Row-wise "Select All"
    $("body").on("change", ".rowSelectAll", function () {
        var $row = $(this).closest("tr");
        var isChecked = $(this).is(":checked");

        $row.find('input[type="checkbox"]').each(function () {
            if (
                !$(this).hasClass("rowSelectAll") &&
                !$(this).is(":disabled") &&
                !$(this).closest("span").hasClass("freezeCheckBox")
            ) {
                $(this).prop("checked", isChecked);
            }
        });
    });

    // Watch individual checkbox changes and update rowSelectAll
    $("body").on("change", '#allMenus input[type="checkbox"]:not(.rowSelectAll)', function () {
        var $row = $(this).closest("tr");
        updateRowSelectCheckbox($row);
    });

    function updateRowSelectCheckbox($row) {
        let checkboxes = $row.find('input[type="checkbox"]:not(.rowSelectAll):not(:disabled)').filter(function () {
            return !$(this).closest("span").hasClass("freezeCheckBox");
        });
        let checkedCount = checkboxes.filter(":checked").length;

        $row.find(".rowSelectAll").prop("checked", checkedCount === checkboxes.length && checkboxes.length > 0);
    }

    function updateAllRowSelects() {
        $("#allMenus tr").each(function () {
            updateRowSelectCheckbox($(this));
        });
    }

    // Load permissions via AJAX
    $("body").on("change", 'input[name^="role_id"]', function () {
        let formData = { role_id: this.id };
        $("#allMenus").empty();
        $("#selectAllCheckbox").prop("checked", false);
        $("#permission_table tbody").empty();

        $.ajax({
            url: permission_list_route,
            data: formData,
            type: "GET",
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                if ($.fn.dataTable.isDataTable("#permission_table")) {
                    $("#permission_table").DataTable().clear().destroy();
                }

                let srNo = 1;

                response.data.role_list.forEach(function (permissionDetails) {
                    const frozen = (v) => (v == -1 ? "freezeCheckBox" : "");
                    const checked = (v) => (v == 1 ? "checked" : "");
                    const disableIfParent = (p) => (p == 0 ? "disabled" : "");

                    const row = `
                        <tr>
                            <td class="menu_table_row">${srNo}</td>
                            <td class="menu_table_row">
                                <div class="col-md-2">
                                    <label class="contas">
                                        <input type="checkbox" class="rowSelectAll">
                                        <span class="checkMarks"></span>
                                    </label>
                                </div>
                            </td>
                            <td class="menu_table_row">
                                ${permissionDetails.parent_id == 0 ? `<span class='is_parent'>${permissionDetails.group_name}</span>` : permissionDetails.display_permissions_name}
                            </td>
                            <td class="menu_table_row">
                                <div class="col-md-2">
                                    <label class="contas">
                                        <input type="hidden" name="menu_id[]" value="${permissionDetails.menu_id}" readonly>
                                        <span class="${frozen(permissionDetails.create)}">
                                            <input type="checkbox" name="create[]" value="${permissionDetails.create}" ${checked(permissionDetails.create)} ${disableIfParent(permissionDetails.parent_id)}>
                                            <span class="checkMarks"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td class="menu_table_row">
                                <div class="col-md-2">
                                    <label class="contas">
                                        <span class="${frozen(permissionDetails.read)}">
                                            <input type="checkbox" name="read[]" value="${permissionDetails.read}" ${checked(permissionDetails.read)}>
                                            <span class="checkMarks"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td class="menu_table_row">
                                <div class="col-md-2">
                                    <label class="contas">
                                        <span class="${frozen(permissionDetails.update)}">
                                            <input type="checkbox" name="update[]" value="${permissionDetails.update}" ${checked(permissionDetails.update)} ${disableIfParent(permissionDetails.parent_id)}>
                                            <span class="checkMarks"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td class="menu_table_row">
                                <div class="col-md-2">
                                    <label class="contas">
                                        <span class="${frozen(permissionDetails.delete)}">
                                            <input type="checkbox" name="delete[]" value="${permissionDetails.delete}" ${checked(permissionDetails.delete)} ${disableIfParent(permissionDetails.parent_id)}>
                                            <span class="checkMarks"></span>
                                        </span>
                                    </label>
                                </div>
                            </td>
                        </tr>`;

                    $("#allMenus").append(row);
                    srNo++;
                });

                setTimeout(updateAllRowSelects, 100);

                if (srNo > 1) {
                    $("#permission_table").DataTable({
                        pageLength: 100,
                        lengthMenu: [10, 25, 50, 100, 200],
                        autoWidth: false,
                        columns: [
                            { width: "60px" },
                            { width: "60px" },
                            { width: "250px" },
                            { width: "80px" },
                            { width: "80px" },
                            { width: "80px" },
                            { width: "80px" },
                        ],
                    });
                }
            },
            error: function (xhr) {
                let e = JSON.parse(xhr.responseText);
                Swal.fire({
                    title: "Alert",
                    text: e.message,
                    icon: "warning",
                    customClass: { confirmButton: "btn btn-primary" },
                    buttonsStyling: false,
                });
            },
        });
    });
});

$("#add_update_permission_form").validate({
    ignore: ".ignore",
    rules: {
        role_id: {
            required: true,
        },
    },
    messages: {
        role_id: {},
    },
    highlight: function (element, errorClass, validClass) {
        $(".remove_custome_div").remove();
        $(element)
            .parents("div.control-group")
            .addClass(errorClass)
            .removeClass(validClass);
    },
    unhighlight: function (element, errorClass, validClass) {
        $(".remove_custome_div").remove();
        $(element)
            .parents(".error")
            .removeClass(errorClass)
            .addClass(validClass);
    },
    errorPlacement: function (error, element) {
        if (element.hasClass("custome_select2") && element.next(".select2-container").length) {
            error.insertAfter(element.next(".select2-container"));
        } else if (element.prop("type") === "checkbox" || element.prop("type") === "radio") {
            error.appendTo(element.parent().parent());
        } else {
            error.insertAfter(element);
        }
    },
    submitHandler: function (form) {
        var formData = new FormData();
        var menu_id = $('input[name^="menu_id"]');
        var create = $('input[name^="create"]');
        var read = $('input[name^="read"]');
        var update = $('input[name^="update"]');
        var permission_delete = $('input[name^="delete"]');

        formData.append("role_id", $('input[name^="role_id"]:checked').val());
        for (let i = 0; i < menu_id.length; i++) {
            formData.append("menu_id[]", menu_id[i].value);
            formData.append("create[]", create[i].value == "-1" ? "-1" : (create[i].checked ? 1 : 0));
            formData.append("read[]", read[i].value == "-1" ? "-1" : (read[i].checked ? 1 : 0));
            formData.append("update[]", update[i].value == "-1" ? "-1" : (update[i].checked ? 1 : 0));
            formData.append("delete[]", permission_delete[i].value == "-1" ? "-1" : (permission_delete[i].checked ? 1 : 0));
        }

        var submit_id = document.activeElement.id;
        var submit_text = document.activeElement.innerText;
        $.ajax({
            url: $(form).attr("action"),
            data: formData,
            processData: false,
            contentType: false,
            type: "POST",
            dataType: "json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            beforeSend: function () {
                $("#" + submit_id)
                    .text("Please Wait...")
                    .prop("disabled", true);
            },
            complete: function () {
                $("#" + submit_id)
                    .text(submit_text)
                    .prop("disabled", false);
            },
            success: function (response) {
                Swal.fire({
                    title: "Success",
                    text: response.message,
                    icon: "success",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });
            },
            error: function (xhr) {
                var e = JSON.parse(xhr.responseText);
                var errorCode = e.fields;
                let error_msg = "";
                for (x in errorCode) {
                    error_msg =
                        "<div class='error remove_custome_div' id='" +
                        x +
                        "-error'>" +
                        errorCode[x] +
                        "</div>";
                    $("#add_update_permission_form")
                        .find("input#" + x)
                        .after(error_msg);
                }
                Swal.fire({
                    title: "Alert",
                    text: e.message,
                    icon: "warning",
                    customClass: {
                        confirmButton: "btn btn-primary",
                    },
                    buttonsStyling: false,
                });
            },
        });
    },
});

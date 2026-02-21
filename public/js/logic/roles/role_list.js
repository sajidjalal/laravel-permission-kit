$(document).ready(function () {
    let roleDataTable = $("#roleDataTable");

    // Clone header row for column filters
    $("#roleDataTable thead tr").clone(true).appendTo("#roleDataTable thead");

    // Create input boxes per column
    $("#roleDataTable thead tr:eq(1) th").each(function (i) {
        let title = $(this).text().trim();
        let class_attr = "";
        if ($(this).attr("data-smallCol")) class_attr = 'class="small-input"';

        if ($(this).attr("data-dateCol")) {
            $(this).html(
                `<input type="date" ${class_attr} placeholder="${title}" />`
            );
        } else if ($(this).attr("data-hideCol")) {
            $(this).html("");
        } else {
            $(this).html(
                `<input type="text" ${class_attr} placeholder="${title}" />`
            );
        }

        // Input search logic
        $("input", this).on("keyup change", function () {
            const value = this.value;
            if (this.type === "date" && value !== "") {
                $(this).addClass("field-highlight");
            } else {
                $(this).removeClass("field-highlight");
            }

            if (table.column(i).search() !== value) {
                table.column(i).search(value).draw();
            }
        });
    });

    let columns = [
        {
            data: "sr_no",
            name: "sr_number",
            width: "80px",
            orderable: false,
            searchable: false,
        },
        {
            data: "id",
            name: "id",
            width: "100px",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                let html = `<div class="d-flex align-items-center justify-content-center">`;
                if (row.accessRight?.update) {
                    html += `<a class="text-body edit_role_class pointer-cursor" data-role_id='${row.id}'><i class="fa fa-edit fa-sm me-2"></i></a>`;
                }
                if (row.accessRight?.delete) {
                    html += `<a class="text-body delete_record pointer-cursor" data-role_id='${row.id}'><i class="fa fa-trash fa-sm mx-2 text-danger"></i></a>`;
                }
                html += `</div>`;
                return html;
            },
        },
        { data: "name", name: "display_name", width: "150px" },
        { data: "role_prefix", name: "role_prefix", width: "150px" },
        {
            data: "reporting",
            name: "reporting",
            width: "150px",
            searchable: false,
            orderable: false,
        },
        {
            data: "description",
            width: "150px",
            orderable: false,
            render: function (data, type, row) {
                if (data && data.length > 30) {
                    let truncated = data.substring(0, 30) + "...";
                    return `<span title="${data}">${truncated} <a href="#" class="read-more">Read More</a></span>`;
                }
                // Return the full description if it's less than or equal to 50 characters
                return data || ""; // If `data` is null or undefined, return an empty string
            },
        },
        {
            data: "status",
            name: "status",
            width: "150px",
            orderable: false,
            searchable: false,
            render: function (data, type, row) {
                return row.status == 1
                    ? `<span class="badge badge-light-success">${$(
                        "#CONSTANTS_ACTIVE"
                    ).val()}</span>`
                    : `<span class="badge badge-light-danger">${$(
                        "#CONSTANTS_INACTIVE"
                    ).val()}</span>`;
            },
        },
        { data: "created_at", name: "created_at", width: "150px" },
    ];

    let table = initDataTable({
        tableId: "roleDataTable",
        columns: columns,
        ajaxUrl: role_rote,
        extraData: function () {
            return {
                reporting_id: $('#reporting_id').val(),
            };
        },
    });

    bindFilterReload(table, [
        "reporting_id",
    ]);

    // Remove sorting classes from filter row
    roleDataTable.on("init.dt", function () {
        $($("#roleDataTable thead tr")[1])
            .find("th")
            .removeClass(
                "sorting sorting_asc sorting_desc dt-orderable-asc dt-orderable-desc dt-ordering-desc"
            );
    });

    // Display name dropdown filter reload
    $("#reporting_id").on("change", function () {
        table.ajax.reload();
    });

    // Modal reset
    $("#btnAddRoleModal").click(function () {
        resetModel();
    });
});

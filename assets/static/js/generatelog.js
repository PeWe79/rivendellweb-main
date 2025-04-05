/*********************************************************************************************************
 *                                        RIVENDELL WEB BROADCAST                                        *
 *    A WEB SYSTEM TO USE WITH RIVENDELL RADIO AUTOMATION: HTTPS://GITHUB.COM/ELVISHARTISAN/RIVENDELL    *
 *              THIS SYSTEM IS NOT CREATED BY THE DEVELOPER OF RIVENDELL RADIO AUTOMATION.               *
 * IT'S CREATED AS AN HELP TOOL ONLINE BY ANDREAS OLSSON AFTER HE FIXED BUGS IN AN OLD SCRIPT CREATED BY *
 *             BRIAN P. MCGLYNN : HTTPS://GITHUB.COM/BPM1992/RIVENDELL/TREE/RDWEB/WEB/RDPHP              *
 *        USE THIS SYSTEM AT YOUR OWN RISK. IT DO DIRECT MODIFICATION ON THE RIVENDELL DATABASE.         *
 *                 YOU CAN NOT HOLD US RESPONISBLE IF SOMETHING HAPPENDS TO YOUR SYSTEM.                 *
 *                   THE DESIGN IS DEVELOP BY SAUGI: HTTPS://GITHUB.COM/ZURAMAI/MAZER                    *
 *                                              MIT LICENSE                                              *
 *                                   COPYRIGHT (C) 2024 ANDREAS OLSSON                                   *
 *             PERMISSION IS HEREBY GRANTED, FREE OF CHARGE, TO ANY PERSON OBTAINING A COPY              *
 *             OF THIS SOFTWARE AND ASSOCIATED DOCUMENTATION FILES (THE "SOFTWARE"), TO DEAL             *
 *             IN THE SOFTWARE WITHOUT RESTRICTION, INCLUDING WITHOUT LIMITATION THE RIGHTS              *
 *               TO USE, COPY, MODIFY, MERGE, PUBLISH, DISTRIBUTE, SUBLICENSE, AND/OR SELL               *
 *                 COPIES OF THE SOFTWARE, AND TO PERMIT PERSONS TO WHOM THE SOFTWARE IS                 *
 *                       FURNISHED TO DO SO, SUBJECT TO THE FOLLOWING CONDITIONS:                        *
 *            THE ABOVE COPYRIGHT NOTICE AND THIS PERMISSION NOTICE SHALL BE INCLUDED IN ALL             *
 *                            COPIES OR SUBSTANTIAL PORTIONS OF THE SOFTWARE.                            *
 *              THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR               *
 *               IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,                *
 *              FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE              *
 *                AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER                 *
 *             LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,             *
 *             OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE             *
 *                                               SOFTWARE.                                               *
 *********************************************************************************************************/
var dt;
var dt2;
var log_logname;

function tr(translate) {
    var result = false;
    jQuery.ajax({
        type: "POST",
        url: HOST_URL + '/forms/jstrans.php',
        async: false,
        data: {
            translate: translate
        },
        datatype: 'html',
        success: function (data) {
            var mydata = $.parseJSON(data);
            result = mydata.translated;
        }
    });
    return result;
}


$("#checkall").on("click", function (e) {
    if ($(this).is(":checked")) {
        dt.rows().select();
        $(".checked-rows-table-check").prop("checked", true);
    } else {
        dt.rows().deselect();
        $(".checked-rows-table-check").prop("checked", false);
    }
});


function generatelog(logname, status) {
    if (status != 3) {
        Swal.fire({
            text: TRAN_REGENERATETHELOGNOTPOSSIBLE,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    } else {
        Swal.fire({
            text: TRAN_REGENERATETHELOG,
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            showLoaderOnConfirm: true,
            confirmButtonText: TRAN_YES,
            cancelButtonText: TRAN_NO,
            customClass: {
                confirmButton: "btn fw-bold btn-danger",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then(function (result) {
            if (result.value) {
    
                jQuery.ajax({
                    type: "POST",
                    url: HOST_URL + '/forms/loggenerator/regenerate.php',
                    data: {
                        log: logname,
                    },
                    datatype: 'html',
                    success: function (data) {
                        var mydata = $.parseJSON(data);
                        var fel = mydata.error;
                        var kod = mydata.errorcode;
                        if (fel == "false") {
                            dt.ajax.reload();
        
                        }
                    }
                });
                
            }
        });

    }

    

}

function dellog(log) {
    Swal.fire({
        text: TRAN_REMLOGGENDATA,
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        showLoaderOnConfirm: true,
        confirmButtonText: TRAN_YES,
        cancelButtonText: TRAN_NO,
        customClass: {
            confirmButton: "btn fw-bold btn-danger",
            cancelButton: "btn fw-bold btn-active-light-primary"
        }
    }).then(function (result) {
        if (result.value) {

            jQuery.ajax({
                type: "POST",
                url: HOST_URL + '/forms/loggenerator/remove.php',
                data: {
                    log: log,
                },
                datatype: 'html',
                success: function (data) {
                    var mydata = $.parseJSON(data);
                    var fel = mydata.error;
                    var kod = mydata.errorcode;
                    if (fel == "false") {
                        dt.ajax.reload();
    
                    }
                }
            });
            
        }
    });
}

function showlog(log) {
    log_logname = log;
    $('#log_logs').modal('show');
    dt2.ajax.reload();
}

$('#add_form').validate({
    rules: {
        date: {
            required: true,
        },


    },
    messages: {
        date: {
            required: TRAN_SELECTDATENEED
        },
    },
    errorElement: 'span',
    errorPlacement: function (error, element) {
        error.addClass('parsley-error');
        element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
        $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
        $(element).removeClass('is-invalid');
    },
    submitHandler: function () {
        var dataString = $('#add_form').serialize();
        jQuery.ajax({
            type: "POST",
            url: HOST_URL + '/forms/loggenerator/checklogexist.php',
            data: {
                date: $("#date").val(),
                service: $("#selectScheduler").val(),
            },
            datatype: 'html',
            success: function (data) {
                var mydata = $.parseJSON(data);
                var fel = mydata.error;
                var kod = mydata.errorcode;
                if (fel == "false") {

                    jQuery.ajax({
                        type: "POST",
                        url: HOST_URL + '/forms/loggenerator/generatelog.php',
                        data: dataString,
                        success: function (data) {
                            var mydata = $.parseJSON(data);
                            var fel = mydata.error;
                            if (fel == "false") {
                                $('#generate_log').modal('hide');
                                dt.ajax.reload();
                            }
                        }
                    });

                } else {
                    Swal.fire({
                        text: TRAN_SELECTDATELOGEXIST,
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: TRAN_YES,
                        cancelButtonText: TRAN_NO,
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        }
                    }).then(function (result) {
                        if (result.value) {
                            jQuery.ajax({
                                type: "POST",
                                url: HOST_URL + '/forms/loggenerator/generatelog.php',
                                data: dataString,
                                success: function (data) {
                                    var mydata = $.parseJSON(data);
                                    var fel = mydata.error;
                                    if (fel == "false") {
                                        $('#generate_log').modal('hide');
                                        dt.ajax.reload();

                                    }
                                }
                            });


                        }
                    });
                }
            }
        });
    }
});

var KTDatatablesServerSide = function () {
    var initDatatable = function () {
        dt = $("#loggen_table").DataTable({
            searchDelay: 500,
            processing: true,
            responsive: true,
            select: {
                style: 'multi',
                selector: 'td:first-child input[type="checkbox"]',
                className: 'row-selected'
            },
            order: [
                [1, 'desc']
            ],
            stateSave: true,
            serverMethod: 'post',
            ajax: {
                url: HOST_URL + "/tables/loggenerator-table.php",
            },
            language: {
                "emptyTable": TRAN_TABLENODATA,
                "info": TRAN_TABLESHOWS + " _START_ " + TRAN_TABLETO + " _END_ " + TRAN_TABLETOTAL + " _TOTAL_ " + TRAN_TABLEROWS,
                "infoEmpty": TRAN_TABLESHOWS + " 0 " + TRAN_TABLETO + " 0 " + TRAN_TABLETOTAL + " 0 " + TRAN_TABLEROWS,
                "infoFiltered": "(" + TRAN_TABLEFILTERED + " _MAX_ " + TRAN_TABLEROWS + ")",
                "infoThousands": " ",
                "lengthMenu": TRAN_TABLESHOW + " _MENU_ " + TRAN_TABLEROWS,
                "loadingRecords": TRAN_TABLELOADING,
                "processing": TRAN_TABLEWORKING,
                "search": TRAN_TABLESEARCH,
                "zeroRecords": TRAN_TABLENORESULTS,
                "thousands": " ",
                "paginate": {
                    "first": TRAN_TABLEFIRST,
                    "last": TRAN_TABLELAST,
                    "next": TRAN_TABLENEXT,
                    "previous": TRAN_TABLEPREV
                },
                "select": {
                    "rows": {
                        "1": "1 " + TRAN_TABLESELECTED,
                        "_": "%d " + TRAN_TABLESELECTED
                    }
                },
                "aria": {
                    "sortAscending": ": " + TRAN_TABLENSORTRISE,
                    "sortDescending": ": " + TRAN_TABLENSORTFALL
                }
            },
            columns: [
                {
                    data: 'LOGNAME'
                },
                {
                    data: 'LOGNAME'
                },
                {
                    data: 'DESCRIPTION'
                },
                {
                    data: 'GENERATELOG'
                },
                {
                    data: null
                },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm">
                                <input class="form-check-input checked-rows-table-check" name="deletethis" id="delcheck_${data}" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        if (data == 1) {
                            return TRAN_READYTOGENERATE;
                        } else if (data == 2) {
                            return TRAN_GENERATINGLOG;
                        } else if (data == 3) {
                            return TRAN_DONE;
                        } else if (data == 0) {
                            return TRAN_NOTGENERATED;
                        }
                    }
                },


                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    className: 'text-end',
                    render: function (data, type, row) {
                        return `
                        <div class="btn-group mb-3" role="group">
                                    <a href="javascript:;" onclick="generatelog('` + row.LOGNAME + `','` + row.GENERATELOG + `')" class="btn icon btn-success" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="`+ TRAN_GENERATELOG + `"><i class="bi bi-collection-play-fill"></i></a>
                                    <a href="javascript:;" onclick="showlog('` + row.LOGNAME + `')" class="btn icon btn-warning" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="`+ TRAN_SHOWLOGINFO + `"><i class="bi bi-info-circle"></i></a>
                                    <a href="javascript:;" onclick="dellog('` + row.LOGNAME + `')" class="btn icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="`+ TRAN_REMLOGGENLOGDATA + `"><i class="bi bi-x-square"></i></a>
                                </div>
                        `;
                    }
                },
            ],
        });
        dt.on('draw', function () {
            initToggleToolbar();
            toggleToolbars();
        });
    }

    var initDatatableLog = function () {
        dt2 = $("#loglog_table").DataTable({
            searchDelay: 500,
            processing: true,
            responsive: true,
            autoWidth: false,
            order: [
                [1, 'asc']
            ],
            stateSave: true,
            serverMethod: 'post',
            ajax: {
                url: HOST_URL + "/tables/loggeneratorlog-table.php",
                data: function (d) {
                    d.log = log_logname;
                }
            },
            language: {
                "emptyTable": TRAN_TABLENODATA,
                "info": TRAN_TABLESHOWS + " _START_ " + TRAN_TABLETO + " _END_ " + TRAN_TABLETOTAL + " _TOTAL_ " + TRAN_TABLEROWS,
                "infoEmpty": TRAN_TABLESHOWS + " 0 " + TRAN_TABLETO + " 0 " + TRAN_TABLETOTAL + " 0 " + TRAN_TABLEROWS,
                "infoFiltered": "(" + TRAN_TABLEFILTERED + " _MAX_ " + TRAN_TABLEROWS + ")",
                "infoThousands": " ",
                "lengthMenu": TRAN_TABLESHOW + " _MENU_ " + TRAN_TABLEROWS,
                "loadingRecords": TRAN_TABLELOADING,
                "processing": TRAN_TABLEWORKING,
                "search": TRAN_TABLESEARCH,
                "zeroRecords": TRAN_TABLENORESULTS,
                "thousands": " ",
                "paginate": {
                    "first": TRAN_TABLEFIRST,
                    "last": TRAN_TABLELAST,
                    "next": TRAN_TABLENEXT,
                    "previous": TRAN_TABLEPREV
                },
                "select": {
                    "rows": {
                        "1": "1 " + TRAN_TABLESELECTED,
                        "_": "%d " + TRAN_TABLESELECTED
                    }
                },
                "aria": {
                    "sortAscending": ": " + TRAN_TABLENSORTRISE,
                    "sortDescending": ": " + TRAN_TABLENSORTFALL
                }
            },
            columns: [
                {
                    data: 'INFO'
                },
                {
                    data: 'DATE'
                },
            ],

        });
    }

    var initToggleToolbar = function () {
        const container = document.querySelector('#loggen_table');
        const checkboxes = container.querySelectorAll('[type="checkbox"]');
        const deleteSelected = document.querySelector('[data-kt-genlog-table-select="delete_selected"]');
        checkboxes.forEach(c => {
            c.addEventListener('click', function () {
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });

        deleteSelected.addEventListener('click', function () {
            Swal.fire({
                text: TRAN_REMOVEMARKEDLOGGEN,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                confirmButtonText: TRAN_YES,
                cancelButtonText: TRAN_NO,
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    var deleteids_arr = [];
                    $("input:checkbox[name=deletethis]:checked").each(function () {

                        deleteids_arr.push($(this).val());

                    });
                    if (deleteids_arr.length > 0) {
                        $.ajax({
                            url: HOST_URL + '/forms/loggenerator/delmultiplelogs.php',
                            type: 'post',
                            data: {
                                request: 2,
                                deleteids_arr: deleteids_arr
                            },
                            success: function (data) {
                                var mydata = $.parseJSON(data);
                                var fel = mydata.error;
                                var kod = mydata.errorcode;
                                if (fel == "false") {
                                    dt.ajax.reload();
                                }

                            }
                        });

                    } 
                }
            });
        });
    }

    var toggleToolbars = function () {
        const container = document.querySelector('#loggen_table');
        const toolbarBase = document.querySelector('[data-kt-genlog-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-genlog-table-select="selected"]');
        const selectedCount = document.querySelector('[data-kt-genlog-table-select="selected_count"]');
        const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');
        let checkedState = false;
        let count = 0;
        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    }

    const element3 = document.getElementById('generate_log');
    const modal3 = new bootstrap.Modal(element3);

    var initLogGenButtons = function () {
        const cancelButton2 = element3.querySelector('[data-kt-loggennow-modal-action="cancel"]');
        cancelButton2.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: TRAN_CLOSETHEWINDOW,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: TRAN_YES,
                cancelButtonText: TRAN_NO,
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    modal3.hide();
                }
            });
        });
        const closeButton2 = element3.querySelector('[data-kt-loggennow-modal-action="close"]');
        closeButton2.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: TRAN_CLOSETHEWINDOW,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: TRAN_YES,
                cancelButtonText: TRAN_NO,
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    modal3.hide();

                }
            });
        });
    }

    
    const element5 = document.getElementById('log_logs');
    const modal5 = new bootstrap.Modal(element5);

    var initLogLogButtons = function () {
        const cancelButton2 = element5.querySelector('[data-kt-loglog-modal-action="cancel"]');
        cancelButton2.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: TRAN_CLOSETHEWINDOW,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: TRAN_YES,
                cancelButtonText: TRAN_NO,
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    modal5.hide();
                }
            });
        });
        const closeButton2 = element5.querySelector('[data-kt-loglog-modal-action="close"]');
        closeButton2.addEventListener('click', e => {
            e.preventDefault();

            Swal.fire({
                text: TRAN_CLOSETHEWINDOW,
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: TRAN_YES,
                cancelButtonText: TRAN_NO,
                customClass: {
                    confirmButton: "btn btn-primary",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.value) {
                    modal5.hide();

                }
            });
        });
    }


    return {
        init: function () {
            initDatatable();
            initDatatableLog();
            initToggleToolbar();
            toggleToolbars();
            initLogLogButtons();
            initLogGenButtons();


        }
    }
}();

KTDatatablesServerSide.init();

$("#date").flatpickr({
    enableTime: false,
    minDate: "today",
    dateFormat: "Y-m-d",
    locale: {
        firstDayOfWeek: 1,
        weekAbbreviation: "v",

        weekdays: {
            shorthand: [TRAN_SUN, TRAN_MON, TRAN_TUE, TRAN_WED, TRAN_THU, TRAN_FRI, TRAN_SAT],
            longhand: [
                TRAN_SUND,
                TRAN_MOND,
                TRAN_TUED,
                TRAN_WEDD,
                TRAN_THUD,
                TRAN_FRID,
                TRAN_SATD,
            ],
        },

        months: {
            shorthand: [
                TRAN_JAN,
                TRAN_FEB,
                TRAN_MAR,
                TRAN_APR,
                TRAN_MAY,
                TRAN_JUN,
                TRAN_JUL,
                TRAN_AUG,
                TRAN_SEP,
                TRAN_OCT,
                TRAN_NOV,
                TRAN_DEC,
            ],
            longhand: [
                TRAN_JANM,
                TRAN_FEBM,
                TRAN_MARM,
                TRAN_APRM,
                TRAN_MAYM,
                TRAN_JUNM,
                TRAN_JULM,
                TRAN_AUGM,
                TRAN_SEPM,
                TRAN_OCTM,
                TRAN_NOVM,
                TRAN_DECM,
            ],
        },
    }
});
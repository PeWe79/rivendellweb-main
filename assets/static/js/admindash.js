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
var CONTROLLER = HOST_URL +"/update/controller.php";

var sprintf = function () {
	var message = arguments[0];
	for (var i = 1; i < arguments.length; ++i) {
		message = message.replaceAll('{' + (i - 1) + '}', arguments[i]);
	}
	return message;
}

function checkVersion() {
    var current = false;
	$.get(CONTROLLER, "action=VersionIsCurrent").then(function (data) {
		current = data.current;
		if (current) {
            $("#updTitle").html(TRAN_NOUPDATES);
			$("#infoUpdText").html(sprintf(TRAN_UPDATE_VERSIONUPTODATE, data.current_version));
		}
		else {
			$.get(CONTROLLER, "action=ReleaseNotes").then(function (data2) {
				if (data2.releaseNote) {
                    $("#updTitle").html(TRAN_UPDATE_NEWVERSIONISAVALIABLEDASH);
					$("#relNotesText").html(sprintf('<B>{0}</B>', data2.notes));
					$("#relNewsInfoShow").show();
                    $("#infoUpdText").html(sprintf(TRAN_UPDATE_VERSIONOUTOFDATE, data.current_version, data.update_version));
                    $("#updbtnclick").show();
				} else {
                    $("#updTitle").html(TRAN_UPDATE_NEWVERSIONISAVALIABLEDASH);
                    $("#infoUpdText").html(sprintf(TRAN_UPDATE_VERSIONOUTOFDATE, data.current_version, data.update_version));
                    $("#updbtnclick").show();
				}

			}, failed);
		}
	},
		failed);
}

var failed = function (xhr, status, error) {
    $("#errorInfo").show();
	$("#errorInfo").append(sprintf('<div class="error"><b>{0}</b></div>', TRAN_UPDATE_THEUPDATEFAILED));
	if (status === 'parsererror') {
		$("#errorInfo").append(xhr.responseText);
	}
	else {
		$("#errorInfo").append(sprintf('<div class="error"><b>Status:</b> {0}</div>', status));
		$("#errorInfo").append(sprintf('<div class="error"><b>Error:</b>  {0}</div>', error));

		try {
			var errorData = JSON.parse(xhr.responseText);
			$("#errorInfo").append(sprintf('<div class="error"><b>Message:</b>  {0}</div>', errorData.message));
			$("#errorInfo").append(sprintf('<div class="error"><b>Line:</b>  {0}</div>', errorData.line));
			$("#errorInfo").append(sprintf('<div class="error"><b>File:</b>  {0}</div>', errorData.file));
		}
		catch (e) {
			$("#errorInfo").append(printf('<div class="error"><b>Could not parse JSON:</b>  {0}</div>', e.message));
		}
	}
}

$('#usermess_form').validate({
    rules: {
        usrmess: {
            required: true,
        },
    },
    messages: {
        usrmess: {
            required: TRAN_NOTBEEMPTY,
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
        var dataString = $('#usermess_form').serialize();
        jQuery.ajax({
            type: "POST",
            url: HOST_URL + '/forms/usrmess.php',
            data: dataString,
            success: function (data) {
                var mydata = $.parseJSON(data);
                var fel = mydata.error;
                if (fel == "false") {
                    Swal.fire({
                        text: TRAN_MESSSAVED,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: TRAN_OK,
                        customClass: {
                            confirmButton: "btn btn-success"
                        }
                    });
                } else {
                    Swal.fire({
                        text: TRAN_BUG,
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: TRAN_OK,
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });


                }
            }
        });
    }
});

checkVersion();
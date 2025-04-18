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
var CONTROLLER = "controller.php";

$(document).ready(function () {
	setInterval(waiting, 1000);
	executeSteps(StepCounter.step);
	$(document).on('click', 'a', clearLinks);
	$(document).on('click', 'a[href="#"]', function (event) {
		event.preventDefault();
	});
});

var addUndoScripts = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_ADDINGUNDOSCRIPT));
	$.get(CONTROLLER, 'action=AddUndoScripts').then(function (data) {
		clearWaiting();
		if (data.success) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_UNDOISADDED));
			StepCounter.incrementAndExecuteStep(1);
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_FAILEDUNDOSCRIPTADD));
		}
	}, failed);
}

var advanceStep = function (step) {
	return ++step;
}

var backupFiles = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_BACKINGUPFILES));
	$.get(CONTROLLER, "action=BackupFiles").then(function (data) {
		clearWaiting();
		if (data.success) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_BACKUPCREATED));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_BACKUPFAILED));
		}
	}, failed);
}

var checkForBackups = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_CHECKINGFORBACKUPS));
	$.get(CONTROLLER, "action=CheckForBackups").then(function (data) {
		clearWaiting();
		if (data.exists) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_BACKUPSWEREFOUND));
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_WHATWOULDYOUDO));
			$("#info").append(sprintf('<div><a href="#" class="btn btn-info" onclick="StepCounter.setStepAndExecute(Step.CheckVersionFileExists);">{0}</a>&nbsp;<a href="#" class="btn btn-warning" onclick="StepCounter.setStepAndExecute(Step.ChooseBackupFile);" id="restoreVersion">{1}</a></div>', TRAN_UPDATE_CHECKFORUPDATES, TRAN_UPDATE_RESTOREABACKUP));
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_BACKUPSNOTFOUND));
			StepCounter.step = Step.CheckVersionFileExists;
			executeSteps(StepCounter.step);
		}
	}, failed);
}

var checkForScripts = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_CHECKFORSCRIPTS));
	$.get(CONTROLLER, "action=CheckForScripts").then(function (data) {
		clearWaiting();
		if (data.exists) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_SCRIPTEXIST));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_NOSCRIPTTORUN));
			StepCounter.incrementStep(2);
			executeSteps(StepCounter.step);
		}
	}, failed);
}

var checkIfUpdaterFilesBeingUpdated = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_CHECKTOSEUPDATERUP));
	$.get(CONTROLLER, "action=CheckIfUpdaterIsBeingUpdated").then(function (data) {
		clearWaiting();
		if (data.update) {
			StepCounter.incrementAndExecuteStep(1);
		}
		else {
			StepCounter.incrementAndExecuteStep(2);
		}
	}, failed);
}

var checkVersion = function () {
	var current = false;
	$.get(CONTROLLER, "action=VersionIsCurrent").then(function (data) {
		clearWaiting();
		current = data.current;
		if (current) {
			$("#info").append(sprintf('<div>' + TRAN_UPDATE_VERSIONUPTODATE + '</div>', data.current_version));
		}
		else {
			$.get(CONTROLLER, "action=ReleaseNotes").then(function (data2) {
				clearWaiting();
				if (data2.releaseNote) {
					$("#relNotesText").html(sprintf('<B>{0}</B>', data2.notes));
					$("#relNewsInfoShow").show();
					$("#info").append(sprintf('<div>' + TRAN_UPDATE_VERSIONOUTOFDATE + '</div>', data.current_version, data.update_version));
					StepCounter.incrementStep(1);
					$("#info").append(sprintf('<div><a href="#" class="btn btn-danger" onclick="executeSteps(StepCounter.step);" id="updateVersion">{0}</a></div>', TRAN_UPDATE_UPDATENOW));
				} else {
					$("#info").append(sprintf('<div>' + TRAN_UPDATE_VERSIONOUTOFDATE + '</div>', data.current_version, data.update_version));
					StepCounter.incrementStep(1);
					$("#info").append(sprintf('<div><a href="#" class="btn btn-danger" onclick="executeSteps(StepCounter.step);" id="updateVersion">{0}</a></div>', TRAN_UPDATE_UPDATENOW));
				}

			}, failed);
		}
	},
		failed);
}

var checkVersionFileExists = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', TRAN_UPDATE_CHECKVERFILEEXIST));
	$.get(CONTROLLER, 'action=CheckUpdateFileExists').then(function (data) {
		clearWaiting();
		if (data.exists) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_UPDATEFILEEXISTS));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else {
			$("#info").append(sprintf(sprintf('<div>{0}</div>', TRAN_UPDATE_UPDATEFILENOTFOUND), data.url));
		}
	}, failed);

}

var checkRemoteFiles = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_CHECKINGREMOTEFILES));
	$.get(CONTROLLER, "action=CheckRemoteFilesExist").then(function (data) {
		if (data.exists) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_REMOTEFILESEXIST));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_ONEORMOREREMOTENOTFOUND));
		}
	});
}

var checkWritablilty = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', TRAN_UPDATE_CHECKWRITABLEFILES));
	$.get(CONTROLLER, "action=CheckFilesAreWritable").then(function (data) {
		if (data.writable) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_FILESAREWRITABLE));
			StepCounter.incrementStep(1);
			executeSteps(StepCounter.step);
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_FILESNOTWRITABLEFAILED));
		}
		clearWaiting();
	}, failed);
}

var chooseBackupFile = function () {
	$("#info").append(sprintf('<div>{0} <span class=\"waiting\"></span>', TRAN_UPDATE_FETCHBACKUPVERSIONS));
	$.get(CONTROLLER, 'action=ChooseBackupFile').then(function (data) {
		clearWaiting();
		$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_WHICHVERSIONRESTORE));
		for (var i = 0; i < data.versions.length; ++i) {
			$("#info").append(sprintf('<div><a class="btn btn-primary" onclick="findAllNewerBackupsAndRestoreRequestedBackup(\'' + data.versions[i] + '\');" id="restoreBackup-{0}">{0}</a></div>', data.versions[i]));
		}

	}, failed);
}

var clearLinks = function (event) {
	$("#info a").parent("div").remove();
}

var clearWaiting = function () {
	$(".waiting").removeClass("waiting");
}

var createAuxController = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></div>', TRAN_UPDATE_CREATEAUXILIARYCONTR));
	$.get(CONTROLLER, "action=CreateAuxController").then(function (data) {
		clearWaiting();
		$("#info").append(sprintf("<div>{0}</div>", TRAN_UPDATE_AUXILIARYCREATED));
		CONTROLLER = "auxController.php";
		StepCounter.incrementAndExecuteStep(1);
	}, failed);
}

var deleteAuxController = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', TRAN_UPDATE_DELETEAUXILIARY));
	CONTROLLER = "controller.php";
	$.get(CONTROLLER, 'action=DeleteAuxController').then(function (data) {
		clearWaiting();
		if (data.success) {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_AUXICONTDELETED));
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_COULDNOTDELAUXI));
		}
		// It's not critical so continue even if not deleted
		StepCounter.incrementAndExecuteStep(1);
	}, failed);
}

var executeScripts = function (afterVersionUpdate = false) {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', TRAN_UPDATE_EXECUTINSCRIPTS));
	$.get(CONTROLLER, "action=ExecuteScripts&afterVersionUpdate=" + afterVersionUpdate).then(function (data) {

		$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', TRAN_UPDATE_SCRIPTFINISHED));
		StepCounter.incrementStep(1);
		executeSteps(StepCounter.step);

	}, failed);
}

var executeSteps = function (step) {
	switch (step) {
		case Step.CheckForBackups:
			checkForBackups();
			break;
		case Step.BackupFiles:
			backupFiles();
			break;
		case Step.ChooseBackupFile:
			chooseBackupFile();
			break;
		case Step.CheckVersionFileExists:
			checkVersionFileExists();
			break;
		case Step.CheckVersion:
			checkVersion();
			break;
		case Step.CheckWritability:
			checkWritablilty();
			break;
		case Step.CheckRemoteFilesExist:
			checkRemoteFiles();
			break;
		case Step.CheckIfUpdaterFilesBeingUpdated:
			checkIfUpdaterFilesBeingUpdated();
			break;
		case Step.CreateAuxController:
			createAuxController();
			break;
		case Step.InstallFiles:
			installFiles();
			break;
		case Step.UpdateVersion:
			updateVersion();
			break;
		case Step.CheckForScripts:
			checkForScripts();
			break;
		case Step.AddUndoScripts:
			addUndoScripts();
			break;
		case Step.ExecuteScripts:
			executeScripts();
			break;
		case Step.DeleteAuxController:
			deleteAuxController();
			break;
		case Step.Finished:
			finished();
			break;
		case Step.ExecuteScriptsAfterVersionUpdate:
			executeScripts(true);
			break;
		default:
			stepNotFound(step);
			break;
	}
}

var findAllNewerBackupsAndRestoreRequestedBackup = function (version) {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', TRAN_UPDATE_FINDINGNEWERBACKUPS));
	$.get(CONTROLLER, "action=FindAllNewerBackups&restoreVersion=" + version).then(function (data) {
		clearWaiting();
		restoreBackup(data.restoreVersions);

	}, failed);

}

var restoreBackup = function (versionArr) {
	var version = versionArr.shift();
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span>', sprintf(TRAN_UPDATE_RESTORING, version)));
	$.get(CONTROLLER, 'action=restoreBackup&version=' + version).then(
		function (data) {
			clearWaiting();
			$("#info").append(sprintf("<div id=\"restorationFinished\">{0}</div>", sprintf(TRAN_UPDATE_RESTORED, version)));
			if (versionArr.length > 0) {
				restoreBackup(versionArr);
			}
		}, failed);
}

var failed = function (xhr, status, error) {
	$("#info").append(sprintf('<div class="error"><b>{0}</b></div>', TRAN_UPDATE_THEUPDATEFAILED));
	clearWaiting();
	if (status === 'parsererror') {
		$("#info").append(xhr.responseText);
	}
	else {
		$("#info").append(sprintf('<div class="error"><b>Status:</b> {0}</div>', status));
		$("#info").append(sprintf('<div class="error"><b>Error:</b>  {0}</div>', error));

		try {
			var errorData = JSON.parse(xhr.responseText);
			$("#info").append(sprintf('<div class="error"><b>Message:</b>  {0}</div>', errorData.message));
			$("#info").append(sprintf('<div class="error"><b>Line:</b>  {0}</div>', errorData.line));
			$("#info").append(sprintf('<div class="error"><b>File:</b>  {0}</div>', errorData.file));
		}
		catch (e) {
			$("#info").append(printf('<div class="error"><b>Could not parse JSON:</b>  {0}</div>', e.message));
		}
	}
}

var finished = function () {
	$("#info").append(sprintf('<div id="updateFinished">{0}</div>', TRAN_UPDATE_THEUPDATEHASFINISHED));
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', sprintf(TRAN_UPDATE_TRYTOFINDFINISHBUTTON)));
	$.get(CONTROLLER, "action=FinishButton").then(function (data) {
		clearWaiting();
		if (data.finishUrl) {
			$("#info").append(sprintf('<div><a href="{0}" class="btn btn-success">{1}</a></div>', data.url, TRAN_UPDATE_FINISHED));
		}
		else {
			$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_NOFINISHBUTTONFOUND));
		}

	}, failed);
}

var installFiles = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_DOWNLOADINSTALLFILES));
	$.get(CONTROLLER, "action=InstallFiles").then(function (data) {
		clearWaiting();
		$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_FILESINSTALLED));
		StepCounter.incrementStep(1);
		executeSteps(StepCounter.step);
	}, failed);
}

var sprintf = function () {
	var message = arguments[0];
	for (var i = 1; i < arguments.length; ++i) {
		message = message.replaceAll('{' + (i - 1) + '}', arguments[i]);
	}
	return message;
}

var Step = {
	CheckForBackups: 0,
	BackupsExist: 1,
	ChooseBackupFile: 2,
	RestoreBackup: 3,
	RestorationComplete: 4,
	CheckVersionFileExists: 5,
	CheckVersion: 6,
	CheckWritability: 7,
	CheckRemoteFilesExist: 8,
	BackupFiles: 9,
	CheckIfUpdaterFilesBeingUpdated: 10,
	CreateAuxController: 11,
	InstallFiles: 12,
	CheckForScripts: 13,
	AddUndoScripts: 14,
	ExecuteScripts: 15,
	DeleteAuxController: 16,
	UpdateVersion: 17,
	ExecuteScriptsAfterVersionUpdate: 18,
	Finished: 19
}

var StepCounter = {
	step: 0,
	incrementStep: function (incBy) {
		this.step += incBy;
	},

	incrementAndExecuteStep: function (incBy) {
		this.step += incBy;
		executeSteps(this.step);
	},

	setStepAndExecute: function (step) {
		this.step = step;
		executeSteps(this.step);
	}
}

var stepNotFound = function (step) {
	$("#info").append(sprintf(sprintf('<div>{0}</div>', TRAN_UPDATE_STEPNOTFOUND), step));
}

var updateVersion = function () {
	$("#info").append(sprintf('<div>{0} <span class="waiting"></span></div>', TRAN_UPDATE_UPDATINGVERSIONFILE));
	$.get(CONTROLLER, "action=UpdateVersion").then(function (data) {
		clearWaiting();
		$("#info").append(sprintf('<div>{0}</div>', TRAN_UPDATE_VERSIONFILEUPDATED));
		StepCounter.incrementStep(1);
		executeSteps(StepCounter.step);
	}, failed);
}

var waiting = function () {
	$(".waiting").each(function () {
		var dots = $(this).text();
		dots += ".";
		if (dots.length > 3) {
			dots = "";
		}
		$(this).text(dots);
	});

}

function turnOffMain() {
	jQuery.ajax({
		type: "POST",
		url: HOST_URL + '/update/turnon.php',
		data: {
			turnon: 0
		},
		datatype: 'html',
		success: function (data) {
			var mydata = $.parseJSON(data);
			var fel = mydata.error;
			var kod = mydata.errorcode;
			if (fel == "false") {
				location.href = HOST_URL + "/admin/dash";

			}
		}
	});
}

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
let wavesurfer, record
let scrollingWaveform = false
var wavesurferfile;
var wavesurferfileedit;
var regionsplugin;
var timelineplugin;
var filenamelisten;
var stat = 1;
var stattalk = 0;
var stattalkend = 0;
var statsegue = 0;
var statsegueend = 0;
var statfadeup = 0;
var statfadedown = 0;
var stathook = 0;
var stathookend = 0;
var sampleOne = ["32000", "44100", "48000"];
var sampleTwo = ["16000", "22050", "32000", "44100", "48000"];
var bitOne = ["32", "48", "56", "64", "80", "96", "112", "128", "160", "192", "224", "256", "320", "384"];
var bitTwo = ["32", "40", "48", "56", "64", "80", "96", "112", "128", "160", "192", "224", "256", "320", "VBR"];
var ordtype = CUT_ORDER;
var cartid;
var cutname;

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

const createWaveSurfer = () => {
    if (wavesurfer) {
        wavesurfer.destroy()
    }
    wavesurfer = WaveSurfer.create({
        container: '#mic',
        waveColor: 'rgb(200, 0, 200)',
        progressColor: 'rgb(100, 0, 100)',
    })

    record = wavesurfer.registerPlugin(WaveSurfer.Record.create({ scrollingWaveform, renderRecordedAudio: false }))

    record.on('record-end', (blob) => {
        const container = document.querySelector('#recordings')
        const recordedUrl = URL.createObjectURL(blob)

        const wavesurfer = WaveSurfer.create({
            container,
            waveColor: 'rgb(200, 100, 0)',
            progressColor: 'rgb(100, 50, 0)',
            url: recordedUrl,
        })
        let xrid = Math.floor((Math.random() * 100) + 1);
        const button = container.appendChild(document.createElement('button'))
        button.textContent = TRAN_PLAY
        button.className = "btn btn-success"
        button.onclick = () => wavesurfer.playPause()
        wavesurfer.on('pause', () => (button.textContent = TRAN_PLAY))
        wavesurfer.on('play', () => (button.textContent = TRAN_PAUSE))
        const buttonsave = container.appendChild(document.createElement('button'))
        buttonsave.textContent = TRAN_SAVEREQ
        buttonsave.className = "btn btn-warning"
        buttonsave.id = xrid
        buttonsave.onclick = () => convertBlobToAudioBuffer(blob, xrid)
        const link = container.appendChild(document.createElement('a'))
        Object.assign(link, {
            href: recordedUrl,
            className: "btn btn-primary",
            download: 'recording.' + blob.type.split(';')[0].split('/')[1] || 'webm',
            textContent: TRAN_DOWN_REQ,
        })
    })
    pauseButtonRec.style.display = 'none'
    recButtonRec.textContent = TRAN_RECORD

    record.on('record-progress', (time) => {
        updateProgress(time)
    })

}

const progressRec = document.querySelector('#progress')
const updateProgress = (time) => {
    const formattedTime = [
        Math.floor((time % 3600000) / 60000),
        Math.floor((time % 60000) / 1000),
    ]
        .map((v) => (v < 10 ? '0' + v : v))
        .join(':')
    progressRec.textContent = formattedTime
}

const pauseButtonRec = document.querySelector('#pauserec')
pauseButtonRec.onclick = () => {
    if (record.isPaused()) {
        record.resumeRecording()
        pauseButtonRec.textContent = TRAN_PAUSE
        return
    }

    record.pauseRecording()
    pauseButtonRec.textContent = TRAN_RESUME
}


const recButtonRec = document.querySelector('#recordrec')

recButtonRec.onclick = () => {
    if (record.isRecording() || record.isPaused()) {
        record.stopRecording()
        recButtonRec.textContent = TRAN_RECORD
        pauseButtonRec.style.display = 'none'
        return
    }

    recButtonRec.disabled = true
    record.startRecording(record.startMic()).then(() => {
        recButtonRec.textContent = TRAN_STOP
        recButtonRec.disabled = false
        pauseButtonRec.style.display = 'inline'
    })
}



function getWavBytes(buffer, options) {
    const type = options.isFloat ? Float32Array : Uint16Array
    const numFrames = buffer.byteLength / type.BYTES_PER_ELEMENT

    const headerBytes = getWavHeader(Object.assign({}, options, { numFrames }))
    const wavBytes = new Uint8Array(headerBytes.length + buffer.byteLength);

    // prepend header, then add pcmBytes
    wavBytes.set(headerBytes, 0)
    wavBytes.set(new Uint8Array(buffer), headerBytes.length)

    return wavBytes
}

function getWavHeader(options) {
    const numFrames = options.numFrames
    const numChannels = options.numChannels || 2
    const sampleRate = options.sampleRate || 44100
    const bytesPerSample = options.isFloat ? 4 : 2
    const format = options.isFloat ? 3 : 1

    const blockAlign = numChannels * bytesPerSample
    const byteRate = sampleRate * blockAlign
    const dataSize = numFrames * blockAlign

    const buffer = new ArrayBuffer(44)
    const dv = new DataView(buffer)

    let p = 0

    function writeString(s) {
        for (let i = 0; i < s.length; i++) {
            dv.setUint8(p + i, s.charCodeAt(i))
        }
        p += s.length
    }

    function writeUint32(d) {
        dv.setUint32(p, d, true)
        p += 4
    }

    function writeUint16(d) {
        dv.setUint16(p, d, true)
        p += 2
    }

    writeString('RIFF')
    writeUint32(dataSize + 36)
    writeString('WAVE')
    writeString('fmt ')
    writeUint32(16)
    writeUint16(format)
    writeUint16(numChannels)
    writeUint32(sampleRate)
    writeUint32(byteRate)
    writeUint16(blockAlign)
    writeUint16(bytesPerSample * 8)
    writeString('data')
    writeUint32(dataSize)

    return new Uint8Array(buffer)
}

function convertAudioBufferToBlob(audioBuffer) {
    var channelData = [],
        totalLength = 0,
        channelLength = 0;

    for (var i = 0; i < audioBuffer.numberOfChannels; i++) {
        channelData.push(audioBuffer.getChannelData(i));
        totalLength += channelData[i].length;
        if (i == 0) channelLength = channelData[i].length;
    }

    const interleaved = new Float32Array(totalLength);

    for (
        let src = 0, dst = 0;
        src < channelLength;
        src++, dst += audioBuffer.numberOfChannels
    ) {
        for (var j = 0; j < audioBuffer.numberOfChannels; j++) {
            interleaved[dst + j] = channelData[j][src];
        }
    }

    const wavBytes = getWavBytes(interleaved.buffer, {
        isFloat: true,
        numChannels: audioBuffer.numberOfChannels,
        sampleRate: 48000,
    });
    const wav = new Blob([wavBytes], { type: "audio/wav" });
    return wav;
}

function convertBlobToAudioBuffer(myBlob, idnomb) {
    const audioContext = new AudioContext();
    const fileReader = new FileReader();
    $("#" + idnomb).prop("disabled", true);

    fileReader.onloadend = () => {

        let myArrayBuffer = fileReader.result;

        audioContext.decodeAudioData(myArrayBuffer, (audioBuffer) => {

            let blob = convertAudioBufferToBlob(audioBuffer);
            recordToCart(blob, idnomb)
        });
    };
    fileReader.readAsArrayBuffer(myBlob);
}

function recordToCart(thefile, idnomb) {
    var fd = new FormData();
    fd.append("audio_data", thefile, cutname);
    fd.append("cut", cutname);
    fd.append("cart", cartid);
    fd.append("audiochannels", $("#audiochannels_rec").val());
    fd.append("autotrim", $("#autotrim_rec").val());
    fd.append("trimlevel", $("#trimlevel_rec").val());
    fd.append("normalize", $("#normalize_rec").val());
    fd.append("normalizelevel", $("#normalizelevel_rec").val());
    jQuery.ajax({
        type: "POST",
        url: HOST_URL + "/forms/library/importrec.php",
        data: fd,
        async: false,
        success: function () {
            $('body').loading('stop');
            $("#record_voice").modal("hide");
            $("#" + idnomb).prop("disabled", false);
            dt.ajax.reload();
        },
        cache: false,
        contentType: false,
        processData: false
    });
}

function recordcut(cart, cut) {

    if (ALLOW_MOD == 1) {

        cartid = cart;
        cutname = cut;
        createWaveSurfer()
        $("#record_voice").modal("show");

    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }

}
const playButton = document.querySelector('#play')
const forwardButton = document.querySelector('#forward')
const backButton = document.querySelector('#backward')

function addMarker(i) {
    if (i == 1 && stattalk == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            id: 'talkstart',
            content: TRAN_TALKSTART,
            color: 'rgba(31, 22, 204, 0.5)',
        })
        stattalk = 1;
    } else if (i == 1 && stattalkend == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_TALKEND,
            id: 'talkend',
            color: 'rgba(31, 22, 204, 0.5)',
        })
        stattalkend = 1;
    } else if (i == 2 && statsegue == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_SEGUESTART,
            id: 'seguestart',
            color: 'rgba(59, 232, 255, 0.5)',
        })
        statsegue = 1;
    } else if (i == 2 && statsegueend == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_SEGUEEND,
            id: 'segueend',
            color: 'rgba(59, 232, 255, 0.5)',
        })
        statsegueend = 1;
    }
    else if (i == 3 && statfadeup == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_FADEUP,
            id: 'fadeup',
            color: 'rgba(69, 128, 78, 0.5)',
        })
        statfadeup = 1;
    } else if (i == 4 && statfadedown == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_FADEDOWN,
            id: 'fadedown',
            color: 'rgba(69, 128, 78, 0.5)',
        })
        statfadedown = 1;
    } else if (i == 5 && stathook == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_HOOKSTART,
            id: 'hookstart',
            color: 'rgba(204, 22, 156, 0.5)',
        })
        stathook = 1;
    } else if (i == 5 && stathookend == 0) {
        regionsplugin.addRegion({
            start: wavesurferfileedit.getCurrentTime(),
            content: TRAN_HOOKEND,
            id: 'hookend',
            color: 'rgba(204, 22, 156, 0.5)',
        })
        stathookend = 1;
    }
}


function editcutaudio(i) {
    if (ALLOW_AUDIO == 1) {
        Swal.fire({
            text: TRAN_EDITAUDIO,
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
                $("#thecutname").val(i);
                $('#cuestart').val('-1');
                $('#cueend').val('-1');
                $('#seguestart').val('-1');
                $('#segueend').val('-1');
                $('#fadeup').val('-1');
                $('#fadedown').val('-1');
                $('#talkstart').val('-1');
                $('#talkend').val('-1');
                $('#hookstart').val('-1');
                $('#hookend').val('-1');
                $('#markerbody').preloader({
                    text: TRAN_LOADINGAUDIO,
                });

                wavesurferfileedit = WaveSurfer.create({
                    container: '#editnow',
                    height: '150',
                    width: '800',
                    normalize: true,
                    splitChannels: true,
                    waveColor: '#ff4e00',
                    progressColor: '#dd5e98',
                    cursorColor: '#ddd5e9',
                    cursorWidth: 2,
                    dragToSeek: true,
                    url: HOST_URL + '/forms/library/export.php?cutname=' + i + '&mp3=0'
                })
                regionsplugin = wavesurferfileedit.registerPlugin(WaveSurfer.Regions.create());
                timelineplugin = wavesurferfileedit.registerPlugin(WaveSurfer.Timeline.create({
                    height: 10,
                    timeInterval: 1,
                    primaryLabelInterval: 30,
                    secondaryLabelInterval: 10,
                    style: {
                        fontSize: '10px',
                        color: '#ad0e0e',
                    }
                }));

                wavesurferfileedit.once('decode', () => {
                    document.querySelector('input[type="range"]').oninput = (e) => {
                        const minPxPerSec = Number(e.target.value)
                        wavesurferfileedit.zoom(minPxPerSec)
                    }
                    playButton.onclick = () => {
                        wavesurferfileedit.playPause()
                    }

                    forwardButton.onclick = () => {
                        wavesurferfileedit.skip(1)
                    }

                    backButton.onclick = () => {
                        wavesurferfileedit.skip(-1)
                    }

                    $(document).keydown(function (event) {

                        switch (event.which) {
                            case 32:
                                wavesurferfileedit.playPause();
                                return false;
                                break;
                            case 37:
                                wavesurferfileedit.skip(-1);
                                return false;
                                break;
                            case 39:
                                wavesurferfileedit.skip(1);
                                return false;
                                break;
                            case 84:
                                addMarker(1);
                                return false;
                                break;
                            case 83:
                                addMarker(2);
                                return false;
                                break;
                            case 70:
                                addMarker(3);
                                return false;
                                break;
                            case 71:
                                addMarker(4);
                                return false;
                                break;
                            case 72:
                                addMarker(5);
                                return false;
                                break;
                        }

                    });

                    $.ajax({
                        url: HOST_URL + '/forms/library/cutinfo.php',
                        data: "id=" + i,
                        dataType: 'json',
                        success: function (data) {
                            regionsplugin.addRegion({
                                start: data['START_POINT'] / 1000,
                                id: 'cuestart',
                                content: TRAN_CUTSTART,
                                color: 'rgba(115, 5, 5, 0.5)',
                            })

                            regionsplugin.addRegion({
                                start: data['END_POINT'] / 1000,
                                id: 'cueend',
                                content: TRAN_CUTEND,
                                color: 'rgba(115, 5, 5, 0.5)',
                            })

                            if (data['SEGUE_START_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['SEGUE_START_POINT'] / 1000,
                                    id: 'seguestart',
                                    content: TRAN_SEGUESTART,
                                    color: 'rgba(59, 232, 255, 0.5)',
                                })
                            }
                            if (data['SEGUE_END_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['SEGUE_END_POINT'] / 1000,
                                    id: 'segueend',
                                    content: TRAN_SEGUEEND,
                                    color: 'rgba(59, 232, 255, 0.5)',
                                })
                            }
                            if (data['FADEUP_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['FADEUP_POINT'] / 1000,
                                    id: 'fadeup',
                                    content: TRAN_FADEUP,
                                    color: 'rgba(69, 128, 78, 0.5)',
                                })
                            }

                            if (data['FADEDOWN_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['FADEDOWN_POINT'] / 1000,
                                    id: 'fadedown',
                                    content: TRAN_FADEDOWN,
                                    color: 'rgba(69, 128, 78, 0.5)',
                                })
                            }

                            if (data['TALK_START_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['TALK_START_POINT'] / 1000,
                                    id: 'talkstart',
                                    content: TRAN_TALKSTART,
                                    color: 'rgba(31, 22, 204, 0.5)',
                                })
                            }

                            if (data['TALK_END_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['TALK_END_POINT'] / 1000,
                                    id: 'talkend',
                                    content: TRAN_TALKEND,
                                    color: 'rgba(31, 22, 204, 0.5)',
                                })
                            }
                            if (data['HOOK_START_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['HOOK_START_POINT'] / 1000,
                                    id: 'hookstart',
                                    content: TRAN_HOOKSTART,
                                    color: 'rgba(219, 4, 205, 0.5)',
                                })
                            }
                            if (data['HOOK_END_POINT'] != '-1') {
                                regionsplugin.addRegion({
                                    start: data['HOOK_END_POINT'] / 1000,
                                    id: 'hookend',
                                    content: TRAN_HOOKEND,
                                    color: 'rgba(219, 4, 205, 0.5)',
                                })
                            }
                        }
                    });

                    regionsplugin.on('region-created', (region) => {
                        var number = region.start;
                        var fixedNum = number.toFixed(3) * 1000;
                        if (region.id == 'cuestart') {
                            $('#cuestart').val(fixedNum)
                        }
                        if (region.id == 'cueend') {
                            $('#cueend').val(fixedNum)
                        }
                        if (region.id == 'seguestart') {
                            $('#seguestart').val(fixedNum);
                            statsegue = 1;
                        }
                        if (region.id == 'segueend') {
                            $('#segueend').val(fixedNum);
                            statsegueend = 1;
                        }
                        if (region.id == 'fadeup') {
                            $('#fadeup').val(fixedNum);
                            statfadeup = 1;
                        }
                        if (region.id == 'fadedown') {
                            $('#fadedown').val(fixedNum);
                            statfadedown = 1;
                        }
                        if (region.id == 'talkstart') {
                            $('#talkstart').val(fixedNum);
                            stattalk = 1;
                        }
                        if (region.id == 'talkend') {
                            $('#talkend').val(fixedNum);
                            stattalkend = 1;
                        }
                        if (region.id == 'hookstart') {
                            $('#hookstart').val(fixedNum);
                            stathook = 1;
                        }
                        if (region.id == 'hookend') {
                            $('#hookend').val(fixedNum);
                            stathookend = 1;
                        }
                    });
                    regionsplugin.on('region-updated', (region) => {
                        var number = region.start;
                        var fixedNum = number.toFixed(3) * 1000;
                        if (region.id == 'cuestart') {
                            $('#cuestart').val(fixedNum)
                        }
                        if (region.id == 'cueend') {
                            $('#cueend').val(fixedNum)
                        }
                        if (region.id == 'seguestart') {
                            $('#seguestart').val(fixedNum)
                        }
                        if (region.id == 'segueend') {
                            $('#segueend').val(fixedNum)
                        }
                        if (region.id == 'fadeup') {
                            $('#fadeup').val(fixedNum)
                        }
                        if (region.id == 'fadedown') {
                            $('#fadedown').val(fixedNum)
                        }
                        if (region.id == 'talkstart') {
                            $('#talkstart').val(fixedNum)
                        }
                        if (region.id == 'talkend') {
                            $('#talkend').val(fixedNum)
                        }
                        if (region.id == 'hookstart') {
                            $('#hookstart').val(fixedNum)
                        }
                        if (region.id == 'hookend') {
                            $('#hookend').val(fixedNum)
                        }
                    });
                    regionsplugin.on('region-double-clicked', (region) => {
                        region.remove();
                        if (region.id == 'seguestart') {
                            statsegue = 0;
                        }
                        if (region.id == 'segueend') {
                            statsegueend = 0;
                        }
                        if (region.id == 'fadeup') {
                            statfadeup = 0;
                        }
                        if (region.id == 'fadedown') {
                            statfadedown = 0;
                        }
                        if (region.id == 'talkstart') {
                            stattalk = 0;
                        }
                        if (region.id == 'talkend') {
                            stattalkend = 0;
                        }
                        if (region.id == 'hookstart') {
                            stathook = 0;
                        }
                        if (region.id == 'hookend') {
                            stathookend = 0;
                        }

                    });
                    regionsplugin.on('region-removed', (region) => {
                        if (region.id == 'seguestart') {
                            $('#seguestart').val("-1")
                        }
                        if (region.id == 'segueend') {
                            $('#segueend').val("-1")
                        }
                        if (region.id == 'fadeup') {
                            $('#fadeup').val("-1")
                        }
                        if (region.id == 'fadedown') {
                            $('#fadedown').val("-1")
                        }
                        if (region.id == 'talkstart') {
                            $('#talkstart').val("-1")
                        }
                        if (region.id == 'talkend') {
                            $('#talkend').val("-1")
                        }
                        if (region.id == 'hookstart') {
                            $('#hookstart').val("-1")
                        }
                        if (region.id == 'hookend') {
                            $('#hookend').val("-1")
                        }
                    });

                    $('#markerbody').preloader('remove');
                })

                
                $("#audio_editor").modal("show");
            }
        });

    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }
}

$('#for_format').change(function () {
    var selectedCategory = $('#for_format').val();
    if (selectedCategory == '5') {
        $('#for_quality').removeAttr('disabled');
    } else {
        $('#for_quality').attr('disabled', 'disabled');
    }
    if (selectedCategory != "") {

        $('#for_samplerate').find('option').remove();
        $('#for_bitrate').find('option').remove();

        var sizeList = [];

        if (selectedCategory == '2') {
            $('#for_bitrate').removeAttr('disabled');
            for (var i = 0; i <= sampleTwo.length; i++) {
                var sampTwo = sampleTwo[i];
                $('#for_samplerate').append($("<option></option>").attr("value", sampTwo).text(sampTwo));
            }
            for (var i = 0; i <= bitOne.length; i++) {
                var biOne = bitOne[i];
                $('#for_bitrate').append($("<option></option>").attr("value", biOne).text(biOne));
            }
        }
        else {
            if (selectedCategory == '3') {
                $('#for_bitrate').removeAttr('disabled');
                for (var i = 0; i <= bitTwo.length; i++) {
                    var biTwo = bitTwo[i];
                    $('#for_bitrate').append($("<option></option>").attr("value", biTwo).text(biTwo));
                }
            } else {
                $('#for_bitrate').attr('disabled', 'disabled');
            }

            for (var i = 0; i <= sampleOne.length; i++) {
                var sampOne = sampleOne[i];
                $('#for_samplerate').append($("<option></option>").attr("value", sampOne).text(sampOne));
            }
        }
    } else {

        $("#for_bitrate").empty();
        $('#for_bitrate').append($("<option></option>").attr("value", "").text(TRAN_SELECTBITRATE));
        $("#for_samplerate").empty();
        $('#for_samplerate').append($("<option></option>").attr("value", "").text(TRAN_SELECTSAMPLERATE));
    }
});

$('#for_bitrate').change(function () {
    var selectedBitrate = $('#for_bitrate').val();
    if (selectedBitrate == 'VBR') {
        $('#for_quality').removeAttr('disabled');
    } else {
        $('#for_quality').attr('disabled', 'disabled');
    }
});

function exportcut(i) {
    if (ALLOW_AUDIO == 1) {
        $('#for_cutcart').val(i);
        $('#export_cut').modal('show');
    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }
}

Dropzone.autoDiscover = false;
var myDropzone = new Dropzone("#dropzone_upload", {
    url: HOST_URL + "/forms/library/chunk-uploadcut.php",
    parallelUploads: 1,
    parallelChunkUploads: true,
    retryChunks: true,
    retryChunksLimit: 3,
    forceChunking: true,
    chunkSize: 1000000,
    maxFiles: 1,
    chunking: true,
    acceptedFiles: ".mp3,.wav",
    maxFilesize: 500,
    chunksUploaded: function (file, done) {
        let currentFile = file;
        jQuery.ajax({
            type: "POST",
            url: HOST_URL + '/forms/library/chunk-importcut.php',
            data: {
                dzuuid: currentFile.upload.uuid,
                dztotalchunkcount: currentFile.upload.totalChunkCount,
                fileName: currentFile.name.substr((currentFile.name.lastIndexOf('.') + 1)),
                audiochannels: $('#audiochannels').val(),
                autotrim: $('#autotrim').val(),
                trimlevel: $('#trimlevel').val(),
                normalize: $('#normalize').val(),
                normalizelevel: $('#normalizelevel').val(),
                cartid: $('#cartid').val(),
                cutid: $('#cutid_imp').val(),
            },
            datatype: 'html',
            success: function (data) {
                myDropzone.removeFile(file);
                dt.ajax.reload();
            },
            error: function (msg) {
                currentFile.accepted = false;
                myDropzone._errorProcessing([currentFile], msg.responseText);
            }
        });

    },
});

function deletecut(i, c, o) {
    var trans = tr('REMOVECUTWARN {{' + o + '}}');
    if (ALLOW_MOD == 1 || ALLOW_DEL == 1) {
        Swal.fire({
            text: trans,
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
                    url: HOST_URL + '/forms/library/removecut.php',
                    data: {
                        cartid: i,
                        cutid: c
                    },
                    datatype: 'html',
                    success: function (data) {
                        var mydata = $.parseJSON(data);
                        var fel = mydata.error;
                        var kod = mydata.errorcode;
                        if (fel == "false") {
                            dt.ajax.reload();
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

    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }
}

function addcut(i) {
    if (ALLOW_MOD == 1) {
        jQuery.ajax({
            type: "POST",
            url: HOST_URL + "/forms/library/addcut.php",
            data: {
                cartid: i
            },
            datatype: 'html',
            success: function (data) {
                var mydata = $.parseJSON(data);
                var fel = mydata.error;
                if (fel == "false") {
                    dt.ajax.reload();
                }
            }
        });
    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }
}

function importcut(i) {
    if (ALLOW_AUDIO == 1) {
        $('#cutid_imp').val(i);
        $('#import_cut').modal('show');
    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }
}

function getTimeFromMillis(millis) {

    var minutes = '' + Math.floor(millis / 60000);
    millis = millis % 60000;

    while (minutes.length < 2)
        minutes = '0' + minutes;

    var seconds = '' + Math.floor(millis / 1000);
    if (seconds < 0)
        seconds = seconds * -1;

    while (seconds.length < 2)
        seconds = '0' + seconds;

    millis = millis % 1000;

    var tenths = Math.floor(millis / 100);

    if (tenths < 0)
        tenths = tenths * -1;

    return minutes + ':' + seconds + '.' + tenths;

}

function getMillisFromTime(time) {

    time = time.split(':');
    var millis = 0;

    if (time.length == 2 && time[1].indexOf('.') != -1) {

        var temp = time[1].split('.');

        time[1] = temp[0];
        time.push(temp[1]);

    }

    if (time.length >= 2) {

        millis += time[0] * 60000;
        millis += time[1] * 1000;

    }

    if (time.length == 3)
        millis += time[2] * 100;

    return millis;

}

$("#avleng").val(getTimeFromMillis(AV_LENG));
$("#frlength").val(getTimeFromMillis(FO_LENG));

$('#enflength').click(function () {

    if ($("#enflength").is(":checked")) {
        $("#frlength").removeAttr('disabled');
    } else {
        $('#frlength').prop("disabled", true);
    }

});

let choices = document.querySelectorAll(".choices")
let initChoice
for (let i = 0; i < choices.length; i++) {
    if (choices[i].classList.contains("multiple-remove")) {
        initChoice = new Choices(choices[i], {
            delimiter: ",",
            editItems: true,
            maxItemCount: -1,
            removeItemButton: true,
        })
    } else {
        initChoice = new Choices(choices[i], {
            noResultsText: TRAN_SELECTNORESULTS,
            noChoicesText: TRAN_SELECTNOOPTIONS,
            itemSelectText: TRAN_SELECTPRESSSELECT,
        })
    }
}

function getRowColor(cut) {
    var colval;
    jQuery.ajax({
        type: "POST",
        async: false,
        url: HOST_URL + '/forms/library/getrowcolor.php',
        data: {
            cut: cut
        },
        datatype: 'html',
        success: function (data) {
            var mydata = $.parseJSON(data);
            colval = mydata.color;

        }
    });

    return colval;
}


dt = $("#cuts_table").DataTable({
    processing: true,
    scrollY: "500px",
    scrollCollapse: true,
    paging: false,
    dom: "<'table-responsive'tr>",
    ordering: false,
    order: [
        [0, 'desc']
    ],
    stateSave: true,
    ajax: {
        url: HOST_URL + "/tables/cuts-table.php",
        data: function (d) {
            d.cartid = CART_ID;
            d.theorder = ordtype;
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
            data: 'cutname'
        },
        {
            data: 'weight'
        },
        {
            data: 'description'
        },
        {
            data: 'length'
        },
        {
            data: 'lastplaydate'
        },
        {
            data: 'playcounter'
        },
        {
            data: null
        },
    ],
    columnDefs: [

        {
            targets: 1,
            render: function (data, type, row) {


                if (ordtype == 1) {
                    return data;
                } else {
                    return row.playorder;
                }




            }
        },

        {
            targets: 3,
            render: function (data, type, row) {


                return getTimeFromMillis(data);




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
                <a href="javascript:;" onclick="cutinfo('`+ row.cutname + `')" class="btn icon btn-primary" data-bs-toggle="tooltip" data-bs-placement="top"
                title="`+ TRAN_CUTINFOEDIT + `"><i class="bi bi-pencil"></i></a>
                <a href="javascript:;" onclick="editcutaudio('`+ row.cutname + `')" class="btn icon btn-info" data-bs-toggle="tooltip" data-bs-placement="top"
                title="`+ TRAN_EDITAUDIOMARKERS + `"><i class="bi bi-soundwave"></i></a>
                <a href="javascript:;" onclick="recordcut('`+ row.cartnumber + `','` + row.cutname + `')" class="btn icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top"
                title="`+ TRAN_RECORD + `"><i class="bi bi-mic"></i></a>
                <a href="javascript:;" onclick="importcut('`+ row.cutname + `')" class="btn icon btn-success" data-bs-toggle="tooltip" data-bs-placement="top"
                title="`+ TRAN_IMPORTAUDIO + `"><i class="bi bi-file-music"></i></a>
                <a href="javascript:;" onclick="exportcut('`+ row.cutname + `')" class="btn icon btn-warning" data-bs-toggle="tooltip" data-bs-placement="top"
                title="`+ TRAN_EXPORTAUDIO + `"><i class="bi bi-cloud-download"></i></a>
                <a href="javascript:;" onclick="deletecut('`+ row.cartnumber + `','` + row.cutname + `','` + row.description + `')" class="btn icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top"
                title="`+ TRAN_REMOVECUT + `"><i class="bi bi-x-square"></i></a>
            </div>
                    `;
            }
        },
    ],

    rowCallback: function (row, data, index) {
        $(row).css('background-color', getRowColor(data.cutname));
    },
});

const element1 = document.getElementById('import_cut');
const modal1 = new bootstrap.Modal(element1);

var initImportModalButtons = function () {
    const cancelButton2 = element1.querySelector('[data-kt-import-modal-action="cancel"]');
    cancelButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSECUTIMPORT,
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
                modal1.hide();
            }
        });
    });
    const closeButton2 = element1.querySelector('[data-kt-import-modal-action="close"]');
    closeButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSECUTIMPORT,
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
                modal1.hide();

            }
        });
    });
}

const element2 = document.getElementById('export_cut');
const modal2 = new bootstrap.Modal(element2);

var initExportModalButtons = function () {
    const cancelButton2 = element2.querySelector('[data-kt-export-modal-action="cancel"]');
    cancelButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSEAUDIOEXPORTWINDOW,
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
                modal2.hide();
            }
        });
    });
    const closeButton2 = element2.querySelector('[data-kt-export-modal-action="close"]');
    closeButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSEAUDIOEXPORTWINDOW,
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
                modal2.hide();

            }
        });
    });
}

const element3 = document.getElementById('cut_info');
const modal3 = new bootstrap.Modal(element3);

var initCutInfoModalButtons = function () {
    const cancelButton2 = element3.querySelector('[data-kt-cutinfo-modal-action="cancel"]');
    cancelButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSECUTINFOWINDOW,
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
    const closeButton2 = element3.querySelector('[data-kt-cutinfo-modal-action="close"]');
    closeButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSECUTINFOWINDOW,
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

const element4 = document.getElementById('audio_editor');
const modal4 = new bootstrap.Modal(element4);
const formed4 = element4.querySelector('#edit_audio_form');

var initEditMarkerModalButtons = function () {
    const cancelButton2 = element4.querySelector('[data-kt-editmarker-modal-action="cancel"]');
    cancelButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSEEDITMARKERWINDOW,
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
                modal4.hide();
                wavesurferfileedit.destroy();
                statsegue = 0;
                statsegueend = 0;
                statfadedown = 0;
                statfadeup = 0;
                stathook = 0;
                stathookend = 0;
                stattalk = 0;
                stattalkend = 0;
            }
        });
    });
    const closeButton2 = element4.querySelector('[data-kt-editmarker-modal-action="close"]');
    closeButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_CLOSEEDITMARKERWINDOW,
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
                modal4.hide();
                wavesurferfileedit.destroy();
                statsegue = 0;
                statsegueend = 0;
                statfadedown = 0;
                statfadeup = 0;
                stathook = 0;
                stathookend = 0;
                stattalk = 0;
                stattalkend = 0;

            }
        });
    });
    const submitButton2 = element4.querySelector('[data-kt-editmarker-modal-action="submit"]');
    submitButton2.addEventListener('click', e => {
        e.preventDefault();

        Swal.fire({
            text: TRAN_EDITSAVEAUDIOMARK,
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
                submitButton2.disabled = true;
                var dataString = $(formed4).serialize();
                jQuery.ajax({
                    type: "POST",
                    url: HOST_URL + '/forms/library/savemarkers.php',
                    data: dataString,
                    success: function (data) {
                        var mydata = $.parseJSON(data);
                        var fel = mydata.error;
                        var kod = mydata.errorcode;
                        if (fel == "false") {
                            submitButton2.disabled = false;

                            modal4.hide();
                            wavesurferfileedit.destroy();
                            statsegue = 0;
                            statsegueend = 0;
                            statfadedown = 0;
                            statfadeup = 0;
                            stathook = 0;
                            stathookend = 0;
                            stattalk = 0;
                            stattalkend = 0;


                        } else {
                            if (kod == 1) {
                                Swal.fire({
                                    text: TRAN_NOTPOSSSAVEMAKRES,
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: TRAN_OK,
                                    customClass: {
                                        confirmButton: "btn btn-primary"
                                    }
                                });
                                submitButton2.disabled = false;
                            }
                        }
                    }
                });

            }
        });
    });
}

const element5 = document.getElementById('record_voice');
const modal5 = new bootstrap.Modal(element5);

var initRecordVoiceButtons = function () {
    const cancelButton2 = element5.querySelector('[data-kt-record-modal-action="cancel"]');
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
                wavesurfer.destroy();
            }
        });
    });
    const closeButton2 = element5.querySelector('[data-kt-record-modal-action="close"]');
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
                wavesurfer.destroy();

            }
        });
    });
}



$('#export_audio_form').validate({
    rules: {
        audioformat: {
            required: true,
        },
        audiochannels: {
            required: true
        },
        samplerate: {
            required: true
        },
    },
    messages: {
        audioformat: {
            required: TRAN_NOTBEEMPTY
        },
        audiochannels: {
            required: TRAN_NOTBEEMPTY
        },
        samplerate: {
            required: TRAN_NOTBEEMPTY
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
        var excut = $("#for_cutcart").val();
        var exformat = $("#for_format").val();
        var exchannels = $("#for_channels").val();
        var exsample = $("#for_samplerate").val();
        var exbit = $("#for_bitrate").val();
        var exqual = $("#for_quality").val();
        var exmeta = $("#for_exportmeta").val();
        var exnorma = $("#for_normalize").val();
        var exnormalev = $("#for_normalizelevel").val();
        if (exmeta == '1') {
            exmeta = 1;
        } else {
            exmeta = 0;
        }
        if (exnorma == '1') {
            exnorma = 1;
        } else {
            exnorma = 0;
        }

        window.open(HOST_URL + '/exportaudio.php?cut=' + excut + '&format=' + exformat + '&channels=' + exchannels + '&sample=' + exsample + '&bit=' + exbit + '&qual=' + exqual + '&meta=' + exmeta + '&norma=' + exnorma + '&norlev=' + exnormalev);
        $('#export_cut').modal('hide');
    }
});

$('#cut_form').validate({
    rules: {
        cdesc: {
            required: true,
        },
        weight: {
            digits: true,
        },
    },
    messages: {
        cdesc: {
            required: TRAN_NOTBEEMPTY
        },
        weight: {
            required: TRAN_ONLYDIGITS
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
        var dataString = $('#cut_form').serialize();
        jQuery.ajax({
            type: "POST",
            url: HOST_URL + '/forms/library/updatecut.php',
            data: dataString,
            success: function (data) {
                var mydata = $.parseJSON(data);
                var fel = mydata.error;
                if (fel == "false") {
                    $('#cut_info').modal('hide');
                    dt.ajax.reload();
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

$('#cart_form').validate({
    rules: {
        title: {
            required: true,
        },
    },
    messages: {
        title: {
            required: TRAN_NOTBEEMPTY
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
        var dataString = $('#cart_form').serialize();
        jQuery.ajax({
            type: "POST",
            url: HOST_URL + '/forms/library/updatecart.php',
            data: dataString,
            success: function (data) {
                var mydata = $.parseJSON(data);
                var fel = mydata.error;
                if (fel == "false") {
                    Swal.fire({
                        text: TRAN_CARTSAVED,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: TRAN_OK,
                        customClass: {
                            confirmButton: "btn btn-primary"
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

function cutinfo(i) {
    if (ALLOW_MOD == 1) {
        $.ajax({
            url: HOST_URL + '/forms/library/cutinfo.php',
            data: "id=" + i,
            dataType: 'json',
            success: function (data) {
                var cutname = data['CUT_NAME'];
                var cartnumber = data['CART_NUMBER'];
                var evergreen = data['EVERGREEN'];
                var outcue = data['OUTCUE'];
                var description = data['DESCRIPTION'];
                var isrc = data['ISRC'];
                var isci = data['ISCI'];
                var origin = data['ORIGIN_DATETIME'];
                var enddate = data['END_DATETIME'];
                var sun = data['SUN'];
                var mon = data['MON'];
                var tue = data['TUE'];
                var wed = data['WED'];
                var thu = data['THU'];
                var fri = data['FRI'];
                var sat = data['SAT'];
                var sdaypart = data['START_DAYPART'];
                var edaypart = data['END_DAYPART'];
                var sdatetime = data['START_DATETIME'];
                var edatetime = data['END_DATETIME'];
                var originname = data['ORIGIN_NAME'];
                var originlogin = data['ORIGIN_LOGIN_NAME'];
                var sourcehost = data['SOURCE_HOSTNAME'];
                if (ordtype == 1) {
                    var weight = data['WEIGHT'];
                    $("#cutwalab").html(TRAN_WEIGHT);
                } else {
                    var weight = data['PLAY_ORDER'];
                    $("#cutwalab").html(TRAN_ORDER);
                }
                var lastplaydate = data['LAST_PLAY_DATETIME'];
                var uploaddate = data['UPLOAD_DATETIME'];
                var playcounter = data['PLAY_COUNTER'];
                $('#cutid').val(cutname);
                $('#cdesc').val(description);
                $('#coutcue').val(outcue);
                $('#ciscicode').val(isci);
                $('#cisrc').val(isrc);
                $('#weight').val(weight);
                $('#csource').html(originlogin + "@" + originname);
                $('#cingest').html(originname + " - " + origin);
                $('#clastplayed').html(lastplaydate);
                $('#cofplays').html(playcounter);

                if (evergreen == "Y") {
                    $("#evergreen").prop('checked', true);
                    $('#weight').prop("disabled", true);
                    $('#airenable').prop("disabled", true);
                    $('#adstart').prop("disabled", true);
                    $('#adend').prop("disabled", true);
                    $('#airdaypartactive').prop("disabled", true);
                    $('#adaystart').prop("disabled", true);
                    $('#adayend').prop("disabled", true);
                    $('#daymon').prop("disabled", true);
                    $('#daytue').prop("disabled", true);
                    $('#daywed').prop("disabled", true);
                    $('#daythu').prop("disabled", true);
                    $('#dayfri').prop("disabled", true);
                    $('#daysat').prop("disabled", true);
                    $('#daysun').prop("disabled", true);
                    $("#adstart").val("");
                    $("#adend").val("");

                } else {
                    $("#evergreen").prop('checked', false);
                    $("#weight").removeAttr('disabled');
                    $("#airenable").removeAttr('disabled');
                    $("#adstart").removeAttr('disabled');
                    $("#adend").removeAttr('disabled');
                    $("#airdaypartactive").removeAttr('disabled');
                    $("#adaystart").removeAttr('disabled');
                    $("#adayend").removeAttr('disabled');
                    $("#daymon").removeAttr('disabled');
                    $("#daytue").removeAttr('disabled');
                    $("#daywed").removeAttr('disabled');
                    $("#daythu").removeAttr('disabled');
                    $("#dayfri").removeAttr('disabled');
                    $("#daysat").removeAttr('disabled');
                    $("#daysun").removeAttr('disabled');
                }
                if (sdatetime) {
                    $("#airenable").prop('checked', true);
                    $("#adstart").removeAttr('disabled');
                    $("#adend").removeAttr('disabled');
                    $('#adstart').val(sdatetime);
                    $('#adend').val(edatetime);
                } else {
                    $("#airenable").prop('checked', false);
                    $('#adstart').val('');
                    $('#adend').val('');
                    $('#adstart').prop("disabled", true);
                    $('#adend').prop("disabled", true);
                }
                if (sdaypart) {
                    $("#airdaypartactive").prop('checked', true);
                    $('#adaystart').val(sdaypart);
                    $('#adayend').val(edaypart);
                    $("#adaystart").removeAttr('disabled');
                    $("#adayend").removeAttr('disabled');
                } else {
                    $("#airdaypartactive").prop('checked', false);
                    $('#adaystart').prop("disabled", true);
                    $('#adayend').prop("disabled", true);
                    $('#adaystart').val('');
                    $('#adayend').val('');
                }
                if (sun == "Y") {
                    $("#daysun").prop('checked', true);
                } else {
                    $("#daysun").prop('checked', false);
                }
                if (mon == "Y") {
                    $("#daymon").prop('checked', true);
                } else {
                    $("#daymon").prop('checked', false);
                }
                if (tue == "Y") {
                    $("#daytue").prop('checked', true);
                } else {
                    $("#daytue").prop('checked', false);
                }
                if (wed == "Y") {
                    $("#daywed").prop('checked', true);
                } else {
                    $("#daywed").prop('checked', false);
                }
                if (thu == "Y") {
                    $("#daythu").prop('checked', true);
                } else {
                    $("#daythu").prop('checked', false);
                }
                if (fri == "Y") {
                    $("#dayfri").prop('checked', true);
                } else {
                    $("#dayfri").prop('checked', false);
                }
                if (sat == "Y") {
                    $("#daysat").prop('checked', true);
                } else {
                    $("#daysat").prop('checked', false);
                }
                $('#cut_info').modal('show');
            }
        });


    } else {
        Swal.fire({
            text: TRAN_NORIGHTS,
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: TRAN_OK,
            customClass: {
                confirmButton: "btn fw-bold btn-primary"
            }
        });
    }

}

$('#evergreen').click(function () {

    if ($("#evergreen").is(":checked")) {

        $('#weight').prop("disabled", true);
        $('#airenable').prop("disabled", true);
        $('#adstart').prop("disabled", true);
        $('#adend').prop("disabled", true);
        $('#airdaypartactive').prop("disabled", true);
        $('#adaystart').prop("disabled", true);
        $('#adayend').prop("disabled", true);
        $('#daymon').prop("disabled", true);
        $('#daytue').prop("disabled", true);
        $('#daywed').prop("disabled", true);
        $('#daythu').prop("disabled", true);
        $('#dayfri').prop("disabled", true);
        $('#daysat').prop("disabled", true);
        $('#daysun').prop("disabled", true);
        $("#adstart").val("");
        $("#adend").val("");

    } else {
        $("#weight").removeAttr('disabled');
        $("#airenable").removeAttr('disabled');
        $("#adstart").removeAttr('disabled');
        $("#adend").removeAttr('disabled');
        $("#airdaypartactive").removeAttr('disabled');
        $("#adaystart").removeAttr('disabled');
        $("#adayend").removeAttr('disabled');
        $("#daymon").removeAttr('disabled');
        $("#daytue").removeAttr('disabled');
        $("#daywed").removeAttr('disabled');
        $("#daythu").removeAttr('disabled');
        $("#dayfri").removeAttr('disabled');
        $("#daysat").removeAttr('disabled');
        $("#daysun").removeAttr('disabled');
    }

});

$('#airenable').click(function () {

    if ($("#airenable").is(":checked")) {


        $("#adstart").removeAttr('disabled');
        $("#adend").removeAttr('disabled');

    } else {
        $('#adstart').prop("disabled", true);
        $('#adend').prop("disabled", true);
        $("#adstart").val("");
        $("#adend").val("");
    }

});

$('#airdaypartactive').click(function () {

    if ($("#airdaypartactive").is(":checked")) {


        $("#adaystart").removeAttr('disabled');
        $("#adayend").removeAttr('disabled');

    } else {
        $('#adaystart').prop("disabled", true);
        $('#adayend').prop("disabled", true);
        $("#adaystart").val("");
        $("#adayend").val("");
    }

});

$("#adstart").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    time_24hr: true,
    enableSeconds: true,
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
        rangeSeparator: " " + TRAN_TO + " ",
        time_24hr: true,
    }
});

$("#adend").flatpickr({
    enableTime: true,
    dateFormat: "Y-m-d H:i:S",
    time_24hr: true,
    enableSeconds: true,
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
        rangeSeparator: " " + TRAN_TO + " ",
        time_24hr: true,
    }
});

$("#adaystart").flatpickr({
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i:S",
    enableSeconds: true,
    time_24hr: true
});

$("#adayend").flatpickr({
    enableTime: true,
    noCalendar: true,
    dateFormat: "H:i:S",
    enableSeconds: true,
    time_24hr: true
});

$('#schedcuts').on('change', function (e) {

    if ($('#schedcuts').val() == 'Y') {
        ordtype = 1;
        $("#tabord1").html(TRAN_WT);
        $("#tabord2").html(TRAN_WT);
        dt.ajax.reload();
    } else {
        ordtype = 2;
        $("#tabord1").html(TRAN_ORD);
        $("#tabord2").html(TRAN_ORD);
        dt.ajax.reload();
    }
});


initImportModalButtons();
initExportModalButtons();
initCutInfoModalButtons();
initEditMarkerModalButtons();
initRecordVoiceButtons();
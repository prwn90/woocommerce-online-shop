(function($){
    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;

    var AudioContext = AudioContext || window.AudioContext  || window.webkitAudioContext || false;
    if(AudioContext === false){ return; }

    var audioContext = new AudioContext;
    if (audioContext.createScriptProcessor == null){
        audioContext.createScriptProcessor = audioContext.createJavaScriptNode;
    }

    var $voiceNotes = $('.wplc-voice-notes'),
        $recording = $('#recording'),
        $recordingList = $('#wplc-voice-notes__recording-list');

    var microphone = undefined,
        microphoneLevel = audioContext.createGain(),
        mixer = audioContext.createGain();
    microphoneLevel.connect(mixer);
    mixer.connect(audioContext.destination);

    jQuery(document).on("tcx_dom_ready", function(e) {
        jQuery('.nifty_media_prompt').append('<li><i class="fa fa-microphone wplc-voice-note-toggle"></i></li>');
    });

    jQuery('body').on('click', '.wplc-voice-note-toggle', function(){
        jQuery(this).toggleClass('recording');
        wplc_voice_note_toggle_recording(jQuery(this).hasClass('recording'));
    }); 

    var audioRecorder = new WebAudioRecorder(mixer, {
        workerDir: wplc_visitor_voice.plugin_url
    });
    var encodingProcess = 'background';


    function wplc_voice_note_toggle_recording(recording){
        if (microphone == null){
            audioContext = new AudioContext;
            if (audioContext.createScriptProcessor == null){
                audioContext.createScriptProcessor = audioContext.createJavaScriptNode;
            }
            microphoneLevel = audioContext.createGain();
            mixer = audioContext.createGain();
            microphoneLevel.connect(mixer);
            mixer.connect(audioContext.destination);

            audioRecorder = new WebAudioRecorder(mixer, {
                workerDir: wplc_visitor_voice.plugin_url
            });

            // event handlers
            audioRecorder.onTimeout = function(recorder) {
                stopRecording(true);
            };

            audioRecorder.onComplete = function(recorder, blob) {
                saveRecording(blob, recorder.encoding);
                //$voiceNotes.addClass('is-active');
            };
        }

        navigator.getUserMedia({ audio: true },
            function(stream) {
                microphone = audioContext.createMediaStreamSource(stream);
                microphone.connect(microphoneLevel);
                stream.getAudioTracks()[0].enabled = false;

                if (recording){
                    stream.getAudioTracks()[0].enabled = true;
                    if (!audioRecorder.isRecording()) {
                        //audioTimeout = setTimeout(function () {
                        startRecording();
                        //}, 1000);
                    }
                } else {
                    if (audioRecorder.isRecording()) {
                        //clearTimeout(audioTimeout);
                        stopRecording(true);
                        stream.getAudioTracks()[0].enabled = false;
                        stream.getTracks()[0].stop();
                        audioContext.close();
                        microphone = null;
                    }
                }
            },
            function(error) {
                if (typeof $microphone !== 'undefined') {
                    $microphone[0].checked = false;
                }
                audioRecorder.onError(audioRecorder, "Could not get audio input.");
            }
        );
    }


    //save/delete recording
    function saveRecording(blob, encoding) {
        var time = new Date(),
            url = URL.createObjectURL(blob); 

        /*,
            html = "<p recording='" + url + "'>" +
                "<audio controls src='" + url + "'></audio> " +
                " (" + encoding.toUpperCase() + ") " +
                time +
                " <a class='btn btn-default wplc-send-voice-notes-btn' href='" + url +
                "'>" + wplc_visitor_voice.str_save + "</a> " +
                "<button class='btn btn-danger' recording='" +
                url +
                "'>" + wplc_visitor_voice.str_delete + "</button>" +
                "</p>";
        $recordingList.empty();
        $recordingList.prepend($(html));*/

        var fd = new FormData();
        fd.append('file', blob);
        fd.append('action', 'wplc_save_voice_notes');
        $.ajax({
            url: wplc_visitor_voice.ajax_url,
            type: 'POST',
            data: fd,
            contentType: false,
            processData: false,
            success: function (data) {
                $('#inputMessage').val(data);
                //$('.wplc-send-voice-notes-btn').data('file', data);
            }
        });
    }

    $recordingList.on('click', 'button', function(event) {
        var url = $(event.target).attr('recording');
        $("p[recording='" + url + "']").remove();
        URL.revokeObjectURL(url);
        $voiceNotes.removeClass('is-active');
    });

    $recordingList.on('click', '.wplc-send-voice-notes-btn', function (event) {
        event.preventDefault();
        $('#inputMessage').val($(this).data('file'));
        jQuery("#wplc_send_msg").click();
    });

    // encoding progress report modal
    var progressComplete = false;

    function startRecording() {
        $recording.removeClass('hidden');
        audioRecorder.setOptions({
            timeLimit: 120,
            encodeAfterRecord: true
        });
        audioRecorder.startRecording();
    }

    function stopRecording(finish) {
        $recording.addClass('hidden');
        if (finish) {
            audioRecorder.finishRecording();
        } else
            audioRecorder.cancelRecording();
    }

    $('.wplc-voice-notes__close').on('click', function () {
        $voiceNotes.removeClass('is-active');
    });

    jQuery(document).on("tcx_add_message_chatbox tcx_messages_added", function(e) {
        setTimeout(function(){
            jQuery('.wplc-msg-content-audio').each(function(){
                var current_note = jQuery(this);
                if(current_note.attr('data-audio-wave') === undefined){
                    var current_message_body = current_note.find('.messageBody');
                    var audio_link = current_message_body.find('a').attr('href');
                    current_note.attr('data-audio-wave', audio_link);

                    var wave_div = "<div id='wplc-waveform-" + current_note.attr('mid') + "'></div>";
                    current_message_body.html(wave_div);

                    var wavesurfer = WaveSurfer.create({
                        container: "#wplc-waveform-" + current_note.attr('mid'),
                        progressColor: '#0776b8',
                        cursorColor: '#BBBBBB',
                        height: 30
                    });

                    wavesurfer.load(audio_link);

                    current_note.find('.wplc-msg-content-audio-icon').on('click', function(){
                        wavesurfer.playPause();
                    });

                }
            });
        }, 200);
    });
})(jQuery);
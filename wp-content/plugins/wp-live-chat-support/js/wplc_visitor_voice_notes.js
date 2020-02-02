(function($){

    navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;

    var AudioContext = AudioContext || window.AudioContext  || window.webkitAudioContext || false;
    if(AudioContext === false){ return; }

    var audioContext = new AudioContext;
    if (audioContext.createScriptProcessor == null){
        audioContext.createScriptProcessor = audioContext.createJavaScriptNode;
    }

    var chatIcon = $('#wp-live-chat-header');

    var microphone = undefined,
        microphoneLevel = audioContext.createGain(),
        mixer = audioContext.createGain();
    microphoneLevel.connect(mixer);
    mixer.connect(audioContext.destination);

    var audioRecorder = new WebAudioRecorder(mixer, {
        workerDir: wplc_visitor_voice.plugin_url
    });

    var encodingProcess = 'background';


    $('#wplc_start_chat_btn').on('click', initVoiceNotes);

    $(document).on("wplc_animation_done", function(e) {
        var chatStatus = Cookies.get('nc_status');
        if (chatStatus !== 'undefined' && chatStatus === 'active') {
            initVoiceNotes();
        }
    });

    jQuery('body').on('click', '.wplc-voice-note-toggle', function(){
        jQuery(this).toggleClass('recording');
        wplc_voice_note_toggle_recording(jQuery(this).hasClass('recording'));
    }); 

    function initVoiceNotes() {
        if(! jQuery('.wplc-voice-note-toggle').length > 0){
            jQuery('#wplc_user_message_div').prepend('<i class="fa fa-microphone wplc-voice-note-toggle wplc-color-bg-1 wplc-color-2"></i>');
        }
    }

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
                //chatIcon.removeClass('is-recording');
            };

        }

        navigator.getUserMedia({ audio: true },
            function(stream) {
                microphone = audioContext.createMediaStreamSource(stream);
                microphone.connect(microphoneLevel);
                stream.getAudioTracks()[0].enabled = false;

                if (recording) {
                    stream.getAudioTracks()[0].enabled = true;
                    if (!audioRecorder.isRecording()) {
                        startRecording();
                    }
                } else {
                    if (audioRecorder.isRecording()) {
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

        
    function saveRecording(blob, encoding) {
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
                $('#wplc_chatmsg').val(data);
            }
        });
    }

    var progressComplete = false;

    function startRecording() {
        chatIcon.addClass('is-recording');
        audioRecorder.setOptions({
            timeLimit: 120,
            encodeAfterRecord: true
        });
        audioRecorder.startRecording();
    }

    function stopRecording(finish) {
        chatIcon.removeClass('is-recording');
        if (finish) {
            audioRecorder.finishRecording();
        } else
            audioRecorder.cancelRecording();
    }

    jQuery(document).on("tcx_new_message wplc_animation_done", function(e) {
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
@extends('layouts.admin')
@section('content')
<script>
    var videoPlayer = false;
    videoId = -1;
    uploadStart = 0;

    function secondsToHuman(timeInSeconds) {
        var seconds = timeInSeconds % 60;
        var minutes = Math.floor(timeInSeconds / 60);
        var hours = Math.floor(timeInSeconds / 3600);

        secsStr = seconds != 0 ? seconds + ' second' : '';
        minsStr = minutes % 60 != 0 ? minutes + ' minute': '';
        hourStr = hours != 0 ? hours + ' hour' : '';

        if (seconds > 1)
            secsStr += 's';
        if (minutes % 60 != 0 && minutes > 1)
            minsStr += 's';
        if (hours > 1)
            hourStr += 's';
        
        var strs = [hourStr, minsStr, secsStr];
        return strs.filter(e => e.length > 0).join(', ');
    }

    function getPresignedUrl() {
        var exts = ['mov', 'mp4'];
        var name = $('#name').val();
        var city = $('#city').val();
        var path = $('#upload-input').val();
        var extension = path.substring(path.lastIndexOf('.') + 1);
        var error = '';
        $('#error-msg').hide();

        if (path.length == 0)
            error = 'Please, select your video file.';
        else if (exts.indexOf(extension) < 0)
            error = 'The video extension should be contained in this list: ' + exts.join(', ') + '.'; 
        
        if (error.length > 0) {
            $('#error-msg').html(error).show();
            return false;
        }
        
        params = new FormData();
        params.append('name', name);
        params.append('city', city);
        params.append('extension', extension);

        $.ajax({
            url: API_BASE + 'addVideoWalk?api_token=' + API_TOKEN,
            data: params,
            type: 'POST',
            processData: false,
            contentType: false,
            success: function(data) {
                console.log(data);
                
                if (data.state == 'ok') {
                    videoId = data.id;

                    var inputs = data.videoUploadData.formInputs;
                    var action = data.videoUploadData.action;

                    $('#upload-form').attr('action', action);

                    for (const key in inputs) {
                        $("<input type='hidden' />")
                        .attr("name", key)
                        .attr("value", inputs[key])
                        .prependTo("#upload-form");
                    }
                    $('#upload-form').submit();
                    // map.removeLayer(currentClickedMarker);
                }
                else {
      
                }
            }
        });
    }

    function loadVideo(id) {
        $('#video-entries div[data-id=' + id + ']').trigger('click');
    }

    $(document).ready(function() {
        var bar = $('#progressBar');
        var percent = $('.percent');
        var status = $('#status');
        var file = $('#upload-input');
        $('#error-msg').hide();

        $("#videoModal").on("hidden.bs.modal", function () {
            console.log('modal cerrado');
            if (videoPlayer)
                videoPlayer.pause();
            // put your default event here
        });

        $(document).on('click', '.video-thumb', function() {
            var videoId = $(this).data('id');

            if (!videoId)
                return false;
            
            var src = $(this).data('src');
            var poster = $(this).data('poster');
            var subs = $(this).data('subs');
            console.log('configuro video ' + videoId + ' ' + subs);
            var myModal = new bootstrap.Modal(document.getElementById("videoModal"), {});

            videoPlayer = videojs('#video-player', {
                poster: poster,
                sources: [{src: src, type: 'application/x-mpegURL'}],
                tracks: [
                    { src: subs, kind: 'captions', srclang: 'en', label: 'English', default: true}
                ]
            });

            // FIX FOR CELL PHONES
            videoPlayer.landscapeFullscreen({
                fullscreen: {
                    alwaysInLandscapeMode: true, // Always enter fullscreen in landscape mode even when device is in portrait mode (works on chromium, firefox, and ie >= 11)
                }
            });

            videoPlayer.ready(function(){
                var settings = this.textTrackSettings;
                settings.setValues({
                        "backgroundColor": "#000",
                        "backgroundOpacity": "0",
                        "edgeStyle": "uniform",
                    });
                    settings.updateDisplay();
            });
            
            $('#videoModal').modal('show')
            // video.appendChild(source);
        });
        $(document).on({
            mouseenter: function () {
                $(this).find('.video-details, .play-icon').show();
            },
            mouseleave: function () {
                $(this).find('.video-details, .play-icon').hide();
            }
        }, ".video-thumb"); 
        /* $('.video-thumb').hover(function(){
            $(this).find('.video-details, .play-icon').show();
        },function(){
            $(this).find('.video-details, .play-icon').hide();
        }); */

        $('.video-js').each(function () {
            // videojs(this);
        });

        /* $(file).on('change', () => {
            $('#upload-btn').prop('disabled', file.val().length == 0)
        }) */

        $('form').ajaxForm({
            beforeSend: function() {
                status.empty();
                var percentVal = '0%';
                uploadStart = new Date().getTime() / 1000;
                bar.width(percentVal);
                percent.html(percentVal);

                $('.initial-form').addClass("initial-form-hidden");
                setTimeout(() => { 
                    $('.progress-div').attr('style', 'display: block !important');
                }, 1000);
            },
            beforeSubmit: function(data, $form, options) {
                var error = '';
                var file = data.find(e => e.name == 'file').value;
                var filename = file.name;
                var ext = filename.substring(filename.lastIndexOf('.') + 1);
                
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var fullPercent = (position / total) * 100;
                var percentVal = fullPercent.toFixed(2) + '%';
                var secsElapsed = (new Date().getTime() / 1000) - uploadStart;
                var eta = Math.round(Math.round((100 * secsElapsed) / fullPercent) - secsElapsed);
                
                // console.log(event, position, total, percentComplete, secsElapsed, eta);
                $('#upload-stats').html('<b>Estimated remaining time: </b>' + secondsToHuman(eta));

                // console.log($('.percent').css('display'))
                bar.width(percentVal);
                percent.html(percentVal);
            },
            complete: function(xhr) {
                console.log(xhr);
                status.html(xhr.responseText);
                params = new FormData();
                params.append('id', videoId);

                $('#upload-state').fadeOut(400, function() {
                    $('#upload-stats').hide();
                    $('.progress').hide();
                    $(this).html("Your video is now uploaded and it is being processed. This may take a while, but if you do not want to wait you can just come back later as your video will be automatically generated.").fadeIn(400);
                });

                var myInterval = setInterval(function(){
                    $.ajax({
                        url: API_BASE + 'transcodeVideo?api_token=' + API_TOKEN,
                        data: params, type: 'POST', processData: false, contentType: false,
                        success: function(data) {
                            console.log(data);
                            if (data.state == '3') {
                                console.log('corto el loop');
                                $('#upload-state').fadeOut(400, function() {
                                $(this).html("Your video is now ready! <a href='#' onclick='loadVideo(" + data.id + ");'>Watch video</a>").fadeIn(400);
                                var newVideo = '<div class="col-3 py-2">';
                                newVideo += '<div style="position: relative;" class="video-thumb" data-id="' + data.id + '"';
                                newVideo += 'data-src="' + data.streamUrl + '" data-poster="' + data.poster + '"';
                                newVideo += 'data-subs="' + data.subtitles + '">';
                                newVideo += '<img src="' + data.poster + '" style="max-width: 100%; height: auto;" />';
                                newVideo += '<div class="play-icon"></div>';
                                newVideo += '<div class="video-details text-left" style="display: none;">';
                                newVideo += '<span>City of ' + data.city + ' | ' + data.name + '</span>';
                                newVideo += '</div></div></div>';
                                $('#video-entries').append(newVideo);
                            });
                            
                                clearInterval(myInterval);
                            }
                        }
                    });      
                },5000);
                
            }
        });
    });
</script>
<style>
@font-face {
    font-family: 'Favorit Regular';
    src: url("{{ asset('/fonts/Favorit-Regular.ttf') }}") format("ttf");
    src: url("{{ asset('/fonts/Favorit-Regular.otf') }}") format("opentype");
  }
  @font-face {
    font-family: 'Favorit Light';
    font-weight: 300;
    src: url("{{ asset('/fonts/Favorit-Light.ttf') }}") format("ttf");
    src: url("{{ asset('/fonts/Favorit-Light.otf') }}") format("opentype");
  }

h1,h4, .instructions, #video-gallery, .video-details, .about, input {
    font-family: 'Favorit Regular' !important;
}
body, .card{
background: white;
}
.progress-bar {
    height: 100%;
}
.initial-form {
    max-height: 3000px;
}
.initial-form input {
    font-size: 2.4rem;
}
.initial-form-hidden {
    max-height: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    overflow: hidden;
    transition: 1s ease;
}
.video-details {
    position: absolute; 
    bottom: 0; 
    left: 0; 
    width: 100%; 
    color: white;
    background-color: rgba(0,0,0,0.6);
    height: 30%; 
    display: none;
    text-align: center;
    cursor: pointer;
}
.video-details span {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
    width: 100%;
    font-size: 0.9rem;
}
.play-icon {
    z-index: 1;
    position: absolute;
    cursor: pointer;
    top: 0;
    width: 100%;
    height: 100%;
    display: none;
    background-image: url("{{ asset('/img/play-icon.png') }}");
    background-repeat: no-repeat;
    background-position: center;
}
.vjs-text-track-cue div, .vjs-text-track-cue-en {
    font-family: 'Favorit' !important;
    top: 45% !important;
}
.direct-upload input { font-size: 1.5rem; }
.instructions { font-size: 1.5rem; color: black;}
.instructions .small { font-size: 1.4rem; line-height: 1.7rem; display: block; color: #4A4A4A;}
#upload-btn { font-size: 1.7rem; background-color: #004CFF !important;}
.progress, .progress-bar {
    height: 5vh !important;
}
ol { padding-left: 1rem !important;}
h1 { 
    font-size: 2.5rem;
}
.video-thumb img { max-height: 140px; }
@media (max-width: 575.98px) { 
    h1 {
        font-size: 2rem;
    }
    #upload-btn {
        font-size: 1.3rem;
    }
    .about {
        padding-right: 0;
    }
    .initial-form input {
        font-size: 1.5rem;
    }
    .instructions {
        font-size: 1.3rem;
    }
    .instructions .small {
        font-size: 1.1rem;
    }
    .video-thumb img {
        width: 100%;
        max-height: 80px;
    }
}
h4 { }
.about {
    font-size: 1.4rem;
    line-height: 1.7rem;
    text-align: justify;
    padding-right: 5vw;
    color: #4A4A4A;
}
</style>
<div class="container">
    <div class="w-100 text-center">
        <div class="card border-0 text-left mt-2 py-2 w-100">
            <h1>LISTENING TO THE CITY AS A FORM OF WRITING</h1>
            <h4>a soundwalk project by Julián Galay</h4><br /><br />
            <div class="about">The proposal for this soundwalk is divided in two: in the first part you will walk and listen, while recording, around your city. In the second part we will recollect on that experience to rethink the sound of our city. How could we talk about listening without superimposing the sound of our voice to the sound of the landscape?<br /><br />

            The first edition of "Listening to the city as a form of writing" was developed in between
            Haus de Statistics and Satellit, in Berlin on May 18 2022, together with Peter Schmidt and
            Erik Goengrich. This second edition, composed especially for the Club Tipping Point Berlin
            website, was realized in correspondence with Christoph Gosepath, Emilia Pascarelli and
            Federico Isasti.<br /><br />
        Julián Galay is an “undisciplinary” composer that works with sound, moving image and
        language through installation, performance and experimental film. He often explores the
        unconscious of architectural spaces and institutions through personal diaries, dreams and
        archives. — <a href="http://www.juliangalay.com" target="_blank">www.juliangalay.com</a>
            </div>
            <div class="initial-form mt-5">
                <div class="text-justify col-12 w-100 pl-0">
                    <h1>INSTRUCTIONS</h1>
                    <div class="instructions">
                        01. Leave the Internet and go for a walk in the city.*<br />
                        02. Film 10 minutes using the cellphone in horizontal format.**<br />
                        03. Walk paying attention to the sounds and the environment.***<br />
                        04. After coming back: Upload the video to the CTP website and wait.****<br />
                        <span class="small mt-5">*You can walk alone or accompanied and at any pace (faster, slower or even stop). **Without talking. Filming is free (you are allowed to lose control, change the point of view, etc). You can use the zoom, not use it and also generate more abstract images. You don't need to be looking at what you are filming while filming. ***Let yourself be guided by sounds. ****Re-watch and re-listen to the experience while reading.</span><br />
                    </div>
                </div>
                <div class="col-md-8 offset-md-2">
                    <div id="error-msg" class="alert alert-danger fs-4" role="alert"></div>
                </div>
                <div class="text-left p-3 col-md-8 offset-md-2 col-12 w-100">
                    <form method="POST" class="w-100 mx-auto">
                        <div class="form-group">
                            <!-- <label for="name">Name</label> -->
                            <input type="text" class="form-control w-100" id="name" value="" name="name" placeholder="Your name">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control w-100" id="city" value="" name="city" placeholder="Name of the city">
                        </div>
                    </form>
                    <form id="upload-form" action="" method="POST" enctype="multipart/form-data" class="direct-upload w-100 mx-auto">
                        <input class="form-control fileinput-button my-2" type="file" id="upload-input" name="file">
                    </form>
                    <div class="text-center">
                        <button type="button" class="btn btn-primary start" id="upload-btn" onclick="getPresignedUrl()">Upload</button>
                    </div>
                </div>
            </div>
            <div class="col-md-8 offset-md-2 progress-div d-none">
                <h3 id="upload-state">Upload is in progress...</h3>
                <div id="upload-stats"></div>
                
                <div class="progress mt-2">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 50%;">
                        <span class="percent">70%</span>
                    </div>
                </div>

                <div id="status"></div>
            </div>
        </div>
    </div>
    <div id="video-gallery" class="card border-0 mt-2">
        <h2 class="text-left">WALKS & CITIES</h2>
        <div class="row mt-md-4 mt-1" id="video-entries">
        @foreach ($videos as $video)
            <div class="col-md-3 col-6 p-2">
                <div style="position: relative;" class="video-thumb" data-id="{{ $video->id }}"
                data-src="{{ $video->streamUrl }}" data-poster="{{ $video->poster }}"
                data-subs="{{ $video->subtitles }}">
                    <img src="{{ $video->poster }}" style="width: 100%; height: auto;" />
                    <div class="play-icon">
                    </div>
                    <div class="video-details text-left" style="display: none;">
                        <span>{{ $video->city }} | {{ $video->name }}</span>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    </div>
    <div class="modal fade" tabindex="-1" id="videoModal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-body">
                    <video controls class="video-thumb video-js vjs-fluid vjs-default-skin vjs-big-play-centered" id="video-player">
                    </video>
                </div>
            </div>
        </div>
        </div>
</div>
@stop
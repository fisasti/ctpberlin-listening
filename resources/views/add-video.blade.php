@extends('layouts.admin')
@section('content')
<script>
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
        $('#error-msg').hide();

        if (exts.indexOf(extension) < 0) {
            error = 'The video extension should be contained in this list: ' + exts.join(', ') + '.'; 
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

    $(document).ready(function() {
        var bar = $('#progressBar');
        var percent = $('.percent');
        var status = $('#status');
        var file = $('#upload-input');
        $('#error-msg').hide();

        $('.video-js').each(function () {
            videojs(this);
        });

        $(file).on('change', () => {
            $('#upload-btn').prop('disabled', file.val().length == 0)
        })

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
                    $(this).html("Your video is now uploaded and it is being processed. This may take a while, but if you do not want to wait you can just come back later as your video will be automatically generated.").fadeIn(400);
                });

                var myInterval = setInterval(function(){
                    $.ajax({
                        url: API_BASE + 'transcodeVideo?api_token=' + API_TOKEN,
                        data: params, type: 'POST', processData: false, contentType: false,
                        success: function(data) {
                            console.log(data);
                            if (data.status == 'ready') {
                                console.log('corto el loop');
                                $('#upload-state').fadeOut(400, function() {
                                $(this).html("Your video is now ready!").fadeIn(400);
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
body, .card{
background: white;
}
.progress-bar {
    height: 100%;
}
.video-thumb {
    width: 100%;
    height: 100%;
}
.initial-form {
    max-height: 900px;
}
.initial-form input {
    font-size: 2rem;
}
.initial-form-hidden {
    max-height: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    overflow: hidden;
    transition: 1s ease;
}
.direct-upload input { font-size: 1.5rem; }
.instructions { font-size: 1.5rem; }
.instructions .small { font-size: 1.2rem; line-height: 1.2rem; }
#upload-btn { font-size: 1.5rem; }
.progress, .progress-bar {
    height: 5vh !important;
}
ol { padding-left: 1rem !important;}
h1 { font-size: 3rem; }
</style>
<div class="container">
    <div class="w-100 text-center">
        <div class="card border-1 text-center mt-2 py-2">
            <h1>LISTENING TO THE CITY AS A FORM OF WRITING</h1>
            <div class="initial-form">
                <div class="text-justify p-3 col-12 w-100">
                    <h2>INSTRUCTIONS</h2>
                    <div class="instructions">
                        <ol>
                            <li>Leave the Internet and go for a walk in the city.*</li>
                            <li>Film 10 minutes using the cellphone in horizontal format.**</li> 
                            <li>Walk paying attention to the sounds and the environment.***</li> 
                            <li>After coming back: Upload the video to the CTP website and wait.****</li>
                        </ol>
                        <span class="small">*You can walk alone or accompanied and at any pace (faster, slower or even stop). **Without talking. Filming is free (you are allowed to lose control, change the point of view, etc). You can use the zoom, not use it and also generate more abstract images. You don't need to be looking at what you are filming while filming. ***Let yourself be guided by sounds. ****Re-watch and re-listen to the experience while reading.</span>
                    </div>
                </div>
                <div class="col-md-10 offset-md-1">
                    <div id="error-msg" class="alert alert-danger fs-4" role="alert"></div>
                </div>
                <div class="text-left p-3 col-6 offset-md-3 w-100">
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
                        <button type="button" class="btn btn-primary start" id="upload-btn" disabled onclick="getPresignedUrl()">Upload</button>
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
    <div id="video-gallery">
        <h2 class="text-left card border-1">WALKS AROUND THE CITIES</h2>
        <div class="row">
        @foreach ($videos as $video)
            <div class="col-2 py-2">
                <video controls class="video-thumb video-js vjs-fluid vjs-default-skin vjs-big-play-centered">
                    <source src="{{ $video->streamUrl }}" type="application/x-mpegURL" />
                    @if (!empty($video->subtitles))
                    <track label="Subtitles" kind="subtitles" srclang="en" src="{{ $video->subtitles }}" default />
                    @endif
                </video>
            </div>
        @endforeach
        </div>
    </div>
</div>
@stop
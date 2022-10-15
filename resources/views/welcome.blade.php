@extends('layouts.admin')
@section('content')
<script>
    $(document).ready(function() {
        var bar = $('#progressBar');
        var percent = $('.percent');
        var status = $('#status');
        var file = $('#upload-input');
        $('#error-msg').hide();

        $(file).on('change', () => {
            $('#upload-btn').prop('disabled', file.val().length == 0)
        })
        $('form').ajaxForm({
            beforeSend: function() {
                status.empty();
                var percentVal = '0%';
                
                bar.width(percentVal);
                percent.html(percentVal);
                $('.progress').attr('style', 'display: block !important');
            },
            beforeSubmit: function(data, $form, options) {
                var error = '';
                var exts = ['mov', 'mp4'];
                var file = data.find(e => e.name == 'file').value;
                var filename = file.name;
                var ext = filename.substring(filename.lastIndexOf('.') + 1);

                if (exts.indexOf(ext) < 0) {
                    error = 'The video extension should be contained in this list: ' + exts.join(', ') + '.'; 
                }
                $('#error-msg').hide();

                if (error.length > 0) {
                    $('#error-msg').html(error).show();
                
                    return false;
                }
            },
            uploadProgress: function(event, position, total, percentComplete) {
                var percentVal = percentComplete + '%';
                // console.log($('.percent').css('display'))
                bar.width(percentVal);
                percent.html(percentVal);
            },
            complete: function(xhr) {
                status.html(xhr.responseText);
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
</style>
<div class="container">
    <div class="row pt-5">
        <div class="col-sm-12">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert">×</button>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (Session::has('success'))
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                <p>{{ Session::get('success') }}</p>
            </div>
            @endif
        </div>
    <!--<div class="col-sm-8">
        @if (count($videos) > 0)
        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
            <div class="carousel-inner">
                @foreach ($videos as $video)
                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                    <img class="d-block w-100" src="{{ $video['src'] }}" alt="First slide">
                    <div class="carousel-caption">
                    <form action="{{ url('videos/' . $video['name']) }}" method="POST">
                    {{ csrf_field() }}
                    {{ method_field('DELETE') }}
                    <button type="submit" class="btn btn-default">Remove</button>
                    </form>
                    </div>
                </div>
                @endforeach
            </div>
            <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
            </a>
        </div>
        @else
        <p>Nothing found</p>
        @endif
        -->
    </div>
    <div class="w-100 text-center">
        <div class="card border-1 text-center">
        <div class="form-row text-start">
            <div class="form-group w-75">
                <label for="titulo">Name</label>
                <input type="text" class="form-control w-100" id="name" value="" name="name" placeholder="Your name">
                </div>
            </div>
            <form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="direct-upload w-100">
                @foreach ($formInputs as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}" />
                @endforeach
                <label for="upload-input" class="form-label"><h3>Upload your video file</h3></label>
                <div id="error-msg" class="alert alert-danger" role="alert">
                </div>
                <input class="form-control fileinput-button mx-auto w-50 my-2" type="file" id="upload-input" name="file">
                <button type="submit" class="btn btn-primary start" id="upload-btn" disabled>Upload</button>
            </form>
            
            <div class="progress mt-2 d-none">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" role="progressbar" style="width: 50%;">
                    <span class="percent">70%</span>
                </div>
            </div>

            <div id="status"></div>
        </div>
    </div>
</div>
@stop
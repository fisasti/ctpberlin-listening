<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:url"                content="https://ctp-berlin.com/listeningtothecity/" />
    <meta property="og:type"               content="website" />
    <meta property="og:title"              content="Listening to the city as a form of writing" />
    <meta property="og:description"        content="LISTENING TO THE CITY AS A FORM OF WRITING is a interactive soundwalk project by Julián Galay created especially for the Club Tipping Point Berlin´s web." />
    <meta property="og:image"              content="https://ctp-berlin.com/listeningtothecity/img/lttc.jpg" />  
    <title>{{ config('app.name', 'Listening to the city as a form of writing') }}</title>
    <meta name="description" content="LISTENING TO THE CITY AS A FORM OF WRITING is a interactive soundwalk project by Julián Galay created especially for the Club Tipping Point Berlin´s web." />
    <!-- jQuery, Bootstrap, jQuery form, videojs, videojs-http-streaming -->
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js" defer></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <script src="https://malsup.github.io/jquery.form.js"></script> 
    <link rel="shortcut icon" href="favicon.ico"">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/js/bootstrap.min.js" integrity="sha512-EKWWs1ZcA2ZY9lbLISPz8aGR2+L7JVYqBAYTq5AXgBkSjRSuQEGqWx8R1zAX16KdXPaCjOCaKE8MCpU0wcHlHA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://vjs.zencdn.net/7.20.3/video-js.css" rel="stylesheet" />
    <script src="https://vjs.zencdn.net/7.20.3/video.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/videojs-landscape-fullscreen@11.1111.0/dist/videojs-landscape-fullscreen.min.js"></script>
    <script src="https://unpkg.com/@videojs/http-streaming@2.15.0/dist/videojs-http-streaming.js"></script>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript">
      const API_BASE = document.location.href.indexOf('localhost') >= 0 ? '/api/' : '/listeningtothecity/api/';
      const API_TOKEN = '{{ auth()->user()->api_token }}';
    </script>

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app" class="vh-100">
      <div class="container-fluid h-100">
          <main role="main" class="col-md-10 offset-md-1 col-12 offset-0 h-100 pt-3">
            @yield('content')
          </main>
      </div>
    </div>
</body>
</html>

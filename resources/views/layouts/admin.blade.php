<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Listening to the City') }}</title>
    <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script> -->
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.js" defer></script>
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" data-auto-replace-svg="nest"></script>

    <!-- jQuery, DataTable y DatePicker -->
   

    <!-- <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/leaflet.markercluster.js" integrity="sha512-OFs3W4DIZ5ZkrDhBFtsCP6JXtMEDGmhl0QPlmWYBJay40TT1n3gt2Xuw8Pf/iezgW9CdabjkNChRqozl/YADmg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.css" integrity="sha512-mQ77VzAakzdpWdgfL/lM1ksNy89uFgibRQANsNneSTMD/bj0Y/8+94XMwYhnbzx8eki2hrbPpDm0vD0CiT2lcg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.markercluster/1.5.3/MarkerCluster.Default.css" integrity="sha512-6ZCLMiYwTeli2rVh3XAPxy3YoR5fVxGdH/pz+KMCzRY2M65Emgkw00Yqmhh8qLGeYQ3LbVZGdmOX9KUjSKr0TA==" crossorigin="anonymous" referrerpolicy="no-referrer" /> -->
    
    <!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.11.3/r-2.2.9/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.11.3/r-2.2.9/datatables.min.js" defer></script> -->

    <!-- Scripts -->
    <script src="{{ mix('js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script type="text/javascript">
      const API_BASE = document.location.href.indexOf('localhost') >= 0 ? '/api/' : '/geovisualizador/api/';
      const API_TOKEN = '{{ auth()->user()->api_token }}';
      var spreadsheet = false;
      
      $(document).ready( function () {
        /* $('#spreadsheet').change(function() {
          var url = $(this).val();
          spreadsheet = { 
            'url': url,
            'titulo': $(this).text(),
            'sheetId': url.substring(url.indexOf('/d/') + 3).split('/')[0] 
          };
          
          console.log(spreadsheet.sheetId)
          loadSpreadsheet();
        });
        $('#spreadsheet').trigger('change'); */
     
        // if ($(window).width() < 768)
        // $('.table').DataTable({ responsive: true });
        // $('input[name="date"]').datepicker( { dateFormat: "mm-dd-yy" });
        $('input[name="fecha_informacion"]').datepicker( { dateFormat: "mm-dd-yy" });
        (function () {
          var oldVal;

          $('.id_reclamo').on('change textInput input', function () {
              var val = this.value;
              if (val !== oldVal) {
                  oldVal = val;

                  if (val.length >= 5) {
                    $.get(API_BASE + 'getNombre', { id_reclamo: val, api_token: API_TOKEN },  (data) => {
                      var nombre = '';
                      // console.log(data);
                      if (data.length > 0) {
                        nombre = data[0].apellidonombre
                        // alert(nombre);
                      }
                      $('#apellido_nombre').val(nombre);
                    });
                  }
              }
          });
      }());
      });
    </script>
    <!-- Styles -->
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{ mix('css/admin.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app" class="vh-100">
      <nav class="navbar navbar-light sticky-top bg-white flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 px-3" href="#" style="background-color: rgb(0, 176, 240); margin-right: 0 !important;">
          <img src="{{ asset('/img/renabap.png') }}" class="w-75" />
        </a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <ul class="navbar-nav px-3">
          <li class="nav-item text-nowrap">
            Buen día, <b>{{ auth()->user()->username }}</b> ||
            <a class="nav-link d-inline" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Cerrar sesión</a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
              {{ csrf_field() }}
          </form>
          </li>
        </ul>
      </nav>

      <div class="container-fluid h-100">
        <div class="row h-100">
          <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
            <div class="sidebar-sticky pt-3">
              <ul class="nav flex-column">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-3 mb-1 text-muted">
                  <span>Geovisualizador</span>
                </h6>
                @if (Auth::user()->hasRole('admin')) 
                <li class="nav-item">
                  <a class="nav-link {{ strstr(Request::url(), 'spreadsheets') || Request::path() == 'panel' ? 'active' : ''}}" href="{{ route('spreadsheets.index') }}">
                    Spreadsheets
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ strstr(Request::url(), 'usuarios')? 'active' : ''}}" href="{{ route('usuarios.index') }}">
                    Usuarios
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link {{ strstr(Request::url(), 'organizaciones')? 'active' : ''}}" href="{{ route('organizaciones.index') }}">
                    Organizaciones Sociales
                  </a>
                </li>
                @endif
                <li class="nav-item">
                  <a class="nav-link {{ strstr(Request::url(), 'devolucion') || (Request::path() == 'panel' && !Auth::user()->hasRole('admin')) ? 'active' : ''}}" href="{{ route('devolucion.index') }}">
                    Devolución
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="{{ route('visualizador') }}" target="_blank">
                    Visualizador
                  </a>
                </li>
              </ul>
            </div>
          </nav>

          <main role="main" class="col-md-9 ml-sm-auto col-lg-10 offset-md-3 offset-lg-2 h-100 pt-3">
            @yield('content')
          </main>
        </div>
      </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'G-Order') }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ URL::to('img/logoe.jpg')}}" />
    <!-- Styles -->
    <!-- Stylesheets -->
    <link href='https://fonts.googleapis.com/css?family=Lato:300,400,700,400italic,300italic' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="assets/css/docs.theme.min.css">

    <!-- Owl Stylesheets -->
    <link rel="stylesheet" href="assets/owlcarousel/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/owlcarousel/assets/owl.theme.default.min.css">
    <script src="assets/vendors/jquery.min.js"></script>
    <script src="assets/owlcarousel/owl.carousel.js"></script>

    </style>
</head>
<body>
    <!-- body -->
    <div class="home-demo">
      <div class="row">
        <div class="large-12 columns">
          <h3>Demo</h3>
          <div class="owl-carousel owl-theme owl-loaded owl-drag">
            <div class="item">
              <h2>Swipe</h2>
            </div>
            <div class="item">
              <h2>Drag</h2>
            </div>
            <div class="item">
              <h2>Responsive</h2>
            </div>
            <div class="item">
              <h2>CSS3</h2>
            </div>
            <div class="item">
              <h2>Fast</h2>
            </div>
            <div class="item">
              <h2>Easy</h2>
            </div>
            <div class="item">
              <h2>Free</h2>
            </div>
            <div class="item">
              <h2>Upgradable</h2>
            </div>
            <div class="item">
              <h2>Tons of options</h2>
            </div>
            <div class="item">
              <h2>Infinity</h2>
            </div>
            <div class="item">
              <h2>Auto Width</h2>
            </div>
          </div>
        </div>
      </div>
	<div class="row">
        <div class="large-12 columns">
          <h3>Demo</h3>
          <div class="owl-carousel  owl-drag">
            <div class="item">
              <h2>Swipe</h2>
            </div>
            <div class="item">
              <h2>Drag</h2>
            </div>
            <div class="item">
              <h2>Responsive</h2>
            </div>
            <div class="item">
              <h2>CSS3</h2>
            </div>
            <div class="item">
              <h2>Fast</h2>
            </div>
            <div class="item">
              <h2>Easy</h2>
            </div>
            <div class="item">
              <h2>Free</h2>
            </div>
            <div class="item">
              <h2>Upgradable</h2>
            </div>
            <div class="item">
              <h2>Tons of options</h2>
            </div>
            <div class="item">
              <h2>Infinity</h2>
            </div>
            <div class="item">
              <h2>Auto Width</h2>
            </div>
          </div>
        </div>
      </div>
<div class="row">
        <div class="large-12 columns">
          <h3>Demo</h3>
          <div class="owl-carousel">
            <div class="item">
              <h2>Swipe</h2>
            </div>
            <div class="item">
              <h2>Drag</h2>
            </div>
            <div class="item">
              <h2>Responsive</h2>
            </div>
            <div class="item">
              <h2>CSS3</h2>
            </div>
            <div class="item">
              <h2>Fast</h2>
            </div>
            <div class="item">
              <h2>Easy</h2>
            </div>
            <div class="item">
              <h2>Free</h2>
            </div>
            <div class="item">
              <h2>Upgradable</h2>
            </div>
            <div class="item">
              <h2>Tons of options</h2>
            </div>
            <div class="item">
              <h2>Infinity</h2>
            </div>
            <div class="item">
              <h2>Auto Width</h2>
            </div>
          </div>
        </div>
      </div>

    </div>
    <script>
      var owl = $('.owl-carousel');
      owl.owlCarousel({
        margin: 10,
        loop: false,
        nav: false,
        responsive: {
          0: {
            items: 1
          },
          600: {
            items: 3
          },
          1000: {
            items: 6
          }
        }
      })
    </script>
</body>
</html>

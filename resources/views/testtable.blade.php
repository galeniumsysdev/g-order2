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
    <!--<link rel="stylesheet" href="{{ asset('font-awesome/css/font-awesome4.7.0.min.css') }}">-->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::to('css/mystyle.css') }}">
</head>
<body>

  <!-- begin snippet: js hide: false -->
<!-- language: lang-css -->

<style type="text/css">
@media only screen and (max-width: 800px) {
        /* Force table to not be like tables anymore */
        #no-more-tables table,
        #no-more-tables thead,
        #no-more-tables tbody,
        #no-more-tables th,
        #no-more-tables td,
        #no-more-tables tr {
        display: block;
        }

        /* Hide table headers (but not display: none;, for accessibility) */
        #no-more-tables thead tr {
        position: absolute;
        top: -9999px;
        left: -9999px;
        }

        #no-more-tables tr { border: 1px solid #ccc; }

        #no-more-tables td {
        /* Behave like a "row" */
        border: none;
        border-bottom: 1px solid #eee;
        position: relative;
        padding-left: 50%;
        white-space: normal;
        text-align:left;
        }

        #no-more-tables td:before {
        /* Now like a table header */
        position: absolute;
        /* Top/left values mimic padding */
        top: 6px;
        left: 6px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        text-align:left;
        font-weight: bold;
        }

        /*
        Label the data
        */
        #no-more-tables td:before { content: attr(data-title); }
</style>

      <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center">
                    Timetable
                </h2>
            </div>
            <div id="no-more-tables">
                <table class="col-sm-12 table-bordered table-striped table-condensed cf">
                <thead class="cf">
                  <tr>
                <th>      </th>
                    <th>Monday</th>
                    <th>Tuesday</th>
                    <th>Wednesday</th>
                    <th>Thursday</th>
                    <th>Friday</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                <td data-title="      ">07:45 |1| 08:35</td>
                    <td data-title="Monday">Lesson</td>
                    <td data-title="Tuesday">Lesson</td>
                    <td data-title="Wednesday">Lesson</td>
                    <td data-title="Thursday">Lesson</td>
                    <td data-title="Friday">Lesson</td>
                  </tr>
                  <tr>
                <td data-title="      ">08:35 |2| 09:25</td>
                    <td data-title="Monday">Lesson</td>
                    <td data-title="Tuesday">Lesson</td>
                    <td data-title="Wednesday">Lesson</td>
                    <td data-title="Thursday">Lesson</td>
                    <td data-title="Friday">Lesson</td>
                  </tr>
                  <tr>
                <td data-title="      ">09:30 |3| 10:20</td>
                    <td data-title="Monday">Lesson</td>
                    <td data-title="Tuesday">Lesson</td>
                    <td data-title="Wednesday">Lesson</td>
                    <td data-title="Thursday">Lesson</td>
                    <td data-title="Friday">Lesson</td>
                  </tr>
                  <tr>
                <td data-title="      ">10:35 |4| 11:25</td>
                    <td data-title="Monday">Lesson</td>
                    <td data-title="Tuesday">Lesson</td>
                    <td data-title="Wednesday">Lesson</td>
                    <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
              <tr>
            <td data-title="      ">11:30 |5| 12:20</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>            
              <td data-title="Friday">Lesson</td>
              <tr>
            <td data-title="      ">12:20 |6| 13:10</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
              <tr>
            <td data-title="      ">13:10 |7| 14:00</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
              <tr>
            <td data-title="      ">14:00 |8| 14:50</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
              <tr>
            <td data-title="      ">15:00 |9| 15:50</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
              <tr>
            <td data-title="      ">15:55 |10| 16:45	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
              <tr>
            <td data-title="      ">16:50 |11| 17:40	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
          <tr>
            <td data-title="      ">17:40 |12| 18:30	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
          <tr>
            <td data-title="      ">18:55 |13| 19:40	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
          <tr>
            <td data-title="      ">19:40 |14| 20:25	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
          <tr>
            <td data-title="      ">20:30 |15| 21:15	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
          <tr>
            <td data-title="      ">21:15 |16| 22:00	</td>
                <td data-title="Monday">Lesson</td>
                <td data-title="Tuesday">Lesson</td>
                <td data-title="Wednesday">Lesson</td>
                <td data-title="Thursday">Lesson</td>
                <td data-title="Friday">Lesson</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</body>

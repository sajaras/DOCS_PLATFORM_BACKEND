
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>States List Abstract</title>
    <link href="{{ public_path('css/report.css') }}" rel="stylesheet">
    <style>
    .page-break {
      page-break-after:always;
    }
    </style>
  </head>

  <body id="content">


  <table class="table12px table-bordered">
    <thead>
        <tr>
            <td>Code</td>
            <td>Name</td>
            <td>Country</td>
            </tr>
    </thead>

    <tbody>
        @foreach($reportData as $eachdata)
        <tr>
            <td>{{$eachdata->code}}</td>
            <td>{{$eachdata->name}}</td>
            <td>{{$eachdata->country->name}}</td>
        </tr>
        @endforeach

    </tbody>
  

</table>
  <p class="endofreportbox subheadingnormal">{{$reportFooterLabel}}</p>


  <script type="text/javascript" src="{{public_path('js/table.js')}}"></script>
  </body>
</html>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>States List Abstract</title>
    <link href="{{ public_path('css/boostrap5.css') }}" rel="stylesheet">
    <link href="{{ public_path('css/reportoverride.css') }}" rel="stylesheet">
    <style>
    .page-break {
      page-break-after:always;
    }
    </style>
  </head>

  <body id="content">

    @include('reports.common.report-header',['reportFormat'=>$reportFormat,'reportName'=>'MASTERS: PERMISSIONS LIST REPORT','filterText'=>$filterText])

   


  <table class="table  table-sm table-bordered table-striped">
    <thead>
        <tr>
            <th  class="btn-success text-white text-end"> Id</th>
            <th class="text-start">Name </th>
            </tr>
    </thead>

    <tbody>
        @foreach($reportData as $eachdata)
        <tr>
            <td class="text-end">{{$eachdata->id}}</td>
            <td>{{$eachdata->name}}</td>
        </tr>
        @endforeach

    </tbody>
  

</table>
  <p class="endofreportbox text-center">{{$reportFooterLabel}}</p>


  <script type="text/javascript" src="{{public_path('js/table.js')}}"></script>
  </body>
</html>

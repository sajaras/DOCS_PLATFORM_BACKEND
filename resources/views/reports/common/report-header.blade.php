@php
    
    $userOrganization = \Auth::user()->currentOrganization;
    
@endphp

@if($userOrganization && $userOrganization->logo_path)

<div class="container-fluid">
    <div class="row">

        <div class="organization-block">
            @if($reportFormat == 'pdf')
            <img class="organization-logo mt-2" src="{{public_path($userOrganization->logo_path)}}" >
            @endif
            <p>Rapidev Pvt Ltd .Green Villa <br>
                Pothencode Tvm . GSTIN 34234284723663
            </p>
            <p> {{$reportName}}</p>
            @if($filterText)
            <p>Filters: {{$filterText}}</p>
            @endif
        </div>
    </div>
</div>
@endif
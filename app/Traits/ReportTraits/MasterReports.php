<?php
namespace App\Traits\ReportTraits;

use App\Models\District;
use App\Models\State;
use App\Services\AddressService;
use App\Services\DistrictService;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Support\Facades\Request;


trait MasterReports
{


    public function masters($request)
    {
        $filterText = '';

        if ($request->selectedOption == 'states-list') {


            

            if ($request->reportType == 'abstract') {
                $reportData = State::orderBy('code')->get();
                return [
                    'viewName' => 'reports.masters.states-list-abstract',
                    'fileName' => 'states-list-abstract',
                    'footerSettings' => $this->getDefaultPDFFooterSettings(),
                    'data' => [
                        'reportData' => $reportData,
                        'filterText' => $filterText,
                        'reportFooterLabel'=>$this->getReportFooterLabel()
                    ]
                ];
            }
        }
        else if ($request->selectedOption == 'districts-list') {
            if ($request->reportType == 'abstract') {
                $districtService = new DistrictService($request);
                $sortColumns = [
                    ['column'=>'code','direction'=>'asc']
                ];
                $reportData =  $districtService->getAll([],$sortColumns);
                return [
                    'viewName' => 'reports.masters.districts-list-abstract',
                    'fileName' => 'districts-list-abstract',
                    'footerSettings' => $this->getDefaultPDFFooterSettings(),
                    'data' => [
                        'reportData' => $reportData,
                        'filterText' => $filterText,
                        'reportFooterLabel'=>$this->getReportFooterLabel()
                    ]
                ];
            }
        }
        else if ($request->selectedOption == 'addresses-list') {
            if ($request->reportType == 'abstract') {
                $addressService = new AddressService($request);
                $sortColumns = [
                    ['column'=>'code','direction'=>'asc']
                ];
                $reportData =  $addressService->getAll([],$sortColumns);
                return [
                    'viewName' => 'reports.masters.addresses-list-abstract',
                    'fileName' => 'addresses-list-abstract',
                    'footerSettings' => $this->getDefaultPDFFooterSettings(),
                    'data' => [
                        'reportData' => $reportData,
                        'filterText' => $filterText,
                        'reportFooterLabel'=>$this->getReportFooterLabel()
                    ]
                ];
            }
        }
        else if ($request->selectedOption == 'permissions-list') {
            if ($request->reportType == 'abstract') {
                $addressService = new PermissionService($request);
                $sortColumns = [
                    ['column'=>'id','direction'=>'asc']
                ];
                $reportData =  $addressService->getAll([],$sortColumns);
                return [
                    'viewName' => 'reports.masters.permissions-list-abstract',
                    'fileName' => 'permissions-list-abstract',
                    'footerSettings' => $this->getDefaultPDFFooterSettings(),
                    'data' => [
                        'reportData' => $reportData,
                        'filterText' => $filterText,
                        'reportFooterLabel'=>$this->getReportFooterLabel()
                    ]
                ];
            }
        }
        else if ($request->selectedOption == 'roles-list') {
            if ($request->reportType == 'abstract') {
                $roleService = new RoleService($request);
                $sortColumns = [
                    ['column'=>'id','direction'=>'asc']
                ];
                $reportData =  $roleService->getAll([],$sortColumns);
                return [
                    'viewName' => 'reports.masters.roles-list-abstract',
                    'fileName' => 'roles-list-abstract',
                    'footerSettings' => $this->getDefaultPDFFooterSettings(),
                    'data' => [
                        'reportData' => $reportData,
                        'filterText' => $filterText,
                        'reportFooterLabel'=>$this->getReportFooterLabel()
                    ]
                ];
            }
        } else if ($request->selectedOption == 'users-list') {
            if ($request->reportType == 'abstract') {
                $userService = new UserService($request);
                $sortColumns = [
                    ['column'=>'id','direction'=>'asc']
                ];
                $reportData =  $userService->getAll([],$sortColumns);
                return [
                    'viewName' => 'reports.masters.users-list-abstract',
                    'fileName' => 'users-list-abstract',
                    'footerSettings' => $this->getDefaultPDFFooterSettings(),
                    'data' => [
                        'reportData' => $reportData,
                        'filterText' => $filterText,
                        'reportFooterLabel'=>$this->getReportFooterLabel()
                    ]
                ];
            }
        }
    }
}

<?php

// app/Traits/FunctionsTrait.php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait FunctionsTrait
{
    /**
     * Check if the authenticated user has all the given permissions.
     * Throws an exception listing all missing permissions if any check fails.
     *
     * @param string|array $permissionNames The permission(s) to check.
     * @return bool
     * @throws HttpException
     */
    public function checkForPermission($permissionNames)
    {
        $user = Auth::user();

        if (!$user) {
            throw new HttpException(401, 'Unauthenticated.');
        }

        // 1. Get the list of required permissions, ensuring it's an array.
        $requiredPermissions = (array) $permissionNames;

        if (empty($requiredPermissions)) {
            return true;
        }

        // 2. Get all permissions the user has, across all roles and guards, into a simple array of names.
        $userPermissions = $user->getAllPermissions()->pluck('name')->unique()->values()->all();

        // 3. Find the difference. This will give us an array of permissions that are required but not owned by the user.
        $missingPermissions = array_diff($requiredPermissions, $userPermissions);

        // 4. If the array of missing permissions is not empty, throw an exception listing them.
        if (!empty($missingPermissions)) {
            $missingPermissionsString = join(", ", $missingPermissions);
            throw new HttpException(403, 'Permission Denied. User is missing the following required permissions: ' . $missingPermissionsString);
        }

        // 5. If the array is empty, the user has all required permissions.
        return true;
    }


    public function beginOfFinancialYear($dateValue)
    {
        $requiredDate = null;
        $month = date("m", strtotime($dateValue));
        $year = date("Y", strtotime($dateValue));
        if ($month > 3) {
            $requiredDate = $year . '-04-01';
        } else {
            $requiredDate = ($year - 1) . '-04-01';
        }
        return $requiredDate;
    }


    public function endOfFinancialYear($dateValue)
    {

        $requiredDate = null;
        $month = date("m", strtotime($dateValue));
        $year = date("Y", strtotime($dateValue));
        if ($month > 3) {
            $requiredDate = ($year + 1) . '-03-31';
        } else {
            $requiredDate = $year . '-03-31';
        }
        return $requiredDate;
    }


    public function getMonthsBetweenTwoDates($startDate, $endDate)
    {

        $requiredMonths = [];
        // $startMonth = Carbon::createFromFormat('Y-m-d',$startDate)->startOfMonth();
        $endMonth = Carbon::createFromFormat('Y-m-d', $endDate)->startOfMonth();
        $currentMonth = Carbon::createFromFormat('Y-m-d', $startDate)->startOfMonth();
        while ($currentMonth <= $endMonth) {
            array_push($requiredMonths, $currentMonth->format('Y-m-d'));
            $currentmonth = $currentMonth->addMonth();
        }
        return $requiredMonths;
    }

    public function financialYearText($dateValue)
    {


        $currentFinancialYear = null;
        $nextFinancialYear = null;
        $finyear_text = "";
        $month = date("m", strtotime($dateValue));
        $year = date("Y", strtotime($dateValue));
        if ($month > 3) {
            $currentFinancialYear = $year;
        } else {
            $currentFinancialYear = ($year - 1);
        }
        $nextFinancialYear = $currentFinancialYear + 1;
        $nextFinancialYear = substr($nextFinancialYear, 2, 2);
        return $currentFinancialYear . '-' . $nextFinancialYear;
    }

    public function beginMonth($dateValue)
    {

        return date('Y-m-01', strtotime($dateValue));
    }

    public function previousYearSameDate($dateValue)
    {

        $year = date("Y", strtotime($dateValue));
        $month = date("m", strtotime($dateValue));
        $day = date("d", strtotime($dateValue));
        return ($year - 1) . '-' . $month . '-' . $day;
    }

    public function addDates($dateValue, $noOfDays)
    {

        return Carbon::createFromFormat('Y-m-d', $dateValue)->addDays($noOfDays)->format('Y-m-d');
    }


    public function collectionDifference($newCollection, $existingCollection)
    {


        $collectionNew  = array_map('serialize', $newCollection);
        $collectionExisting = array_map('serialize', $existingCollection);
        $diffInsert = array_map('unserialize', array_diff($collectionNew, $collectionExisting));
        $diffDelete = array_map('unserialize', array_diff($collectionExisting, $collectionNew));
        return ["inserted" => $diffInsert, "deleted" => $diffDelete];
    }

    public function arrayDifference($newArray, $existingArray)
    {

        $newArray = array_filter($newArray);
        sort($newArray);
        $existingArray = array_filter($existingArray);
        sort($existingArray);

        $inserted = array_values(array_diff($newArray, $existingArray));
        $deleted = array_values(array_diff($existingArray, $newArray));
        return ["inserted" => $inserted, "deleted" => $deleted];
    }

    public function removeNotFilledKeyValuesFromArray($checkArray)
    {

        $finalArray = [];
        foreach ($checkArray as $key => $value) {

            if (!empty($value)) {
                array_push($finalArray, [$key, $value]);
            }
        }
        return $finalArray;
    }

    public function generateFilterText($parameters)
    {
        $filterText = '';
        if (!$parameters) {
            return '';
        }
        foreach ($parameters as $label => $valueArray) {

            if (count(array_values(array_filter($valueArray))) > 0) {
                if ($filterText != '') {
                    $filterText .=  ' , ';
                }
                $filterText .= $label;
                for ($i = 0; $i < count($valueArray[0]); $i++) {
                    $filterText .= ((string) $valueArray[1][$i]) . '.' . ((string) $valueArray[0][$i]);
                    if ($i != 0) {
                        $filterText .= ',';
                    }
                }
            }
        }


        return $filterText;
    }

    public function applyWhereAndWhereIn($query, $conditionsArray)
    {
        // ['maingroup_gid'=>['value'=>,332,'filterLabels'=>['value'=>[['2322','22332'],['dddd','ssssss']],'label'=>'Main Group'] 
        $filterText = '';
        foreach ($conditionsArray as $databaseField => $fieldSettings) {
            if (!empty($fieldSettings['value'])) {
                if (is_array($fieldSettings['value'])) {
                    $query->whereIn($databaseField, $fieldSettings['value']);
                } else {
                    $query->where($databaseField, $fieldSettings['value']);
                }

                foreach ($fieldSettings['filterLabels'] as  $filterSetting) {
                    $filterText .=  $filterSetting['label'] . ':';

                    if (is_array($fieldSettings['value'])) {
                        foreach ($filterSetting['value'] as $displaySetting) {
                            for ($i = 0; $i < count($displaySetting[0]); $i++) {
                                $filterText .=  $displaySetting[0][$i] . '.' . $displaySetting[1][$i];
                                if ($i != (count($displaySetting[0]) - 1)) {
                                    $filterText .= ',';
                                }
                            }
                        }
                    }
                    else
                    {
                        $filterText .= $filterSetting['value'][0] . '.' .  $filterSetting['value'][1];
                    }
                }
            }
        }
        return $filterText;
    }



    public function convertRupeesToWords($num)
    {

        $milli = 0;
        $cror = 0;
        $lakh = 0;
        $thou = 0;
        $hund = 0;
        $numb = 0;
        $pais = 0;

        $milli = floor($num / 1000000000);
        $cror = floor($num / 10000000);
        $lakh = floor($num / 100000);
        $thou = floor($num / 1000);
        $hund = floor($num / 100);
        $numb = floor($num);
        $pais = (trim($num - $numb, 2)) * 100;
        $numb = $numb - $hund * 100;
        $hund = $hund - $thou * 10;
        $thou = $thou - $lakh * 100;
        $lakh = $lakh - $cror * 100;
        $cror = $cror - $milli * 100;

        $strmilli = $this->getHund($milli);
        $strcror = $this->getHund($cror);
        $strlakh = $this->getHund($lakh);
        $strthou = $this->getHund($thou);
        $strhund = $this->getHund($hund);
        $strnumb = $this->getHund($numb);
        $strpais = $this->getHund($pais);

        if ($milli > 0) {
            $txtmilli = $strmilli . ' million ';
        } else {
            $txtmilli = '';
        }
        if ($cror > 0) {
            $txtcror = $strcror . ' Crore ';
        } else {
            $txtcror = '';
        }
        if ($lakh > 0) {
            $txtlakh = $strlakh . ' lakh ';
        } else {
            $txtlakh = '';
        }
        if ($thou > 0) {
            $txtthou = $strthou . " thousand ";
        } else {
            $txtthou = ' ';
        }
        if ($hund > 0) {
            $txthund = $strhund . ' hundred ';
        } else {
            $txthund = ' ';
        }

        if ($numb > 0) {
            $txtnumb = $strnumb;
        } else {
            $txtnumb = '';
        }
        if ($pais > 0) {
            $txtpais = ' and ' . $strpais . 'Paise Only';
        } else {
            $txtpais = ' only';
        }
        if ($num > 0 && $numb > 0) {
            $txtand = '';
        } else {
            $txtand = '';
        }
        if (floor($num > 0)) {
            $lastnum = "Rupees" . $txtmilli . $txtcror . $txtlakh . $txtthou . $txthund . $txtnumb . $txtpais;
            return $lastnum;
        } else {
            $lastnum = '';
            return $lastnum;
        }
    }




    public function getDefaultPDFFooterSettings() 
    {
        return ['type' => 'pagenumbering&customfooter', 'position' => 'footer-right', 'textformat' => 'Page [page] of [topage]', 'footerviewname' => 'reports.store.footers.printedby'];
    }


    public function getReportFooterLabel() 
    {
        return 'Printed by ' . Auth::user()->name .  ' at '. Carbon::now()->format('d F Y h:i a') ;
    }









    // write new functions above this

    public function generateReportAsMatrix($matrix, $data)
    {


        $drillcount = count($matrix["sequence"]);

        $iteration = 0;
        $output = [];
        $heads = array_keys($matrix['columns']);
        $headvalues = array_values($matrix['columns']);

        $headscount = count($heads);
        $datagenerationindex = $matrix["datagenerate"]["sequenceindex"];
        $sequence = array_keys($matrix["sequence"]);
        //next set of data iteration
        while (isset($data[$iteration])) {




            $thisdrill = 0;
            $currentconditions = '';
            while ($thisdrill < $drillcount) {

                $temp = $sequence[$thisdrill++];

                ${"thisdrill_$temp"} = $data[$iteration][$temp];
                $currentconditions = $currentconditions . '#' . $data[$iteration][$temp];
            }



            $collected_data = $data;
            foreach ($sequence as $eachsequence) {
                $collected_data = $collected_data->where($eachsequence, ${"thisdrill_$eachsequence"});
            }


            $headings = $collected_data->unique($matrix['matrix']['field'])->pluck($matrix['matrix']['field'])->toArray();
            if ($matrix['datagenerate']['tray']) {


                $matrixfield = $matrix['matrix']['field'];
                $maxsplits_shipnos = $collected_data->map(function ($eachdata) use ($matrixfield) {


                    return collect($eachdata)
                        ->only(['prod_shortname', 'max_split', 'ship_nos', 'ship_uomname'])
                        ->all();
                });
            }


            for ($k = count($matrix['columns']) - 1; $k >= 0; $k--) {

                array_unshift($headings, $heads[$k]);
            }



            //now heading will contain the running headings

            $collected = $collected_data->values()->all();

            $sno = 1;
            // dd($collected);
            $iteration = $iteration + count($collected);

            if (isset($matrix['sortheading']) && $matrix['sortheading'] == 'date') {

                $headingstosort = array_slice($headings, count($matrix['columns']), count($headings));
                // function date_sort($a, $b) {
                //   return strtotime($a) - strtotime($b);
                // }
                usort($headingstosort, function ($a, $b) {
                    return strtotime($a) - strtotime($b);
                });
                $l = 0;
                for ($v = count($matrix['columns']); $v < count($headings); $v++) {
                    $headings[$v] = $headingstosort[$l++];
                }

                // dd($headings);

            } else if (isset($matrix['sortheading']) && $matrix['sortheading'] == 'numeric') {

                $headingstosort = array_slice($headings, count($matrix['columns']), count($headings));

                if ($matrix['sorttype'] == 'asc') {
                    sort($headingstosort);
                } else if ($matrix['sorttype'] == 'desc') {
                    rsort($headingstosort);
                }

                $l = 0;
                for ($v = count($matrix['columns']); $v < count($headings); $v++) {
                    $headings[$v] = $headingstosort[$l++];
                }
            }



            $output[$currentconditions]["headings"] = $headings;
            //initilise total for this data set
            $total = array_fill(0, count($headings), []);













            // dd($headings);


            $i = 0;




            // $temp = array_fill(0,count($headings),'');

            while ($i < count($collected)) {
                $temp = [];
                $tray = [];
                $tempcounter = 0;
                $prev_basedon  = $now_basedon = $collected[$i][$matrix['datagenerate']['based_on']];
                while ($i < count($collected) && $prev_basedon  == $now_basedon) {


                    if ($tempcounter == 0) {
                        //filling normal  coulumns
                        for ($headcounter = 0; $headcounter < count($headvalues); $headcounter++) {

                            if ($headvalues[$headcounter] == "incrementing") {

                                $temp[$tempcounter++] = $sno++;
                            } else {

                                $temp[$tempcounter++] = $collected[$i][$headvalues[$headcounter]];
                            }
                        }
                    }


                    $foundposition = array_search($collected[$i][$matrix['matrix']['field']], $headings);



                    for ($x = 0; $x < $matrix['matrix']['colsplit']; $x++) {

                        if (!isset($total[$foundposition][$x])) {
                            $total[$foundposition][$x] = 0;
                        }


                        if (isset($temp[$foundposition][$x])) {
                            $temp[$foundposition][$x] = $temp[$foundposition][$x] + $collected[$i][$matrix['matrix']['colsplitvalues'][$x]];
                        } else {

                            $temp[$foundposition][$x] = $collected[$i][$matrix['matrix']['colsplitvalues'][$x]];
                        }

                        $total[$foundposition][$x] =  $total[$foundposition][$x] + $collected[$i][$matrix['matrix']['colsplitvalues'][$x]];
                    }


                    $prev_basedon = $collected[$i][$matrix['datagenerate']['based_on']];
                    $i++;


                    if ($i < count($collected)) {

                        $now_basedon = $collected[$i][$matrix['datagenerate']['based_on']];
                    }
                    // dd($prev_basedon.$now_basedon);
                }
                // dd($maxsplits_shipnos);
                // fill empty positiions with a null array
                $colsplit_count = $matrix['matrix']['colsplit'];
                $tray = [];



                for ($local = $headscount; $local < count($headings); $local++) {
                    if (!(isset($temp[$local]))) {
                        $temp[$local] = [];
                    }

                    if ($matrix['datagenerate']['tray']) {
                        $currentprod = $maxsplits_shipnos->where($matrix['matrix']['field'], $headings[$local])->first();
                        // dd($maxsplits_shipnos);
                        for ($split = 0; $split < $colsplit_count; $split++) {
                            if (!isset($tray[0][$split])) {
                                $tray[0][$split] = 0;
                            }
                            // dd([$local,$total[$local]]);
                            if (count($total[$local])) {



                                if ($currentprod["ship_uomname"] == "tray" && $currentprod['ship_nos'] > 0 && $currentprod['max_split'] > 0) {


                                    $traycount = floor($total[$local][$split] / $currentprod['ship_nos']);
                                    $tray[0][$split] = $tray[0][$split] + $traycount;
                                    $remaining = $total[$local][$split] % $currentprod['ship_nos'];
                                    if ($remaining > $currentprod['max_split']) {
                                        $tray[$local][$split] = (string)($traycount) . ' + 1 (' . (string)($remaining) . ')';
                                        $tray[0][$split] = $tray[0][$split] + 1;
                                        // $tray=$tray+1;
                                    } else {
                                        $tray[$local][$split] = (string)($traycount) . ' + 0 (' . (string)($remaining) . ')';
                                    }
                                } else {
                                    $tray[$local][$split] = 0;
                                }
                            } else {
                                $tray[$local][$split] = 0;
                            }







                            // $maxsplits_shipnos->where('prod_shortname','BM250N')->first()
                        }
                    }
                }

                // $temp['tray'] =



                $output[$currentconditions][$prev_basedon] = $temp;
            }
            // dd($temp);

            if ($matrix['sequence'][$matrix['datagenerate']['sequenceindex']]['total']) {
                $output[$currentconditions]["total"] = $total;
            }

            if ($matrix['datagenerate']['tray']) {

                $output[$currentconditions]["tray"] = $tray;
            }

            // dd($output);




        }
        // dd($output);
        // dd($output);
        return $output;
    }

    public function getHund($number)
    {

        $i = null;
        $str1 = " ";
        $str2 = " ";

        $no = $number;
        $no = round($no, 0);
        $sd = floor($no / 10);
        $fd = floor($no - $sd * 10);


        if ($fd == 0) {
            $str1 = " ";
        } else if ($fd == 1) {
            $str1 = 'One';
        } else if ($fd == 2) {
            $str1 = 'Two';
        } else if ($fd == 3) {
            $str1 = 'Three';
        } else if ($fd == 4) {
            $str1 = 'Four';
        } else if ($fd == 5) {
            $str1 = 'Five';
        } else if ($fd == 6) {
            $str1 = 'Six';
        } else if ($fd == 7) {
            $str1 = 'Seven';
        } else if ($fd == 8) {
            $str1 = 'Eight';
        } else if ($fd == 9) {
            $str1 = 'Nine';
        }
        if ($sd == 1) {
            if ($no == 10) {
                $str1 = ' Ten';
            } else if ($no == 11) {
                $str1 = ' Eleven';
            } else if ($no == 12) {
                $str1 = ' Twelve';
            } else if ($no == 13) {
                $str1 = ' Thirteen';
            } else if ($no == 14) {
                $str1 = ' Fourteen';
            } else if ($no == 15) {
                $str1 = ' Fifteen';
            } else if ($no == 16) {
                $str1 = ' Sixteen';
            } else if ($no == 17) {
                $str1 = ' Seventeen';
            } else if ($no == 18) {
                $str1 = ' Eighteen';
            } else if ($no == 19) {
                $str1 = ' Nineteen';
            }
        } else {
            if ($sd == 0) {
                $str2 = " ";
            } else if ($sd == 2) {
                $str2 = ' Twenty ';
            } else if ($sd == 3) {
                $str2 = ' Thirty ';
            } else if ($sd == 4) {
                $str2 = ' Forty ';
            } else if ($sd == 5) {
                $str2 = ' Fifty ';
            } else if ($sd == 6) {
                $str2 = ' Sixty ';
            } else if ($sd == 7) {
                $str2 = ' Seventy ';
            } else if ($sd == 8) {
                $str2 = ' Eighty ';
            } else if ($sd == 9) {
                $str2 = ' Ninety ';
            } else if ($sd == 2) {
                $str2 = ' Twenty ';
            }
        }

        $newstr =  $str2 . $str1;
        return $newstr;
    }
    
   
}

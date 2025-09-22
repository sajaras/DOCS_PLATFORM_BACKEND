<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class ViewExport implements FromView
{
    protected $data; // Optional: To pass data to the view
    protected $viewName;

    public function __construct($data = [],$viewName)
    {
        $this->data = $data; // Optional: Set data via constructor
        $this->viewName = $viewName; // Optional: Set data via constructor
    }

    public function view(): View
    {
        // Return your Blade view.
        // Make sure the view contains an HTML table structure.
        return view($this->viewName, $this->data);
    }
}
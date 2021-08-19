<?php

namespace App\Exports;

use App\User;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Spacerdata;
use App\Models\Child;
use App\Models\Spacersession;
use App\Models\Schedulesession;
use App\Models\Compliancetech;
use Validator, DB, Hash, Mail;

class ReportattackExport implements FromView
{
    public function view(): View
    {
        $get_data = Spacersession::select(DB::raw("DATE_FORMAT(date, '%m-%Y') as month"),DB::raw('SUM(is_attack) as month_attack',''))->where('is_attack','1')->groupBy('month')
                    ->orderBy('date','DESC')
                    ->get();
        // $get_data = Compliancetech::select(DB::raw('SUM(technique) as month_technique'),DB::raw('SUM(compliance) as month_compliance',''),DB::raw("DATE_FORMAT(date, '%m-%Y') as month"))
        // 			->groupBy('month')
        // 			->orderBy('date','DESC')
        // 			->get();

         		
        foreach ($get_data as $key => $value) {
        	
        	 
        	 
        	$data[] = array($value->month,$value->month_attack);
        }
        return view('attack', [
            'data' => $data
        ]);
    }
}
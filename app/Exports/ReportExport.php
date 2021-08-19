<?php

namespace App\Exports;

use App\User;
//use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Spacerdata;
use App\Models\Child;
use App\Models\Calendar;
use App\Models\Spacersession;
use App\Models\Schedulesession;
use App\Models\Compliancetech;
use Validator, DB, Hash, Mail;

class ReportExport implements FromView
{
    public function view(): View
    {
        // $get_data = Compliancetech::select(DB::raw('SUM(technique) as month_technique'),DB::raw('SUM(compliance) as month_compliance',''),DB::raw("DATE_FORMAT(date, '%m-%Y') as month"))
        // 			->groupBy('month')
        // 			->orderBy('date','DESC')
        // 			->get();


         // $get_data = Compliancetech::select(DB::raw('SUM(technique) as month_technique'),DB::raw('SUM(compliance) as month_compliance',''), DB::raw('YEAR(date) year, MONTH(date) month'))
                   
         //            ->groupBy(DB::raw("MONTH(date)"))                     
         //            ->orderBy('date','DESC')
         //            ->get();

        // $data = array();		
        // foreach ($get_data as $key => $value) {
        	
        // 	$date = explode('-', $value->month);
           
        // 	$month_technique = $value->month_technique / cal_days_in_month(CAL_GREGORIAN, $date[0],$date[1]);
        // 	$month_compliance = $value->month_compliance / cal_days_in_month(CAL_GREGORIAN,$date[0],$date[1]);
        	 
        // 	$data[] = array($value->month,number_format($month_technique, 2, '.', ''),number_format($month_compliance, 2, '.', ''));
        // }
        $value_c_array = array();
        $value_t_array = array();
        $check_startenddate = Calendar::select(DB::raw('MIN(date) as start_date'),DB::raw('MAX(date) as end_date'))->get();
        if(!empty($check_startenddate)){
            $check_startenddate = $check_startenddate[0];
            $ts1    = strtotime($check_startenddate->start_date);
            $ts2    = strtotime(date('Y-m-d'));
            //echo $check_startenddate->start_date;
            $year1  = date('Y', $ts1);
            $year2  = date('Y', $ts2);

            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);

            $day1   = date('d', $ts1); /* I'VE ADDED THE DAY VARIABLE OF DATE1 AND DATE2 */
            $day2   = date('d', $ts2);

            $diff   = (($year2 - $year1) * 12) + ($month2 - $month1); 
            for ($i=0; $i < $diff; $i++) { 

                if($i!=0){
                    $compliance_data      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-'.$i.' month')))->groupBy('date')->get();
                    $report_month = date('Y-m', strtotime('-'.$i.' month'));
                }else{
                    $compliance_data = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m'))->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "<=", date('Y-m-d'))->groupBy('date')->get();
                    $report_month = date('Y-m');
                }

                $d_compliance_total = 0;
                $compliance_month = 0;
                foreach ($compliance_data as $dkey => $dvalue) {
                    
                   $get_compliance = Spacersession::select('id')
                                    ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                                    ->where('is_attack','0')->get()->count(); 
                    
                    $schedule_count = $dvalue->total_sessions;
                    
                    if($get_compliance){
                        if($schedule_count){
                            $d_compliance   = ($get_compliance / $schedule_count)*100;
                        }else{
                             $d_compliance   = $get_compliance*100;
                        }
                        
                        $d_compliance_total = $d_compliance_total + $d_compliance;
                    }
                     

                }

                if($d_compliance_total){
                    if($i!=0)
                        $compliance_month = $d_compliance_total/cal_days_in_month(CAL_GREGORIAN,  date('m', strtotime('-'.$i.'  month')),  date('Y', strtotime('-'.$i.'  month')));
                    else
                         $compliance_month = $d_compliance_total/date('d');
                }else{
                    $compliance_month = 0;
                }

               
                #Technique
                if($i!=0){
                    $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-'.$i.' month')))
                            ->groupBy('date')->get(); 
                }else{
                    $get_day_sesssion = Spacersession::select('date')
                                ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m'))
                                ->groupBy('date')->get();
                } 
                $technique_month = 0;
                $month_tech = 0;
                $session_taken = 0;
                foreach ($get_day_sesssion as $key => $value) {
            
                    $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                                    ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get();
                   
                    $total_session = count($get_tech);
                    $daily_tech = 0;
                    foreach ($get_tech as $dkey => $dvalue) {
                      if($dvalue->totalpasscount || $dvalue->totalfailcount)
                      $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100); 

                    }
                    if($daily_tech)
                    $month_tech = $month_tech +  ($daily_tech / $total_session);
                }

                if($month_tech){
                    $technique_month = $month_tech/count($get_day_sesssion);
                }else{
                    $technique_month = 0;
                }


                 
                $data[] = array($report_month,number_format($technique_month, 1, '.', ''),number_format($compliance_month, 1, '.', ''));


            }
         
            
        }
       
        


        return view('tech_comp', [
            'data' => $data
        ]);
    }
}
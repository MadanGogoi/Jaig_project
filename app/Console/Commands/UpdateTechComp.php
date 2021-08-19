<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Models\Spacerdata;
use App\Models\Child;
use App\Models\Spacersession;
use App\Models\Schedulesession;
use App\Models\Compliancetech;
use Validator, DB, Hash, Mail;
use Carbon\Carbon;

class UpdateTechComp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'techcomp:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Compliance and Technique for everyday';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $childata = Child::select('id','last_sync')->get();
         
        foreach ($childata as $childkey => $childvalue) {
                #Compliance
                $checkdata = Compliancetech::where('child_id', '=', $childvalue->id)->get()->count();  

                if($checkdata>0){
                     $get_session_data = Schedulesession::select('sessions','local_time','id')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m-%d'))"), ">=", date('Y-m-d',strtotime($childvalue->last_sync)))->where('child_id',$childvalue->id)->where('type','1')->get();
                }else{
                     $get_session_data = Schedulesession::select('sessions','local_time','id')->where('child_id',$childvalue->id)->where('type','1')->get();
                } 
                //dd($get_session_data);
                foreach ($get_session_data as $key => $session_value) {
                //if($get_session_data!=NULL){         
                    $d_compliance = 0;
                    $d_techinique = 0;
                    $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($session_value->local_time)))
                            ->where('is_attack','0')->get()->count(); 

                    $schedule_count = json_decode($session_value->sessions);
                    $d_compliance   = ($get_compliance / count($schedule_count)*100);
                     

                    #Technique
                    $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($session_value->local_time)))->get();
           
                    $total_session = count($get_tech);
                    $daily_tech = 0;
                    foreach ($get_tech as $dkey => $dvalue) {
                      if($dvalue->totalpasscount)
                      $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100);    
                    }
                    if($daily_tech)
                    $d_techinique = $daily_tech / $total_session;     

                    $exist = Compliancetech::where('child_id', '=', $childvalue->id)
                                 ->where('date',     '=', date('Y-m-d',strtotime($session_value->local_time)))
                                 ->first();     
                    if($exist != null) {            
                        $update = Compliancetech::where('id', '=',$exist->id)
                                        ->update([
                                                    'child_id'=> $childvalue->id,
                                                    'technique'=> $d_techinique,
                                                    'compliance'=> $d_compliance,
                                                    'updated_at'=>  date('Y-m-d H:i:s')
                                                 ]);
                    }else{
                        $insert = Compliancetech::create([
                                                    'date'=> date('Y-m-d',strtotime($session_value->local_time)),
                                                    'child_id'=> $childvalue->id,
                                                    'technique'=> $d_techinique,
                                                    'compliance'=> $d_compliance,
                                                    'updated_at'=>  date('Y-m-d H:i:s')
                                                 ]);  
                    }
                }                   
        }
    }
}

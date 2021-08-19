<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Models\Child;
use App\Models\Schedulesession;
use App\Models\Calendar;
use App\Models\Spacerdataline;
use App\Models\Spacersession;
use App\Models\Scheduleappointment;

use Illuminate\Validation\Rule;


use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator, DB, Hash, Mail;

class SyncController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['']]);
    }
    
    public function sync_scheduled_sessions(Request $request)
    { 
		$data     = $request->json()->all();
		
		$child_id = $request->header('CHILD-ID');

		 
		$user = auth('api')->user();
		$user_id = $user->id;
		#Get Child Informations
		$get_child = Schedulesession::select('id')->where('child_id', '=', $child_id)
		 								->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m-%d'))"),date('Y-m-d',strtotime($data['localTimestamp'])))
		 								->where('type', '=', '1')->get()->count();
		if($get_child==0){
			$child = Schedulesession::create([
									            'child_id' 		=> $child_id,
									            'sessions' 		=> json_encode($data['scheduledSessions']),
									            'server_time' 	=> date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])),
									            'local_time' => date('Y-m-d H:i:s',strtotime($data['localTimestamp'])),
									            'type' 			=> '1'
			        ]);
		}else{
			$session_data = Schedulesession::where('child_id', '=', $child_id)->where('type', '=', '1')->get()->first();
  
			if(date('Y-m-d H:i:s O',strtotime($data['serverTimestamp'])) > date('Y-m-d H:i:s O',strtotime($session_data->server_time))){
				$childinfo = Schedulesession::where('child_id', '=',$child_id)->where('type', '=','1')
					->update(['sessions'=> json_encode($data['scheduledSessions']),
							  'server_time'=> date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])),
							  'local_time'=> date('Y-m-d H:i:s',strtotime($data['localTimestamp']))
							]);
			}

		}

        $session_list_array = array();
        $session_data = Schedulesession::where('child_id', '=', $child_id)->where('type', '=', '1')->get()->first();
         
        if(isset($session_data->id)){
          $session_list_array = array('serverTimestamp'=>date('Y-m-d H:i:s O',strtotime($session_data->server_time)),'scheduledSessions'=>json_decode($session_data->sessions));
        }
        return response()->json([
            'status'  => true,
            'message'   => "success",
            'data'    => $session_list_array
        ]);
    }
    public function sync_scheduled_appointments(Request $request)
    { 
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');
		$scheduledAppointments  = $data['scheduledAppointments'];
		$serverTimestamp 		= $data['serverTimestamp'];
		$localTimestamp 		= $data['localTimestamp'];
		
		$user = auth('api')->user();
		$user_id = $user->id;
		if(!empty($scheduledAppointments)){
			$get_child = Scheduleappointment::where('child_id', '=', $child_id)
			 								->where('type', '=', '2')->delete();

			foreach ($scheduledAppointments as $key => $value) {

				$child = Scheduleappointment::create([
									            'child_id' 		=> $child_id,
									            'date' 			=> date('Y-m-d H:i:s',strtotime($value['date'])),
									            'server_time' 	=> date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])),
									            'local_time' 	=> date('Y-m-d H:i:s',strtotime($data['localTimestamp'])),
									            'type' 			=> '2'
				]);
				 
			}
		}
		
		#Get Child Informations
		// $get_child = Schedulesession::select('id')->where('child_id', '=', $child_id)->where('type', '=', '2')
		// 			->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m-%d'))"),date('Y-m-d',strtotime($data['localTimestamp'])))
		// 			->get()->count();
		// if($get_child==0){
		// 	$child = Schedulesession::create([
		// 							            'child_id' 		=> $child_id,
		// 							            'sessions' 		=> json_encode($data['scheduledAppointments']),
		// 							            'server_time' 	=> date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])),
		// 							            'local_time' => date('Y-m-d H:i:s',strtotime($data['localTimestamp'])),
		// 							            'type' 			=> '2'
		// 	        ]);
		// }else{
		// 	$session_data = Schedulesession::where('child_id', '=', $child_id)->where('type', '=', '2')->get()->first();
  
		// 	if(date('Y-m-d H:i:s O',strtotime($data['serverTimestamp'])) > date('Y-m-d H:i:s O',strtotime($session_data->server_time))){
		// 		$childinfo = Schedulesession::where('child_id', '=',$child_id)->where('type', '=','2')
		// 			->update(['sessions'=> json_encode($data['scheduledAppointments']),
		// 					  'server_time'=> date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])),
		// 					  'local_time'=> date('Y-m-d H:i:s',strtotime($data['localTimestamp']))
		// 					]);
		// 	}

		// }

        $appointment_array = array();
        $response = array();
        $response['serverTimestamp'] = date('Y-m-d H:i:s O');
        
        $session_data = Scheduleappointment::where('child_id', '=', $child_id)->where('type', '=', '2');
         
        // if(isset($session_data->id)){
        //   $session_list_array = array('serverTimestamp'=>date('Y-m-d H:i:s O'),'scheduledAppointments'=>json_decode($session_data->sessions));
        // }

        if($data['serverTimestamp']!=''){ 
			$session_data = $session_data->where('created_at','>', date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])));
		}
		$session_data = $session_data->get();
		foreach ($session_data as $key => $value) {
			$appointment_array[] = array('date'=>date('Y-m-d H:i:s O',strtotime($value->date)));
		}
		$response['scheduledAppointments'] = $appointment_array;
        return response()->json([
            'status'  => true,
            'message'   => "success",
            'data'    => $response
        ]);
    }

    public function sync_calendar(Request $request)
    { 
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');

		 
		$user = auth('api')->user();
		$user_id = $user->id;
		if(!empty($data['calendar'])){

			foreach ($data['calendar'] as $key => $value) {

				$exist = Calendar::where('child_id', '=', $child_id)
								 ->where('date',     '=', date('Y-m-d',strtotime($value['date'])))
								 ->first();
								  
				if($exist != null) {
				    if(date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) > date('Y-m-d H:i:s',strtotime($exist->local_time))){

				    	$update = Calendar::where('id', '=',$exist->id)
				    				->update(['total_sessions'=> $value['totalScheduledSessions'],
				    						 'scheduled_sessions'=> $value['scheduledSessions'],
				    			'local_time'=>  date('Y-m-d H:i:s',strtotime($value['localTimestamp']))]);
				    }
				}else{
				    $insert = Calendar::create([
			           			'child_id' 		 => $child_id,
					            'total_sessions' => $value['totalScheduledSessions'],
					            'scheduled_sessions' => $value['scheduledSessions'],
					            'date' 			 => date('Y-m-d',strtotime($value['date'])),
					            'local_time'  => date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) 
			        ]);
				}
				
			}
		}
		
		#Get Calendar Informations
		$calender_info = Calendar::where('child_id', '=', $child_id);
		if($data['serverTimestamp']!=''){
			$calender_info = $calender_info->where('created_at','>=', date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])));
		}
		$calender_info = $calender_info->get();
		 
        $calender_response = array(); 
        foreach ($calender_info as $key => $value) {
         $total_sessions =  (int) $value->total_sessions;

         $calender_response[] = array('date'=>date('Y-m-d',strtotime($value->date)),'totalScheduledSessions'=>$total_sessions,'scheduledSessions'=>$value->scheduled_sessions);
        }
        $response['serverTimestamp'] = date('Y-m-d H:i:s O'); 
        $response['calendar']		 =  $calender_response;
        return response()->json([
            'status'  => true,
            'message' => "success",
            'data'    => $response
        ]);
    }
    public function sync_spacer_dataline(Request $request)
    { 
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');

		 
		$user = auth('api')->user();
		$user_id = $user->id;
		if(!empty($data['spacerDataLine'])){

			foreach ($data['spacerDataLine'] as $key => $value) {

				$exist = Spacerdataline::where('child_id', '=', $child_id)
								 ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d %H:%i:%s'))"),date('Y-m-d H:i:s',strtotime($value['date'])))
								 ->first();
								  
				if($exist != null) {

				    if(date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) > date('Y-m-d H:i:s',strtotime($exist->local_time))){

				    	$update = Spacerdataline::where('id', '=',$exist->id)
				    				->update(['passCount'=> $value['passCount'],
				    						 'passCount'=> $value['failCount'],
				    						 'date' 			 => date('Y-m-d H:i:s',strtotime($value['date'])),
				    						 'local_time'=>  date('Y-m-d H:i:s',strtotime($value['localTimestamp']))
				    						 ]);
				    }
				}else{
				    $insert = Spacerdataline::create([
			           			'child_id' 		 => $child_id,
					            'passcount' => $value['passCount'],
					            'failcount' => $value['failCount'],
					            'date' 			 => date('Y-m-d H:i:s',strtotime($value['date'])),
					            'local_time'  => date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) 
			        ]);
				}
				
			}
		}
		
		#Get Calendar Informations
		$spacer_info = Spacerdataline::where('child_id', '=', $child_id);
		if($data['serverTimestamp']!=''){ 
			$spacer_info = $spacer_info->where('created_at','>=', date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])));
		}
		$spacer_info = $spacer_info->get();
		 
        $spacer_response = array(); 
        foreach ($spacer_info as $key => $value) {
         $passcount =  (int) $value->passcount;
         $failcount =  (int) $value->failcount;
         $spacer_response[] = array('date'=>date('Y-m-d H:i:s O',strtotime($value->date)),'passCount'=>$passcount,'failCount'=>$failcount);
        }
        $response['serverTimestamp'] = date('Y-m-d H:i:s O'); 
        $response['spacerData']		 =  $spacer_response;
        return response()->json([
            'status'  => true,
            'message' => "success",
            'data'    => $response
        ]);
    }
     public function updatesessiondata(Request $request){



    	$spcerdata = Spacersession::where('type','=',0)->get();
    	foreach ($spcerdata as $key => $value) {

    		$schedule_sessions_1 = '';
            $schedule_sessions_2 = '';
            $schedule_sessions_3 = '';
            $median_time_1 = '';
            $median_time_2 = '';
            $median_time_3 = '';
            #Get Scheduled Session time
            $schedule_sessiondata = Calendar::select('id','total_sessions','date','scheduled_sessions')
            						->where('child_id', "=", $value->child_id)
            						->where('date', "=", date('Y-m-d',strtotime(date('Y-m-d',strtotime($value->date)))))
            						->get();

            if(count($schedule_sessiondata)){
            	 
                $schedule_sessiondata = $schedule_sessiondata[0];
                if($schedule_sessiondata->scheduled_sessions!=''){
                    $schedule_sessions = explode(',', $schedule_sessiondata->scheduled_sessions);
                    $schedule_sessions_1 = $schedule_sessions[0];
                     
                    if(isset($schedule_sessions[1])){ 
                        $schedule_sessions_2 = $schedule_sessions[1];
                        $median_time_1 = $this->convertMedianTime($schedule_sessions_1, $schedule_sessions_2);  
                    }
                    if(isset($schedule_sessions[2])){
                        $schedule_sessions_3 = $schedule_sessions[2]; 
                        $median_time_2 = $this->convertMedianTime($schedule_sessions_2, $schedule_sessions_3);
                    }                    
                }
                echo 'median_time_1='.$median_time_1.'<br>';
                echo 'median_time_2='.$median_time_2.'<br>';

                $session_no = '';
	            if($schedule_sessions_1!='' && $schedule_sessions_2!='' && $schedule_sessions_3!=''){

	            	if(strtotime($value->firsttime)>strtotime('00:00:01') && strtotime($value->firsttime) <= strtotime($median_time_1)){
	            		$session_no = '1';

	            	}else if(strtotime($value->firsttime)>strtotime($median_time_1) && strtotime($value->firsttime) <= strtotime($median_time_2)){
	            		$session_no = '2';
	            		echo $median_time_2; echo '<br>';

	            	}else{
	            		$session_no = '3';
	            	}

	            }else if($schedule_sessions_1!='' && $schedule_sessions_2!=''){
	            	if(strtotime($value->firsttime)>strtotime('00:00:01') && strtotime($value->firsttime) <= strtotime($median_time_1)){
	            		$session_no = '1';
	            	}else{
	            		$session_no = '2';
	            	}
	            }else{
	            	$session_no = '1';
	            }
	            echo 'session_no='.$session_no.'<br>';
	          $update = Spacersession::where('id', '=',$value->id)->update(['session_no' => $session_no]);
            }

            
    	}


    	$childata = Child::select('id','last_sync')->get();
         
        foreach ($childata as $childkey => $childvalue) {
                	 
                	$child_session = Spacersession::where('child_id', '=', $childvalue->id)	
									->where('type','0')
									->orderBy('date','ASC')
									->orderBy('firsttime','ASC')
									->get();

 					foreach ($child_session as $key => $value) {

 						# code...
	                    $session_tech = 0;
						$exist_session = Spacersession::select('id','totalpasscount','totalfailcount','date')
										->where('date', "=", date('Y-m-d',strtotime($value->date)))
										->where('session_no',$value->session_no)
										->where('child_id', '=', $childvalue->id)
										->where('type','0')
										
										->orderBy('firsttime','ASC')
										->get();

						if(count($exist_session)){
							$tech_summary = 0;
							$tech_session_count = 0;
							foreach ($exist_session as $key => $value_tech) {
								if($tech_session_count==0){
									$update_session_id = $value_tech->id;
								}else{
									$update = Spacersession::where('id', '=',$value_tech->id)
					    				->update(['session_tech' 	 => 0]);
								}
								if($value_tech['totalpasscount']){
								$tech_summary = $tech_summary + (($value_tech['totalpasscount']/($value_tech['totalpasscount'] + $value_tech['totalfailcount']))*100);
								}
								$tech_session_count++;

								// if($value_tech->date =='2019-06-02 00:00:00'){
								
								// echo $value->date.'-'.$value->firsttime.'('.count($exist_session).')'; echo '=';
								// echo $value_tech['totalpasscount'];echo '<br>';
								// }
							}
							
							
							$tech_summary = $tech_summary/$tech_session_count;
							$update = Spacersession::where('id', '=',$update_session_id)
					    				->update(['session_tech' 	 => $tech_summary]);

						} 
                    }           
        }

    	 return response()->json([
            'status'  => true,
            'message' => "success" 
        ]);
    }
    public function sync_spacer_sessions(Request $request)
    { 
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');

		 
		$user = auth('api')->user();
		$user_id = $user->id;

		$child_info = Child::where('id',$child_id)->first();

		if(!empty($data['spacerSession'])){

			foreach ($data['spacerSession'] as $key => $value) {

				$exist = Spacersession::where('child_id', '=', $child_id)
								 ->where('date',     '=', date('Y-m-d',strtotime($value['date'])))
								 ->where('firsttime',     '=',$value['firstTime'])
								 ->where('type',     '=',0)
								 ->first();
								  
				if($exist != null) {

				    if(date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) > date('Y-m-d H:i:s',strtotime($exist->local_time))){

				  //   	$session_tech = 0;
						// if($value['totalPassCount'])
						// 	$session_tech = ($value['totalPassCount']/($value['totalPassCount'] + $value['totalFailCount']))*100;
						$session_tech = 0;
						$exist_session = Spacersession::select('id','totalpasscount','totalfailcount')
										->where('date', "=", date('Y-m-d',strtotime($value['date'])))
										->where('type','0')
										->where('id','!=',$exist->id)
										->where('child_id', '=', $child_id)
										->orderBy('firsttime','ASC')
										->get();
						if(count($exist_session)){
							$tech_summary = 0;
							$tech_session_count = 1;
							foreach ($exist_session as $key => $value_tech) {
								if($tech_session_count==1){
									$update_session_id = $value_tech->id;
								}
								$tech_summary = $tech_summary + (($value_tech['totalpasscount']/($value_tech['totalpasscount'] + $value_tech['totalfailcount']))*100);
								$tech_session_count++;
							}
							if($value['totalPassCount'])
								$tech_summary = $tech_summary + (($value['totalPassCount']/($value['totalPassCount'] + $value['totalFailCount']))*100);


							$tech_summary = $tech_summary/$tech_session_count;

							$update = Spacersession::where('id', '=',$update_session_id)
					    				->update(['session_tech' 	 => $tech_summary]);

						}else{
							if($value['totalPassCount'])
								$session_tech = ($value['totalPassCount']/($value['totalPassCount'] + $value['totalFailCount']))*100;
						}


				    	$update = Spacersession::where('id', '=',$exist->id)
				    				->update([
				    							'firsttime'=> $value['firstTime'],
				    						 	'lasttime'=> $value['lastTime'],
				    						 	'totalpasscount'=> $value['totalPassCount'],
				    						 	'totalfailcount'=> $value['totalFailCount'],
				    						 	'notes'=> $value['notes'],
				    						 	'spacer_string'=> $value['rawData'],
				    						 	'session_tech' 	 => $session_tech,
				    						 	'is_attack'=> $value['isAttack']==true?'1':0,
				    						 	'zone'=>  date('O',strtotime($value['localTimestamp'])),
				    						 	'local_time'=>  date('Y-m-d H:i:s',strtotime($value['localTimestamp'])),
				    						 	'spacer_id'=> $child_info->spacer_id,
				    						 	'date'=>  date('Y-m-d',strtotime($value['date']))
				    						 ]);
				    }
				}else{

					


					$schedule_sessions_1 = '';
	                $schedule_sessions_2 = '';
	                $schedule_sessions_3 = '';
	                $median_time_1 = '';
	                $median_time_2 = '';
	                $median_time_3 = '';
	                #Get Scheduled Session time
	                $schedule_sessiondata = Calendar::select('id','total_sessions','date','scheduled_sessions')
	                						->where('child_id', "=", $child_id)
	                						->where('date', "=", date('Y-m-d',strtotime(date('Y-m-d',strtotime($value['date'])))))
	                						->get();

	                if(!empty($schedule_sessiondata)){
	                    $schedule_sessiondata = $schedule_sessiondata[0];
	                    

	                    if($schedule_sessiondata->scheduled_sessions!=''){
	                        $schedule_sessions = explode(',', $schedule_sessiondata->scheduled_sessions);
	                        $schedule_sessions_1 = $schedule_sessions[0];
	                         
	                        if(isset($schedule_sessions[1])){ 
	                            $schedule_sessions_2 = $schedule_sessions[1];
	                            $median_time_1 = $this->convertMedianTime($schedule_sessions_1, $schedule_sessions_2);  
	                        }
	                        if(isset($schedule_sessions[2])){
	                            $schedule_sessions_3 = $schedule_sessions[2]; 
	                            $median_time_2 = $this->convertMedianTime($schedule_sessions_2, $schedule_sessions_3);
	                        }                    
	                    }
	                }

	                $session_no = '';
	                if($schedule_sessions_1!='' && $schedule_sessions_2!='' && $schedule_sessions_3!=''){
	                	if(strtotime($value['firstTime'])>strtotime('00:00:01') && strtotime($value['firstTime']) <= strtotime($median_time_1)){
	                		$session_no = '1';
	                	}else if(strtotime($value['firstTime'])>strtotime($median_time_1) && strtotime($value['firstTime']) <= strtotime($median_time_2)){
	                		$session_no = '2';
	                	}else{
	                		$session_no = '3';
	                	}

	                }else if($schedule_sessions_1!='' && $schedule_sessions_2!=''){
	                	if(strtotime($value['firstTime'])>strtotime('00:00:01') && strtotime($value['firstTime']) <= strtotime($median_time_1)){
	                		$session_no = '1';
	                	}else{
	                		$session_no = '2';
	                	}
	                }else{
	                	$session_no = '1';
	                }
                

					$session_tech = 0;
					$exist_session = Spacersession::select('id','totalpasscount','totalfailcount')
									->where('date', "=", date('Y-m-d',strtotime($value['date'])))
									->where('session_no',$session_no)
									->where('type','0')
									->where('child_id', '=', $child_id)
									->orderBy('firsttime','ASC')
									->get();
					if(count($exist_session)){
						$tech_summary = 0;
						$tech_session_count = 1;
						foreach ($exist_session as $key => $value_tech) {
							if($tech_session_count==1){
								$update_session_id = $value_tech->id;
							}
							$tech_summary = $tech_summary + (($value_tech['totalpasscount']/($value_tech['totalpasscount'] + $value_tech['totalfailcount']))*100);
							$tech_session_count++;
						}
						if($value['totalPassCount'])
							$tech_summary = $tech_summary + (($value['totalPassCount']/($value['totalPassCount'] + $value['totalFailCount']))*100);

						$tech_summary = $tech_summary/$tech_session_count;

						$update = Spacersession::where('id', '=',$update_session_id)
				    				->update(['session_tech' 	 => $tech_summary]);

					}else{
						if($value['totalPassCount'])
							$session_tech = ($value['totalPassCount']/($value['totalPassCount'] + $value['totalFailCount']))*100;
					}

				    $insert = Spacersession::create([
			           			'child_id' 		 => $child_id,
					            'firsttime'		 => $value['firstTime'],
    						 	'lasttime'		 => $value['lastTime'],
    						 	'totalpasscount' => $value['totalPassCount'],
    						 	'totalfailcount' => $value['totalFailCount'],
    						 	'notes'			 => $value['notes'],
    						 	'spacer_string'  => $value['rawData'],
    						 	'session_no' 	 => $session_no,
    						 	'session_tech' 	 => $session_tech,
    						 	'child_id' 		 => $child_id,
    						 	'is_attack'		 => $value['isAttack']==true?'1':0,
    						 	'zone'			 =>  date('O',strtotime($value['localTimestamp'])),
    						 	'spacer_id'		 => $child_info->spacer_id,
    						 	'local_time'	 =>  date('Y-m-d H:i:s',strtotime($value['localTimestamp'])),
    						 	'date'			 =>  date('Y-m-d',strtotime($value['date']))
			        ]);
				}
				
			}
		}
		
		#Get Calendar Informations
		$spacer_info = Spacersession::where('child_id', '=', $child_id)->where('type', '=', '0');
		if($data['serverTimestamp']!=''){ 

			$spacer_info = $spacer_info->where('created_at','>=', date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])));
		}
		$spacer_info = $spacer_info->get();
		 
        $spacer_response = array(); 
        foreach ($spacer_info as $key => $value) {
         $totalpasscount =  (int) $value->totalpasscount;
         $totalfailcount =  (int) $value->totalfailcount;
         $isAttack		 = $value->is_attack==1?'true':'false';
         $spacer_response[] = array('date'=>date('Y-m-d O',strtotime($value->date)),
         							'firstTime'=>$value->firsttime,
         							'lastTime'=>$value->lasttime,
         							'is_attack'=>$isAttack,
         							'rawData'=>$value->spacer_string,
         							'totalPassCount'=>$totalpasscount,
         							'totalFailCount'=>$totalfailcount,
         							'notes'=>$value->notes
         						   );
        }
        $response['serverTimestamp'] = date('Y-m-d H:i:s O'); 
        $response['spacerSession']	 =  $spacer_response;
        return response()->json([
            'status'  => true,
            'message' => "success",
            'data'    => $response
        ]);
    }

    public function convertMedianTime($starttime, $endtime){
        
        $seconds        = strtotime($endtime) - strtotime($starttime);
        $minutes        =  floor($seconds / 60);
        $median_time    = $minutes/2;
        $min_sec        = explode('.', $median_time); 
        $retrun_time    = date('H:i:s',strtotime('+'.$min_sec[0].' minutes',strtotime($starttime)));
        if(isset($min_sec[1])){   
          $remining_sec = ('0.'.$min_sec[1])*60;
          $retrun_time  = date('H:i:s',strtotime('+'.$remining_sec.' seconds',strtotime($retrun_time)));  
        }
        return $retrun_time;
    }

    public function sync_meattack(Request $request)
    { 
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');

		 
		$user = auth('api')->user();
		$user_id = $user->id;
		if(!empty($data['meAttacks'])){
			$child_info = Child::where('id',$child_id)->first();
			foreach ($data['meAttacks'] as $key => $value) {
 				$exist = Spacersession::where('child_id', '=', $child_id)
								 ->where('date',     '=', date('Y-m-d',strtotime($value['date'])))
								 ->where('firsttime','=',$value['time'])
								 ->where('type',     '=',1)
								 ->first();
								  
				if($exist != null) {

				    if(date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) > date('Y-m-d H:i:s',strtotime($exist->local_time))){

				    	$update = Spacersession::where('id', '=',$exist->id)
				    				->update([
				    							
				    						 	'notes'		=> $value['notes'],
				    						 	'local_time'=>  date('Y-m-d H:i:s',strtotime($value['localTimestamp'])),
				    						 	'spacer_id'=> $child_info->spacer_id,
				    						 	
				    						 ]);
				    }
				}else{
				    $insert = Spacersession::create([
			           			'child_id'  => $child_id,
			           			'firsttime' => $value['time'],
    						 	'notes'		=> $value['notes'],
    						 	'type'		=> '1',
    						 	'local_time'=>  date('Y-m-d H:i:s',strtotime($value['localTimestamp'])),
    						 	'is_attack'=>1,
    						 	'date'		=>  date('Y-m-d',strtotime($value['date'])),
    						 	'spacer_id'=> $child_info->spacer_id,
			        ]);
			    }
				
				
			}
		}
		
		#Get Calendar Informations
		$spacer_info = Spacersession::where('child_id', '=', $child_id)->where('type', '=', '1');
		if($data['serverTimestamp']!=''){ 

			$spacer_info = $spacer_info->where('created_at','>=', date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])));
		}
		$spacer_info = $spacer_info->get();
		 
        $spacer_response = array(); 
        foreach ($spacer_info as $key => $value) {
        
         $spacer_response[] = array('date'=>date('Y-m-d O',strtotime($value->date)),
         							'notes'=>$value->notes
         						   );
        }
        $response['serverTimestamp'] = date('Y-m-d H:i:s O'); 
        $response['meAttacks']	 =  $spacer_response;
        return response()->json([
            'status'  => true,
            'message' => "success",
            'data'    => $response
        ]);
    }
    public function delete_meattack(Request $request)
    { 
        $data     = $request->json()->all();
        $child_id = $request->header('CHILD-ID');
       	if(!empty($data['meAttack'])){
       		$value = $data['meAttack'];
	        $exist = Spacersession::where('child_id', '=', $child_id)
									 ->where('date',     '=', date('Y-m-d',strtotime($value['date'])))
									 ->where('firsttime','=',$value['time'])
									 ->where('type',     '=',1)
									 ->delete();


			return response()->json([
	            'status'  => true,
	            'message' => "You have removed reward successfully." 
	        ]);

      	}else{
      		return response()->json([
	            'status'  => false,
	            'message' => "Invalid format." 
	        ]);
      	}

        

    }
}

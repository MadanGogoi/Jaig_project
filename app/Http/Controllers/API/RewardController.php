<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


use App\User;
use App\Models\Child;
use App\Models\Reward;
use App\Models\Feedback;

use Illuminate\Validation\Rule;
use Validator, DB, Hash, Mail;

class RewardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['']]);
    }
    public function add_reward(Request $request)
    {
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');
			$rules = [

		            'name' 		       => 'string',
	                'startDate'        => 'required',
			        'endDate'          => 'required',
	                'complianceTarget' => 'required',
		        	 ];

	    $validator = Validator::make($request->all(), $rules);



        if ($validator->fails()) {
          if($validator->errors()->get('name')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('name'),
            ]);
          }

        }


        if ($validator->fails()) {
          if($validator->errors()->get('startDate')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('startDate'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('endDate')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('endDate'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('complianceTarget')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('complianceTarget'),
            ]);
          }

        }


		$user = auth('api')->user();
		$user_id = $user->id;

	    $insert = Reward::create([
           			'child_id' 		 => $child_id,
		            'name' => request('name'),
		            'image_id' => request('imageId'),
                'reward_type_id' => request('rewardsCategory'),
		            'compliance' => request('complianceTarget'),
		            'from_date' 			 => date('Y-m-d',strtotime(request('startDate'))),
		            'to_date'  => date('Y-m-d',strtotime(request('endDate'))),
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s')

        ]);
		$reward_id = $insert->id;		
		#Image Upload
        $image    = request('imageData');

        if($image !=''){
          //$filename = md5($image . time()) . rand(1, 99) . '.' . $image->getClientOriginalExtension();

          if($image->getClientOriginalExtension()!=''){
            $filename = md5($image . time()) . rand(1, 99) . '.' . $image->getClientOriginalExtension();
          }else{
             $getMimeType = $image->getMimeType();
             $extension ='';
            if($getMimeType == 'image/png'){
              $extension = 'png';
            }else if($getMimeType == 'image/jpeg'){
              $extension = 'jpg';
            }else if($getMimeType == 'application/pdf'){
              $extension = 'pdf';
            }
            $filename = md5($image . time()) . rand(1, 99) . '.' . $extension;
          }

          $image_original = \Image::make(file_get_contents($image));
          //dd($filename);
          $image_large = \Image::make(file_get_contents($image))->resize(1000, null, function ($constraint) {
              $constraint->aspectRatio();
          });
          $image_medium = \Image::make(file_get_contents($image))->resize(600, null, function ($constraint) {
              $constraint->aspectRatio();
          });
          $image_small = \Image::make(file_get_contents($image))->resize(300, null, function ($constraint) {
              $constraint->aspectRatio();
          });
          $image_thumb = \Image::make(file_get_contents($image))->resize(100, 100);

          \Storage::disk('s3')->put('original/' . $filename, $image_original->stream()->__toString());
          \Storage::disk('s3')->put('large/' . $filename, $image_large->stream()->__toString());
          \Storage::disk('s3')->put('medium/' . $filename, $image_medium->stream()->__toString());
          \Storage::disk('s3')->put('small/' . $filename, $image_small->stream()->__toString());
          \Storage::disk('s3')->put('thumbnail/' . $filename, $image_thumb->stream()->__toString());

          $childinfo = Reward::where('id', '=', $reward_id)->update(['image'=>$filename]);
       }



		#Get Calendar Informations
		$reward_info = Reward::where('child_id',$child_id)->where('id',$reward_id);

		$reward_info = $reward_info->get();

        $reward_response = array();
        foreach ($reward_info as $key => $value) {
        $imageURL = '';
        if($value->image!=null){
            $imageURL = env('AWS_URL').'/small/'.$value->image;
        }
         $complianceReached =  (int) $value->compliance_reached;
         $complianceTarget =  (int) $value->compliance;
         $reward_response[] = array(
	         	'id'=>$value->id,
	         	'name'=>$value->name,
            'imageId'=>$value->image_id,
	         	'imageURL'=>$imageURL,
	         	'startDate'=>date('Y-m-d',strtotime($value->from_date)),
	         	'endDate'=>date('Y-m-d',strtotime($value->to_date)),
	         	'complianceTarget'=>$complianceTarget,
	         	'complianceReached'=>$complianceReached,
	         	'isClaimed'=> (boolean) $value->status
         	);
        }

        $response['reward']		 =  $reward_response;
        return response()->json([
            'status'  => true,
            'message' => "success",
            'data'    => $response
        ]);
    }
    public function sync_rewards(Request $request)
    {
		$data     = $request->json()->all();
		$child_id = $request->header('CHILD-ID');


		$user = auth('api')->user();
		$user_id = $user->id;
		if(!empty($data['rewards'])){

			foreach ($data['rewards'] as $key => $value) {

				$exist = Reward::where('child_id', '=', $child_id)
								 ->where('id',     '=', $value['id'])
								 ->first();

				if($exist != null) {

				    if(date('Y-m-d H:i:s',strtotime($value['localTimestamp'])) > date('Y-m-d H:i:s',strtotime($exist->local_time))){

				    	$update = Reward::where('id', '=',$exist->id)
				    				->update(['name'=> $value['name'],
				    						 'compliance_reached'=> $value['complianceReached'],
				    						 'status'=> $value['isClaimed']=='true'?1:0,
				    						 'local_time'=>  date('Y-m-d H:i:s',strtotime($value['localTimestamp'])),
                         'updated_at'  => date('Y-m-d H:i:s')
				    						 ]);
				    }
				}else{
				    $insert = Reward::create([
			           			'child_id' 		 => $child_id,
					            'name' => $value['name'],
					            'compliance_reached' => $value['complianceReached'],
					            'status'=> $value['isClaimed']=='true'?1:0,
                      'updated_at'  => date('Y-m-d H:i:s'),
					            'local_time'  => date('Y-m-d H:i:s',strtotime($value['localTimestamp']))
			        ]);
				}

			}
		}
		#Get Reward Informations
		$reward_info = Reward::where('child_id', '=', $child_id)->where('child_id', '=', $child_id);

		if($data['serverTimestamp']!=''){
			$reward_info = $reward_info->where('updated_at','>=', date('Y-m-d H:i:s',strtotime($data['serverTimestamp'])));
		}


		$reward_info = $reward_info->get();

        $reward_response = array();
        foreach ($reward_info as $key => $value) {
	        $imageURL = '';
	        if($value->image!=null){
	            $imageURL = env('AWS_URL').'/small/'.$value->image;
	        }
	         $complianceReached =  (int) $value->compliance_reached;
	         $complianceTarget =  (int) $value->compliance;
	         $reward_response[] = array(
		         	'id'=>$value->id,
		         	'name'=>$value->name,
              'imageId'=>$value->image_id,
		         	'imageURL'=>$imageURL,
		         	'startDate'=>date('Y-m-d',strtotime($value->from_date)),
		         	'endDate'=>date('Y-m-d',strtotime($value->to_date)),
		         	'complianceTarget'=>$complianceTarget,
		         	'complianceReached'=>$complianceReached,
		         	'isClaimed'=> (boolean) $value->status
	         	);
        }
        $response['serverTimestamp'] = date('Y-m-d H:i:s O');
        $response['rewards']		 =  $reward_response;
        return response()->json([
            'status'  => true,
            'message' => "success",
            'data'    => $response
        ]);

    }

    public function delete_reward(Request $request)
    { 
        $data     = $request->json()->all();
        $child_id = $request->header('CHILD-ID');
        $rules = [  'reward_id'  => 'required|integer'];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          if($validator->errors()->get('reward_id')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('reward_id'),
            ]);
          }

        }

        $exist = Reward::where('child_id', '=', $child_id)->where('id','=', $data['reward_id'])
                   ->delete();

        return response()->json([
            'status'  => true,
            'message' => "You have removed reward successfully." 
        ]);

    }

    public function send_feedback(Request $request)
    {
		$data     = $request->json()->all();

		  $rules = [  'option' 	=> 'required',
	                'feedback'  => 'required'
		        	 ];

	    $validator = Validator::make($request->all(), $rules);



        if ($validator->fails()) {
          if($validator->errors()->get('option')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('option'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('feedback')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('feedback'),
            ]);
          }

        }



		$user = auth('api')->user();
		$user_id = $user->id;

	    $insert = Feedback::create([
           			'user_id' 		 => $user_id,
		            'type' => request('option'),
		            'content' => request('feedback'),
		            'created_at'=> date('Y-m-d H:i:s'),
		            'updated_at'  => date('Y-m-d H:i:s'),

        ]);

        return response()->json([
            'status'  => true,
            'message' => "Thank you for your feedback!"
        ]);
    }
}

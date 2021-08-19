<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\User;
use App\Models\Child;

use Illuminate\Validation\Rule;



use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator, DB, Hash, Mail;

class ChildController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['']]);
    }
    /**
     * API Register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_child(Request $request)
    {	
    	$user = auth('api')->user();
    	$user_id = $user->id;
    	$rules = [
		            
		            'name' 		         => 'required|string',
                'birthday'         => 'required|string',
                'country'          => 'required',
                'height'           => 'required',
		            'weight'           => 'required',
                'gender'           => 'required|string',
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
          if($validator->errors()->get('birthday')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('birthday'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('country')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('country'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('height')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('height'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('weight')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('weight'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('gender')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('gender'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('profileImageData')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('profileImageData'),
            ]);
          }

        }

        $birthday = request('birthday');
        $birthday = $birthday!='' ? date('Y-m-d',strtotime($birthday)):$birthday;

        $child = Child::create([
			            'name' 				=> request('name'),
			            'dob' 				=> $birthday,
			            'name' 				=> request('name'),
			            'join_date' 	=> date('Y-m-d H:i:s'),
			            'user_id' 	  => $user_id,
			            'gender' 	    => request('gender'),
                  'height'      => request('height'),
                  'weight'      => request('weight'),
                  'country_id'  => request('country') 
			        ]);
        $child_id = $child->id;

        #Image Upload
        $image    = request('profileImageData');
        if($image !=''){
          $filename = md5($image . time()) . rand(1, 99) . '.' . $image->getClientOriginalExtension();
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

          //dd(\Storage::disk('s3')->put('original/' . $filename, $image_original->stream()->__toString()));
          $childinfo = Child::where('id', '=', $child_id)->update(['profile_image'=>$filename]);
       }
        
        #Get Child Informations
        $child_list = Child::select('id','spacer_id as deviceUUID', 'profile_image as imageURL','name')->where('user_id', '=', $user_id)->get();
        $child_list_array = array();
        

        foreach ($child_list as $key => $value) {
          $deviceUUID = '';
          if($value->deviceUUID!=null){
            $deviceUUID = $value->deviceUUID;
          }
          $imageURL = '';
          if($value->imageURL!=null){
            $imageURL = env('AWS_URL').'/small/'.$value->imageURL;
          }

          $child_list_array[] = array('id'=>$value->id,'name'=>$value->name,'deviceUUID'=>$deviceUUID,'imageURL'=>$imageURL);
        }
        return response()->json([
            'status'  => true,
            'message'   => "success",
            'data'    => [ 'children' =>$child_list_array]
        ]);
    }

    public function get_children(Request $request)
    { 
      $user = auth('api')->user();
      $user_id = $user->id;
      #Get Child Informations
        $child_list = Child::select('id','name','spacer_id as deviceUUID', 'profile_image as imageURL')->where('user_id', '=', $user_id)->get();
        $child_list_array = array();
        

        foreach ($child_list as $key => $value) {
          $deviceUUID = '';
          if($value->deviceUUID!=null){
            $deviceUUID = $value->deviceUUID;
          }
          $imageURL = '';
          if($value->imageURL!=null){
            $imageURL = env('AWS_URL').'/small/'.$value->imageURL;
          }

          $child_list_array[] = array('id'=>$value->id,'name'=>$value->name,'deviceUUID'=>$deviceUUID,'imageURL'=>$imageURL);
        }
        return response()->json([
            'status'  => true,
            'message'   => "success",
            'data'    => [ 'children' =>$child_list_array]
        ]);
    }
    public function get_child(Request $request)
    { 
      $user = auth('api')->user();
      $child_id = $request->header('CHILD-ID');
      $user_id = $user->id;
      #Get Child Informations
        $child_info = Child::select('id','name','spacer_id as deviceUUID', 'profile_image as imageURL','dob','country_id','height','weight','gender','created_at')->where('id', '=', $child_id)->get();
        $response = array();
        
        if(!empty($child_info)){
          $child_info = $child_info[0];

          $deviceUUID = '';
          if($child_info->deviceUUID!=null){
            $deviceUUID = $child_info->deviceUUID;
          }
          $imageURL = '';
          if($child_info->imageURL!=null){
            $imageURL = env('AWS_URL').'/small/'.$child_info->imageURL;
          }
          $country = array();
          if($child_info->country_id!=null){
            $country = array('id'=>$child_info->country->id,'name'=>$child_info->country->name);
          }else{
            $country = (object)array();
          } 
          $response['id'] = $child_info->id;
          $response['name'] = $child_info->name;
          $response['deviceUUID'] = $child_info->deviceUUID;
          $response['imageURL'] = $imageURL;
          $response['joinedDate'] = date('Y-m-d H:i:s O',strtotime($child_info->created_at));
          $response['birthday'] = date('Y-m-d',strtotime($child_info->dob));
          $response['country'] = $country;
          $response['height'] = $child_info->height;
          $response['weight'] = $child_info->weight;
          $response['gender'] = $child_info->gender;

          return response()->json([
              'status'  => true,
              'message'   => "success",
              'data'    => $response
          ]);

        }else{
          return response()->json([
              'status'  => flase,
              'message'   => "Invalid child id" 
          ]);
        }
         
        
    }
    public function edit_child(Request $request)
    { 
      $user = auth('api')->user();
      $user_id = $user->id;
      $child_id = $request->header('CHILD-ID');
      $rules = [
                
                'name'             => 'required|string',
                'birthday'         => 'required|string',
                'country'          => 'required',
                'height'           => 'required',
                'weight'           => 'required',
                'gender'           => 'required|string',
                'isImageChanged'           => 'required',
                
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
          if($validator->errors()->get('birthday')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('birthday'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('country')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('country'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('height')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('height'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('weight')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('weight'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('gender')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('gender'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('isImageChanged')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('isImageChanged'),
            ]);
          }

        }

        $birthday = request('birthday');
        $birthday = $birthday!='' ? date('Y-m-d',strtotime($birthday)):$birthday;

        $child = Child::where('id', '=', $child_id)->update([
                  'name'        => request('name'),
                  'dob'         => $birthday,
                  'name'        => request('name'),
                  'gender'      => request('gender'),
                  'height'      => request('height'),
                  'weight'      => request('weight'),
                  'country_id'     => request('country') 
              ]);
        

        #Image Upload
        if(request('isImageChanged')=='true'){
            $image    = request('profileImageData');
            if($image !=''){
              $filename = md5($image . time()) . rand(1, 99) . '.' . $image->getClientOriginalExtension();
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

              $childinfo = Child::where('id', '=', $child_id)->update(['profile_image'=>$filename]);
           }
        }
        #Get Child Informations
        $child_info = Child::select('id','name','spacer_id as deviceUUID', 'profile_image as imageURL','dob','country_id','height','weight','gender','created_at')->where('id', '=', $child_id)->get();
        $response = array();
        
        if(!empty($child_info)){
          $child_info = $child_info[0];

          $deviceUUID = '';
          if($child_info->deviceUUID!=null){
            $deviceUUID = $child_info->deviceUUID;
          }
          $imageURL = '';
          if($child_info->imageURL!=null){
            $imageURL = env('AWS_URL').'/small/'.$child_info->imageURL;
          }
          $country = array();
          if($child_info->country_id!=null){
            $country = array('id'=>$child_info->country->id,'name'=>$child_info->country->name);
          }else{
            $country = (object)array();
          } 
          $response['id'] = $child_info->id;
          $response['name'] = $child_info->name;
          $response['deviceUUID'] = $child_info->deviceUUID;
          $response['imageURL'] = $imageURL;
          $response['joinedDate'] = date('Y-m-d H:i:s O',strtotime($child_info->created_at));
          $response['birthday'] = date('Y-m-d',strtotime($child_info->dob));
          $response['country'] = $country;
          $response['height'] = $child_info->height;
          $response['weight'] = $child_info->weight;
          $response['gender'] = $child_info->gender;

          return response()->json([
              'status'  => true,
              'message'   => "success",
              'data'    => $response
          ]);

        }else{
          return response()->json([
              'status'  => flase,
              'message'   => "Invalid child id" 
          ]);
        }
        
    }
    public function delete_child(Request $request)
    { 
       
        $child_id = $request->header('CHILD-ID');
        $user = auth('api')->user();
        $user_id = $user->id;
        if($child_id){
           
          $id = Child::where('id',$child_id)->where('user_id',$user_id)->first();
          if($id!=null){
            $delete = Child::where('id',$child_id)->where('user_id',$user_id)->delete();
      
            return response()->json([
                'status'  => true,
                'message' => "You have removed ".$id->name." successfully." 
            ]);
          }else{
             return response()->json([
                'status'  => false,
                'message' => "Sorry, child not found." 
            ]);
          }
        }else{
          return response()->json([
              'status'  => false,
              'message' => "Sorry, child id not found." 
          ]);
        }
        
      }
    public function set_deviceuuid(Request $request)
    { 
       
      $rules = [
                //'id'          => 'required',
                'deviceUUID'  => 'required|string'
             ];
     
      $child_id = $request->header('CHILD-ID');

      $validator = Validator::make($request->all(), $rules);

      

        if ($validator->fails()) {
          if($validator->errors()->get('id')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('id'),
            ]);
          }

        }
        //  if ($validator->fails()) {
        //   if($validator->errors()->get('deviceUUID')) {
        //     return response()->json([
        //       'status' => false,
        //       'message' => $validator->errors()->first('deviceUUID'),
        //     ]);
        //   }

        // }

         if (!$child_id) {        
            return response()->json([
              'status' => false,
              'message' => 'Child ID Required',
            ]);
        }

        // if(request('deviceUUID')==''){
           // $deviceUUID = $this->random_strings('32');

           // $get_deviceinfo = Child::select('id')
           //        ->where('id', '!=', $child_id)
           //        ->where('spacer_id', '=', request('deviceUUID'))->get()->count();
           // if($get_deviceinfo>0){
           //    $deviceUUID = $this->random_strings('32');
           // }
        //    $childinfo = Child::where('id', '=',$child_id)->update(['spacer_id'=>$deviceUUID]);

        //    $child_data = array('id'=> $child_id,'deviceUUID'=>$deviceUUID);
               
        //        return response()->json([
        //           'status'  => true,
        //           'message' => "success",
        //           'data'    => $child_data
        //       ]);


        // }else{
        // #Get Child Informations
        //     $get_child = Child::select('id')
        //           ->where('id', '!=', $child_id)
        //           ->where('spacer_id', '=', request('deviceUUID'))->get()->count();
        //     if($get_child==0){
               
        //        $childinfo = Child::where('id', '=',$child_id)->update(['spacer_id'=>request('deviceUUID')]);
        //       // $child_id = (int) request('id');
        //        $child_data = array('id'=> $child_id,'deviceUUID'=>request('deviceUUID'));
               
        //        return response()->json([
        //           'status'  => true,
        //           'message' => "success",
        //           'data'    => $child_data
        //       ]);

        //     }else{
        //       return response()->json([
        //           'status'  => false,
        //           'message' => "Sorry, this spacer is already owned by another child." 
        //       ]);
        //     }
        // }  

        $get_child = Child::select('id')
                  ->where('id', '!=', $child_id)
                  ->where('spacer_id', '=', request('deviceUUID'))->get()->count();
        if($get_child==0 || request('deviceUUID')==''){
           
           do {
             $deviceUUID = $this->random_strings('8');
           } while ( Child::where( 'spacer_id', $deviceUUID )->exists() );
           //dd($deviceUUID);
           // $get_deviceinfo = Child::select('id')
           //        ->where('id', '!=', $child_id)
           //        ->where('spacer_id', '=', $deviceUUID)->get()->count();
           // if($get_deviceinfo>0){
           //    $deviceUUID = $this->random_strings('32');
           // }

           $childinfo = Child::where('id', '=',$child_id)->update(['spacer_id'=>$deviceUUID]);
          // $child_id = (int) request('id');
           $child_data = array('id'=> $child_id,'deviceUUID'=>$deviceUUID);
           
           return response()->json([
              'status'  => true,
              'message' => "success",
              'data'    => $child_data
          ]);

        }else{
          return response()->json([
              'status'  => false,
              'message' => "Sorry, this spacer is already owned by another child." 
          ]);
        }


        
    }
    public function random_strings($length_of_string) 
    { 
      
        // String of all alphanumeric character 
        $str_result = '0123456789ABCDEF'; 
      

        // Shufle the $str_result and returns substring 
        // of specified length 
        $finalkey = substr(str_shuffle($str_result),  
                           0, 2);
        $finalkey .= substr(str_shuffle($str_result),  
                           0, 2); 
        $finalkey .= substr(str_shuffle($str_result),  
                           0, 2); 
        $finalkey .= substr(str_shuffle($str_result),  
                           0, 2);  

         return $finalkey;
    }
    public function remove_deviceuuid(Request $request)
    { 
       
        $rules = [
                'id'          => 'required',
                'deviceUUID'  => 'required|string'
             ];

      $validator = Validator::make($request->all(), $rules);

      

        if ($validator->fails()) {
          if($validator->errors()->get('id')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('id'),
            ]);
          }

        }
         if ($validator->fails()) {
          if($validator->errors()->get('deviceUUID')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('deviceUUID'),
            ]);
          }

        }

        #Get Child Informations
        $get_child = Child::select('id')
              ->where('id', '=', request('id'))
              ->where('spacer_id', '=', request('deviceUUID'))->get()->count();
        if($get_child>0){
           
           $childinfo = Child::where('id', '=',request('id'))->update(['spacer_id'=>'']);
           $child_id = (int) request('id');
           $child_data = array('id'=> $child_id);
           
           return response()->json([
              'status'  => true,
              'message' => "success",
              'data'    => $child_data
          ]);

        }else{
          return response()->json([
              'status'  => false,
              'message' => "Sorry, spacer is't pair with this child." 
          ]);
        }
        
      }
}

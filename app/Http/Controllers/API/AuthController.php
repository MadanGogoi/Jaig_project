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
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use \App\Mail\ForgotPassword;
use \App\Mail\ChangePassword;

class AuthController extends Controller
{
    
	/**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['email_login','facebook_login', 'register', 'forgot_password', 'changepassword']]);
    }

    /**
     * API Email Login, on success return JWT Auth token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function email_login(Request $request)
    {	
        $rules = [
            'email' 		=> 'required|email',
            'password' 		=> 'required|string',
            'device_type' 	=> 'required|string',
            'app_version' 	=> 'required|string',
            'os_version' 	=> 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
          if($validator->errors()->get('email')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('email'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('password')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('password'),
            ]);
          }

        }
        $credentials = $request->only('email', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = auth('api')->attempt($credentials)) {
            	
                return response()->json([
                    'status' => false, 
                    'message' => 'Invalid email or password. Please try again.'
                ], 200);
            }else  if(auth('api')->user()->activation_status==0){
            		auth()->logout();
	                return response()->json([
	                    'status' => false, 
	                    'message' => 'Please verify your account via email to proceed.'
	                ], 200);
	        }

        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'status' => false, 
                'message' => 'Failed to login, please try again.'
            ], 200);
        }
        
        #Validating first login
        $children_ist = array();
        $user = auth('api')->user();

    		if(isset($user->child)){
          $user_id = $user->id;
          #Get Child Informations
          $child_list = Child::select('id','name','spacer_id as deviceUUID', 'profile_image as imageURL')->where('user_id', '=', $user->id)->get();
    			foreach ($child_list as $child) {
    			      
                $deviceUUID = '';
              if($child->deviceUUID!=null){
                $deviceUUID = $child->deviceUUID;
              }
              $imageURL = '';
              if($child->imageURL!=null){
                $imageURL = env('AWS_URL').'/small/'.$child->imageURL;
              }

              $children_ist[] = array('id'=>$child->id,'name'=>$child->name,'deviceUUID'=>$deviceUUID,'imageURL'=>$imageURL);
    			}
    		}
        // all good so return the token
        return response()->json([
            'status' 	=> true,
            'message' 	=> "success",
            'data' 		=> [
			                'authorizationKey' 	 => $token,
			                'name' 				 => auth('api')->user()->name,
			                'email' 			 => auth('api')->user()->email,
			                'facebookID' 		 => auth('api')->user()->facebook_id,
			                'children' 			 => $children_ist,
			               ]
        ]);
    }

    /**
     * API Facebook Login, on success return JWT Auth token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function facebook_login(Request $request)
    {	
        $rules = [
            'email' 		=> 'email',
            'facebookID' 	=> 'required|string',
            'device_type' 	=> 'required|string',
            'app_version' 	=> 'required|string',
            'os_version' 	=> 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          if($validator->errors()->get('email')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('email'),
            ]);
          }

        }
        if ($validator->fails()) {
          if($validator->errors()->get('facebookID')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('facebookID'),
            ]);
          }

        }
        //$credentials = $request->only('facebook_id');
        $facebook_id 	= request('facebookID');
        $email 			= request('email');

       
        try {
      			$user = User::where('facebook_id', $facebook_id)->first();
      			 
      			if(!isset($user)){
      				$user = User::where('email', $email)->first();
      				if(isset($user)){
      					$update_fb = User::where('id', $user->id)->update(['facebook_id' => $facebook_id,'activation_status' => '1']);
      				}else{
      					return response()->json([
                          'status' => false, 
                          'message' => 'Invalid email or password. Please try again.'
                      	], 200);
      				}
      			}

        	            // attempt to verify the credentials and create a token for the user
            if (!$token = auth('api')->tokenById($user->id)) {
            	 
                return response()->json([
                    'status' => false, 
                    'message' => 'Invalid email or password. Please try again.'
                ], 200);
            }


        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json([
                'status' => false, 
                'message' => 'Failed to login, please try again.'
            ], 200);
        }
         
        #Validating first login
        $children_ist = array();

        //$user = User::where('id', $user_id)->first();

       
		if(isset($user->child)){
			foreach ($user->child as $child) {
			     $children_ist[] = array('id'=>$child->id, 'name'=>$child->name);
			}
		}
        // all good so return the token
        return response()->json([
            'status' 	=> true,
            'message' 	=> "success",
            'data' 		=> [
			                'authorizationKey' 	 => $token,
			                'name' 				 => $user->name,
			                'email' 			 => $user->email,
			                'facebookID' 		 => $user->facebook_id,
			                'children' 			 => $children_ist,
			               ]
        ]);
    }

    /**
     * Log out
     * Invalidate the token, so user cannot use it anymore
     * They have to relogin to get a new token
     *
     * @param Request $request
     */
    public function logout(Request $request)
    { 
        try {

        	$request_array = (array) $request->server;
        	$request_array = array_values($request_array);
        	$request_array = $request_array[0];
        	$tokenkey	   = $request_array['HTTP_AUTHORIZATION'];
        	$tokenkey	   = str_replace('Bearer ','',$tokenkey);
        	
            auth('api')->logout();
            $user 		   = Session::where('token', '=', $tokenkey)->update(['revoked'=>1]);

            return response()->json(['status' => true, 'message' => "You have successfully logged out."]);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['status' => false, 'message' => 'Failed to logout, please try again.'], 401);
        }
    }

    /**
     * API Register
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {	
    	$credentials = $request->only('email', 'password');
    	$rules = [
		            'email' 	=> 'required|email|max:255',
		            'password' 	=> 'required|min:8',
		            'name' 		=> 'required|string',
                
		            'facebookID'=> 'nullable|string',
	        	 ];

	    $validator = Validator::make($request->all(), $rules);

	    if ($validator->fails()) {
          if($validator->errors()->get('email')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('email'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('name')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('name'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('password')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('password'),
            ]);
          }

        }

        $validator = Validator::make($request->all(), ['email'  =>  'required|unique:users']);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' =>  'This email has been registered. Please use a different email address.',]);
        }

        $register_type = 2;
        if(request('facebookID')!=''){
        	$register_type = 3;
        }
        $password = $request->password;
        $activation_status = 1;
        $user = User::create([
			            'email' 				=> request('email'),
			            'password' 				=> Hash::make($password),
			            'name' 					=> request('name'),
			            'facebook_id' 			=> request('facebookID'),
			            'register_type' 		=> $register_type,
			            'activation_status' 	=> $activation_status
			        ]);
       	return response()->json([
            'status' 	=> true,
            'message' 	=> "Please check your email to verify your account." 
        ]);
    }
    public function forgot_password(Request $request)
    {
        $rules = [
            'email' => 'required|email'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
          if($validator->errors()->get('email')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('email'),
            ]);
          }

        }

         

        $user = User::where('email', request('email'))->first();
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => "This email is not registered."
            ]);
        }
        $temp_password = $this->rand_string(6);
        $user->password = Hash::make($temp_password);
        $user->save();

        \Mail::to($user)->send(new ChangePassword($user, $temp_password));
        return response()->json(['status' => true, 'message' => "Your password has been sent to your email!"]);
    }

    public function change_password(Request $request){
        $rules = [
            
            'newPassword' => ['required', function ($attr, $value, $fail) {
                if ($value != null) {
                        if (!(preg_match("/.{8}/", $value))) {
                            return $fail('The ' . $attr . ' must contain minimum 8 characters.');
                        }
                }
            }],
            'oldPassword' => 'required|string'
        ];
        $validator = Validator::make($request->all(), $rules);
         
        if ($validator->fails()) {
          if($validator->errors()->get('newPassword')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('newPassword'),
            ]);
          }

        }

        if ($validator->fails()) {
          if($validator->errors()->get('resetCode')) {
            return response()->json([
              'status' => false,
              'message' => $validator->errors()->first('resetCode'),
            ]);
          }

        }

        
        $userinfo = auth('api')->user();

        $user = User::where('id', $userinfo->id)->first();
        
        if(!Hash::check(request('oldPassword'), $user->password)) {
            return response()->json([
                'status' => false,
                'message' => "The password you entered is incorrect."
            ]);
        } 

        

        $newPassword = request('newPassword');
        $user->password = Hash::make($newPassword);
        $user->save();
        return response()->json(['status' => true, 'message' => "Your password have been changed successfully."]);
    }
     protected function rand_string($length)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        return substr(str_shuffle($chars), 0, $length);
    }
}

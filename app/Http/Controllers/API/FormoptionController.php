<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Country;

class FormoptionController extends Controller
{
    //
    public function index(Request $request){
    	#Country
    	 // $country = array(array('id'=>1,'name'=>'Singapore'),
    		// 		  array('id'=>2,'name'=>'Malaysia')
    		// 		 );

       $country = Country::all($columns = array('id','name'));
        $feedbackOptions = array(array('id'=>1,'name'=>'Report a Problem'),
                      array('id'=>2,'name'=>'Suggestions'),
                      array('id'=>3,'name'=>'Enquiries'),
                      array('id'=>4,'name'=>'Others')
                     );
         
    	 

    	return response()->json([
			'status' 	=> true,
			'message' 	=> 'success',
			'data' 		=> [
							'countries' 	  => $country,
                            'feedbackOptions'       => $feedbackOptions
                             
							]
		]);
    }
}

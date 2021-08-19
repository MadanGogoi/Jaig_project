<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Faq;

use Validator, DB, Hash, Mail;

class FaqController extends Controller
{
  public function view(Request $request)
  {
    $faq = Faq::where('id','1')->first();
    $data['faq']   = $faq->content;
    return view('faq'  , compact('data'));
  }
  
   
}

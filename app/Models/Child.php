<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use App\User;
use Illuminate\Database\Eloquent\SoftDeletes;


use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Session;

class Child extends Model
{
    use CrudTrait;
    use SoftDeletes;
    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'child';
    protected $primaryKey = 'id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['name','dob','join_date','user_id','gender','height','weight','spacer_id','country_id1',];
    // protected $hidden = [];
    protected $dates = ['dob','join_date'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }
    public function parent()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    public function rewards()
    {
        return $this->hasMany("App\Models\Reward");
    }
    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | ACCESORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
    public function getUserEmail(){
        return \App\User::where('id', $this->user_id)->first()->email;
         
    }
    public function attackbutton($crud = false){

        return '<a href="'.url('/admin/report/'.$this->id.'/attack/1/'.date('Y-m-d',strtotime("-6 days"))).'" class="btn btn-xs btn-default" data-button-type="get"><i class="fa fa-times-circle"></i> View Attack</a>';
    }
    public function compliancebutton($crud = false){

        return '<a href="'.url('/admin/report/'.$this->id.'/technique_compliance/1/'.date('Y-m-d',strtotime("-6 days"))).'" class="btn btn-xs btn-default" data-button-type="get"><i class="fa fa-times-circle"></i> View Technique vs Compliance</a>';
    }
    public function viewrewardbutton($crud = false){

        return '<a href="'.url('/admin/child/'.$this->id.'/viewreward/').'" class="btn btn-xs btn-default" data-button-type="get"><i class="fa fa-times-circle"></i> View Rewards</a>';
    }
    public function viewspacerdatabutton($crud = false){

        return '<a href="'.url('/admin/child/'.$this->id.'/viewspacerdata/').'" class="btn btn-xs btn-default" data-button-type="get"><i class="fa fa-times-circle"></i> View Spacer Data</a>';
    }
    public function viewreportbutton($crud = false){

        
         return '<a href="'.url('/admin/report/'.$this->id.'/attack/1/'.date('Y-m-d',strtotime("-6 days"))).'" class="btn btn-xs btn-default" data-button-type="get"><i class="fa fa-times-circle"></i> View Report</a>';
    }
    public function setImageAttribute($value)
    {   
        
        if(\Request::is('admin/*'))
        { 
            $attribute_name = "image";
            $disk = "s3";
            $destination_path = env('AWS_URL').'/';
 
            // if the image was erased
            if ($value==null) {
                if(strpos($value, 'default')===false){
                  // delete the image from disk
                  \Storage::disk($disk)->delete('original/'.$this->image);
                  \Storage::disk($disk)->delete('large/'.$this->image);
                  \Storage::disk($disk)->delete('thumbnail/'.$this->image);

                  
                }

                // set null in the database column
                $this->attributes[$attribute_name] = null;
            }

            // if a base64 was sent, store it in the db
            if (starts_with($value, 'data:image'))
            {
                // 0. Make the image
                $image_original = \Image::make($value);

                //dd($filename);
                $image_large = \Image::make($value)->resize(1000, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image_medium = \Image::make($value)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image_small = \Image::make($value)->resize(300, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $image_thumb = \Image::make($value)->resize(100, 100);

                $img = explode(',', $value);
                $ini =substr($img[0], 11);
                $type = explode(';', $ini);
                $extension = $type[0]; // result png 
                // 1. Generate a filename.
                $filename = md5($value.time()).rand(1,99).'.'.$extension;
                // 2. Store the image on disk.
                // \Storage::disk($disk)->put('original/'.$filename, $image_original->stream()->__toString());
                // \Storage::disk($disk)->put('thumbnail/'.$filename, $image_thumb->stream()->__toString());
                \Storage::disk('s3')->put('original/' . $filename, $image_original->stream()->__toString());
                \Storage::disk('s3')->put('large/' . $filename, $image_large->stream()->__toString());
                \Storage::disk('s3')->put('medium/' . $filename, $image_medium->stream()->__toString());
                \Storage::disk('s3')->put('small/' . $filename, $image_small->stream()->__toString());
                \Storage::disk('s3')->put('thumbnail/' . $filename, $image_thumb->stream()->__toString());
                // 3. Save the path to the database
               $this->attributes[$attribute_name] = $filename;
            }
            
        } else { 
            $this->attributes['image'] = $value;
        }
    }
}

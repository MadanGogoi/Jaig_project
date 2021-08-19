<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use App\Models\Child;
use App\User;

class Reward extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'reward';
    protected $primaryKey = 'id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['child_id','name','description','compliance','from_date','to_date','status','image','image_id'];
    // protected $hidden = [];
    // protected $dates = [];

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
    public function child()
    {
        return $this->belongsTo('App\Models\Child', 'child_id');
    }
    public function parent()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
    // public function parent()
    // {
    //     //$parent_id = $child->parent->id;
    //     $parent_id = 1;
    //     return $this->belongsTo(Child::class)->whereHas('child.parent', function($q) use($parent_id) {
    //                             $q->where('user_id', $parent_id);
    //                         });
    // }
    
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
    public function getStatus(){
        if($this->compliance_reached < $this->compliance &&  $this->to_date < date('Y-m-d')){
            return 'Incomplete';
        } else if($this->compliance < $this->compliance_reached &&  $this->to_date > date('Y-m-d')){
            return 'Ongoing';  
        }else if($this->status=='1'){
            return 'Claimed';  
        }else if($this->status=='0'){
            return 'Not Claimed';  
        }else{
            return '';
        }
         
         
    }
    public function getDateSet(){
        $from_date = date('d/m/Y',strtotime( $this->from_date));
        $to_date = date('d/m/Y',strtotime( $this->to_date));
        return $from_date.' - '.$to_date;
         
    }
    public function getParentName(){
        $child_info = Child::select('child.name as childname','users.name as parentname','email')
                    ->leftJoin('users', 'users.id', '=', 'child.user_id')
                    ->where('child.id', '=', $this->child_id)->first();
        return $child_info->parentname;
         
    }
    public function getParentEmail(){
        $child_info = Child::select('child.name as childname','users.name as parentname','email')
                    ->leftJoin('users', 'users.id', '=', 'child.user_id')
                    ->where('child.id', '=', $this->child_id)->first();

                   
        return $child_info->email;
         
         
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

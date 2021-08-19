<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use App\Models\Child;


use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Session;
class Spacersession extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'spacersession';
    protected $primaryKey = 'id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['id','child_id','firsttime','lasttime','totalpasscount','totalfailcount','notes','date','local_time','type','spacer_id','zone','is_attack','session_no','session_tech','spacer_string'];
    // protected $hidden = [];
    protected $dates = ['date','local_time'];

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
    public function getParentName(){
        $child_info = Child::select('child.name as childname','users.name as parentname','email')
                    ->leftJoin('users', 'users.id', '=', 'child.user_id')
                    ->where('child.id', '=', $this->child_id)->first();
        return $child_info->parentname;
    }
    public function getChildCountry(){
        $child_info = Child::select('country.name')
                    ->leftJoin('country', 'country.id', '=', 'child.country_id')
                    ->where('child.id', '=', $this->child_id)->first();
        return $child_info->name;
    }

    
    public function getAge(){
        $age =  \App\Models\Child::where('id', $this->child_id)->first()->dob;
        $age = date_diff(date_create($age), date_create('now'))->y;
        return $age;
         
    }
    public function getGender(){
        return \App\Models\Child::where('id', $this->child_id)->first()->gender;
         
    }
    public function getTime(){
        return date('H:i',strtotime($this->datetime));
         
    }
    public function getNoAttackSession(){
        return '-';
    }
    public function getSessionCorrection(){
        return '-';
    }
    public function getTechnique(){
        if($this->totalpasscount!=NULL && $this->totalpasscount != NULL){
            $techique = ($this->totalpasscount / ($this->totalpasscount+$this->totalfailcount)) * 100;
            $techique = number_format($techique,'2','.',true).'%';
        }else{
             $techique = '-';
        }

        return $techique;
    }
}

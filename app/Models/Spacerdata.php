<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\CrudTrait;
use App\Models\Child;

class Spacerdata extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'spacerdata';
    protected $primaryKey = 'id';
    public $timestamps = true;
    // protected $guarded = ['id'];
    protected $fillable = ['spacer_id','spacer_string','sync_date','datetime','timezone','country','child_id','technique','is_attack','daily_session','attack_session','notes'];
    // protected $hidden = [];
    protected $dates = ['sync_date','datetime'];

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
        //return \App\Models\Child::where('id', $this->user_id)->first()->email;
        return 'Email';
         
    }
    public function getAge(){
        return \App\Models\Child::where('id', $this->child_id)->first()->gender;
         
    }
    public function getGender(){
        return \App\Models\Child::where('id', $this->child_id)->first()->gender;
         
    }
    public function getTime(){
        return date('H:i',strtotime($this->datetime));
         
    }
}

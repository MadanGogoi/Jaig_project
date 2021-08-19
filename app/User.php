<?php


namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Backpack\CRUD\CrudTrait;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Session;

class User extends Authenticatable  implements JWTSubject
{
    use Notifiable, CrudTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'activation_status','facebook_id','register_type'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','activation_status',
    ];

     /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function child()
    {
        return $this->hasMany("App\Models\Child");
    }
    // public function childreward()
    // {
    //     return $this->hasManyThrough(User::class,
    //                             Child::class,
    //                             'user_id',
    //                             'id',
    //                             'id');
    // }
    public function rewards()
    {
        return $this->hasManyThrough(Reward::class, Child::class);
    }

    public function viewchildbutton($crud){
        
        return '<a href="'.url('/admin/parent/'.$this->id.'/viewchild/').'" class="btn btn-xs btn-default" data-button-type="get"><i class="fa fa-child"></i> View Child</a>';
    }

     /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}

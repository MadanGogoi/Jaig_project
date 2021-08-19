<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ParentchildRequest as StoreRequest;
use App\Http\Requests\ParentchildRequest as UpdateRequest;
use App\User;
use App\Models\Spacerdata;
use App\Models\Child;
use App\Models\Spacersession;
use Validator, DB, Hash, Mail;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Requests\CrudRequest;
/**
 * Class ParentchildCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ParentchildCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $user_id = \Route::current()->parameter('user_id');
        $this->crud->setModel('App\Models\Child');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/parent/'.$user_id.'/viewchild');
        $this->crud->setEntityNameStrings('View Child', 'View Child');
        $this->crud->denyAccess(['create','update','delete']);
        $this->crud->addClause('where', 'user_id', $user_id);

        $parent_info = User::select('users.name as parentname','email')
                    
                    ->where('id', '=', $user_id)->first();

        $this->crud->parent = $parent_info->parentname;
        $this->crud->parentemail = $parent_info->email;
       

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */
        if(! \Auth::user()->hasPermissionTo('Manage Parents')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }
        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->addButtonFromModelFunction('line', 'viewreward', 'viewrewardbutton');
        $this->crud->addButtonFromModelFunction('line', 'viewspacerdata', 'viewspacerdatabutton');
        $this->crud->addButtonFromModelFunction('line', 'viewreport', 'viewreportbutton', 'end');
        $this->crud->setColumns([
        // [     
        //         'label' => 'Parent Full Name',
        //         'type' => 'select',
        //         'name' => 'user_id',
        //         'entity' => 'parent',
        //         'attribute' => 'name',
        //         'model' => "App\User",
        //     ], 
              
        //     [     
        //         'label' => 'Email',
        //         'type' => 'select',
        //         'name' => 'user_id',
        //         'key' => 'parent_email',
        //         'entity' => 'parent',
        //         'attribute' => 'name',
        //         'model' => "App\User",
        //     ],
            [
                'name' => 'name',
                'label' => 'Child Name',
                'type' => 'text'
            ],
            [
                'name' => 'dob',
                'label' => 'Birthday',
                'type' => 'date',
                'format' =>'d/m/Y'
            ],
            [     
                'label' => 'Country',
                'type' => 'select',
                'name' => 'country',
                'key' => 'country',
                'entity' => 'country',
                'attribute' => 'name',
                'model' => "App\Models\Country",
            ],
            [
                'name' => 'height',
                'label' => 'Height(cm)',
                'type' => 'text'
            ],
            [
                'name' => 'weight',
                'label' => 'Weight(kg)',
                'type' => 'text'
            ],
            [
                'name' => 'gender',
                'label' => 'Gender(F/M)',
                'type' => 'text'
            ],
            [
                'name' => 'spacer_id',
                'label' => 'Spacer Id',
                'type' => 'text'
            ],
        ]);
        $this->crud->setListView('backpack::crud.list_parentchild', $this->data);
        // add asterisk for fields that are required in ParentchildRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
    }

    public function store(StoreRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::storeCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }

    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }
}

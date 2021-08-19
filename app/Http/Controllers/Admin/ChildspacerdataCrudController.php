<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ChildspacerdataRequest as StoreRequest;
use App\Http\Requests\ChildspacerdataRequest as UpdateRequest;
use App\Models\Child;

/**
 * Class ChildspacerdataCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ChildspacerdataCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $child_id = \Route::current()->parameter('child_id');
        $this->crud->setModel('App\Models\Spacersession');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/child/'.$child_id.'/viewspacerdata');
        $this->crud->setEntityNameStrings('View Child Spacer Data', 'View Child Spacer Data');
        $this->crud->addClause('where', 'child_id', $child_id);
        $this->crud->denyAccess(['create','update','delete']);
        $this->crud->allowAccess(['show']);

        if(! \Auth::user()->hasPermissionTo('Manage Spacer Data')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }
        
        $child_info = Child::select('child.name as childname','users.name as parentname','email')
                    ->leftJoin('users', 'users.id', '=', 'child.user_id')
                    ->where('child.id', '=', $child_id)->first();

        $this->crud->parent = $child_info->parentname;
        $this->crud->parentemail = $child_info->email;
        $this->crud->child = $child_info->childname;

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setColumns([
           // [     
           //      'label' => 'Parent Full Name',
           //      'type' => 'select',
           //      'name' => 'child_id',
           //      'key' => 'parent_name',
           //      'entity' => 'parent',
           //      'attribute' => 'name',
           //      'model' => "App\Models\Child",
           //  ],
            // [
            //     'name' => 'parent_name',
            //     'label' => 'Parent Full Name',
            //     'type' => 'model_function',
            //     'function_name' => 'getParentName',
            // ],
            // [     
            //     'label' => 'Email',
            //     'type' => 'select',
            //     'name' => 'child_id',
            //     'key' => 'parent_email',
            //     'entity' => 'parent',
            //     'attribute' => 'email',
            //     'model' => "App\Models\Child",
            // ],
            // [     
            //     'label' => 'Child Name',
            //     'type' => 'select',
            //     'name' => 'child_id',
            //     'entity' => 'child',
            //     'attribute' => 'name',
            //     'model' => "App\User",
            // ],
            // [
            //     'name' => 'age',
            //     'label' => 'Age',
            //     'type' => 'model_function',
            //     'function_name' => 'getAge',
            // ], 
            // [
            //     'name' => 'gender',
            //     'label' => 'Gender(F/M)',
            //     'type' => 'model_function',
            //     'function_name' => 'getGender',
            // ], 
            [
                'name' => 'type',
                'label' => 'Type',
                'type' => 'select_from_array',
                'options' => ['0'=>'Session', '1'=>'Manual Attack']
            ],
            [
                'name' => 'date',
                'label' => 'Session Date',
                'type' => 'date',
                'key' => 'session_date',
                'format' =>'d/m/Y'
            ],
            [
                'name' => 'firsttime',
                'label' => 'Creation Time',
                'type' => 'date',
                 
                'format' =>'H:i'
            ],
            [
                'name' => 'lasttime',
                'label' => 'Last Modified Time',
                'type' => 'date',
                 
                'format' =>'H:i'
            ],
            [
                'name' => 'technique',
                'label' => 'Technique(%)',
                'type' => 'model_function',
                'function_name' => 'getTechnique',
            ],
            [
                'name' => 'is_attack',
                'label' => 'Attack(Yes/No)',
                'type' => 'select_from_array',
                'options' => ['0'=>'No', '1'=>'Yes']
            ],
            // [
            //     'name' => 'daily_session',
            //     'label' => 'Daily Session Correction',
            //     'type' => 'model_function',
            //     'function_name' => 'getSessionCorrection',
            // ],
            // [
            //     'name' => 'daily_attacksession',
            //     'label' => 'No Of Attack Session',
            //     'type' => 'model_function',
            //     'function_name' => 'getNoAttackSession',
            // ],
            [
                'name' => 'zone',
                'label' => 'Time Zone',
                'type' => 'text'
            ],
            [
                'name' => 'child_country',
                'label' => 'Country',
                'type' => 'model_function',
                'function_name' => 'getChildCountry',
            ],
            // [
            //     'name' => 'country',
            //     'label' => 'Geographical Location / Country',
            //     'type' => 'text'
            // ],
            // [
            //     'name' => 'spacer_id',
            //     'label' => 'Spacer ID',
            //     'type' => 'text'
            // ],
            [
                'name' => 'spacer_string',
                'label' => 'Device Raw Data String',
                'type' => 'text'
            ],
            [
                'name' => 'created_at',
                'label' => 'Date and Time of Sync',
                'type' => 'datetime'
            ],
            [
                'name' => 'notes',
                'label' => 'Notes',
                'type' => 'text'
            ],
        ]);

        $this->crud->addFilter([ // dropdown filter
          'name' => 'type',
          'type' => 'dropdown',
          'label'=> 'Type'
        ], [
          0 => 'Session ',
          1 => 'Manual Attack ',
          
       
        ], function($value) { // if the filter is active
            $this->crud->addClause('where', 'type', $value);
        });

        $this->crud->addFilter([ // dropdown filter
          'name' => 'is_attack',
          'type' => 'dropdown',
          'label'=> 'Attack'
        ], [
          1 => 'Yes ',
          0 => 'No ',
          
       
        ], function($value) { // if the filter is active
            $this->crud->addClause('where', 'is_attack', $value);
        });

         $this->crud->addFilter([ // daterange filter
           'type' => 'date_range',
           'name' => 'date',
           'label'=> 'Date Range'
         ],
         false,
         function($value) { // if the filter is active, apply these constraints
           $dates = json_decode($value);
           $this->crud->addClause('where', 'date', '>=', $dates->from);
           $this->crud->addClause('where', 'date', '<=', $dates->to);
         });

        $this->crud->enableExportButtons();
        // add asterisk for fields that are required in ChildspacerdataRequest
        $this->crud->setRequiredFields(StoreRequest::class, 'create');
        $this->crud->setRequiredFields(UpdateRequest::class, 'edit');
        $this->crud->setListView('backpack::crud.list_childspacerdata', $this->data);
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

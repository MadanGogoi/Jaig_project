<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ChildrewardRequest as StoreRequest;
use App\Http\Requests\ChildrewardRequest as UpdateRequest;
use App\Models\Child;
/**
 * Class ChildrewardCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ChildrewardCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $child_id = \Route::current()->parameter('child_id');
        $this->crud->setModel('App\Models\Reward');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/child/'.$child_id.'/viewreward');
        $this->crud->setEntityNameStrings('Individual Rewards', 'Individual Rewards');
        $this->crud->addClause('where', 'child_id', $child_id);
        $this->crud->denyAccess(['create','update','delete']);
        $this->crud->allowAccess(['show']);

        $child_info = Child::select('child.name as childname','users.name as parentname','email')
                    ->leftJoin('users', 'users.id', '=', 'child.user_id')
                    ->where('child.id', '=', $child_id)->first();

        $this->crud->parent = $child_info->parentname;
        $this->crud->parentemail = $child_info->email;
        $this->crud->child = $child_info->childname;

        if(! \Auth::user()->hasPermissionTo('Manage Child') && ! \Auth::user()->hasPermissionTo('Manage Rewards')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setColumns([
            // [     
            //     'label' => 'Parent Full Name',
            //     'type' => 'select',
            //     'name' => 'child_id',
            //     'entity' => 'parent',
            //     'attribute' => 'name',
            //     'model' => "App\Models\Child",
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
            //     'key' => 'child_name',
            //     'entity' => 'child',
            //     'attribute' => 'name',
            //     'model' => "App\Models\Reward",
            // ],
            [
                'name' => 'name',
                'label' => 'Rewards',
                'type' => 'text',
                 
            ],
            [
                'name' => 'from_date',
                'label' => 'Date Set',
                'type' => 'model_function',
                'function_name' => 'getDateSet',
            ],
             [
                'name' => 'compliance',
                'label' => 'Compliance Set',
                'type' => 'text'
            ], 
            [
                'name' => 'status',
                'label' => 'Reward Status',
                'type' => 'select_from_array',
                'options' => ['0'=>'Not Claimed', '1'=>'Claimed']
            ]
        ]);
         
        // add asterisk for fields that are required in ChildrewardRequest
         
        $this->crud->setListView('backpack::crud.list_childreward', $this->data);
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
     public function show($id)
    { 
        $this->crud->hasAccessOrFail('show');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $data = $this->crud->getEntry($id);
        // set columns from db
        $this->crud->addColumn([
                'name' => 'parent_name',
                'label' => 'Parent Full Name',
                'type' => 'model_function',
                'function_name' => 'getParentName',
        ]);
        $this->crud->addColumn([
                'name' => 'parent_email',
                'label' => 'Email',
                'type' => 'model_function',
                'function_name' => 'getParentEmail',
            ])->afterColumn('parent_name');
        $this->crud->addColumn([     
                'label' => 'Child Name',
                'type' => 'select',
                'name' => 'child_id',
                'key' => 'child_name',
                'entity' => 'child',
                'attribute' => 'name',
                'model' => "App\Models\Reward",
            ])->afterColumn('parent_email');
       // $this->crud->addColumns(['name','compliance','compliance_reached','from_date','to_date']);
         $this->crud->addColumn([
                'name' => 'name',
                'label' => 'Reward',
                'type' => 'text',
          ])->afterColumn('child_id'); 
          $this->crud->addColumn([
                'name' => 'compliance',
                'label' => 'Compliance  ',
                'type' => 'text',
          ])->afterColumn('name');   
          $this->crud->addColumn([
                'name' => 'compliance_reached',
                'label' => 'Compliance reached',
                'type' => 'text',
          ])->afterColumn('compliance');   
          $this->crud->addColumn([
                'name' => 'from_date',
                'label' => 'From date',
                'type' => 'text',
          ])->afterColumn('compliance_reached'); 
          $this->crud->addColumn([
                'name' => 'to_date',
                'label' => 'To date',
                'type' => 'text',
          ])->afterColumn('from_date');         
        //$this->crud->setFromDb();
        //$this->crud->removeColumn(['image_id','local_time','created_at','updated_at','deleted_at']); 
        
        $this->crud->addColumn([   // Image
            'name' => 'image',
            'label' => 'Image',
            'type' => 'image',
            'prefix' => env('AWS_URL').'/large/',
            'height' => '300px',
        ])->afterColumn('to_date');
        $this->crud->addColumn([ 'name' => 'status','label' => 'Reward Status', 'type' => 'radio','options' => ['0'=>'Not Claimed', '1'=>'Claimed']])->afterColumn('image');
         //dd($this->crud->columns);
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // remove preview button from stack:line
        $this->crud->removeButton('show');
        $this->crud->removeButton('delete');

       // dd($this->data);
        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getShowView(), $this->data);
    
    }
}

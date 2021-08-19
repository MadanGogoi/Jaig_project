<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\RewardRequest as StoreRequest;
use App\Http\Requests\RewardRequest as UpdateRequest;

class RewardCrudController extends CrudController
{
    public function setup()
    {

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Reward');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/reward');
        $this->crud->setEntityNameStrings('reward', 'rewards');
        $this->crud->allowAccess(['show']);
        $this->crud->denyAccess(['create','update','delete']);
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */

        $this->crud->setColumns([
            // [     
            //     'label' => 'Parent Full Name',
            //     'type' => 'select',
            //     'name' => 'child_id',
            //     'key' => 'parent_name',
            //     'entity' => 'rewards',
            //     'attribute' => 'name',
            //     'model' => "App\User",
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
            [
                'name' => 'parent_name',
                'label' => 'Parent Full Name',
                'type' => 'model_function',
                'function_name' => 'getParentName',
            ],
            [
                'name' => 'parent_email',
                'label' => 'Email',
                'type' => 'model_function',
                'function_name' => 'getParentEmail',
            ],
            [     
                'label' => 'Child Name',
                'type' => 'select',
                'name' => 'child_id',
                'key' => 'child_name',
                'entity' => 'child',
                'attribute' => 'name',
                'model' => "App\Models\Reward",
            ],
            [
                'name' => 'name',
                'label' => 'Reward Name',
                'type' => 'text' 
            ],
        ]);

        $this->crud->addField([
            'name' => 'name',
            'label' => 'Reward Name',
            'type' => 'text',
        ]);

        // ------ CRUD FIELDS
        // $this->crud->addField($options, 'update/create/both');
        // $this->crud->addFields($array_of_arrays, 'update/create/both');
        // $this->crud->removeField('name', 'update/create/both');
        // $this->crud->removeFields($array_of_names, 'update/create/both');

        // ------ CRUD COLUMNS
        // $this->crud->addColumn(); // add a single column, at the end of the stack
        // $this->crud->addColumns(); // add multiple columns, at the end of the stack
        // $this->crud->removeColumn('column_name'); // remove a column from the stack
        // $this->crud->removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
        // $this->crud->setColumnDetails('column_name', ['attribute' => 'value']); // adjusts the properties of the passed in column (by name)
        // $this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);

        // ------ CRUD BUTTONS
        // possible positions: 'beginning' and 'end'; defaults to 'beginning' for the 'line' stack, 'end' for the others;
        // $this->crud->addButton($stack, $name, $type, $content, $position); // add a button; possible types are: view, model_function
        // $this->crud->addButtonFromModelFunction($stack, $name, $model_function_name, $position); // add a button whose HTML is returned by a method in the CRUD model
        // $this->crud->addButtonFromView($stack, $name, $view, $position); // add a button whose HTML is in a view placed at resources\views\vendor\backpack\crud\buttons
        // $this->crud->removeButton($name);
        // $this->crud->removeButtonFromStack($name, $stack);
        // $this->crud->removeAllButtons();
        // $this->crud->removeAllButtonsFromStack('line');

        // ------ CRUD ACCESS
        // $this->crud->allowAccess(['list', 'create', 'update', 'reorder', 'delete']);
        // $this->crud->denyAccess(['list', 'create', 'update', 'reorder', 'delete']);

        // ------ CRUD REORDER
        // $this->crud->enableReorder('label_name', MAX_TREE_LEVEL);
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('reorder');

        // ------ CRUD DETAILS ROW
        // $this->crud->enableDetailsRow();
        // NOTE: you also need to do allow access to the right users: $this->crud->allowAccess('details_row');
        // NOTE: you also need to do overwrite the showDetailsRow($id) method in your EntityCrudController to show whatever you'd like in the details row OR overwrite the views/backpack/crud/details_row.blade.php

        // ------ REVISIONS
        // You also need to use \Venturecraft\Revisionable\RevisionableTrait;
        // Please check out: https://laravel-backpack.readme.io/docs/crud#revisions
        // $this->crud->allowAccess('revisions');

        // ------ AJAX TABLE VIEW
        // Please note the drawbacks of this though:
        // - 1-n and n-n columns are not searchable
        // - date and datetime columns won't be sortable anymore
        // $this->crud->enableAjaxTable();

        // ------ DATATABLE EXPORT BUTTONS
        // Show export to PDF, CSV, XLS and Print buttons on the table view.
        // Does not work well with AJAX datatables.
        // $this->crud->enableExportButtons();

        // ------ ADVANCED QUERIES
        // $this->crud->addClause('active');
        // $this->crud->addClause('type', 'car');
        // $this->crud->addClause('where', 'name', '==', 'car');
        // $this->crud->addClause('whereName', 'car');
        // $this->crud->addClause('whereHas', 'posts', function($query) {
        //     $query->activePosts();
        // });
        // $this->crud->addClause('withoutGlobalScopes');
        // $this->crud->addClause('withoutGlobalScope', VisibleScope::class);
        // $this->crud->with(); // eager load relationships
        // $this->crud->orderBy();
        // $this->crud->groupBy();
        // $this->crud->limit();
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
        //dd($data);
        // set columns from db
        $this->crud->addField([
            'name' => 'name',
            'label' => 'Reward Name',
            'type' => 'text',
        ]);
        $this->crud->addColumns(['compliance','compliance_reached','from_date','to_date']);
        //$this->crud->setFromDb();
        $this->crud->removeColumn(['image_id','local_time','created_at','updated_at','deleted_at']); 

        // $this->crud->addColumn([   // Image
        //     'name' => 'image',
        //     'label' => 'Image',
        //     'type' => 'image',
        //     'prefix' => env('AWS_URL').'/large/',
        //     'height' => '300px',
        // ])->afterColumn('to_date');
        // $this->crud->addColumn([ 'name' => 'status','label' => 'Reward Status', 'type' => 'radio','options' => ['0'=>'Not Claimed', '1'=>'Claimed','4']])->afterColumn('to_date');
         //dd($this->crud->columns);
        // get the info for that entry
        $this->crud->addColumn([ 'name' => 'status','label' => 'Reward Status', 'type' => 'model_function','function_name' => 'getStatus'])->afterColumn('to_date');
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

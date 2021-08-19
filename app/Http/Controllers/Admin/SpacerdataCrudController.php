<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\SpacerdataRequest as StoreRequest;
use App\Http\Requests\SpacerdataRequest as UpdateRequest;

class SpacerdataCrudController extends CrudController
{
    public function setup()
    {

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Spacersession');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/spacerdata');
        $this->crud->setEntityNameStrings('Manage Spacer Data', 'Manage Spacer Data');
        $this->crud->denyAccess(['create','update','delete']);
        $this->crud->allowAccess(['show']);
        $this->crud->orderBy('created_at', 'DESC');

        if(! \Auth::user()->hasPermissionTo('Manage Spacer Data')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }
        
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */

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
            [
                'name' => 'parent_name',
                'label' => 'Parent Full Name',
                'type' => 'model_function',
                'function_name' => 'getParentName',
            ],
            // [     
            //     'label' => 'Email',
            //     'type' => 'select',
            //     'name' => 'child_id',
            //     'key' => 'parent_email',
            //     'entity' => 'parent',
            //     'attribute' => 'email',
            //     'model' => "App\Models\Child",
            // ],
            //  [
            //     'name' => 'child_id',
            //     'label' => 'child id',
            //     'type' => 'text',
                
            // ],
            // [     
            //     'label' => 'Child Name',
            //     'type' => 'select',
            //     'name' => 'child_id',
            //     'entity' => 'child',
            //     'key' => 'child_email',
            //     'attribute' => 'name',
            //     'model' => "App\Models\Child",
            //     'orderable'=>true
            // ],
             
            [
                'name' => 'age',
                'label' => 'Age',
                'type' => 'model_function',
                'function_name' => 'getAge',
            ], 
            [
                'name' => 'gender',
                'label' => 'Gender(F/M)',
                'type' => 'model_function',
                'function_name' => 'getGender',
            ], 
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


        // $fda = [     
        //         'label' => 'Child Name',
        //         'type' => 'select',
        //         'name' => 'child_id',
        //         'entity' => 'child',
        //         'attribute' => 'name',
        //         'model' => "App\User",
        //         'orderable'=>true
        //     ];
        // $this->crud->setColumnDetails('child_id', $fda);
        
        $this->crud->addColumn([
                'label' => "Child Name",
                'type' => "select",
                'name' => 'child_id',
                'entity' => 'child',
                'attribute' => "name", // combined name & date column
                'orderable' => false,
                'model' => "App\Models\Child",
                    'searchLogic' => function ($query, $column, $searchTerm) {
                        $query->orWhereHas('child', function ($q) use ($column, $searchTerm) {
                            $q->where('name', 'like', '%'.$searchTerm.'%');
                        });
                    }
            ])->beforeColumn('age');

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


        $this->crud->setListView('backpack::crud.list_spacerdata', $this->data);
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
        $this->crud->enableExportButtons();
        //$this->crud->addClause('with', 'child');
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
        //$this->crud->enableResponsiveTable();
        $this->crud->disableResponsiveTable();
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
        // $this->crud->addField([
        //     'name' => 'name',
        //     'label' => 'Reward Name',
        //     'type' => 'text',
        // ]);
        $this->crud->addColumns(['date']);
        //$this->crud->setFromDb();
        $this->crud->removeColumn(['lasttime','local_time','created_at','updated_at','deleted_at','actions','session_no','session_tech']); 

         $this->crud->addColumn([
            'name' => 'firsttime',
            'label' => 'Intake Time',
            'type' => 'text',
        ])->afterColumn('date');;

         $this->crud->addColumn([ 'name' => 'gender','label' => 'Gender(F/M)', 'type' => 'model_function','function_name' => 'getGender'])->afterColumn('firsttime');

         $this->crud->addColumn([ 'name' => 'type','label' => 'Type', 'type' => 'select_from_array','options' => ['0'=>'Session', '1'=>'Manual Attack']])->afterColumn('gender');
         
         $this->crud->addColumn([ 'name' => 'technique','label' => 'Technique(%)', 'type' => 'model_function','function_name' => 'getTechnique'])->afterColumn('type');

         $this->crud->addColumn([ 'name' => 'is_attack','label' => 'Attack(Yes/No)', 'type' => 'select_from_array','options' => ['0'=>'No', '1'=>'Yes']])->afterColumn('technique');
            
        $this->crud->addColumn([
            'name' => 'totalpasscount',
            'label' => 'Total Pass Count',
            'type' => 'text',
        ])->afterColumn('is_attack');     
        
         $this->crud->addColumn([
            'name' => 'totalfailcount',
            'label' => 'Total Fail Count',
            'type' => 'text',
        ])->afterColumn('totalpasscount');    

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

         $this->crud->addColumn([
            'name' => 'notes',
            'label' => 'Notes',
            'type' => 'text',
        ])->afterColumn('totalfailcount');
         
        $this->crud->addColumn([ 'name' => 'child_country','label' => 'Country', 'type' => 'model_function','function_name' => 'getChildCountry'])->afterColumn('notes');

       


        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // remove preview button from stack:line
        $this->crud->removeButton('show');
        $this->crud->removeButton('Actions');
        $this->crud->removeButton('delete');
       // dd($this->data);
        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getShowView(), $this->data);
    
    }
}

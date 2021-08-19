<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\UserRequest as StoreRequest;
use App\Http\Requests\UserRequest as UpdateRequest;

use App\Exports\UsersExport;
use Backpack\CRUD\app\Http\Requests\CrudRequest;
use Illuminate\Http\Request;
use App\User;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserCrudController extends CrudController
{ 
    public function setup()
    {

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\User');
        $this->crud->setRoute(config('backpack.base.route_prefix','admin') . '/parent');
        $this->crud->setEntityNameStrings('user', 'Parents');
        $this->crud->allowAccess(['show']);
        $this->crud->addButtonFromModelFunction('line', 'viewchild', 'viewchildbutton', 'end');

        if(! \Auth::user()->hasPermissionTo('Manage Parents')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => 'Full Name',
                'type' => 'text'
            ],
            
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email'
            ],
            [
                'name' => 'activation_status',
                'label' => 'Activation Status(Y/N)',
                'type' => 'select_from_array',
                'options' => ['0'=>'No', '1'=>'Yes']
            ]
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
         $this->crud->addField([
            'name' => 'name',
            'label' => 'Full Name',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'email',
            'label' => trans('backpack::permissionmanager.email'),
            'type' => 'email',
        ]);


        $this->crud->addField([
            'name' => 'password',
            'label' => trans('backpack::permissionmanager.password'),
            'type' => 'password',
        ]);

        $this->crud->addField([
            'name' => 'password_confirmation',
            'label' => trans('backpack::permissionmanager.password_confirmation'),
            'type' => 'password',
        ]);

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
        $this->validate(request(), [
            'name' => 'required|max:255',
            
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    return $query->whereIn('email', [request('email')])->whereNull('deleted_at');
                })
            ],
            'password' => [
                'required',
                'confirmed',
                function ($attr, $value, $fail) {
                    if ($value != null) {
                        if (!(preg_match("/.{8}/", $value))) {
                            return $fail('The ' . $attr . ' must contain minimum 8 characters.');
                        }
                    }
                }
            ],
            
            
            
        ]);
       $this->crud->hasAccessOrFail('create');

        // fallback to global request instance
        if (is_null($request)) {
            $request = \Request::instance();
        }
        // replace empty values with NULL, so that it will work with MySQL strict mode on
        foreach ($request->input() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $request->request->set($key, null);
            }
        }

         $this->handlePasswordInputCreate($request);

        $user = $request->except(['save_action', '_token', '_method']);
        $user['activation_status']    = '1';
        $item = $this->crud->create($user);
         
        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();
        return \Redirect::to('admin/parent');
        // your additional operations before save here
        //$redirect_location = parent::storeCrud($user);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
       //  return $redirect_location;
    }
    public  function destroy($id) {
        // $email          = User::where('id', $id)->first()->email;
        // $update_session = User::where('id', $id)->update(['email' => date('YmdHis').$email]); 

        $this->crud->hasAccessOrFail('delete');

        return $this->crud->delete($id);
    }
    public function update(UpdateRequest $request)
    {
        // your additional operations before save here
         $this->validate(request(), [
            'name' => 'required|max:255',
            
            'email' => [
                'required',
                'email',
                'max:255',
                 
            ],
            'password' => [
                'confirmed',
                function ($attr, $value, $fail) {
                    if ($value != null) {
                        if (!(preg_match("/.{8}/", $value))) {
                            return $fail('The ' . $attr . ' must contain minimum 8 characters.');
                        }
                    }
                }
            ],
            
            
            
        ]);

        $this->handlePasswordInputCreate($request);
        $redirect_location = parent::updateCrud($request);
        // your additional operations after save here
        // use $this->data['entry'] or $this->crud->entry
        return $redirect_location;
    }
    public function export() 
    {
        return Excel::download(new UsersExport, 'users.xlsx');
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
        //$this->crud->addColumns(['date']);
        //$this->crud->setFromDb();
        $this->crud->removeColumn(['register_type','remember_token','created_at','updated_at','deleted_at','actions']); 

        $this->crud->addColumn([ 'name' => 'facebook_id','label' => 'Facebook Id', 'type' => 'text'])->afterColumn('activation_status');

       


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

    protected function handlePasswordInputCreate(Request $request)
    {
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', bcrypt($request->input('password')));
        } else {
            $request->request->remove('password');
        }
    }
    protected function handlePasswordInput(CrudRequest $request)
    {   
        // Remove fields not present on the user.
        $request->request->remove('password_confirmation');

        // Encrypt password if specified.
        if ($request->input('password')) {
            $request->request->set('password', bcrypt($request->input('password')));
        } else {
            $request->request->remove('password');
        }
    }
}

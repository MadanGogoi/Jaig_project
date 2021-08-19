<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\AdminRequest as StoreRequest;
use App\Http\Requests\AdminRequest as UpdateRequest;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Requests\CrudRequest;
use App\Models\Admin;
use App\Models\Child;
use App\Models\Feedback;
use App\User;
use Validator, DB, Hash, Mail;
use Illuminate\Validation\Rule;
/**
 * Class AdminCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class AdminCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Admin');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/administrator');
        $this->crud->setEntityNameStrings('admin', 'admins');
        $this->crud->backbuttontxt = 'Manage Admins';
        //$this->crud->allowAccess(['show']);

        if(! \Auth::user()->hasPermissionTo('Manage Admins')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */

         /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
         */
        
        // Columns.
        $this->crud->setColumns([
            [
                'name' => 'name',
                'label' => 'Full Name',
                'type' => 'text',
            ],
            [
                'name' => 'email',
                'label' => 'Email',
                'type' => 'email',
            ],
            [ // n-n relationship (with pivot table)
               'label'     => trans('backpack::permissionmanager.roles'), // Table column heading
               'type'      => 'select',
               'name'      => 'roles', // the method that defines the relationship in your Model
               'entity'    => 'roles', // the method that defines the relationship in your Model
               'attribute' => 'name', // foreign key attribute that is shown to user
               'model'     => config('permission.models.role'), // foreign key model
            ],
        ]);

        // Fields
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


        $this->crud->addField([
            // two interconnected entities
            'label'             => trans('backpack::permissionmanager.user_role_permission'),
            'field_unique_name' => 'user_role_permission',
            'type'              => 'checklist_dependency',
            'attributes' => [
                'required' => 'required'
            ],
            'name'              => 'roles_and_permissions', // the methods that defines the relationship in your Model
            'subfields'         => [
                    'primary' => [
                        'label'            => trans('backpack::permissionmanager.roles'),
                        'name'             => 'roles', // the method that defines the relationship in your Model
                        'entity'           => 'roles', // the method that defines the relationship in your Model
                        'entity_secondary' => 'permissions', // the method that defines the relationship in your Model
                        'attribute'        => 'name', // foreign key attribute that is shown to user
                        'model'            => config('permission.models.role'), // foreign key model
                        'pivot'            => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns'   => 3, //can be 1,2,3,4,6
                    ],
                    'secondary' => [
                        'label'          => ucfirst(trans('backpack::permissionmanager.permission_singular')),
                        'name'           => 'permissions', // the method that defines the relationship in your Model
                        'entity'         => 'permissions', // the method that defines the relationship in your Model
                        'entity_primary' => 'roles', // the method that defines the relationship in your Model
                        'attribute'      => 'name', // foreign key attribute that is shown to user
                        'model'          => config('permission.models.permission'), // foreign key model
                        'pivot'          => true, // on create&update, do you need to add/delete pivot table entries?]
                        'number_columns' => 3, //can be 1,2,3,4,6
                    ],
                ],
            ]);

        ////$this->crud->setFromDb();

        // ------ CRUD COLUMNS
        // $this->crud->addColumn(); // add a single column, at the end of the stack
        // $this->crud->addColumns(); // add multiple columns, at the end of the stack
        // $this->crud->removeColumn('column_name'); // remove a column from the stack
        // $this->crud->removeColumns(['column_name_1', 'column_name_2']); // remove an array of columns from the stack
        // $this->crud->setColumnDetails('column_name', ['attribute' => 'value']); // adjusts the properties of the passed in column (by name)
        // $this->crud->setColumnsDetails(['column_1', 'column_2'], ['attribute' => 'value']);

        // ------ CRUD FIELDS
        // $this->crud->addField($options, 'update/create/both');
        // $this->crud->addFields($array_of_arrays, 'update/create/both');
        // $this->crud->removeField('name', 'update/create/both');
        // $this->crud->removeFields($array_of_names, 'update/create/both');

        // add asterisk for fields that are required in AdminRequest
        /// $this->crud->setRequiredFields(StoreRequest::class, 'create');
        ///$this->crud->setRequiredFields(UpdateRequest::class, 'edit');

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
        // $this->crud->addClause('where', 'name', '=', 'car');
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

    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|max:255',
            
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins')->where(function ($query) {
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
        $this->handlePasswordInputCreate($request);
        // replace empty values with NULL, so that it will work with MySQL strict mode on
        foreach ($request->input() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $request->request->set($key, null);
            }
        }

        $admin = $request->except(['save_action', '_token', '_method']);
         
        // insert item in the db
        $item = $this->crud->create($admin);
        $item->assignRole('admin');
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.insert_success'))->flash();

        // save the redirect choice for next time
        $this->setSaveAction();

        return $this->performSaveAction($item->getKey());
    }

    public function update(CrudRequest $request)
    {
         $this->crud->hasAccessOrFail('update');

        $this->validate(request(), [ 
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('admins')->where(function ($query) {
                    return $query->whereNotIn('id',[request('id')])->whereNull('deleted_at');
                })
            ],
            'roles_show' => [
                'required'
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
        //dd(request());
        $this->handlePasswordInput($request);

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

        // update the row in the db
        $item = $this->crud->update(
            $request->get($this->crud->model->getKeyName()),
            $request->except('save_action', '_token', '_method')
        );
        $this->data['entry'] = $this->crud->entry = $item;

        // show a success message
        \Alert::success(trans('backpack::crud.update_success'))->flash();

        // save the redirect choice for next time
        $this->setSaveAction();

        return $this->performSaveAction($item->getKey());
    }
    public function dashboard()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title
        
        $cmscount       = User::select('id')->where('register_type','1')->where('activation_status','1')->get()->count();
        $emailcount     = User::select('id')->where('register_type','2')->where('activation_status','1')->get()->count();
        $facebookcount  = User::select('id')->where('register_type','3')->where('activation_status','1')->get()->count();

        $problemcount   = Feedback::select('id')->where('type','1')->get()->count();
        $suggestioncount= Feedback::select('id')->where('type','2')->get()->count();
        $enquirycount   = Feedback::select('id')->where('type','3')->get()->count();
        $otherscount    = Feedback::select('id')->where('type','4')->get()->count();


        $month1  = User::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m'))
                                     ->where('activation_status','1')->get()->count();
        $month2  = User::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-1 month')))
                                     ->where('activation_status','1')->get()->count();
        $month3  = User::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-2 month')))
                                     ->where('activation_status','1')->get()->count();
        $month4  = User::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-3 month')))
                                     ->where('activation_status','1')->get()->count();
        $month5  = User::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-4 month')))
                                     ->where('activation_status','1')->get()->count();
        $month6  = User::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-5 month')))
                                     ->where('activation_status','1')->get()->count();



        $cmonth1  = Child::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m'))->get()->count();
        $cmonth2  = Child::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-1 month')))->get()->count();
        $cmonth3  = Child::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-2 month')))->get()->count();
        $cmonth4  = Child::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-3 month')))->get()->count();
        $cmonth5  = Child::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-4 month')))->get()->count();
        $cmonth6  = Child::select('id')->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"), "=", date('m', strtotime('-5 month')))->get()->count();

        

        $this->data['activated_user'] =  array('month1'     => $month1,
                                               'month2'     => $month2,
                                               'month3'     => $month3,
                                               'month4'     => $month4,
                                               'month5'     => $month5,
                                               'month6'     => $month6); 
        $this->data['registered_child'] =  array('cmonth1'     => $cmonth1,
                                               'cmonth2'     => $cmonth2,
                                               'cmonth3'     => $cmonth3,
                                               'cmonth4'     => $cmonth4,
                                               'cmonth5'     => $cmonth5,
                                               'cmonth6'     => $cmonth6); 

        $this->data['registered_user'] = array('cmscount'       => $cmscount,
                                               'emailcount'     => $emailcount,
                                               'facebookcount'  => $facebookcount); 

        $this->data['feedback']        = array('problemcount'    => $problemcount,
                                               'suggestioncount' => $suggestioncount,
                                               'enquirycount'    => $enquirycount,
                                               'otherscount'     => $otherscount); 

        return view('backpack::dashboard', $this->data);
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
}

<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ReportRequest as StoreRequest;
use App\Http\Requests\ReportRequest as UpdateRequest;
use App\User;
use App\Models\Spacerdata;
use App\Models\Child;
use App\Models\Spacersession;
use Validator, DB, Hash, Mail;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Requests\CrudRequest;
/**
 * Class ReportCrudController
 * @package App\Http\Controllers\Admin
 * @property-read CrudPanel $crud
 */
class ReportCrudController extends CrudController
{
    public function setup()
    {
        /*
        |--------------------------------------------------------------------------
        | CrudPanel Basic Information
        |--------------------------------------------------------------------------
        */
        $child_id = \Route::current()->parameter('child_id');
        $this->crud->setModel('App\Models\Child');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/child/'.$child_id.'/viewchild');
        $this->crud->setEntityNameStrings('viewreport', 'viewreport');
        $this->crud->addClause('where', 'child_id', $child_id);

        if(! \Auth::user()->hasPermissionTo('Manage Reports')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }

        /*
        |--------------------------------------------------------------------------
        | CrudPanel Configuration
        |--------------------------------------------------------------------------
        */

        // TODO: remove setFromDb() and manually define Fields and Columns
        $this->crud->setFromDb();

        // add asterisk for fields that are required in ReportRequest
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

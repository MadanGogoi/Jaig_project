<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;

// VALIDATION: change the requests to match your own file names if you need form validation
use App\Http\Requests\ChildreportRequest as StoreRequest;
use App\Http\Requests\ChildreportRequest as UpdateRequest;
use App\User;
use App\Models\Spacerdata;
use App\Models\Child;
use App\Models\Calendar;
use App\Models\Spacersession;
use App\Models\Schedulesession;
use Validator, DB, Hash, Mail;
use Illuminate\Http\Request;
use Backpack\CRUD\app\Http\Requests\CrudRequest;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use App\Exports\ReportattackExport;


class ChildreportCrudController extends CrudController
{
    public function setup()
    {

        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->setModel('App\Models\Child');
        $this->crud->setRoute(config('backpack.base.route_prefix') . '/childreport');
        $this->crud->setEntityNameStrings('child report', 'child report');
        $this->crud->denyAccess(['create','update','delete']);

        if(! \Auth::user()->hasPermissionTo('Manage Child') && ! \Auth::user()->hasPermissionTo('Manage Reports')) {
            $this->crud->denyAccess(['show','list','create','update','delete']);
        }
        
        /*
        |--------------------------------------------------------------------------
        | BASIC CRUD INFORMATION
        |--------------------------------------------------------------------------
        */
        $this->crud->addButtonFromModelFunction('line', 'attack', 'attackbutton');
        $this->crud->addButtonFromModelFunction('line', 'compliance', 'compliancebutton', 'end');
        $this->crud->setColumns([
        [     
                'label' => 'Parent Full Name',
                'type' => 'select',
                'name' => 'user_id',
                'entity' => 'parent',
                'attribute' => 'name',
                'model' => "App\User",
            ], 
              
            [     
                'label' => 'Email',
                'type' => 'select',
                'name' => 'user_id',
                'key' => 'parent_email',
                'entity' => 'parent',
                'attribute' => 'email',
                'model' => "App\User",
            ],
            [
                'name' => 'name',
                'label' => 'Child Name',
                'type' => 'text'
            ],
             
            [
                'name' => 'gender',
                'label' => 'Gender(F/M)',
                'type' => 'text'
            ],
             
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
     public function report()
    {
        $this->data['title'] = 'Manage Reports'; // set the page title


        #Attack 
        $firstyear      = Spacersession::select('id')->where(DB::raw("(DATE_FORMAT(date,'%Y'))"), "=", date('Y'))->where('is_attack','1')->get()->count();
         
        $secondyear     = Spacersession::select('id')->where(DB::raw("(DATE_FORMAT(date,'%Y'))"), "=", (date('Y')-1))->where('is_attack','1')->get()->count();
        $thirdyear      = Spacersession::select('id')->where(DB::raw("(DATE_FORMAT(date,'%Y'))"), "=", (date('Y')-2))->where('is_attack','1')->get()->count();


        
        $m_compliance_firstmonth = 0;
        $m_compliance_secondmonth = 0;
        $m_compliance_thirdmonth = 0;
        $m_compliance_fourmonth = 0;
        $m_compliance_fivemonth = 0;
        $m_compliance_sixmonth = 0;

        #Compliance - Firstmonth
        // $d_com_firstmonth      = Schedulesession::select('sessions','local_time')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m'))"), "=", date('Y-m'))->where('type','1')->get();
        $d_com_firstmonth      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m'))->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "<=", date('Y-m-d'))->groupBy('date')->get();

         

        $d_compliance_total = 0;
       
        foreach ($d_com_firstmonth as $dkey => $dvalue) {
            
           $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                            ->where('is_attack','0')->get()->count(); 
            
            $schedule_count = $dvalue->total_sessions;
            
            if($get_compliance){
                if($schedule_count){
                    $d_compliance   = ($get_compliance / $schedule_count)*100;
                }else{
                     $d_compliance   = $get_compliance*100;
                }
                
                $d_compliance_total = $d_compliance_total + $d_compliance;
            }
             

        }
        if($d_compliance_total)
        $m_compliance_firstmonth = $d_compliance_total/date('d');

       
        #Compliance - 2nd month
        //$d_com_secondmonth     = Schedulesession::select('sessions','local_time')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m'))"), "=", date('Y-m', strtotime('-1 month')))->where('type','1')->get();
        
        $d_com_secondmonth      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-1 month')))->groupBy('date')->get();

        $d_compliance_total = 0;
        foreach ($d_com_secondmonth as $dkey => $dvalue) {
         
            $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                            ->where('is_attack','0')->get()->count(); 
            
            $schedule_count = $dvalue->total_sessions;
           
            if($get_compliance){
                if($schedule_count){
                    $d_compliance   = ($get_compliance / $schedule_count)*100;
                }else{
                     $d_compliance   = $get_compliance*100;
                }
                
                $d_compliance_total = $d_compliance_total + $d_compliance;
            }
             
           
        }
        if($d_compliance_total)
        $m_compliance_secondmonth = $d_compliance_total/cal_days_in_month(CAL_GREGORIAN,  date('m', strtotime('-1 month')),  date('Y', strtotime('-1 month')));;

       
         
        #Compliance - 3rd month
        // $d_com_thirdmonth      = Schedulesession::select('sessions','local_time')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m'))"), "=", date('Y-m', strtotime('-2 month')))->where('type','1')->get();
        $d_com_thirdmonth      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-2 month')))->groupBy('date')->get();

        $d_compliance_total = 0;
        foreach ($d_com_thirdmonth as $dkey => $dvalue) {
         
             $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                            ->where('is_attack','0')->get()->count(); 
            
            $schedule_count = $dvalue->total_sessions;
            if($get_compliance){
                if($schedule_count){
                    $d_compliance   = ($get_compliance / $schedule_count)*100;
                }else{
                     $d_compliance   = $get_compliance*100;
                }
                $d_compliance_total = $d_compliance_total + $d_compliance;
            }
             
           
        }
        if($d_compliance_total)
        $m_compliance_thirdmonth = $d_compliance_total/cal_days_in_month(CAL_GREGORIAN,  date('m', strtotime('-2 month')),  date('Y', strtotime('-2 month')));;



        #Compliance - 4 month
        // $d_com_month      = Schedulesession::select('sessions','local_time')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m'))"), "=", date('Y-m', strtotime('-3 month')))->where('type','1')->get();
        $d_com_month      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-3 month')))->groupBy('date')->get();

        $d_compliance_total = 0;
       
        foreach ($d_com_month as $dkey => $dvalue) {
         
            $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                            ->where('is_attack','0')->get()->count(); 
            
            $schedule_count = $dvalue->total_sessions;
            if($get_compliance){
                if($schedule_count){
                    $d_compliance   = ($get_compliance / $schedule_count)*100;
                }else{
                     $d_compliance   = $get_compliance*100;
                }
                $d_compliance_total = $d_compliance_total + $d_compliance;
            }
             
           
        }
        if($d_compliance_total)
        $m_compliance_fourmonth = $d_compliance_total/cal_days_in_month(CAL_GREGORIAN,  date('m', strtotime('-3 month')),  date('Y', strtotime('-3 month')));;



        #Compliance - 5 month
        // $d_com_month      = Schedulesession::select('sessions','local_time')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m'))"), "=", date('Y-m', strtotime('-4 month')))->where('type','1')->get();
        $d_com_month      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-4 month')))->groupBy('date')->get();

        $d_compliance_total = 0;
        foreach ($d_com_month as $dkey => $dvalue) {
         
             $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                            ->where('is_attack','0')->get()->count(); 
            
            $schedule_count = $dvalue->total_sessions;
            if($get_compliance){
                if($schedule_count){
                    $d_compliance   = ($get_compliance / $schedule_count)*100;
                }else{
                     $d_compliance   = $get_compliance*100;
                }
                $d_compliance_total = $d_compliance_total + $d_compliance;
            }
             
           
        }
        if($d_compliance_total)
        $m_compliance_fivemonth = $d_compliance_total/cal_days_in_month(CAL_GREGORIAN,  date('m', strtotime('-4 month')),  date('Y', strtotime('-4 month')));;


        #Compliance - 6 month
        // $d_com_month      = Schedulesession::select('sessions','local_time')->where(DB::raw("(DATE_FORMAT(local_time,'%Y-%m'))"), "=", date('Y-m', strtotime('-5 month')))->where('type','1')->get();
        $d_com_month      = Calendar::select(DB::raw('sum(total_sessions) as total_sessions'),'date')->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-5 month')))->groupBy('date')->get();

        $d_compliance_total = 0;
        foreach ($d_com_month as $dkey => $dvalue) {
         
             $get_compliance = Spacersession::select('id')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($dvalue->date)))
                            ->where('is_attack','0')->get()->count(); 
            
            $schedule_count = $dvalue->total_sessions;
            if($get_compliance){
                if($schedule_count){
                    $d_compliance   = ($get_compliance / $schedule_count)*100;
                }else{
                     $d_compliance   = $get_compliance*100;
                }
                $d_compliance_total = $d_compliance_total + $d_compliance;
            }
             
           
        }
        if($d_compliance_total)
        $m_compliance_sixmonth = $d_compliance_total/cal_days_in_month(CAL_GREGORIAN,  date('m', strtotime('-5 month')),  date('Y', strtotime('-5 month')));;

        #Technique
        $tech_firstmonth    = 0;
        $tech_secondmonth   = 0;
        $tech_thirdmonth    = 0;
        $tech_fourmonth     = 0;
        $tech_fivemonth     = 0;
        $tech_sixmonth      = 0;

        $month_tech = 0;
        $session_taken = 0;
        $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m'))
                            ->groupBy('date')->get(); 
                            
        foreach ($get_day_sesssion as $key => $value) {
            
            $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get();
           
            $total_session = count($get_tech);
            $daily_tech = 0;
            foreach ($get_tech as $dkey => $dvalue) {
              if($dvalue->totalpasscount || $dvalue->totalfailcount)
              $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100); 

            }
            if($daily_tech)
            $month_tech = $month_tech +  ($daily_tech / $total_session);
        }
        if($month_tech){
            $tech_firstmonth = $month_tech/count($get_day_sesssion);
        }

        #Technique - 2 month
         $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-1 month')))
                            ->groupBy('date')->get(); 
        
        $month_tech = 0;
        foreach ($get_day_sesssion as $dkey => $value) {
         
            $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get();

            $total_session = count($get_tech);
            $daily_tech = 0;
            foreach ($get_tech as $dkey => $dvalue) {
                if($dvalue->totalpasscount || $dvalue->totalfailcount)
                $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100);    
            }
            if($daily_tech)
            $month_tech = $month_tech +  ($daily_tech / $total_session);
             
           
        }
        if($month_tech)
            $tech_secondmonth = $month_tech/count($get_day_sesssion);
        
        
        #Technique - 3 month
        $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-2 month')))
                            ->groupBy('date')->get(); 

        $month_tech = 0;
        foreach ($get_day_sesssion as $dkey => $value) {
         
            $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get();
            
            $total_session = count($get_tech);
            $daily_tech = 0;
            foreach ($get_tech as $dkey => $dvalue) {
                if($dvalue->totalpasscount || $dvalue->totalfailcount)
                $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100);    
            }
            if($daily_tech)
            $month_tech = $month_tech +  ($daily_tech / $total_session);
             
           
        }
        if($month_tech)
            $tech_thirdmonth = $month_tech/count($get_day_sesssion);


         #Technique - 4 month
        $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-3 month')))
                            ->groupBy('date')->get(); 

        $month_tech = 0;
        foreach ($get_day_sesssion as $dkey => $value) {
            
            $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get(); 
            
            $total_session = count($get_tech);
            
            $daily_tech = 0;
            foreach ($get_tech as $dkey => $dvalue) {
                if($dvalue->totalpasscount || $dvalue->totalfailcount)
                $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100);    
            }
            if($daily_tech)
            $month_tech = $month_tech +  ($daily_tech / $total_session);
             
           
        }
        if($month_tech)
            $tech_fourmonth = $month_tech/count($get_day_sesssion);

        #Technique - 5 month
        $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-4 month')))
                            ->groupBy('date')->get(); 

        $month_tech = 0;
        foreach ($get_day_sesssion as $dkey => $value) {
         
            $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get(); 
            
            $total_session = count($get_tech);
            $daily_tech = 0;
            foreach ($get_tech as $dkey => $dvalue) {
                if($dvalue->totalpasscount || $dvalue->totalfailcount)
                $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100);    
            }
            if($daily_tech)
            $month_tech = $month_tech +  ($daily_tech / $total_session);
             
           
        }
        if($month_tech)
            $tech_fivemonth = $month_tech/count($get_day_sesssion);

        #Technique - 6 month
        $get_day_sesssion = Spacersession::select('date')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m', strtotime('-5 month')))
                            ->groupBy('date')->get(); 

        $month_tech = 0;
        foreach ($get_day_sesssion as $dkey => $value) {
         
            $get_tech = Spacersession::select('totalpasscount','totalfailcount')
                            ->where(DB::raw("(DATE_FORMAT(date,'%Y-%m-%d'))"), "=", date('Y-m-d',strtotime($value->date)))->get(); 
            
            $total_session = count($get_tech);
            $daily_tech = 0;
            foreach ($get_tech as $dkey => $dvalue) {
                if($dvalue->totalpasscount || $dvalue->totalfailcount)
                    $daily_tech = $daily_tech + (($dvalue->totalpasscount/($dvalue->totalpasscount+$dvalue->totalfailcount))*100);    
            }
            if($daily_tech)
            $month_tech = $month_tech +  ($daily_tech / $total_session);
             
           
        }
        if($month_tech)
            $tech_sixmonth = $month_tech/count($get_day_sesssion);


        $this->data['attacks'] = array('firstyear'       => $firstyear,
                                       'secondyear'     => $secondyear,
                                       'thirdyear'  => $thirdyear); 

        $this->data['compliance'] = array( 'm_compliance_firstmonth'  => number_format($m_compliance_firstmonth, 1, '.', ''),
                                           'm_compliance_secondmonth' => number_format($m_compliance_secondmonth, 1, '.', ''),
                                           'm_compliance_thirdmonth'  => number_format($m_compliance_thirdmonth, 1, '.', ''),
                                           'm_compliance_fourmonth'   => number_format($m_compliance_fourmonth, 1, '.', ''),
                                           'm_compliance_fivemonth'   => number_format($m_compliance_fivemonth, 1, '.', ''),
                                           'm_compliance_sixmonth'    => number_format($m_compliance_sixmonth, 1, '.', '')); 
        $this->data['technique'] = array( 'tech_firstmonth'  => number_format($tech_firstmonth, 1, '.', ''),
                                           'tech_secondmonth' => number_format($tech_secondmonth, 1, '.', ''),
                                           'tech_thirdmonth'  => number_format($tech_thirdmonth, 1, '.', ''),
                                           'tech_fourmonth'   => number_format($tech_fourmonth, 1, '.', ''),
                                           'tech_fivemonth'   => number_format($tech_fivemonth, 1, '.', ''),
                                           'tech_sixmonth'    => number_format($tech_sixmonth, 1, '.', '')); 


        

        //dd($this->data['attacks']);

        return view('backpack::reports', $this->data);
    }
    public function attack($id){
        $type       = request('type');
        $startdate  = request('startdate');
        

        if($type==1){
            if($startdate==null){
               $startdate = date('Y-m-d',strtotime("-6 days"));
               $enddate   = date('Y-m-d');
            }else{
                $startdate = date('Y-m-d',strtotime($startdate));
                $enddate   = date('Y-m-d',strtotime("+7 day",strtotime($startdate))) ;
            }
            $label_array = array();
            for ($i=0; $i <= 6; $i++) { 
                if($i!=0)
                    $label_array[]   = date('d M Y',strtotime($i." day",strtotime($startdate)));
                else
                    $label_array[]   = date('d M Y',strtotime($startdate));
            }
            $value_array = array();
            for ($i=0; $i <= 6; $i++) { 
                $attack_session = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('is_attack','1');
                if($i!=0)
                      $attack_session =  $attack_session->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $attack_session =  $attack_session->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $value_array[] =  $attack_session =  $attack_session->get()->count();
            }
             
        }else if($type==2){
            if($startdate==null){
               $startdate = date('Y-m-d',strtotime("-5 month"));
               $enddate   = date('Y-m-d');
            }else{
                $startdate = date('Y-m-d',strtotime($startdate));
                $enddate   = date('Y-m-d',strtotime("+6 month",strtotime($startdate))) ;
            }

            $label_array = array();
            for ($i=0; $i < 6; $i++) { 
                if($i!=0)
                    $label_array[]   = date('M Y',strtotime($i." month",strtotime($startdate)));
                else
                    $label_array[]   = date('M Y',strtotime($startdate));
            }
            $value_array = array();
            for ($i=0; $i < 6; $i++) { 
                 $attack_session = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('is_attack','1');
                if($i!=0)
                      $attack_session =  $attack_session->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $attack_session =  $attack_session->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $value_array[] =  $attack_session =  $attack_session->get()->count();
            }
        }else if($type==3){
            if($startdate==null){
               $startdate = date('Y-m-d',strtotime("-11 month"));
               $enddate   = date('Y-m-d');
            }else{
                $startdate = date('Y-m-d',strtotime($startdate));
                $enddate   = date('Y-m-d',strtotime("+12 month",strtotime($startdate))) ;
            }

            $label_array = array();
            for ($i=0; $i < 12; $i++) { 
                if($i!=0)
                    $label_array[]   = date('M Y',strtotime($i." month",strtotime($startdate)));
                else
                    $label_array[]   = date('M Y',strtotime($startdate));
            }
            $value_array = array();
            for ($i=0; $i < 12; $i++) { 

                $attack_session = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('is_attack','1');
                if($i!=0)
                      $attack_session =  $attack_session->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $attack_session =  $attack_session->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $value_array[] =  $attack_session =  $attack_session->get()->count();
            }
        }
        
        $child_info = Child::where('id', '=', $id)->first(); 
         
        $this->data['title'] = 'Manage Reports : Individual No. Of Attacks Page'; // set the page title
         
        // $firstyear      = Spacerdata::select('id')->where(DB::raw("(DATE_FORMAT(datetime,'%Y'))"), "=", date('Y'))->where('is_attack','1')->get()->count();
         
        // $secondyear     = Spacerdata::select('id')->where(DB::raw("(DATE_FORMAT(datetime,'%Y'))"), "=", (date('Y')-1))->where('is_attack','1')->get()->count();
        // $thirdyear      = Spacerdata::select('id')->where(DB::raw("(DATE_FORMAT(datetime,'%Y'))"), "=", (date('Y')-2))->where('is_attack','1')->get()->count();

       


        // $this->data['attacks'] = array('firstyear'       => $firstyear,
        //                                'secondyear'     => $secondyear,
        //                                'thirdyear'  => $thirdyear); 
        $this->data['attacks'] = array('label'     => $label_array,
                                       'value'     => $value_array,
                                       'startdate' => $startdate,
                                       'type'      => $type); 
        $this->data['child_info'] = $child_info;

        //dd($this->data['attacks']);

        return view('backpack::attack', $this->data);
    }
    public function convertMedianTime($starttime, $endtime){
        
        $seconds        = strtotime($endtime) - strtotime($starttime);
        $minutes        =  floor($seconds / 60);
        $median_time    = $minutes/2;
        $min_sec        = explode('.', $median_time); 
        $retrun_time    = date('H:i:s',strtotime('+'.$min_sec[0].' minutes',strtotime($starttime)));
        if(isset($min_sec[1])){   
          $remining_sec = ('0.'.$min_sec[1])*60;
          $retrun_time  = date('H:i:s',strtotime('+'.$remining_sec.' seconds',strtotime($retrun_time)));  
        }
        return $retrun_time;
    }
    public function technique_compliance($id){


        $type       = request('type');
        $startdate  = request('startdate');

        if($type==1){
            if($startdate==null){
               $startdate = date('Y-m-d',strtotime("-6 days"));
               $enddate   = date('Y-m-d');
            }else{
                $startdate = date('Y-m-d',strtotime($startdate));
                $enddate   = date('Y-m-d',strtotime("+7 day",strtotime($startdate))) ;
            }
            $label_array = array();
            for ($i=0; $i <= 6; $i++) { 
                if($i!=0)
                    $label_array[]   = date('d M Y',strtotime($i." day",strtotime($startdate)));
                else
                    $label_array[]   = date('d M Y',strtotime($startdate));
            }
            #Compliance
            $value_c_array = array();

            $value_cs1_array = array();
            for ($i=0; $i <= 6; $i++) { 
                // $schedule_sessions_1 = '';
                // $schedule_sessions_2 = '';
                // $schedule_sessions_3 = '';
                // $median_time_1 = '';
                // $median_time_2 = '';
                // $median_time_3 = '';
                // #Get Scheduled Session time
                // $schedule_sessiondata = Calendar::select('id','total_sessions','date','scheduled_sessions')->where('child_id', "=", $id);
                // if($i!=0)
                //      $schedule_sessiondata = $schedule_sessiondata->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate))));
                // else
                //     $schedule_sessiondata =  $schedule_sessiondata->where('date', "=", date('Y-m-d',strtotime($startdate)));

                // $schedule_sessiondata =  $schedule_sessiondata->get();

                // if(!empty($schedule_sessiondata)){
                //     $schedule_sessiondata = $schedule_sessiondata[0];
                //     echo $schedule_sessiondata->scheduled_sessions;

                //     if($schedule_sessiondata->scheduled_sessions!=''){
                //         $schedule_sessions = explode(',', $schedule_sessiondata->scheduled_sessions);
                //         $schedule_sessions_1 = $schedule_sessions[0];
                         
                //         if(isset($schedule_sessions[1])){ 
                //             $schedule_sessions_2 = $schedule_sessions[1];
                //             $median_time_1 = $this->convertMedianTime($schedule_sessions_1, $schedule_sessions_2);  
                //         }
                //         if(isset($schedule_sessions[2])){
                //             $schedule_sessions_3 = $schedule_sessions[2]; 
                //             $median_time_2 = $this->convertMedianTime($schedule_sessions_2, $schedule_sessions_3);
                //         }                    
                //     }
                // }
                
                #Get Spacer Session Details
                $compliance_s1_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s1_total =  $compliance_s1_total->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $compliance_s1_total =  $compliance_s1_total->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $compliance_s1_total =  $compliance_s1_total->get()->count();
                 
                if($compliance_s1_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s1_total){
                            $value_ts1 = $compliance_s1_total*100;
                            if($value_ts1>100)
                               $value_ts1 = 100;
                            if($value_ts1>0){
                                $value_cs1_array[] = number_format($value_ts1, 2, '.', '');
                            }else{
                                $value_cs1_array[] = $value_ts1;
                            }
                        }
                        else 
                            $value_cs1_array[] = 0;
                    else
                        if($compliance_s1_total){
                             
                            $value_ts1 = $compliance_s1_total*100;
                            if($value_ts1>0){
                                if($value_ts1>100)
                                    $value_ts1 = 100;

                                $value_cs1_array[] = number_format($value_ts1, 2, '.', '');
                            }else{
                                $value_cs1_array[] = $value_ts1;
                            }
                        }
                        else 
                                $value_cs1_array[] = 0;
                }else{
                    $value_cs1_array[] = 0;
                }
            }
            $value_c_array[] = $value_cs1_array;

            $value_cs2_array = array();
            for ($i=0; $i <= 6; $i++) { 
                $compliance_s2_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s2_total =  $compliance_s2_total->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $compliance_s2_total =  $compliance_s2_total->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $compliance_s2_total =  $compliance_s2_total->get()->count();
                 
                if($compliance_s2_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s2_total){
                            $value_ts2 = $compliance_s2_total*100;
                            if($value_ts2>100)
                               $value_ts2 = 100;
                            if($value_ts2>0){
                                $value_cs2_array[] = number_format($value_ts2, 2, '.', '');
                            }else{
                                $value_cs2_array[] = $value_ts2;
                            }
                        }
                        else 
                            $value_cs2_array[] = 0;
                    else
                        if($compliance_s2_total){
                             
                            $value_ts2 = $compliance_s2_total*100;
                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_cs2_array[] = number_format($value_ts2, 2, '.', '');
                            }else{
                                $value_cs2_array[] = $value_ts2;
                            }
                        }
                        else 
                                $value_cs2_array[] = 0;
                }else{
                    $value_cs2_array[] = 0;
                }
            }
            $value_c_array[] = $value_cs2_array;

            $value_cs3_array = array();
            for ($i=0; $i <= 6; $i++) { 
                 $compliance_s3_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s3_total =  $compliance_s3_total->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $compliance_s3_total =  $compliance_s3_total->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $compliance_s3_total =  $compliance_s3_total->get()->count();
                 
                if($compliance_s3_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s3_total){
                            $value_ts3 = $compliance_s3_total*100;
                            if($value_ts3>100)
                               $value_ts3 = 100;
                            if($value_ts3>0){
                                $value_cs3_array[] = number_format($value_ts3, 2, '.', '');
                            }else{
                                $value_cs3_array[] = $value_ts3;
                            }
                        }
                        else 
                            $value_cs3_array[] = 0;
                    else
                        if($compliance_s3_total){
                             
                            $value_ts3 = $compliance_s3_total*100;
                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_cs3_array[] = number_format($value_ts3, 2, '.', '');
                            }else{
                                $value_cs3_array[] = $value_ts3;
                            }
                        }
                        else 
                                $value_cs3_array[] = 0;
                }else{
                    $value_cs3_array[] = "";
                }
            }
            $value_c_array[] = $value_cs3_array;



            #Technique
            $value_t_array = array();
            $value_ts1_array = array();
            for ($i=0; $i <= 6; $i++) { 
                $tech_s1_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1');
                if($i!=0)
                      $tech_s1_total =  $tech_s1_total->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $tech_s1_total =  $tech_s1_total->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $tech_s1_total =  $tech_s1_total->get();
                if($tech_s1_total){
                    $tech_s1_total =  $tech_s1_total[0];
                    if($i!=0)
                        if($tech_s1_total->total!=NULL){
                            

                            $value_ts1 = $tech_s1_total->total;
                            if($value_ts1>100)
                               $value_ts1 = 100;
                            if($value_ts1>0){
                                $value_ts1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_ts1_array[] = $value_ts1;
                            }
                        }
                        else 
                            $value_ts1_array[] = 0;
                    else
                        if($tech_s1_total!=NULL){
                            

                            $value_ts1 = $tech_s1_total->total;
                            if($value_ts1>0){
                                $value_ts1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_ts1_array[] = $value_ts1;
                            }
                        }
                        else 
                                $value_ts1_array[] = 0;
                }else{
                    $value_ts1_array[] = 0;
                }
            }
            $value_t_array[] = $value_ts1_array;

            $value_ts2_array = array();
            for ($i=0; $i <= 6; $i++) { 
                 $tech_s2_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2');
                if($i!=0)
                      $tech_s2_total =  $tech_s2_total->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $tech_s2_total =  $tech_s2_total->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $tech_s2_total =  $tech_s2_total->get();
                if($tech_s2_total){
                    $tech_s2_total =  $tech_s2_total[0];
                    if($i!=0)
                        if($tech_s2_total->total!=NULL){
                            $value_ts2 = $tech_s2_total->total;
                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_ts2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_ts2_array[] = $value_ts2;
                            }
                        }
                        else 
                            $value_ts2_array[] = 0;
                    else
                        if($tech_s2_total!=NULL){
                             
                            $value_ts2 = $tech_s2_total->total;
                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_ts2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_ts2_array[] = $value_ts2;
                            }
                        }
                        else 
                                $value_ts2_array[] = 0;
                }else{
                    $value_ts2_array[] = 0;
                }
            }
            $value_t_array[] = $value_ts2_array;

            $value_ts3_array = array();
            for ($i=0; $i <= 6; $i++) { 
                 $tech_s3_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3');
                if($i!=0)
                      $tech_s3_total =  $tech_s3_total->where('date', "=", date('Y-m-d',strtotime($i." day",strtotime($startdate)))); 
                else
                     $tech_s3_total =  $tech_s3_total->where('date', "=", date('Y-m-d',strtotime($startdate)));

                $tech_s3_total =  $tech_s3_total->get();
                if($tech_s3_total){
                    $tech_s3_total =  $tech_s3_total[0];
                    if($i!=0){
                        if($tech_s3_total->total!=NULL){
                            $value_ts3 = $tech_s3_total->total;
                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_ts3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_ts3_array[] = $value_ts3;
                            }
                        }
                        else 
                            $value_ts3_array[] = 0;
                    }else{
                        if($tech_s3_total!=NULL){
                             
                            $value_ts3 = $tech_s3_total->total;
                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_ts3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_ts3_array[] = $value_ts3;
                            }
                        }
                        else 
                                $value_ts3_array[] = 0;
                    }
                }else{
                    $value_ts3_array[] = "";
                }
            }
            $value_t_array[] = $value_ts3_array;
            
        }else if($type==2){
            if($startdate==null){
               $startdate = date('Y-m-d',strtotime("-5 month"));
               $enddate   = date('Y-m-d');
            }else{
                $startdate = date('Y-m-d',strtotime($startdate));
                $enddate   = date('Y-m-d',strtotime("+6 month",strtotime($startdate))) ;
            }

            $label_array = array();
            for ($i=0; $i < 6; $i++) { 
                if($i!=0)
                    $label_array[]   = date('M Y',strtotime($i." month",strtotime($startdate)));
                else
                    $label_array[]   = date('M Y',strtotime($startdate));
            }
            #Compliance
            $value_c_array = array();

            $value_cs1_array = array();
            for ($i=0; $i < 6; $i++) { 
                 $compliance_s1_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s1_total =  $compliance_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $compliance_s1_total =  $compliance_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $compliance_s1_total =  $compliance_s1_total->get()->count();
                 
                if($compliance_s1_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s1_total){
                            $value_ts1 = $compliance_s1_total*100;
                            $value_ts1 = $value_ts1/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($i." month",strtotime($startdate))),  date('Y',strtotime($i." month",strtotime($startdate))));

                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_cs1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_cs1_array[] = $value_ts1;
                            }
                        }
                        else 
                            $value_cs1_array[] = 0;
                    else
                        if($compliance_s1_total){
                             
                            $value_ts1 = $compliance_s1_total*100;
                            $value_ts1 = $value_ts1/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($startdate)),  date('Y',strtotime($startdate)));
                            
                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_cs1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_cs1_array[] = $value_ts1;
                            }
                        }
                        else 
                                $value_cs1_array[] = 0;
                }else{
                    $value_cs1_array[] = 0;
                }
            }
            $value_c_array[] = $value_cs1_array;

            $value_cs2_array = array();
            for ($i=0; $i < 6; $i++) { 
                 $compliance_s2_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s2_total =  $compliance_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $compliance_s2_total =  $compliance_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $compliance_s2_total =  $compliance_s2_total->get()->count();
                 
                if($compliance_s2_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s2_total){
                            $value_ts2 = $compliance_s2_total*100;
                            $value_ts2 = $value_ts2/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($i." month",strtotime($startdate))),  date('Y',strtotime($i." month",strtotime($startdate))));

                            if($value_ts2>100)
                               $value_ts2 = 100;


                            if($value_ts2>0){
                                $value_cs2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_cs2_array[] = $value_ts2;
                            }
                        }
                        else 
                            $value_cs2_array[] = 0;
                    else
                        if($compliance_s2_total){
                             
                            $value_ts2 = $compliance_s2_total*100;
                            $value_ts2 = $value_ts2/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($startdate)),  date('Y',strtotime($startdate)));
                            
                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts1>0){
                                $value_cs2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_cs2_array[] = $value_ts2;
                            }
                        }
                        else 
                                $value_cs2_array[] = 0;
                }else{
                    $value_cs2_array[] = 0;
                }
            }
            $value_c_array[] = $value_cs2_array;

            $value_cs3_array = array();
            for ($i=0; $i < 6; $i++) { 
                  $compliance_s3_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s3_total =  $compliance_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $compliance_s3_total =  $compliance_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $compliance_s3_total =  $compliance_s3_total->get()->count();
                 
                if($compliance_s3_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s3_total){
                            $value_ts3 = $compliance_s3_total*100;
                            $value_ts3 = $value_ts3/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($i." month",strtotime($startdate))),  date('Y',strtotime($i." month",strtotime($startdate))));

                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_cs3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_cs3_array[] = $value_ts3;
                            }
                        }
                        else 
                            $value_cs3_array[] = 0;
                    else
                        if($compliance_s3_total){
                             
                            $value_ts3 = $compliance_s3_total*100;
                            $value_ts3 = $value_ts3/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($startdate)),  date('Y',strtotime($startdate)));
                            
                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_cs3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_cs3_array[] = $value_ts3;
                            }
                        }
                        else 
                                $value_cs3_array[] = 0;
                }else{
                    $value_cs3_array[] = "";
                }
            }
            $value_c_array[] = $value_cs3_array;


            #Technique
            $value_t_array = array();

            $value_ts1_array = array();
            for ($i=0; $i < 6; $i++) { 

                
                $tech_s1_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1');
                $tech_s1_record = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1');
                if($i!=0){
                      $tech_s1_total =  $tech_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                      $tech_s1_record =  $tech_s1_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                }else{
                     $tech_s1_total =  $tech_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                     $tech_s1_record =  $tech_s1_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                }

                $tech_s1_total =  $tech_s1_total->get();
                $tech_s1_count =  $tech_s1_record->groupBy('date')->groupBy('session_no')->get()->count();
                if($tech_s1_total){
                    $tech_s1_total =  $tech_s1_total[0];
                    if($i!=0)
                        if($tech_s1_total->total!=NULL){
                            $value_ts1 = 0;
                            if($tech_s1_total->total && $tech_s1_count)
                                $value_ts1 = $tech_s1_total->total/$tech_s1_count;

                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_ts1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_ts1_array[] = $value_ts1;
                            }
                        }
                        else 
                            $value_ts1_array[] = 0;
                    else
                        if($tech_s1_total!=NULL){
                            $value_ts1 = 0;
                            if($tech_s1_total->total && $tech_s1_count)
                            $value_ts1 = $tech_s1_total->total/$tech_s1_count;

                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_ts1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_ts1_array[] = $value_ts1;
                            }
                        }
                        else 
                                $value_ts1_array[] = 0;
                }else{
                    $value_ts1_array[] = 0;
                }


            }
            $value_t_array[] = $value_ts1_array;
            
            $value_ts2_array = array();
            for ($i=0; $i < 6; $i++) { 
                 

                $tech_s2_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2');

                $tech_s2_record = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2');
                if($i!=0){
                      $tech_s2_total =  $tech_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate))));
                      $tech_s2_record =  $tech_s2_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate))));  
                }else{
                     $tech_s2_total =  $tech_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                     $tech_s2_record =  $tech_s2_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                }

                $tech_s2_total =  $tech_s2_total->get();
                $tech_s2_count =  $tech_s2_record->groupBy('date')->groupBy('session_no')->get()->count();
                if($tech_s2_total){
                    $tech_s2_total =  $tech_s2_total[0];
                    if($i!=0)
                        if($tech_s2_total->total!=NULL){

                            $value_ts2 = 0;
                            if($tech_s2_total->total && $tech_s2_count)
                            $value_ts2 = $tech_s2_total->total/$tech_s2_count;

                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_ts2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_ts2_array[] = $value_ts2;
                            }
                        }
                        else 
                            $value_ts2_array[] = 0;
                    else
                        if($tech_s2_total!=NULL){
                            $value_ts2 = 0;
                            if($tech_s2_total->total && $tech_s2_count)
                                $value_ts2 = $tech_s2_total->total/$tech_s2_count;

                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_ts2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_ts2_array[] = $value_ts2;
                            }
                        }
                        else 
                                $value_ts2_array[] = 0;
                }else{
                    $value_ts2_array[] = 0;
                }

            }
            $value_t_array[] = $value_ts2_array;

            $value_ts3_array = array();
            for ($i=0; $i < 6; $i++) { 
                $tech_s3_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3');
                $tech_s3_record = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3');
                if($i!=0){
                      $tech_s3_total =  $tech_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                      $tech_s3_record =  $tech_s3_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                }else{
                     $tech_s3_total =  $tech_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                     $tech_s3_record =  $tech_s3_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                }

                $tech_s3_total =  $tech_s3_total->get();
                $tech_s3_count =  $tech_s3_record->groupBy('date')->groupBy('session_no')->get()->count();
                if($tech_s3_total){
                    $tech_s3_total =  $tech_s3_total[0];
                    if($i!=0){
                        if($tech_s3_total->total!=NULL){
                            $value_ts3 = 0;
                            if($tech_s3_total->total && $tech_s3_count)
                                $value_ts3 = $tech_s3_total->total/$tech_s3_count;

                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_ts3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_ts3_array[] = $value_ts3;
                            }
                        }
                        else 
                            $value_ts3_array[] = 0;
                    }else{
                        if($tech_s3_total!=NULL){
                            $value_ts3 = 0;
                            if($tech_s3_total->total && $tech_s3_count) 
                            $value_ts3 = $tech_s3_total->total/$tech_s3_count;
                            
                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_ts3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_ts3_array[] = $value_ts3;
                            }
                        }
                        else 
                                $value_ts3_array[] = 0;
                    }
                }else{
                    $value_ts3_array[] = "";
                }

            }
            $value_t_array[] = $value_ts3_array;
             

        }else if($type==3){
            if($startdate==null){
               $startdate = date('Y-m-d',strtotime("-11 month"));
               $enddate   = date('Y-m-d');
            }else{
                $startdate = date('Y-m-d',strtotime($startdate));
                $enddate   = date('Y-m-d',strtotime("+12 month",strtotime($startdate))) ;
            }

            $label_array = array();
            for ($i=0; $i < 12; $i++) { 
                if($i!=0)
                    $label_array[]   = date('M Y',strtotime($i." month",strtotime($startdate)));
                else
                    $label_array[]   = date('M Y',strtotime($startdate));
            }
            #Compliance
            $value_c_array = array();

            $value_cs1_array = array();
            for ($i=0; $i < 12; $i++) { 
                 $compliance_s1_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s1_total =  $compliance_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $compliance_s1_total =  $compliance_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $compliance_s1_total =  $compliance_s1_total->get()->count();
                 
                if($compliance_s1_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s1_total){
                            $value_ts1 = $compliance_s1_total*100;
                            $value_ts1 = $value_ts1/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($i." month",strtotime($startdate))),  date('Y',strtotime($i." month",strtotime($startdate))));
                            
                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_cs1_array[] = number_format($value_ts1, 2, '.', '');
                            }else{
                                $value_cs1_array[] = $value_ts1;
                            }
                        }
                        else 
                            $value_cs1_array[] = 0;
                    else
                        if($compliance_s1_total){
                             
                            $value_ts1 = $compliance_s1_total*100;
                            $value_ts1 = $value_ts1/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($startdate)),  date('Y',strtotime($startdate)));
                            
                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_cs1_array[] = number_format($value_ts1, 2, '.', '');
                            }else{
                                $value_cs1_array[] = $value_ts1;
                            }
                        }
                        else 
                                $value_cs1_array[] = 0;
                }else{
                    $value_cs1_array[] = 0;
                }
            }
            $value_c_array[] =  $value_cs1_array;

            $value_cs2_array = array();
            for ($i=0; $i < 12; $i++) { 
                 $compliance_s2_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s2_total =  $compliance_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $compliance_s2_total =  $compliance_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $compliance_s2_total =  $compliance_s2_total->get()->count();
                 
                if($compliance_s2_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s2_total){
                            $value_ts2 = $compliance_s2_total*100;
                            $value_ts2 = $value_ts2/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($i." month",strtotime($startdate))),  date('Y',strtotime($i." month",strtotime($startdate))));

                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_cs2_array[] = number_format($value_ts2, 2, '.', '');
                            }else{
                                $value_cs2_array[] = $value_ts2;
                            }
                        }
                        else 
                            $value_cs2_array[] = 0;
                    else
                        if($compliance_s2_total){
                             
                            $value_ts2 = $compliance_s2_total*100;
                            $value_ts2 = $value_ts2/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($startdate)),  date('Y',strtotime($startdate)));
                            
                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_cs2_array[] = number_format($value_ts2, 2, '.', '');
                            }else{
                                $value_cs2_array[] = $value_ts2;
                            }
                        }
                        else 
                                $value_cs2_array[] = 0;
                }else{
                    $value_cs2_array[] = 0;
                }
            }
            $value_c_array[] =  $value_cs2_array;

            $value_cs3_array = array();
            for ($i=0; $i < 12; $i++) { 
                   $compliance_s3_total = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3')
                                    ->where('is_attack','0');
                if($i!=0)
                      $compliance_s3_total =  $compliance_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                else
                     $compliance_s3_total =  $compliance_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));

                $compliance_s3_total =  $compliance_s3_total->get()->count();
                 
                if($compliance_s3_total){
                    //$compliance_s1_total =  $compliance_s1_total[0];
                    if($i!=0)
                        if($compliance_s3_total){
                            $value_ts3 = $compliance_s3_total*100;
                            $value_ts3 = $value_ts3/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($i." month",strtotime($startdate))),  date('Y',strtotime($i." month",strtotime($startdate))));

                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_cs3_array[] = number_format($value_ts3, 2, '.', '');
                            }else{
                                $value_cs3_array[] = $value_ts3;
                            }
                        }
                        else 
                            $value_cs3_array[] = 0;
                    else
                        if($compliance_s3_total){
                             
                            $value_ts3 = $compliance_s3_total*100;
                            $value_ts3 = $value_ts3/cal_days_in_month(CAL_GREGORIAN,  date('m',strtotime($startdate)),  date('Y',strtotime($startdate)));
                            
                            if($value_ts3>100)
                               $value_ts3 = 100;

                            if($value_ts3>0){
                                $value_cs3_array[] = number_format($value_ts3, 2, '.', '');
                            }else{
                                $value_cs3_array[] = $value_ts3;
                            }
                        }
                        else 
                                $value_cs3_array[] = 0;
                }else{
                    $value_cs3_array[] = "";
                }
            }
            $value_c_array[] =  $value_cs3_array;



            #Technique
            $value_t_array = array();

            $value_ts1_array = array();
            for ($i=0; $i < 12; $i++) { 
                 $tech_s1_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1');
                $tech_s1_record = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','1');
                if($i!=0){
                      $tech_s1_total =  $tech_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                      $tech_s1_record =  $tech_s1_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate))));

                }else{
                     $tech_s1_total =  $tech_s1_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                     $tech_s1_record =  $tech_s1_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                }

                $tech_s1_total =  $tech_s1_total->get();
                $tech_s1_count =  $tech_s1_record->groupBy('date')->groupBy('session_no')->get()->count();
                if($tech_s1_total){
                    $tech_s1_total =  $tech_s1_total[0];
                    if($i!=0){
                        if($tech_s1_total->total!=NULL){
                             $value_ts1 = 0;
                            if($tech_s1_total->total && $tech_s1_count)
                                $value_ts1 = $tech_s1_total->total/$tech_s1_count;
                            
                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_ts1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_ts1_array[] = $value_ts1;
                            }
                        }
                        else 
                            $value_ts1_array[] = 0;
                    }else{
                        if($tech_s1_total!=NULL){
                            
                            $value_ts1 = 0;
                            if($tech_s1_total->total && $tech_s1_count)
                                $value_ts1 = $tech_s1_total->total/$tech_s1_count;

                            if($value_ts1>100)
                               $value_ts1 = 100;

                            if($value_ts1>0){
                                $value_ts1_array[] = number_format($value_ts1, 1, '.', '');
                            }else{
                                $value_ts1_array[] = $value_ts1;
                            }
                        }
                        else 
                                $value_ts1_array[] = 0;
                    }
                }else{
                    $value_ts1_array[] = 0;
                }
            }
            $value_t_array[] = $value_ts1_array;

            $value_ts2_array = array();
            for ($i=0; $i < 12; $i++) { 
                 $tech_s2_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2');
                $tech_s2_record = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','2');
                if($i!=0){
                      $tech_s2_total =  $tech_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate)))); 
                      $tech_s2_record =  $tech_s2_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate))));
                }else{
                     $tech_s2_total =  $tech_s2_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                     $tech_s2_record =  $tech_s2_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                }
                $tech_s2_total =  $tech_s2_total->get();
                $tech_s2_count =  $tech_s2_record->groupBy('date')->groupBy('session_no')->get()->count();
                if($tech_s2_total){
                    $tech_s2_total =  $tech_s2_total[0];
                    if($i!=0){
                        if($tech_s2_total->total!=NULL){
                            $value_ts2 = 0;
                            if($tech_s2_total->total && $tech_s2_count)
                                $value_ts2 = $tech_s2_total->total/$tech_s2_count;

                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_ts2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_ts2_array[] = $value_ts2;
                            }
                        }
                        else 
                            $value_ts2_array[] = 0;
                    }else{
                        if($tech_s2_total!=NULL){
                            
                            $value_ts2 = 0;
                            if($tech_s2_total->total && $tech_s2_count) 
                                $value_ts2 = $tech_s2_total->total/$tech_s2_count;

                            if($value_ts2>100)
                               $value_ts2 = 100;

                            if($value_ts2>0){
                                $value_ts2_array[] = number_format($value_ts2, 1, '.', '');
                            }else{
                                $value_ts2_array[] = $value_ts2;
                            }
                        }
                        else 
                                $value_ts2_array[] = 0;
                    }
                }else{
                    $value_ts2_array[] = 0;
                }
            }
            $value_t_array[] = $value_ts2_array;

            $value_ts3_array = array();
            for ($i=0; $i < 12; $i++) { 
                  $tech_s3_total = Spacersession::select(DB::raw('sum(session_tech) as total'))
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3');
                
                $tech_s3_record = Spacersession::select('id')
                                    ->where('child_id', "=", $id)
                                    ->where('session_no','3');

                if($i!=0){
                      $tech_s3_total =  $tech_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate))));
                      $tech_s3_record =  $tech_s3_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($i." month",strtotime($startdate))));  
                }else{
                     $tech_s3_total =  $tech_s3_total->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                      $tech_s3_record =  $tech_s3_record->where(DB::raw("(DATE_FORMAT(date,'%Y-%m'))"), "=", date('Y-m',strtotime($startdate)));
                }

                $tech_s3_total =  $tech_s3_total->get();
                $tech_s3_count =  $tech_s3_record->groupBy('date')->groupBy('session_no')->get()->count();
                if($tech_s3_total){
                    $tech_s3_total =  $tech_s3_total[0];
                    if($i!=0){
                        if($tech_s3_total->total!=NULL){
                            $value_ts3 = 0;
                            if($tech_s3_total->total && $tech_s3_count) 
                            $value_ts3 = $tech_s3_total->total/$tech_s3_count;

                            if($value_ts3>100)
                               $value_ts3 = 100;


                            if($value_ts3>0){
                                $value_ts3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_ts3_array[] = $value_ts3;
                            }
                        }
                        else 
                            $value_ts3_array[] = 0;
                    }else{
                        if($tech_s3_total!=NULL){
                            $value_ts3 = 0;
                            if($tech_s3_total->total && $tech_s3_count)  
                                $value_ts3 = $tech_s3_total->total/$tech_s3_count;

                            if($value_ts3>100)
                               $value_ts3 = 100;
                           
                            if($value_ts3>0){
                                $value_ts3_array[] = number_format($value_ts3, 1, '.', '');
                            }else{
                                $value_ts3_array[] = $value_ts3;
                            }
                        }
                        else 
                                $value_ts3_array[] = "";
                    }
                }else{
                    $value_ts3_array[] = "";
                }
            }
            $value_t_array[] = $value_ts3_array;
        }

        $this->data['title'] = 'Manage Reports : Individual Compliance VS Technique'; // set the page title
        $child_info = Child::where('id', '=', $id)->first(); 
        $firstyear      = Spacerdata::select('id')->where(DB::raw("(DATE_FORMAT(datetime,'%Y'))"), "=", date('Y'))->where('is_attack','1')->get()->count();
         
        $secondyear     = Spacerdata::select('id')->where(DB::raw("(DATE_FORMAT(datetime,'%Y'))"), "=", (date('Y')-1))->where('is_attack','1')->get()->count();
        $thirdyear      = Spacerdata::select('id')->where(DB::raw("(DATE_FORMAT(datetime,'%Y'))"), "=", (date('Y')-2))->where('is_attack','1')->get()->count();

       


        // $this->data['attacks'] = array('firstyear'       => $firstyear,
        //                                'secondyear'     => $secondyear,
        //                                'thirdyear'  => $thirdyear); 

        $this->data['attacks'] = array('label'     => $label_array,
                                       'value_c'   => $value_c_array,
                                       'value_t'   => $value_t_array,
                                       'startdate' => $startdate,
                                       'type'      => $type); 

         $this->data['child_info'] = $child_info;

        //dd($this->data['attacks']);

        return view('backpack::techniquecompliance', $this->data);
    }

    public function export_techcompaliance() 
    {
        return Excel::download(new ReportExport, 'Export_TechniqueCompaliance.xlsx');
    }
    public function export_attack() 
    {
        return Excel::download(new ReportattackExport, 'Export_Attack.xlsx');
    }

}

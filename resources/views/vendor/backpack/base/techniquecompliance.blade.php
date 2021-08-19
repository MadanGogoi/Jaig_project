@extends('backpack::layout')
<?php 
//dd($child_info);

$parent = $child_info->parent;
?>
@section('header')
    <section class="content-header">
      <h1>
       Manage Reports : Individual Compliance VS Technique
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ backpack_url() }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="active">{{ trans('backpack::base.dashboard') }}</li>
      </ol>
    </section>
@endsection


@section('content')
    

<script src="/packages/chart/Chart.bundle.js"></script>
<script src="/packages/chart/samples/utils.js"></script>
<style>
  canvas {
    -moz-user-select: none;
    -webkit-user-select: none;
    -ms-user-select: none;
  }
  .chardata{
    border: solid 1px #ccc;
  }
  .chardata_feedback{
    border: solid 1px #ccc;
    float: left;
    width: 100%;
  }
  .chardata h2, .chardata_feedback h2, h2{
    margin-top:0px;
    background-color:#3c8dbc;
    color: #FFF;
    font-size: 14px;
    padding: 15px 0px 15px 5px;
    margin-bottom: 0;
  }
   
  .feebackrow1, .feebackrow2, .feebackrow3, .feebackrow4{
    float: left;
    width: 100%;
    padding: 0px;
    margin: 0px;
  }
  .feebackrtype{
    float: left;
    width: 80%;
    padding:10px 5px;
    text-align: center; 
    border-right: solid 1px #ccc;  
  }
   .feebackcount{
    float: left;
    width: 20%;
    padding:10px 5px;
    text-align: center; 
  }
  .feebackrow1{
    background-color: #ecf0f5;
    color: red;
  }
  .feebackrow2{
    background-color: #FFFFFF;
    color: orange;
  }
  .feebackrow3{
    background-color: #ecf0f5;
    color: green;
  }
  .feebackrow4{
    background-color: #FFFFFF;
    color: blue;
  }
  span.active a, .nextlink a{
    color: #3c8dbc;
    font-weight: bold;
  }
  </style>
  <h2>Compliance VS Technique</h2>
  <div class="row">
      <div class="col-md-6">&nbsp;</div>
  </div>
  <div class="row">
      <div class="col-md-6">Select Start Date: <input onchange="callReport(this.value)" type="text" readonly class="date" name="startdate" value="<?php echo date('Y-m-d',strtotime($attacks['startdate'])); ?>"></div>
      <div class="col-md-6">

        <span class="@if ($attacks['type']==1) active @endif"><a href="/admin/report/<?php echo request('id');?>/technique_compliance/1/<?php echo request('startdate');?>">View weekly</a></span> | 
        <span class="@if ($attacks['type']==2) active @endif"><a href="/admin/report/<?php echo request('id');?>/technique_compliance/2/<?php echo request('startdate');?>">View 6 months</a></span> | 
        <span class="@if ($attacks['type']==3) active @endif"><a href="/admin/report/<?php echo request('id');?>/technique_compliance/3/<?php echo request('startdate');?>">View 1 Year</a></span></div>
  </div>
  <div class="row">
      <div class="col-md-6">&nbsp;</div>
  </div>
  <div class="row">
      <div class="col-md-4">
        <b>Parent Full Name :</b> {{$parent->name}}<br>
        <b>Child Name :</b> {{$child_info->name}}<br>
        <b>Spacer ID:</b> {{$child_info->spacer_id}}<br>
        <br>
      </div>
      <?php if(request('type')==1){ ?>
               <div class="col-md-4 nextlink">

                  <a href="/admin/report/<?php echo request('id');?>/technique_compliance/1/<?php echo date('Y-m-d',strtotime("-7 days", strtotime(request('startdate'))));?>"><</a> <?php echo date('jS M',strtotime(request('startdate'))); ?> - <?php echo date('jS M Y',strtotime("+6 day",strtotime(request('startdate')))); ?> <a href="/admin/report/<?php echo request('id');?>/technique_compliance/1/<?php echo date('Y-m-d',strtotime("+7 day",strtotime(request('startdate'))));?>">></a>
              </div>
      <?php }else if(request('type')==2){?>
                <div class="col-md-4  nextlink">

                  <a href="/admin/report/<?php echo request('id');?>/technique_compliance/2/<?php echo date('Y-m-d',strtotime("-6 month", strtotime(request('startdate'))));?>"><</a> <?php echo date('M Y',strtotime(request('startdate'))); ?> - <?php echo date('M Y',strtotime("+5 month",strtotime(request('startdate')))); ?> <a href="/admin/report/<?php echo request('id');?>/technique_compliance/2/<?php echo date('Y-m-d',strtotime("+6 month",strtotime(request('startdate'))));?>">></a>

                </div>
      <?php }else if(request('type')==3){?>
                 <div class="col-md-4  nextlink">

                  <a href="/admin/report/<?php echo request('id');?>/technique_compliance/3/<?php echo date('Y-m-d',strtotime("-12 month", strtotime(request('startdate'))));?>"><</a> <?php echo date('M Y',strtotime(request('startdate'))); ?> - <?php echo date('M Y',strtotime("+11 month",strtotime(request('startdate')))); ?> <a href="/admin/report/<?php echo request('id');?>/technique_compliance/3/<?php echo date('Y-m-d',strtotime("+12 month",strtotime(request('startdate'))));?>">></a>

                </div>
      <?php  }?>
  </div>
  <div class="row">
      <div class="col-md-6">&nbsp;</div>
  </div>
 <div class="row">
        <div class="col-md-12">  
          <div class="chardata">
         
            <div style="width: 10%; float: left; text-align: center;padding-top: 10%">
                  &nbsp;
              </div>
            <div id="container2" style="width: 70%; float: left;">
              <canvas id="canvas2"></canvas>
            </div> 
              
          </div>
        </div>
 </div>
  
  <div class="row">
        <div class="col-md-12">  
          
          
            <div style="width: 20%; float: left;padding-top: 10%;text-align: center;">
                 <input type="button" name="" value="Back" class="btn btn-primary view-button" onclick="window.location='/admin/childreport'">
                  <input type="button" name="" value="View No. Of Attacks" class="btn btn-primary view-button" onclick="window.location='/admin/report/{{$child_info->id}}/attack/1/{{date("Y-m-d",strtotime("-6 days"))}}'">
              </div>    
          </div>
        </div>
 </div>
 <?php
    $label_array = $attacks['label'];
    $value_c =  $attacks['value_c'];
    $value_t =  $attacks['value_t'];
    $c_color = array('1'=>'#eabac0', '2'=>'#ea8b96', '3'=>'#f4011e');
    $t_color = array('1'=>'#c2c9e9', '2'=>'#90a2ed', '3'=>'#002bdf');
    
 ?>
  <script>

    function callReport(value){
      if(value!= '<?php echo request('startdate');?>')
      window.location='/admin/report/<?php echo request('id');?>/technique_compliance/<?php echo $attacks['type']?>/'+value;
      //alert(value);
    }
    var label_array = [];
    <?php 
    foreach ($label_array as $key => $value) { ?>
       label_array.push('<?php echo $value;?>');
      
    <?php } ?>


    //import Chart from 'chart.js';
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var color = Chart.helpers.color;
    var barChartData = {
      labels: ["{{date('Y')}}","{{date('Y', strtotime('-1 year'))}}","{{date('Y', strtotime('-2 year'))}}"],
      datasets: [{
        label: 'No. Of Attacks',
        backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
        borderColor: window.chartColors.blue,
        borderWidth: 1,
        data: [20,30,40]
      }]

    };
    //Line Chart
       
    var config = {
      type: 'line',
      data: {
        labels: label_array,
        datasets: [
          <?php  
         
           
          $count = 0;
          foreach ($value_c as $value) { $count++;?>
            {
              label: 'Compliance : Session <?php echo $count;?>',
              backgroundColor: '<?php echo $c_color[$count]?>',
              borderColor: '<?php echo $c_color[$count]?>',
              data: <?php echo json_encode($value)?>,
              fill: false,
            },
          <?php } ?>

          <?php  
         
           
          $count = 0;
          foreach ($value_t as $value) { $count++;?>
            {
              label: 'Technique : Session <?php echo $count;?>',
              backgroundColor: '<?php echo $t_color[$count]?>',
              borderColor: '<?php echo $t_color[$count]?>',
              data: <?php echo json_encode($value)?>,
              fill: false,
            },
          <?php } ?>
             
        ]
      },
      options: {
        responsive: true,
        title: {
          display: false,
          text: 'Chart.js Line Chart'
        },
        legend: {
            position: 'right',
            display: true,
          },
        tooltips: {
          mode: 'index',
          intersect: false,
        },
        hover: {
          mode: 'nearest',
          intersect: true
        },
        scales: {
          xAxes: [{
            display: true,
            scaleLabel: {
              display: true,
              labelString: 'Date'
            }
          }],
          yAxes: [{
            display: true,
            scaleLabel: {
              display: true,
              labelString: 'Value (%)'
            },
            ticks: {
                    beginAtZero:true,
                    //suggestedMin: 1,
                    //stepSize: 1,
                    //min: 1,
                    max: 100

                }
          }]
        }
      }
    };
      



    window.onload = function() {
      // var ctx = document.getElementById('canvas').getContext('2d');
      // window.myBar = new Chart(ctx, {
      //   type: 'bar',
      //   data: barChartData,
      //   options: {
      //     responsive: true,
      //     legend: {
      //       position: 'top',
      //       display: false,
      //     },
      //     title: {
      //       display: false,
      //       text: 'Chart.js Bar Chart'
      //     },
      //     scales: {
      //       yAxes: [{
      //           ticks: {
      //               beginAtZero:true
      //           }
      //       }]
      //   }
      //   }
      // });
      // var ctx2 = document.getElementById('canvas2').getContext('2d');
      // window.myBar2 = new Chart(ctx2, {
      //   type: 'bar',
      //   data: barChartData,
      //   options: {
      //     responsive: true,
      //     legend: {
      //       position: 'top',
      //       display: false,
      //     },
      //     title: {
      //       display: false,
      //       text: 'Chart.js Bar Chart'
      //     }
      //   }
      // });
      var ctx2 = document.getElementById('canvas2').getContext('2d');
      window.myLine = new Chart(ctx2, config);
      // var ctx3 = document.getElementById('chart-area').getContext('2d');
      // window.myPie = new Chart(ctx3, config);

    };

    // document.getElementById('randomizeData').addEventListener('click', function() {
    //   var zero = Math.random() < 0.2 ? true : false;
    //   barChartData.datasets.forEach(function(dataset) {
    //     dataset.data = dataset.data.map(function() {
    //       return zero ? 0.0 : randomScalingFactor();
    //     });

    //   });
    //   window.myBar.update();
    // });
    // console.log(color(dsColor).alpha(0.5).rgbString());
    // var colorNames = Object.keys(window.chartColors);
    // document.getElementById('addDataset').addEventListener('click', function() {
    //   var colorName = colorNames[barChartData.datasets.length % colorNames.length];
    //   var dsColor = window.chartColors[colorName];
    //   var newDataset = {
    //     label: 'Dataset ' + barChartData.datasets.length,
    //     backgroundColor: color(dsColor).alpha(0.5).rgbString(),
    //     borderColor: dsColor,
    //     borderWidth: 1,
    //     data: []
    //   };

    //   for (var index = 0; index < barChartData.labels.length; ++index) {
    //     newDataset.data.push(randomScalingFactor());
    //   }

    //   barChartData.datasets.push(newDataset);
    //   window.myBar.update();
    // });

   

    // document.getElementById('removeDataset').addEventListener('click', function() {
    //   barChartData.datasets.splice(0, 1);
    //   window.myBar.update();
    // });

    // document.getElementById('removeData').addEventListener('click', function() {
    //   barChartData.labels.splice(-1, 1); // remove the label first

    //   barChartData.datasets.forEach(function(dataset) {
    //     dataset.data.pop();
    //   });

    //   window.myBar.update();
    // });
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/css/bootstrap-datepicker.css" rel="stylesheet">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.5.0/js/bootstrap-datepicker.js"></script>


  <script type="text/javascript">

    $('.date').datepicker({  

       format: 'yyyy-mm-dd'

     });  

</script> 
  @endsection
@extends('backpack::layout')
<?php 
//dd($attacks['label']);
$parent = $child_info->parent;
?>
@section('header')
    <section class="content-header">
      <h1>
       Manage Reports : Individual No. Of Attacks Page
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
input[type=date]::-webkit-inner-spin-button,
input[type=date]::-webkit-outer-spin-button {
  -webkit-appearance: none;
}
input[type=date]::-moz-clear { display: none; }

  </style>
  <h2>Individual No. Of Attacks Page</h2>
  <div class="row">
      <div class="col-md-6">&nbsp;</div>
  </div>
  <div class="row">
      <div class="col-md-6">Select Start Date: <input readonly onchange="callReport(this.value)" type="text" class="date" id="datepicker" name="startdate" value="<?php echo date('Y-m-d',strtotime($attacks['startdate'])); ?>"></div>
      <div class="col-md-6">

        <span class="@if ($attacks['type']==1) active @endif"><a href="/admin/report/<?php echo request('id');?>/attack/1/<?php echo request('startdate');?>">View weekly</a></span> | 
        <span class="@if ($attacks['type']==2) active @endif"><a href="/admin/report/<?php echo request('id');?>/attack/2/<?php echo request('startdate');?>">View 6 months</a></span> | 
        <span class="@if ($attacks['type']==3) active @endif"><a href="/admin/report/<?php echo request('id');?>/attack/3/<?php echo request('startdate');?>">View 1 Year</a></span></div>
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

                  <a href="/admin/report/<?php echo request('id');?>/attack/1/<?php echo date('Y-m-d',strtotime("-7 days", strtotime(request('startdate'))));?>"><</a> <?php echo date('jS M',strtotime(request('startdate'))); ?> - <?php echo date('jS M Y',strtotime("+6 day",strtotime(request('startdate')))); ?> <a href="/admin/report/<?php echo request('id');?>/attack/1/<?php echo date('Y-m-d',strtotime("+7 day",strtotime(request('startdate'))));?>">></a>
              </div>
      <?php }else if(request('type')==2){?>
                <div class="col-md-4  nextlink">

                  <a href="/admin/report/<?php echo request('id');?>/attack/2/<?php echo date('Y-m-d',strtotime("-6 month", strtotime(request('startdate'))));?>"><</a> <?php echo date('M Y',strtotime(request('startdate'))); ?> - <?php echo date('M Y',strtotime("+5 month",strtotime(request('startdate')))); ?> <a href="/admin/report/<?php echo request('id');?>/attack/2/<?php echo date('Y-m-d',strtotime("+6 month",strtotime(request('startdate'))));?>">></a>

                </div>
      <?php }else if(request('type')==3){?>
                 <div class="col-md-4  nextlink">

                  <a href="/admin/report/<?php echo request('id');?>/attack/3/<?php echo date('Y-m-d',strtotime("-12 month", strtotime(request('startdate'))));?>"><</a> <?php echo date('M Y',strtotime(request('startdate'))); ?> - <?php echo date('M Y',strtotime("+11 month",strtotime(request('startdate')))); ?> <a href="/admin/report/<?php echo request('id');?>/attack/3/<?php echo date('Y-m-d',strtotime("+12 month",strtotime(request('startdate'))));?>">></a>

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
            <div id="container" style="width: 70%; float: left;">
              <canvas id="canvas"></canvas>
            </div> 
              
          </div>
        </div>
 </div>
  
  <div class="row">
        <div class="col-md-12">  
          
          
            <div style="width: 40%; float: left;padding-top: 10%;text-align: center;">
                 <input type="button" name="" value="Back" class="btn btn-primary view-button" onclick="window.location='/admin/childreport'">
                  <input type="button" name="" value="View Compliance VS Technique" class="btn btn-primary view-button" onclick="window.location='/admin/report/{{$child_info->id}}/technique_compliance/1/{{date("Y-m-d",strtotime("-6 days"))}}'">
              </div>    
          </div>
        </div>
 </div>
 <?php
    $label_array = $attacks['label'];
 ?>
  <script>

    function callReport(value){
       
      if(value!= '<?php echo request('startdate');?>')
      window.location='/admin/report/<?php echo request('id');?>/attack/<?php echo $attacks['type']?>/'+value;
      //alert(value);
    }
    var label_array = [];
    <?php 
    foreach ($label_array as $key => $value) { ?>
       label_array.push('<?php echo $value;?>');
      
    <?php } ?>
     
   //  var str="&#039;[&quot;11 Jun 2019&quot;,&quot;12 Jun 2019&quot;,&quot;13 Jun 2019&quot;,&quot;14 Jun 2019&quot;,&quot;15 Jun 2019&quot;,&quot;16 Jun 2019&quot;]&#039;";
   // // var str_esc=escape(str);
   //  var lab_str = JSON.stringify(str);
   // // var lab_str2 = lab_str.replace(/&quot;/g,"'");
   //  //var lab_str = JSON.parse(lab_str);
   //  console.log(lab_str);
    //var lab_str = 'decodeHTMLEntities(" ")';
    //console.log(decodeHTMLEntities(" "));

    //import Chart from 'chart.js';
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var color = Chart.helpers.color;
    var barChartData = {
     // labels: ["{{date('Y')}}","{{date('Y', strtotime('-1 year'))}}","{{date('Y', strtotime('-2 year'))}}"],
     labels: label_array,
      datasets: [{
        label: 'No. Of Attacks',
        backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
        borderColor: window.chartColors.blue,
        borderWidth: 1,
       //data: [20,30,40]
       data: {{json_encode($attacks['value'])}}
      }]

    };
     

    window.onload = function() {
      var ctx = document.getElementById('canvas').getContext('2d');
      window.myBar = new Chart(ctx, {
        type: 'bar',
        data: barChartData,
        options: {
          responsive: true,
          legend: {
            position: 'top',
            display: true,
          },
          title: {
            display: false,
            text: 'Chart.js Bar Chart'
          },
          scales: {
            yAxes: [{
               // type: 'categoryPercentage',
                scaleLabel: {
                  display: true,
                  labelString: 'No. Of Attacks'
                },
                ticks: {
                    beginAtZero:true,
                    //suggestedMin: 1,
                    stepSize: 1,
                    //min: 1,
                    //max: 50

                }
            }]
        }
        }
      });
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
      // var ctx2 = document.getElementById('canvas2').getContext('2d');
      // window.myLine = new Chart(ctx2, config);
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

    var colorNames = Object.keys(window.chartColors);
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
@extends('backpack::layout')
<?php 
//dd($registered_user);

?>
@section('header')
    <section class="content-header">
      <h1>
        {{ trans('backpack::base.dashboard') }}<small>{{ trans('backpack::base.first_page_you_see') }}</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ backpack_url() }}">{{ config('backpack.base.project_name') }}</a></li>
        <li class="active">{{ trans('backpack::base.dashboard') }}</li>
      </ol>
    </section>
@endsection


@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-title">{{ trans('backpack::base.login_status') }} test</div>
                </div>

                <div class="box-body">{{ trans('backpack::base.logged_in') }}</div>
            </div>
        </div>
    </div>

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
  .chardata h2, .chardata_feedback h2{
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
  </style>
 <div class="row">
        <div class="col-md-12">
             <div class="col-md-6">
               <div class="chardata">
                  <h2>Registered Users</h2>
                  <div id="canvas-holder" style="width:95%">
                    <canvas id="chart-area" style="width: 100%"></canvas>
                  </div>
              </div>
            </div>
            <div class="col-md-6">
                <div class="chardata">
                  <h2>Activated Users</h2>
                  <div id="container" style="width: 95%;">
                  <canvas id="canvas"></canvas>
                  </div>
                </div>
            </div>
            
         </div>
 </div>
  <div class="row">
        <div class="col-md-12">
            
            <div class="col-md-6">
                <div class="chardata">
                  <h2>Registered Child</h2>
                  <div id="registeredchild_container" style="width: 95%;">
                  <canvas id="canvas2"></canvas>
                  </div>
                </div>
                 
            </div>
            <div class="col-md-6">
               <div class="chardata_feedback">
                  <h2>Pending Feedback</h2>
                  <div class="feebackrow1">
                    <div class="feebackrtype ">Report a Problem</div>
                    <div class="feebackcount">{{$feedback['problemcount']}}</div>
                  </div>
                  <div class="feebackrow2">
                    <div class="feebackrtype">Suggestion</div>
                    <div class="feebackcount">{{$feedback['suggestioncount']}}</div>
                  </div>
                  <div class="feebackrow3">
                    <div class="feebackrtype">Enquiries</div>
                    <div class="feebackcount">{{$feedback['enquirycount']}}</div>
                  </div>
                  <div class="feebackrow4">
                    <div class="feebackrtype">Others</div>
                    <div class="feebackcount">{{$feedback['otherscount']}}</div>
                  </div>
              </div>
            </div>
         </div>
 </div>
 <?php
   $date_array = array();  
   $label_array = array();
   for ($i = 1; $i < 6; $i++) {
    $label_array[] =  date('F', strtotime("-$i month"));
    $date_array[]  =  date('Ym', strtotime("-$i month"));
  }
  //dd($label_array);
  
 // echo ($label_arrayout);
 //  // var_dump( $label_arrayout);
 //  dd($label_arrayout);
 ?>
  <script>
    //import Chart from 'chart.js';
    var MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var color = Chart.helpers.color;
    var barChartData = {
      labels: ["{{date('F Y')}}","{{date('F Y', strtotime('-1 month'))}}","{{date('F Y', strtotime('-2 month'))}}","{{date('F Y', strtotime('-3 month'))}}","{{date('F Y', strtotime('-4 month'))}}","{{date('F Y', strtotime('-5 month'))}}"],
      datasets: [{
        label: 'Activated Users',
        backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
        borderColor: window.chartColors.blue,
        borderWidth: 1,
        data: [
          {{$activated_user['month1']}},
          {{$activated_user['month2']}},
          {{$activated_user['month3']}},
          {{$activated_user['month4']}},
          {{$activated_user['month5']}},
          {{$activated_user['month6']}},
        ]
      }]

    };

    var registerChildtData = {
      labels: ["{{date('F Y')}}","{{date('F Y', strtotime('-1 month'))}}","{{date('F Y', strtotime('-2 month'))}}","{{date('F Y', strtotime('-3 month'))}}","{{date('F Y', strtotime('-4 month'))}}","{{date('F Y', strtotime('-5 month'))}}"],
      datasets: [{
        label: 'Registered Child',
        backgroundColor: color(window.chartColors.blue).alpha(0.5).rgbString(),
        borderColor: window.chartColors.blue,
        borderWidth: 1,
        data: [
          {{$registered_child['cmonth1']}},
          {{$registered_child['cmonth2']}},
          {{$registered_child['cmonth3']}},
          {{$registered_child['cmonth4']}},
          {{$registered_child['cmonth5']}},
          {{$registered_child['cmonth6']}},
        ]
      }]

    };
    //PIE Chart
      var randomScalingFactor = function() {
        return Math.round(Math.random() * 100);
      };

      var config = {
        type: 'pie',
        data: {
          datasets: [{
            data: [{{$registered_user['cmscount']}},{{$registered_user['emailcount']}},{{$registered_user['facebookcount']}}],
            backgroundColor: [
              window.chartColors.red,
              window.chartColors.orange,
              window.chartColors.yellow,
            ],
            label: 'Dataset 1',
            
          }],
          labels: [
            'CMS',
            'Email',
            'Facebook'
          ]
        },
        options: {
          responsive: true,

        }
      };



    Chart.plugins.register({
      afterDatasetsDraw: function(chart) {
        var ctx = chart.ctx;

        chart.data.datasets.forEach(function(dataset, i) {
          var meta = chart.getDatasetMeta(i);
          
          if (!meta.hidden && meta['type']=='pie') {
            meta.data.forEach(function(element, index) {
              // Draw the text in black, with the specified font
              ctx.fillStyle = 'rgb(0, 0, 0)';

              var fontSize = 16;
              var fontStyle = 'normal';
              var fontFamily = 'Helvetica Neue';
              ctx.font = Chart.helpers.fontString(fontSize, fontStyle, fontFamily);

              // Just naively convert to string for now
              var dataString = dataset.data[index].toString();

              // Make sure alignment settings are correct
              ctx.textAlign = 'center';
              ctx.textBaseline = 'middle';

              var padding = 5;
              var position = element.tooltipPosition();
              ctx.fillText(dataString, position.x, position.y - (fontSize / 2) - padding);
            });
          }
        });
      }
    });

    window.onload = function() {
      var ctx = document.getElementById('canvas').getContext('2d');
      window.myBar = new Chart(ctx, {
        type: 'bar',
        data: barChartData,
        options: {
          responsive: true,
          legend: {
            position: 'top',
            display: false,
          },
          title: {
            display: false,
            text: 'Chart.js Bar Chart'
          }
        }
      });
      var ctx2 = document.getElementById('canvas2').getContext('2d');
      window.myBar2 = new Chart(ctx2, {
        type: 'bar',
        data: registerChildtData,
        options: {
          responsive: true,
          legend: {
            position: 'top',
            display: false,
          },
          title: {
            display: false,
            text: 'Chart.js Bar Chart'
          }
        }
      });

      var ctx3 = document.getElementById('chart-area').getContext('2d');
      window.myPie = new Chart(ctx3, config);

    };

    document.getElementById('randomizeData').addEventListener('click', function() {
      var zero = Math.random() < 0.2 ? true : false;
      barChartData.datasets.forEach(function(dataset) {
        dataset.data = dataset.data.map(function() {
          return zero ? 0.0 : randomScalingFactor();
        });

      });
      window.myBar.update();
    });

    var colorNames = Object.keys(window.chartColors);
    document.getElementById('addDataset').addEventListener('click', function() {
      var colorName = colorNames[barChartData.datasets.length % colorNames.length];
      var dsColor = window.chartColors[colorName];
      var newDataset = {
        label: 'Dataset ' + barChartData.datasets.length,
        backgroundColor: color(dsColor).alpha(0.5).rgbString(),
        borderColor: dsColor,
        borderWidth: 1,
        data: []
      };

      for (var index = 0; index < barChartData.labels.length; ++index) {
        newDataset.data.push(randomScalingFactor());
      }

      barChartData.datasets.push(newDataset);
      window.myBar.update();
    });

    document.getElementById('addData').addEventListener('click', function() {
      if (barChartData.datasets.length > 0) {
        var month = MONTHS[barChartData.labels.length % MONTHS.length];
        barChartData.labels.push(month);

        for (var index = 0; index < barChartData.datasets.length; ++index) {
          // window.myBar.addData(randomScalingFactor(), index);
          barChartData.datasets[index].data.push(randomScalingFactor());
        }

        window.myBar.update();
      }
    });

    document.getElementById('removeDataset').addEventListener('click', function() {
      barChartData.datasets.splice(0, 1);
      window.myBar.update();
    });

    document.getElementById('removeData').addEventListener('click', function() {
      barChartData.labels.splice(-1, 1); // remove the label first

      barChartData.datasets.forEach(function(dataset) {
        dataset.data.pop();
      });

      window.myBar.update();
    });
  </script>
  @endsection
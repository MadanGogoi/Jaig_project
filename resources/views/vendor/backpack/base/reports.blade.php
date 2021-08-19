@extends('backpack::layout')
<?php 
//dd($registered_user);

?>
@section('header')
    <section class="content-header">
      <h1>
       Manage Reports
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
          <div class="chardata">
              <h2>No.Of Attacks</h2>
             
              <div id="container" style="width: 70%; float: left;">
                   <canvas id="canvas"></canvas>
              </div> 
              <div style="width: 20%; float: left;padding-top: 10%;text-align: center;">
                  <input type="button" name="" value="View Reports List" class="btn btn-primary view-button" onclick="window.location='/admin/childreport'">

                  <input type="button" name="" value="Export" class="btn btn-primary view-button" onclick="window.location='/admin/export_attack'">
              </div>   
          </div>
        </div>
 </div>
 <div class="row">
        <div class="col-md-12">  
          <div class="chardata">
          <h2>Compliance VS Technique</h2>
            
            <div id="container2" style="width: 70%; float: left;">
              <canvas id="canvas2"></canvas>
            </div> 
            <div style="width: 20%; float: left;padding-top: 10%;text-align: center;">
                  <input type="button" name="" value="View Reports List" class="btn btn-primary view-button" onclick="window.location='/admin/childreport'">

                  <input type="button" name="" value="Export" class="btn btn-primary view-button" onclick="window.location='/admin/export_techcompaliance'">
              </div>    
          </div>
        </div>
 </div>
 <?php
    
  //dd($attacks);
  
 // echo ($label_arrayout);
 //  // var_dump( $label_arrayout);
 //  dd($label_arrayout);
 ?>
  <script>
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
        data: [{{$attacks['firstyear']}},{{$attacks['secondyear']}},{{$attacks['thirdyear']}}]
      }]

    };
    //Line Chart
     
    var config = {
      type: 'line',
      data: {
        labels: ["{{date('F Y')}}","{{date('F Y', strtotime('-1 month'))}}","{{date('F Y', strtotime('-2 month'))}}","{{date('F Y', strtotime('-3 month'))}}","{{date('F Y', strtotime('-4 month'))}}","{{date('F Y', strtotime('-5 month'))}}"],
        datasets: [{
          label: 'Compliance',
          backgroundColor: window.chartColors.red,
          borderColor: window.chartColors.red,
          data: [
            {{$compliance['m_compliance_firstmonth']}},
            {{$compliance['m_compliance_secondmonth']}},
            {{$compliance['m_compliance_thirdmonth']}},
            {{$compliance['m_compliance_fourmonth']}},
            {{$compliance['m_compliance_fivemonth']}},
            {{$compliance['m_compliance_sixmonth']}}
          ],
          fill: false,
        }, {
          label: 'Technique',
          fill: false,
          backgroundColor: window.chartColors.blue,
          borderColor: window.chartColors.blue,
          data: [
            {{$technique['tech_firstmonth']}},
            {{$technique['tech_secondmonth']}},
            {{$technique['tech_thirdmonth']}},
            {{$technique['tech_fourmonth']}},
            {{$technique['tech_fivemonth']}},
            {{$technique['tech_sixmonth']}}
          ],
        }]
      },
      options: {
        responsive: true,
        title: {
          display: false,
          text: 'Chart.js Line Chart'
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
              labelString: 'Month'
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
          },
          scales: {
            yAxes: [{
                ticks: {
                    beginAtZero:true
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
      var ctx2 = document.getElementById('canvas2').getContext('2d');
      window.myLine = new Chart(ctx2, config);
      // var ctx3 = document.getElementById('chart-area').getContext('2d');
      // window.myPie = new Chart(ctx3, config);

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
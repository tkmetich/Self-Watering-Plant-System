<html>
  <head>
    <title>My IoT Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <script src='https://cdn.plot.ly/plotly-2.20.0.min.js'></script>
    <script>
      
      function startTimer() {
        getData();
        setInterval(getData, 1000);
      }
      
      function getData() {
        const results = document.getElementById("results");
        fetch("http://192.168.1.254/readings.php")
        .then((response) => {
          return response.json();
        })
        .then((json) => {
          console.log(json);
          
          var html = "<table><tr><th>Time</th><th>Temperature</th><th>Humidity</th><th>Soil Moisture</th></tr>";
          var dates = json.date;
          var temps = json.temp;
          var humids = json.humid;
          var moists = json.moist;
          
          for (var i = 0; i < 40; i++) {
            html+="<tr>";
            html+="<td>"+dates[i]+"</td>";
            html+="<td>"+temps[i]+" F</td>";
            html+="<td>"+humids[i]+"%</td>";
            html+="<td>"+moists[i]+"%</td>";
            html+="</tr>";
          }
          html+="</table>";
          results.innerHTML = html;
          
          showData(json.temp, json.humid, json.moist, json.date);
        })
        .catch((error) => {
          console.log(error);
        });
      }

      function showData(temp, humid, moist, date) {
          var trace1 = {
            x: date,
            y: temp,
            name: "Temperature",
            mode: "lines+markers",
            type: 'scatter',
            line: {
              color: "rgba(210, 0, 255, 0.75)",
              width: 3
            }
          };
         
          var trace2 = {
            x: date,
            y: humid,
            name: "Humidity",
            mode: "lines+markers",
            type: 'scatter',
            line: {
              color: "rgba(0, 255, 150, .75)",
              width: 3
            }
          };
          
          var trace3 = {
            x: date,
            y: moist,
            name: "Soil Moisture",
            mode: "lines+markers",
            type: 'scatter',
            line: {
              color: "rgba(0, 0, 225, 0.75)",
              width: 3
            }
          };

          var data = [trace1, trace2, trace3];
          var layout = {
            title: "Plant Readings"
          };
          var config = {
            responsive: true
          };

          Plotly.newPlot('chart', data, layout, config);
      }
    </script>
    
    <style>
    body {
      background-image: url("purpleteal");
      background-size: 100% 100%;
    }
   
    .container-fluid {
      padding: 100px;
      margin-top: 100px;
    }
   
    #title {
      text-align: center;
      border-style: solid;
      border-radius: 30px;
      border-width: 3px;
      border-color: blue;
      color: black;
      font-weight: bold;  
    }
   
    #button {
      text-align: center;
    }
    
    table {
      font-family: arial, sans-serif;
      border-collapse: collapse;
      width: 100%;
    }

    td, th {
      border: 1px solid black;
      text-align: left;
      padding: 8px;
      font-weight: bold;
    }

    tr:nth-child(even) {
      background-color: hot pink;
    }
    
    tr:nth-child(odd) {
      background-color: hot pink;
    }
    
    
  </style>

  </head>
  
  <body onload="startTimer()">
    <div class="container-fluid">
      <div class="row">
        
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <h1 id="title">Get Data</h1>
          <button onclick="getData()" id="button" class="btn btn-primary btn-block">Get Data</button>
        </div>
      </div>
    
      <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <h1 id="title">Results</h1>
          <div id="results"></div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
          <h1 id="title">Chart</h1>
          <div id="chart"></div>
        </div>
      </div>
      
    </div>
  </body>
</html>

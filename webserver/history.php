<?php 

$thelist="";
function scan_dir($dir) {
    $ignored = array('.', '..', '.svn', '.htaccess');

    $files = array();    
    foreach (scandir($dir) as $file) {
        if (in_array($file, $ignored)) continue;
        $files[$file] = filemtime($dir . '/' . $file);
    }

    arsort($files);
    $files = array_keys($files);

    return ($files) ? $files : false;
}
$filelist=scan_dir('./json');
$maxtoshow=96;
$showprogress=1;
foreach ($filelist as $valuefile) {
	if ($showprogress<=$maxtoshow) {
		$selectedstyle="";
		if(isset($_GET['time'])) {
			if(basename($valuefile,".json")==$_GET['time']) {$selectedstyle="style=\"background:#D9D9D9\"";};}
    $thelist .= '<li class="nav-item"><a '.$selectedstyle.'class="nav-link" href="history.php?time='.basename($valuefile,".json").'"><span data-feather="file"></span>'.date("Y-m-d H:i:s",basename($valuefile,".json")).'</a></li>';
	$showprogress++;
	}
}
?>


<?php


$files = scandir('json', SCANDIR_SORT_DESCENDING);
$newest_file = $files[0];

if(isset($_GET['time'])) {
    $thisfile = $_GET['time'].".json";
} else {
    $thisfile = $newest_file;
}


$json=json_decode(file_get_contents('./json/'.$thisfile),true);
?>


<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="favicon.ico">

    <title>Dwy NN Status</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="dashboard.css" rel="stylesheet">
  </head>

  <body>
    <nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
      <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">Dwy NN Status</a>
      <ul class="navbar-nav px-3">
      </ul>
    </nav>

    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
        <div class="sidebar-sticky">
                                        <ul id="historyul" style="list-style: none;" class="nav-item">
                                                <li><span data-feather="file-plus"></span> History
                                                        <div id="history" class="collapse">
                                                                <ul class="nav flex-column">
                                                                        <?php
                                                                        echo $thelist;
                                                                        ?>
                                                                </ul>
                                                        </div>
                                                </li>
                                        </ul>
                                </div>
	</nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
		<div class="row">
		  <div class="col-md-3" style="font-size:20px;padding:10px;"><?php echo date("Y-m-d H:i:s",basename($thisfile,".json")); ?></div>
        </div>
		  				 
          <h2>Processes</h2>
          <div class="table-responsive">
        <table class="table table-striped table-sm">
                                                <thead>
                                                        <tr>
                                                                <th>Name</th>
                                                                <th>Block</th>
                                                                <th>Headers</th>
                                                                <th>Hash</th>
                                                                <th>Lastnotarized</th>
                                                                <th>LastNotarizedHash</th>
                                                                <th>LastNotarizedTXID</th>

                                                        </tr>
                                                </thead>
                                                <tbody>
                                                        <?php foreach ($json['coin'] as $value) {
                                                                if ($value['running']==1) { echo "<tr style='background:#86C98A;'>";} else { echo "<tr style='background:#D46D6A;'>";}
                                                                echo "
                                                                <td>".$value['name']."</td>";
                                                                if ($value['running']==0) {echo "<td style='background:#D46D6A;'>";} else if ($value['block']==$value['headers']) { echo "<td style='background:#86C98A;'>";} else { echo "<td style='background:#98AACA;'>";}
                                                                echo $value['block']."</td>";
                                                                if ($value['running']==0) {echo "<td style='background:#D46D6A;'>";} else if ($value['block']==$value['headers']) { echo "<td style='background:#86C98A;'>";} else { echo "<td style='background:#98AACA;'>";}
                                                                echo $value['headers']."</td>";
                                                                if ($value['running']==0) {echo "<td style='background:#D46D6A;'>";} else if ($value['block']==$value['headers']) { echo "<td style='background:#86C98A;'>";} else { echo "<td style='background:#98AACA;'>";}
                                                                echo $value['bestblockhash']."</td>";
                                                                if ($value['running']==0) {echo "<td style='background:#D46D6A;'>";} else if ($value['block']==$value['headers']) { echo "<td style='background:#86C98A;'>";} else { echo "<td style='background:#98AACA;'>";}
                                                                echo $value['lastnotarized']."</td>";
                                                                if ($value['running']==0) {echo "<td style='background:#D46D6A;'>";} else if ($value['block']==$value['headers']) { echo "<td style='background:#86C98A;'>";} else { echo "<td style='background:#98AACA;'>";}
                                                                echo $value['lastnotarizedbh']."</td>";
                                                                if ($value['running']==0) {echo "<td style='background:#D46D6A;'>";} else if ($value['block']==$value['headers']) { echo "<td style='background:#86C98A;'>";} else { echo "<td style='background:#98AACA;'>";}
                                                                echo $value['lastnotarizedtxid']."</td>";

                                                                echo "</tr>";
                                                                }
                                                        ?>
                                                </tbody>
                                        </table>
  
	</div>
        </main>
      </div>
    </div>

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
    <script src="js/vendor/popper.min.js"></script>

 <script>
$('#historyul').on('click', function(event) {
        $('#history').collapse('toggle');
});

$('#global').on('click', function(event) {
        $('#general').collapse('toggle');
});

<?php

        if(isset($_GET['time'])) {
                echo "$('#history').collapse('toggle');";
        }


?>


        </script>

    <!-- Icons -->
    <script src="https://unpkg.com/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace()
    </script>

  </body>
</html>


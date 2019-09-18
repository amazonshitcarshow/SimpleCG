<?PHP
#ADD PAGE
echo "Creating edit page...".PHP_EOL;
$edit_page = '<?PHP
session_start();
$style_config = json_decode(file_get_contents("./config/style.json"), true);
?>

<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="style.css" />

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <a class="navbar-brand" href="#"><?PHP echo $style_config["org"] ?></a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="./list.php">List</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./add.php">Add </a>
      </li>
      <li class="nav-item active">
        <a class="nav-link" href="#">Edit <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="./admin.php">Admin</a>
      </li>
    </ul>
  </div>
</nav>

<h1 class="display-3">Update</h1>

<?PHP
$authok = false;
$auth_config = json_decode(file_get_contents("./config/auth.json"), true);
$field_config = json_decode(file_get_contents("./config/fields.json"), true);
$sql_config = json_decode(file_get_contents("./config/sql.json"),true);

$mylink = mysqli_connect($sql_config["host"], $sql_config["username"], $sql_config["password"], $sql_config["database"]);
if (!$mylink) {
    echo "Error: Could not connect to MySQL Server." . PHP_EOL;
    echo "Debug-ErrNo: " . mysqli_connect_errno() . PHP_EOL;
    echo "Debug-ErrMsg: " . mysqli_connect_error() . PHP_EOL;
    exit;
}



if(isset($_SESSION["username"]) && isset($_SESSION["password"])){
    if($_SESSION["username"] == $auth_config["admin_user"] && $_SESSION["password"] == $auth_config["admin_pw"]){
        $authok = true;
    }
}



if($authok != true){ #Redirect non-authenticated users to authentication page
    die("<meta http-equiv=refresh content='."'".'0;URL=auth.php'."'".' />");
}

$count = 1;
$buildstr = "";
$valuestr = "";
$hashstr = "";
while(array_key_exists($count,$field_config)){
    if(isset($_POST[$count])){
        if(trim($_POST[$count]) != ""){
            $buildstr = strtolower($buildstr . $field_config[$count]["name"] . " = \'".mysqli_real_escape_string($mylink, $_POST[$count])."\', ");
        }else{
            $buildstr = strtolower($buildstr . $field_config[$count]["name"] . " = \' \', ");
        }
    }
    $count = $count + 1;
}

if(strlen($buildstr)>0){
    mysqli_query($mylink, "UPDATE ".$sql_config["table"]." SET ".$buildstr."crud_overhead=\'".$_POST["data_token"]."\' WHERE crud_overhead=\'".$_POST["data_token"]."\'");
	die(\'<div class="alert alert-success" role="alert">Data edited successfully!</div>\');
}



?>
<form action="edit.php" method="post">
<?PHP
if(!isset($_GET["dt_tkn"])){
    die(\'<div class="alert alert-danger" role="alert">Data token not set!</div>\');
}

$res = mysqli_query($mylink, "SELECT * FROM ".$sql_config["table"]." WHERE crud_overhead=\'".$_GET["dt_tkn"]."\'");
$rdata = mysqli_fetch_row($res);


$count = 1;
while(array_key_exists($count,$field_config)){
    echo \'<div class="input-group mb-3">
      <div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1">\'.$field_config[$count]["name"].\'</span>
      </div>
      <input type="text" class="form-control" placeholder="\'.$field_config[$count]["name"].\'" name="\'.$count.\'" value="\'.$rdata[$count-1].\'">
    </div>
    \';
    $count = $count + 1;
}
echo \'<input type="hidden" name="data_token" value="\'.$_GET["dt_tkn"].\'">\';
?>
<button type="submit" class="btn btn-primary">Submit</button>
</form>
';
file_put_contents("./output/edit.php",$edit_page);
?>

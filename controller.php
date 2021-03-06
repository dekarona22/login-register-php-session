<?php
session_start(); //start session for the user
include( "config.php"); //include database connection
$update=false;
$errors=array();
$user="";
$pass="";
$newuser="";
$fname= $lname= $pass1= $pass2="";
$msg="";
$roles="";
$id=0;


if($_SERVER["REQUEST_METHOD"] == "POST"){ //check if request is POST method
  if (isset($_POST['Login'])) { //Check if POST login is triggered
    login();
  }
}

function login(){
  global $servername;
  global $username;
  global $password;
  global $dbname;
  global $conn;
  global $errors,$user,$pass;
$id= $username= $password= $firstname=$roles= $lastname="";
  if (empty(trim($_POST['username']))) {
    array_push($errors,"Username is required!");
  }else{
      $user=trim($_POST['username']);
  }
  if (empty(trim($_POST['password']))) {
      array_push($errors,"Password is required!");
  }else{
      $pass=trim($_POST['password']);
  }
  if(strlen($user)>0 && strlen($pass)>0 && empty($errors)){
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $param_username);
    $param_username = $user;
    if($stmt->execute()){ //if successfully executed
    $stmt->store_result();
    }
    if($stmt->num_rows == 1){
      $stmt->bind_result($id,$username,$password,$roles,$firstname,$lastname);
      $stmt->fetch();
      $pass=md5($pass);
      if($pass===$password)
       {
         session_start(); //start session for the user
         if($roles=='admin' || $roles=='Admin'){
           $_SESSION['user']=$user; //store username and id to session
           $_SESSION['userid']=$id;
             header("location: admin/admin.php");
         }else{
           session_start(); //start session for the user
           $_SESSION['user']=$user;
           $_SESSION['userid']=$id;
           $_SESSION['Username']=$username;
           header("location: welcome.php");
         }
         }else{
         array_push($errors,"Incorrect password");
       }
      }else{
      array_push($errors,"Account not found!");
    }
  }
}
function display_error(){
  global $errors;
  if(count($errors)>0){
    echo '<div class="errors">';
    foreach ($errors as $error) {
          echo $error . '<br>';
    }
    echo '</div>';
  }
}
if($_SERVER["REQUEST_METHOD"] == "POST"){
  if(isset($_POST['register'])){
    register();
  }
}

function register(){
  global $servername;
  global $username;
  global $password;
  global $dbname;
  global $conn;
  global $user, $errors;
  global $newuser,$fname,$lname,$pass1,$pass2,$msg;
  $roles="User";
if (empty(trim($_POST['firstname']))){
  array_push($errors,"First Name is required");
}else{
  $fname=trim($_POST['firstname']);
}
if (empty(trim($_POST['lastname']))){
  array_push($errors,"Last Name is required");
}else{
  $lname=trim($_POST['lastname']);
}
if (empty(trim($_POST['user']))){
  array_push($errors,"Username is required");
}else{
  $newuser=trim($_POST['user']);
}
if(!empty(trim($_POST['pass'])) && !empty(trim($_POST['confirmpass']))){
  $pass1=trim($_POST['pass']);
  $pass2=trim($_POST['confirmpass']);
  if($pass1!=$pass2){
    array_push($errors, "Password not match!");
    $match=false;
  }else {
    $match=true;
  }
}
else {
  array_push($errors, "Password is required");
}

if(empty($errors)){
  $sql_u = "SELECT * FROM users WHERE username='$newuser'";
  $res_u=$conn->query($sql_u);
  if ($res_u->num_rows > 0) {
    array_push($errors, "Username already taken");
  }else{
    $pass1=md5($pass1);
    $sql = "INSERT INTO users (roles,username, password, firstname, lastname)
    VALUES ('$roles','$newuser', '$pass1', '$fname', '$lname')";
    if ($conn->query($sql) === TRUE) {
      $msg="successfully created!";
      $_SESSION['firstname']=$fname;
      $_SESSION['lastname']=$lname;
      $_SESSION['username']=$newuser;
      $_SESSION['passw']=$pass1;

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
      }
      $conn->close();
    }
  }
}
function islogin(){
  if (empty($_SESSION['user'])){
    session_destroy(); //Remove session
    unset($_SESSION['user']); //Delete user session
    header("location: ../index.php");
  }
}
if (isset($_GET['logout'])) {
	session_destroy();
	unset($_SESSION['user']);
	header("location: ../index.php");
  echo $_SESSION['user'];
}

if(isset($_GET['delete'])){
$id=$_GET['delete'];
  $conn->query("DELETE FROM users WHERE id=$id")or die($conn->error());
  $_SESSION['message']="User Deleted";
}

if(isset($_GET['edit']) && !empty($_GET['edit'])){

  $sql="SELECT * FROM users WHERE id=?";
  if ($stmt=$conn->prepare($sql)) {
    $stmt->bind_param("i",$param_id);
    $param_id=$_GET["edit"];
      if($stmt->execute()){
        $result=$stmt->get_result();
        if ($result->num_rows==1) {
          $row = $result->fetch_array(MYSQLI_ASSOC);
          $fname=$row['firstname'];
          $lname=$row['lastname'];
          $user=$row['username'];
          $roles=$row['roles'];
          $update=true;
        }else{
          array_push($errors, $stmt->error);
        }
      } else {
        array_push($errors, "Something went wrong" . $stmt->error);
      }
    }
  }
if($_SERVER["REQUEST_METHOD"] == "POST"){
  if(isset($_POST["save"])){
    if (isset($_POST["firstname"])) {
      $fname=$_POST['firstname'];
    }else{
      array_push($errors, "First Name is required!");
    }

    if (isset($_POST["lastname"])) {
      $lname=$_POST['lastname'];
    }else{
      array_push($errors, "Last Name is required!");
    }

    if (isset($_POST["roles"])) {
      $roles=$_POST['roles'];
    }else{
      array_push($errors,"Kindly select a role");
    }

    if (isset($_POST["user"])) {
      $newuser=$_POST['user'];
    }else{
      array_push($errors,"Kindly select a role");
    }

    if (isset($_POST["password"])) {
      $pass=$_POST['password'];
    }else{
      array_push($errors,"No Password found");
    }

    if (empty($errors)) {
      $sql_u = "SELECT * FROM users WHERE username=?";
      $stmt=$conn->prepare($sql_u);
      $stmt->bind_param("s", $param_username);
      $param_username = $newuser;

      if ($stmt->execute()) {
        $stmt->store_result();

      }if($stmt->num_rows >0){
        array_push($errors, "Username is already taken");

      }else{
        $sql="INSERT INTO users (roles,username, password, firstname, lastname)
        VALUES (?,?,?,?,?)";
        $stmt=$conn->prepare($sql);
        $stmt->bind_param("sssss", $param_roles,$param_user,$param_pass,$param_fname,$param_lname);
        $param_roles = $roles;
        $param_user = $newuser;
        $param_pass = md5($pass);
        $param_fname = $fname;
        $param_lname = $lname;
        if($stmt->execute()){
          $_SESSION['message']="New account saved!";
        }else{
          array_push($errors,$stmt->error);
        }
      }
    }
  }

  if(isset($_POST['update'])){
      update();
    }
}
function update(){
  global $servername;
  global $username;
  global $password;
  global $dbname;
  global $conn;
  global $user, $errors;
  global $newuser,$fname,$lname,$pass1,$pass2,$msg;
  if(isset($_POST['firstname']) && !empty($_POST['firstname'])) {
    $fname=$_POST['firstname'];

  }else{
    array_push($errors, "First name is required");

  }

  if(isset($_POST['lastname']) && !empty($_POST['lastname'])){
    $lname=$_POST['lastname'];

  }else {
    array_push($errors, "Last Name is required");

  }
  if (isset($_POST['roles']) && !empty($_POST['roles'])) {
    $roles=$_POST['roles'];

  }else{
  array_push($errors, "No Roles selected");

  }

  if (isset($_POST['user']) && !empty($_POST['user'])) {
      $user=$_POST['user'];

  }else{
    array_push($errors, "Username is required");

  }
  if (isset($_POST['password']) && !empty($_POST['password'])) {
    $pass=$_POST['password'];

  }else{
    array_push($errors, "No password is found");

  }

  if(empty($errors)){

    $pass=md5($pass);
    echo $fname;
    $sql_u="SELECT * FROM users WHERE id=?";
    $stmt = $conn->prepare($sql_u);
    $stmt->bind_param("i", $param_id);
    $param_id=$id;
    if($stmt->execute()){
      $stmt->store_result();
    }if($stmt->num_rows == 1){
      $stmt->bind_result($id,$username,$password,$roles,$firstname,$lastname);
      $stmt->fetch();
      $pass=md5($pass);
      if($pass===$password)
      {
        if($pass1==$pass2){
          $pass=md5($pass1);
          $sql = "UPDATE users SET firstname=$fname, lastname=$lname, roles=$roles,password=$pass WHERE username=?";
          $stmt = $conn->prepare($sql);
          $conn->query($sql);
          $_SESSION['message'] = "Account updated!";
        }
        else{
          array_push($errors, "New Password not match");
        }
      }else{
        array_push($errors, "Incorrect Password");
      }
    }
  }
}

?>

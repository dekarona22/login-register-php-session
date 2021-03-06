<?php include('controller.php');

if (isset($_GET['log-out'])) {
  session_destroy();
  unset($_SESSION['user']);
  header("location: index.php");
}
if (empty($_SESSION['user'])){
  session_destroy();
  header("location: index.php");
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="welcomestyle.css">
    <title></title>
  </head>
  <body>
    <?php if (isset($_SESSION['message'])): ?>
	<div class="msg">
		<?php
			echo $_SESSION['message'];
			unset($_SESSION['message']);
		?>
	</div>
<?php endif ?>
    <div class="container">
      <div class="low-header">
        <li><a href="welcome.php?log-out='1'">log-out</a></li>
        </div>
      <div class="header">
        <?php require_once "config.php";
        $result=$conn->query("SELECT id,firstname, lastname, roles, username FROM users");
        ?>
        <div class="table-row">
          <table class="table">
            <thead>
                <tr>
                  <th>First Name</th>
                  <th>Last Name</th>
                  <th>Roles</th>
                  <th>Username</th>
                  <th colspan="4">Action</th>
              </tr>
            </thead>
            <?php
            while($row = $result->fetch_assoc()):
             ?>
             <tr>
               <td><?php echo $row['firstname']; ?></td>
               <td><?php echo $row['lastname']; ?></td>
               <td><?php echo $row['roles']; ?></td>
               <td><?php echo $row['username']; ?></td>
               <td><a href="welcome.php?edit= <?php echo $row['id']; ?>"class="edit_btn">EDIT</a></td>
               <td><a href="welcome.php?delete=<?php echo $row['id']; ?>" class="del_btn">DELETE</a></td>
             </tr>
           <?php endwhile; $result->free();  ?>
          </table>
        </div>
      </div>
          <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <div class="form-group">
              <label>First Name</label>
              <input type="text" name="firstname" value="<?php echo $fname ?>">
            </div>
            <div class="form-group">
              <label>Last Name</label>
              <input type="text" name="lastname" value="<?php echo $lname ?>">
            </div>
            <div class="form-group">
              <label>Role</label>
              <?php if($update==false): ?>
              <select name="roles">
                    <option hidden disabled selected value> -- select an option -- </option>
                    <option value="Admin">Admin</option>
                    <option value="User">User</option>
                      </select>
                  <?php else: ?>
                    <select name="roles">
                      <?php if($roles==="user" || "User"): ?>
                        <option value="<?php echo $roles; ?>"><?php echo $roles; ?></option>
                        <option value="Admin">Admin</option>
                      <?php else: ?>
                        <option value="<?php echo $roles; ?>"><?php echo $roles; ?></option>
                        <option value="User">User</option>
                      <?php endif ?>
              </select>
              <?php endif ?>
            </div>
            <div class="form-group">
              <input type="hidden" name="id" value="<?php echo $id ?>">
              <label>Username</label>
              <input type="text" name="user" value="<?php echo $user ?>">
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" name="password" placeholder="Enter your old password">
            </div>
            <div class="form-group">
              <?php if($update==true): ?>
                <button class="btn" type="submit" name="update">UPDATE</button>
              <?php else: ?>
                <button class="btn" type="submit" name="save">SAVE</button>
              <?php endif ?>
          </div><?php display_error(); ?>
      </form>
    </div>
  </body>
</html>

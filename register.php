<?php
//START
include 'config.php';

if(isset($_POST['submit'])){

   // Name validation
   $name = $_POST['name'];
   $name = filter_var($name, FILTER_SANITIZE_STRING);
   if(empty($name)){
      $message[] = 'Name is required!';
   }elseif(strlen($name) < 3){
      $message[] = 'Name must be at least 3 characters long!';
   }
 elseif (!preg_match('/^[a-zA-Z\s]+$/', $name)) {
   $message[] = 'Name should contain only letters and spaces!';
}

   // Email validation
   $email = $_POST['email'];
   $email = filter_var($email, FILTER_SANITIZE_EMAIL);
   if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
      $message[] = 'Invalid email format!';
   }

   // Password validation
   $pass = $_POST['pass'];
   $cpass = $_POST['cpass'];
   if(empty($pass) || empty($cpass)){
      $message[] = 'Password and Confirm Password are required!';
   }elseif($pass != $cpass){
      $message[] = 'Confirm password not matched!';
   }elseif(strlen($pass) < 6){
      $message[] = 'Password must be at least 6 characters long!';
   } elseif (!preg_match('/^(?=.*\d)(?=.*[a-zA-Z])(?=.*\W).{6,}$/', $pass)) {
      $message[] = 'Password must contain at least one number, one letter, one symbol, and be at least 6 characters long!';
   }else{
      $pass = md5($pass); // Hash the password
   }

   // Image validation
   $image = $_FILES['image']['name'];
   if(empty($image)){
      $message[] = 'Image is required!';
   }else{
      $image_size = $_FILES['image']['size'];
      if($image_size > 20000000000){
         $message[] = 'Image size is too large!';
      }else{
         $image_tmp_name = $_FILES['image']['tmp_name'];
         $image_folder = 'uploaded_img/'.$image;
      }
   }

   // If there are no validation errors, proceed to insert data
   if(empty($message)){
      $select = $conn->prepare("SELECT * FROM `users` WHERE email = ?");
      $select->execute([$email]);

      if($select->rowCount() > 0){
         $message[] = 'User email already exists!';
      }else{
         $insert = $conn->prepare("INSERT INTO `users`(name, email, password, image) VALUES(?,?,?,?)");
         $insert->execute([$name, $email, $pass, $image]);

         if($insert){
            move_uploaded_file($image_tmp_name, $image_folder);
            $message[] = 'Registered successfully!';
            header('location:login.php');
         }else{
            $message[] = 'Error occurred while registering!';
         }
      }
   }

}

//END
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>register</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/components.css">

</head>
<body>

<?php

if(isset($message)){
   foreach($message as $message){
      echo '
      <div class="message">
         <span>'.$message.'</span>
         <i class="fas fa-times" onclick="this.parentElement.remove();"></i>
      </div>
      ';
   }
}

?>
   
<section class="form-container">

   <form action="" enctype="multipart/form-data" method="POST">
      <h3>Register now</h3>
      <input type="text" name="name" class="box" placeholder="enter your name" required>
      <input type="email" name="email" class="box" placeholder="enter your email" required>
      <input type="password" name="pass" class="box" placeholder="enter your password" required>
      <input type="password" name="cpass" class="box" placeholder="confirm your password" required>
      <input type="file" name="image" class="box" required accept="image/jpg, image/jpeg, image/png">
      <input type="submit" value="register now" class="btn" name="submit">
      <p>Already have an account? <a href="login.php">Login now</a></p>
   </form>

</section>


</body>
</html>
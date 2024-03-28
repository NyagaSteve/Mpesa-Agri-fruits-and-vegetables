<?php

@include 'config.php';

require_once('C:\Users\steph\OneDrive\Pictures\Camera Roll\Desktop\TCPDF-main\TCPDF-main\tcpdf.php');

session_start();

$user_id = $_SESSION['user_id'];

if (!isset($user_id)) {
    header('location:login.php');
    exit(); // Ensure script stops executing after redirection
}

if (isset($_POST['order'])) {

    $name = $_POST['name'];
    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $number = $_POST['number'];
    $number = filter_var($number, FILTER_SANITIZE_STRING);
    $email = $_POST['email'];
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $method = $_POST['method'];
    $method = filter_var($method, FILTER_SANITIZE_STRING);
    $address = 'flat no. ' . $_POST['flat'] . ' ' . $_POST['street'] . ' ' . $_POST['city'] . ' ' . $_POST['state'] . ' ' . $_POST['country'] . ' - ' . $_POST['pin_code'];
    $address = filter_var($address, FILTER_SANITIZE_STRING);
    $placed_on = date('d-M-Y');

    $cart_total = 0;
    $cart_products = array(); // Changed from $cart_products[] = '';

    $cart_query = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
    $cart_query->execute([$user_id]);
    if ($cart_query->rowCount() > 0) {
        while ($cart_item = $cart_query->fetch(PDO::FETCH_ASSOC)) {
            $cart_products[] = $cart_item['name'] . ' ( ' . $cart_item['quantity'] . ' )';
            $sub_total = ($cart_item['price'] * $cart_item['quantity']);
            $cart_total += $sub_total;
        }
    }

    $total_products = implode(', ', $cart_products);

    $order_query = $conn->prepare("SELECT * FROM `orders` WHERE name = ? AND number = ? AND email = ? AND method = ? AND address = ? AND total_products = ? AND total_price = ?");
    $order_query->execute([$name, $number, $email, $method, $address, $total_products, $cart_total]);

    if ($cart_total == 0) {
        $message[] = 'your cart is empty';
    } elseif ($order_query->rowCount() > 0) {
        $message[] = 'order placed already!';
    } else {
        $insert_order = $conn->prepare("INSERT INTO `orders`(user_id, name, number, email, method, address, total_products, total_price, placed_on) VALUES(?,?,?,?,?,?,?,?,?)");
        $insert_order->execute([$user_id, $name, $number, $email, $method, $address, $total_products, $cart_total, $placed_on]);
        $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
        $delete_cart->execute([$user_id]);

        // Generate Receipt using TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Receipt');
        $pdf->SetSubject('Receipt');
        $pdf->SetKeywords('Receipt, Order');

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Write receipt content
        $receipt_content = "Name: $name\nNumber: $number\nEmail: $email\nAddress: $address\nTotal Products: $total_products\nTotal Price: $cart_total\nPlaced On: $placed_on";
        $pdf->Write(0, $receipt_content, '', 0, 'L', true, 0, false, false, 0);

        // Save PDF
        $receipt_filename = 'receipt_' . date('YmdHis') . '.pdf';
        $pdf->Output('C:\Users\steph\OneDrive\Pictures\Camera Roll\Desktop\TCPDF-main\TCPDF-main\\' . $receipt_filename, 'D',);
        $receipt_filename = 'receipt_' . date('YmdHis') . '.pdf';
        $pdf->Output('C:\Users\steph\OneDrive\Pictures\Camera Roll\Desktop\TCPDF-main\TCPDF-main\\' . $receipt_filename, 'F',);
        $message[] = 'order placed successfully!';
       // Display messages
if (!empty($message)) {
   foreach ($message as $msg) {
       echo $msg . '<br>';
   }
}
 
        $message[] = '<a href="' . $receipt_filename . '" download>Download Receipt</a>';
       
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
</head>
<body>
    <?php
        if(isset($message)){
            foreach($message as $msg){
                echo "<p>$msg</p>";
            }
        }
    ?>
</body>
</html>

?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>checkout</title>

   <!-- font awesome cdn link  -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <!-- custom css file link  -->
   <link rel="stylesheet" href="css/style.css">

</head>
<body>
   
<?php include 'header.php'; ?>

<section class="display-orders">

   <?php
      $cart_grand_total = 0;
      $select_cart_items = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
      $select_cart_items->execute([$user_id]);
      if($select_cart_items->rowCount() > 0){
         while($fetch_cart_items = $select_cart_items->fetch(PDO::FETCH_ASSOC)){
            $cart_total_price = ($fetch_cart_items['price'] * $fetch_cart_items['quantity']);
            $cart_grand_total += $cart_total_price;
   ?>
   <p> <?= $fetch_cart_items['name']; ?> <span>(<?= '$'.$fetch_cart_items['price'].'/- x '. $fetch_cart_items['quantity']; ?>)</span> </p>
   <?php
    }
   }else{
      echo '<p class="empty">your cart is empty!</p>';
   }
   ?>
   <div class="grand-total">grand total : <span>KES <?= $cart_grand_total; ?>/-</span></div>
</section>

<section class="checkout-orders">

   <form action="" method="POST">

      <h3>place your order</h3>

      <div class="flex">
         <div class="inputBox">
            <span>your name :</span>
            <input type="text" name="name" placeholder="enter your name" class="box" required>
         </div>
         <div class="inputBox">
            <span>your number :</span>
            <input type="number" name="number" placeholder="enter your number" class="box" required>
         </div>
         <div class="inputBox">
            <span>your email :</span>
            <input type="email" name="email" placeholder="enter your email" class="box" required>
         </div>
         <div class="inputBox">
            <span>payment method :</span>
            <select name="method" class="box" required>
               <option value="cash on delivery">cash on delivery</option>
               <option value="credit card">credit card</option>
               <option value="Mpesa">MPESA</option>
               <option value="paypal">paypal</option>
            </select>
         </div>
         <div class="inputBox">
            <span>address line 01 :</span>
            <input type="text" name="flat" placeholder="e.g. flat number" class="box" required>
         </div>
         <div class="inputBox">
            <span>address line 02 :</span>
            <input type="text" name="street" placeholder="e.g. street name" class="box" required>
         </div>
         <div class="inputBox">
            <span>city :</span>
            <input type="text" name="city" placeholder="e.g. Nairobi" class="box" required>
         </div>
         <div class="inputBox">
            <span>state :</span>
            <input type="text" name="state" placeholder="e.g. Olekasasi" class="box" required>
         </div>
         <div class="inputBox">
            <span>country :</span>
            <input type="text" name="country" placeholder="e.g. Kenya" class="box" required>
         </div>
         <div class="inputBox">
            <span>pin code :</span>
            <input type="number" min="0" name="pin_code" placeholder="e.g. 123456" class="box" required>
         </div>
      </div>

      <input type="submit" name="order" class="btn <?= ($cart_grand_total > 1)?'':'disabled'; ?>" value="Place order and print">
   
      <a href="mpesa.php" class="btn btn-primary">Click M-pesa payment</a>

   </form>

</section>








<?php include 'footer.php'; ?>

<script src="js/script.js"></script>

</body>
</html>

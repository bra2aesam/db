<?php

include '../components/connect.php';

session_start();

// التأكد من انه تم تسجيل دخول للادمن من خلال فحص ذاكرة السيشيين

$admin_id = $_SESSION['admin_id'];
if(!isset($admin_id)){
   header('location:admin_login.php');
};

// عند تعبئة فورم اضافة منتج جديد بالاسفل , تأكد من تعبئة البيانات التالي ثم القيام بتشفيرها


if(isset($_POST['add_product'])){

   $name = $_POST['name'];
   $name = htmlspecialchars($name, ENT_QUOTES);
   $price = $_POST['price'];
   $price = htmlspecialchars($price, ENT_QUOTES);
   $details = $_POST['details'];
   $details = htmlspecialchars($details, ENT_QUOTES);


   $category_name = $_POST['category'];

// تحميل صورة و تشفيرها

// قراءة اسم الصورة
   $image = $_FILES['image']['name'];
// قراءة حجم الصورة
   $image_size = $_FILES['image']['size'];
// تحديد المسار الموجودة فيه الصورة
   $image_tmp_name = $_FILES['image']['tmp_name'];
// تحديد المسار الجديد للصورة و تذكر انه يجب انشاء مجلد جديد مشابه للاسم المختار في المسار الجديد
   $image_folder = '../uploaded_img/'.$image;


// قراءة جميع المنتجات الموجودة في الداتابيس لتأكد من ان اسم المنتج غير متكرر , جدول المنتجات-عمود الاسم

// علامة الاستفهام تعني انتظار عنصر في فانكشين ال الاكسكيوت , اذا بدك حط المتغير مباشرة ولكن الافضل هو هاي

   $select_products = $conn->prepare("SELECT * FROM `products` WHERE name = ?");
   $select_products->execute([$name]);

// في حالة ايجاد الاسم اطبع انه المنتج موجود

   if($select_products->rowCount() > 0){
      $message[] = 'product name already exist!';

// غير ذلك , قم برفع المنتج الجديد الى قاعدة البيانات

   }else{

// القيام برفع كافة تفاصيل المنتج التي تم ادخالها و يجب التاكد من ان عدد الاعمدة مساوي لعدد البيانات المراد رفعها

      $insert_products = $conn->prepare("INSERT INTO `products`(name, details, price, image, category_id) VALUES(?,?,?,?,?)");
      $insert_products->execute([$name, $details, $price, $image, $category_name]);


// شرط للتأكد من ان حجم الصورة اقل من 2 ميجا


      if($insert_products){
         if($image_size > 2000000){
            $message[] = 'image size is too large!';
         }else{

// اذا كان حجم الصورة مسموح , انقل الصورة من المسار القديم الى المسار الجديد
            move_uploaded_file($image_tmp_name, $image_folder);
// متغير بتم عرضه دايما فوق مثل الاشعارات
            $message[] = 'new product added!';
         }

      }

   }  

};

// اذا بدك تمسح منتج

// اول اشي اتاكد من انه انكبس على كبسة الديليت 
if(isset($_GET['delete'])){

// اقرء الاي ديه الي مبعوث مع الرابط 
   $delete_id = $_GET['delete'];

// هون بدي امسح الصورة تبعت المنتج الي كبسة على الديليت تبعته
   $delete_product_image = $conn->prepare("SELECT * FROM `products` WHERE product_id = ?");
   $delete_product_image->execute([$delete_id]);
   $fetch_delete_image = $delete_product_image->fetch(PDO::FETCH_ASSOC);
   unlink('../uploaded_img/'.$fetch_delete_image['image']);

// هون بدي امسح المنتج كامل و بعدين اقله انقلني على صفحة المنتجات عشان ما اضطر اعمل ريفريش للصفحة لما احذف منتج
   $delete_product = $conn->prepare("DELETE FROM `products` WHERE product_id = ?");
   $delete_product->execute([$delete_id]);
   header('location:products.php');
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>products</title>

   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

   <link rel="stylesheet" href="../css/admin_style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    
    <!-- font awesome cdn -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
   .flex{
      font-family:'Times New Roman', Times, serif !important;
        /* font-weight: 900 !important; */
   }
   h1{
      font-family:'Times New Roman', Times, serif !important;
        font-weight: 900 !important;
   }
   table, th, td {
  font-size: large;
 }
</style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<section class="add-products">

   <h1 class="heading">Add product</h1>

   <form action="" method="post" enctype="multipart/form-data">
      <div class="flex">
         <div class="inputBox">
            <span>product name </span>
            <input type="text" class="box" required maxlength="100" placeholder="enter product name" name="name">
         </div>
         <div class="inputBox">
            <span>product price </span>
            <input type="number" min="0" class="box" required max="9999999999" placeholder="enter product price" onkeypress="if(this.value.length == 10) return false;" name="price">
         </div>
        <div class="inputBox">
            <span>product image </span>
            <input type="file" name="image" accept="image/jpg, image/jpeg, image/png, image/webp" class="box" required>
        </div>
         <div class="inputBox">
            <span>product details </span>
            <textarea name="details" placeholder="enter product details" class="box" required maxlength="500" cols="30" rows="10"></textarea>
         </div>
         <div class="inputBox">
            <span>product category</span>

         <!-- بدي اعمل قائمة فيه الكاتيجوري الي عندي كخيارات -->
            <select name="category" placeholder="enter product category" class="box" required maxlength="500" cols="60" rows="10">
               <?php
               // اول اشي استدعي كل الاعمدة الي بجدول الكاتيجوري
                     $prepare_category = $conn->prepare("SELECT * FROM `category`");
                     $prepare_category->execute();
               // هون بسأله اذا اصلا فيه بيانات بجدول الكاتيجوري اول , الفانكشن (روو كاونت ) بحسب عدد الصفوف بالجدول فلو كان صفر يعني ما فيه داتا بهادا الجدول

                     if($prepare_category->rowCount() > 0){
                        // اذا الجدول فيه داتا فبقله اقرألي هاي البيانات و اعطيها اسم الي هو فيتش_كاتيجوري
                        while($fetch_category = $prepare_category->fetch(PDO::FETCH_ASSOC)){
               ?>
               <option class="dropdown-item" name="category">
                     <?php 
                     // جوا تاج الاوبشن بقله اطبعلي الاي دييه لكل كاتيجوري بالاضافة لاسمها و بسكر التاج بعيدها
                     echo $fetch_category['category_id'] . "/" . $fetch_category['category_name']; 
                     ?>
               </option>
               <!-- هون بتكون جملة اللوب الاولى تبعت الوايل خلصت , فبرجع بلف كمان مرة و بطلع الكاتيجوري الثانية و هيك -->
               <?php 
               // هاي جملة الايلس تبعت في حال كان عدد الصفوف بالجدول يساوي صفر , طبعا اول قوس كيرلي هو تسكيرة قوس الوايل لانه لازم يكون بعد تاج الاوبشن حتى ما يصير فيه مشاكل بعرض الداتا
                     } } else { echo 'There is no category. Please create one first.';} 
            ?>    
            </select>
         </div>
      </div>
      
      <input type="submit" value="add product" class="option-btn" name="add_product">
   </form>

</section>
<!-- ___________________show-products_________________________ -->
<section class="show-products">

<!-- بدي اعرض حاليا كل المنتجات الي عندي مع الاشياء الخاصة فيها : الاسم - السعر - الصورة - التفاصيل - الكاتيجوري -->

   <h1 class="heading">Products Added</h1>
   <?php
// $sql = "SELECT products.product_id,products.name,products.price,products.price_discount,products.image,products.description, category.category_name
// FROM products
// INNER JOIN category ON products.product_id=category.category_id";

// $select_products = $conn->prepare($sql);
//       $select_products->execute();
// $data = $select_products->fetchAll(PDO::FETCH_ASSOC);

      ?>
<div class="col-lg-12 m-auto ">
                    <div class="table-responsive table--no-card m-b-30">
                        <table class="table table-borderless table-striped table-earning">  <thead>
    <tr>
      <th scope="col">Id</th>
      <th scope="col">Image</th>

      <th scope="col">Description</th>
      
      <th scope="col">Name</th>
      <th scope="col">Price</th>
      <th scope="col">Price_discount</th>
      <th scope="col">Add Sale</th>
      <th scope="col">Remove Sale</th>
      <th scope="col">Catecgory</th>
   
      <th scope="col">Edit</th>
      <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
  <?PHP $select_products = $conn->prepare("SELECT * FROM `products`");
      $select_products->execute();
      if($select_products->rowCount() > 0){
         while($fetch_products = $select_products->fetch(PDO::FETCH_ASSOC)){ 
            $i=0;
      ?>
    <tr>
      <td ><?php echo $fetch_products['product_id'];?></td>
      <td ><img src="../uploaded_img/<?= $fetch_products['image']; ?>" alt="" width="60px"></td>
      <td ><span><?= $fetch_products['details']; ?></td>
      <td ><?php echo $fetch_products['name'];?></td>
      <td >JD<span><?= $fetch_products['price']; ?></td>
      <td style="text-align:center;">JD<span><?= $fetch_products['price_discount']; ?></td>
      <td style="text-align:center;"><a style="color:green;" href="add_sale.php?sale=<?= $fetch_products['product_id']; ?>"><i class="fa-solid fa-square-plus"></i></a></td>
      <td style="text-align:center;"> <a href="remove_sale.php?removeSale=<?= $fetch_products['product_id']; ?>"><i class="fa-solid fa-square-minus delete1"></i></a></td>
      <?php $product_category = $conn->prepare("SELECT * 
                                        FROM `products`
                                        INNER JOIN `category` ON products.category_id = category.category_id");
                  $product_category->execute();
                  if($product_category->rowCount() > 0){
                     while($fetch_product_category = $product_category->fetch(PDO::FETCH_ASSOC)){ 
                        if($i==0 && $fetch_products['category_id'] == $fetch_product_category['category_id'] ){
                        $i++;
            ?>
      <td ><?php echo $fetch_product_category['category_name'];?></td>
      <?php 
                        }
                     }
                  }
            ?>
      <td style="text-align:center;">
         <button type="button" class="s-s"><a class="editbtn" href="update_product.php?update=<?= $fetch_products['product_id']; ?>" ><i class="fa-solid fa-pen-to-square "></i></a></td>
      <td style="text-align:center;">   
         <button type="button" class="s-s"><a style="color:black; text-align:center;" href="products.php?delete=<?= $fetch_products['product_id']; ?>"  onclick="return confirm('delete this product?');"><i class="fa-solid fa-trash delete1"></i></a>
      </td>
    </tr>
    <?php
         }
      }else{
         echo '<p class="empty">no products added yet!</p>';
      }
   ?>
  </tbody>
</table>
<iv>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>



</section>
<!-- ________________ahmad_____________________ -->
<






<script src="../js/admin_script.js"></script>
   
</body>
</html>
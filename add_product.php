<?php
  $page_title = 'Add Product';
  require_once('includes/load.php');
  // Check what level user has permission to view this page
  page_require_level(2);

  if(isset($_POST['add_product'])) {
    $name         = $db->escape($_POST['name']);
    $quantity     = (int) $_POST['quantity'];   // integer
    $buy_price    = (float) $_POST['buy_price']; // decimal
    $sale_price   = (float) $_POST['sale_price']; // decimal
    $categorie_id = (int) $_POST['categorie_id']; // integer
    $date         = make_date();

    // Build query (without photo)
    $query = "INSERT INTO products (name, quantity, buy_price, sale_price, categorie_id, date) 
              VALUES ('{$name}', {$quantity}, {$buy_price}, {$sale_price}, {$categorie_id}, '{$date}')";

    if($db->query($query)) {
      $session->msg("s", "Product added successfully!");
      redirect('add_product.php', false);
    } else {
      $session->msg("d", "Failed to add product. Error: " . $db->error);
      redirect('add_product.php', false);
    }
  }
?>

<?php include_once('layouts/header.php'); ?>
<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>
<div class="row">
  <div class="col-md-7">
    <div class="panel panel-default">
      <div class="panel-heading">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Add New Product</span>
       </strong>
      </div>
      <div class="panel-body">
        <form method="post" action="add_product.php">
          
          <div class="form-group">
            <label>Product Name</label>
            <input type="text" class="form-control" name="name" required>
          </div>

          <div class="form-group">
            <label>Quantity</label>
            <input type="number" class="form-control" name="quantity" required>
          </div>

          <div class="form-group">
            <label>Buying Price</label>
            <input type="number" class="form-control" name="buy_price" step="0.01" required>
          </div>

          <div class="form-group">
            <label>Selling Price</label>
            <input type="number" class="form-control" name="sale_price" step="0.01" required>
          </div>

          <div class="form-group">
            <label>Category</label>
            <select class="form-control" name="categorie_id" required>
              <option value="">Select a category</option>
              <?php foreach (find_all('categories') as $cat): ?>
                <option value="<?php echo (int)$cat['id']; ?>">
                  <?php echo remove_junk($cat['name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include_once('layouts/footer.php'); ?>

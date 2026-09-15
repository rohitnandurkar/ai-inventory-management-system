<?php
  $page_title = 'All Products';
  require_once('includes/load.php');
  // Checkin What level user has permission to view this page
  page_require_level(2);
  $products = join_product_table();
?>
<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Product List</span>
        </strong>
        <a href="add_product.php" class="btn btn-info pull-right">Add New</a>
      </div>
      <div class="panel-body">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th class="text-center" style="width: 50px;">#</th>
              <th> Name </th>
              <th class="text-center" style="width: 15%;"> Category </th>
              <th class="text-center" style="width: 10%;"> Quantity </th>
              <th class="text-center" style="width: 10%;"> Buy Price </th>
              <th class="text-center" style="width: 10%;"> Sale Price </th>
              <th class="text-center" style="width: 15%;"> Added On </th>
              <th class="text-center" style="width: 100px;"> Actions </th>
            </tr>
          </thead>
          <tbody>
            <?php $count = 1; ?>
            <?php foreach ($products as $product): ?>
              <tr>
                <td class="text-center"><?php echo $count++; ?></td>
                <td><?php echo remove_junk($product['name']); ?></td>
                <td class="text-center"><?php echo remove_junk($product['categorie']); ?></td>
                <td class="text-center"><?php echo (int)$product['quantity']; ?></td>
                <td class="text-center"><?php echo number_format($product['buy_price'], 2); ?></td>
                <td class="text-center"><?php echo number_format($product['sale_price'], 2); ?></td>
                <td class="text-center"><?php echo read_date($product['date']); ?></td>
                <td class="text-center">
                  <div class="btn-group">
                    <a href="edit_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-xs btn-warning" data-toggle="tooltip" title="Edit">
                      <i class="glyphicon glyphicon-pencil"></i>
                    </a>
                    <a href="delete_product.php?id=<?php echo (int)$product['id'];?>" class="btn btn-xs btn-danger" data-toggle="tooltip" title="Delete">
                      <i class="glyphicon glyphicon-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>

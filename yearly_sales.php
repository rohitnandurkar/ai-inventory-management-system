<?php
  $page_title = 'Yearly Sales Report';
  require_once('includes/load.php');
  page_require_level(3);

  $sales = [];
  $selected_year = date('Y'); // Default current year

  if(isset($_POST['year'])){
    $selected_year = (int)$_POST['year'];
    $sales = yearlySales($selected_year);
  }
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-12">
    <?php echo display_msg($msg); ?>
  </div>
</div>

<div class="row">
  <div class="col-md-6">
    <form method="post" action="">
      <div class="form-group">
        <label for="year">Select Year</label>
        <select class="form-control" name="year" required>
          <?php
            $currentYear = date("Y");
            for ($y = $currentYear; $y >= $currentYear - 10; $y--): 
          ?>
            <option value="<?php echo $y; ?>" <?php if($selected_year == $y) echo "selected"; ?>>
              <?php echo $y; ?>
            </option>
          <?php endfor; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Show Report</button>
    </form>
  </div>
</div>

<?php if(!empty($sales)): ?>
  <div class="row">
    <div class="col-md-12">
      <div class="panel panel-default">
        <div class="panel-heading">
          <strong>
            <span class="glyphicon glyphicon-th"></span>
            <span>Yearly Sales Report (<?php echo $selected_year; ?>)</span>
          </strong>
        </div>
        <div class="panel-body">
          <table class="table table-bordered table-striped table-hover">
            <thead>
  <tr>
    <th class="text-center">#</th>
    <th>Product Name</th>
    <th>Quantity sold</th>
    <th>Total</th>
    <th>Date</th>
  </tr>
</thead>
<tbody>
  <?php $i=1; $grandTotal=0; foreach($sales as $sale): ?>
    <tr>
      <td class="text-center"><?php echo $i++; ?></td>
      <td><?php echo remove_junk($sale['name']); ?></td>
      <td><?php echo (int)$sale['qty']; ?></td>
      <td>$<?php echo number_format($sale['totalSale'],2); $grandTotal += $sale['totalSale']; ?></td>
      <td><?php echo date("Y-m-d", strtotime($sale['date'])); ?></td>
    </tr>
  <?php endforeach; ?>
  <tr>
    <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
    <td><strong>$<?php echo number_format($grandTotal,2); ?></strong></td>
  </tr>
</tbody>

          </table>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

<?php include_once('layouts/footer.php'); ?>

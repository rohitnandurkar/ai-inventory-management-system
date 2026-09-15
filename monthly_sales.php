<?php
  $page_title = 'Monthly Sales Report';
  require_once('includes/load.php');
  page_require_level(1);

  $sales = array();
  $grand_total = 0;

  if (isset($_POST['submit_monthly'])) {
    $year  = $_POST['year'];
    $month = $_POST['month'];
    $sales = monthlySales($year, $month);

    // Calculate Grand Total
    foreach ($sales as $sale) {
      $grand_total += $sale['price'];
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
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>Monthly Sales Report</span>
        </strong>
      </div>
      <div class="panel-body">

        <!-- Month + Year Selection Form -->
        <form method="post" action="monthly_sales.php" class="form-inline">
          <div class="form-group">
            <label>Month:</label>
            <select name="month" class="form-control" required>
              <option value="">--Select Month--</option>
              <?php for($m=1;$m<=12;$m++): ?>
                <option value="<?php echo $m; ?>" 
                  <?php if(isset($month) && $month == $m) echo 'selected'; ?>>
                  <?php echo date("F", mktime(0,0,0,$m,1)); ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Year:</label>
            <select name="year" class="form-control" required>
              <option value="">--Select Year--</option>
              <?php for($y=date("Y"); $y>=2000; $y--): ?>
                <option value="<?php echo $y; ?>" 
                  <?php if(isset($year) && $year == $y) echo 'selected'; ?>>
                  <?php echo $y; ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <button type="submit" name="submit_monthly" class="btn btn-primary">Generate</button>
        </form>
        <hr>

        <!-- Report Table -->
        <?php if (!empty($sales)): ?>
          <table class="table table-bordered table-striped table-hover">
            <thead>
              <tr>
                <th class="text-center" style="width: 50px;">#</th>
                <th> Product Name </th>
                <th class="text-center" style="width: 15%;"> Quantity Sold </th>
                <th class="text-center" style="width: 15%;"> Total </th>
                <th class="text-center" style="width: 15%;"> Date </th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($sales as $sale): ?>
                <tr>
                  <td class="text-center"><?php echo count_id();?></td>
                  <td><?php echo remove_junk($sale['name']); ?></td>
                  <td class="text-center"><?php echo (int)$sale['qty']; ?></td>
                  <td class="text-center"><?php echo number_format($sale['price'], 2); ?></td>
                  <td class="text-center"><?php echo date("Y-m-d", strtotime($sale['date'])); ?></td>
                </tr>
              <?php endforeach; ?>
              <!-- Grand Total Row -->
              <tr>
                <td colspan="3" class="text-right"><strong>Grand Total:</strong></td>
                <td class="text-center"><strong><?php echo number_format($grand_total, 2); ?></strong></td>
                <td></td>
              </tr>
            </tbody>
          </table>
        <?php elseif(isset($_POST['submit_monthly'])): ?>
          <div class="alert alert-info">No sales found for the selected month.</div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>

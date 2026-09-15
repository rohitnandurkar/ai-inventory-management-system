<?php
  $page_title = 'Sales Report';
  require_once('includes/load.php');
  page_require_level(1);

  $sales = array();

  if(isset($_POST['submit'])) {
    $start_date = $_POST['start_date'];
    $end_date   = $_POST['end_date'];
    $sales = find_sales_by_dates($start_date, $end_date);
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
    <form method="post" action="sales_report.php" class="form-inline">
      <div class="form-group">
        <label>Start Date:</label>
        <input type="date" name="start_date" class="form-control" required>
      </div>
      <div class="form-group">
        <label>End Date:</label>
        <input type="date" name="end_date" class="form-control" required>
      </div>
      <button type="submit" name="submit" class="btn btn-primary">Generate Report</button>
    </form>
  </div>
</div>
<div class="row">
  <div class="col-md-12">
    <?php if(!empty($sales)): ?>
      <div class="panel-body">
    <table class="table table-bordered table-striped table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Product Name</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Date</th>
        </tr>
      </thead>
      </div>
      <tbody>
        <?php foreach ($sales as $sale): ?>
          <tr>
            <td class="text-center"><?php echo count_id(); ?></td>
            <td><?php echo remove_junk($sale['name']); ?></td>
            <td><?php echo (int)$sale['qty']; ?></td>
            <td>$<?php echo (int)$sale['price']; ?></td>
            <td><?php echo date("Y-m-d", strtotime($sale['date'])); ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p class="alert alert-info">No sales found for the selected date range.</p>
    <?php endif; ?>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>


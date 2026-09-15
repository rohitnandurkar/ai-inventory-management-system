<?php
  $page_title = 'Add Sale';
  require_once('includes/load.php');
  // Permission check
  page_require_level(3);

  // Handle final form submit (add sale)
  if (isset($_POST['add_single_sale'])) {
    if (!empty($_POST['s_id'])) {
      $success_count = 0;
      foreach ($_POST['s_id'] as $key => $id) {
        $p_id    = $db->escape((int)$id);
        $s_qty   = isset($_POST['quantity'][$key]) ? (int)$_POST['quantity'][$key] : 0;
        $s_price = isset($_POST['price'][$key]) ? $db->escape($_POST['price'][$key]) : 0;
        $s_total = isset($_POST['total'][$key]) ? $db->escape($_POST['total'][$key]) : 0;
        $s_date  = isset($_POST['date'][$key]) ? $db->escape($_POST['date'][$key]) : make_date();

        $sql  = "INSERT INTO sales (product_id, qty, price, date) VALUES (";
        $sql .= "'{$p_id}','{$s_qty}','{$s_total}','{$s_date}')";
        if ($db->query($sql)) {
          update_product_qty($s_qty, $p_id);
          $success_count++;
        }
      }

      if ($success_count > 0) {
        $session->msg('s', "Sale(s) added successfully.");
      } else {
        $session->msg('d', "Failed to add sale(s).");
      }
      redirect('add_sale.php', false);
    } else {
      $session->msg('d', "No product selected.");
      redirect('add_sale.php', false);
    }
  }
?>

<?php include_once('layouts/header.php'); ?>

<div class="row">
  <div class="col-md-6">
    <?php echo display_msg($msg); ?>
    <!-- Search form (we prevent its default submit in JS) -->
    <form id="sug-form" autocomplete="off">
      <div class="form-group" style="position:relative;">
        <div class="input-group">
          <span class="input-group-btn">
            <button type="submit" class="btn btn-primary">Find It</button>
          </span>
          <!-- NOTE: name is product_name which ajax.php expects -->
          <input type="text" id="sug_input" class="form-control" name="product_name" placeholder="Search for product name" autocomplete="off">
        </div>
        <div id="result" class="list-group" style="position:absolute;z-index:2000;width:100%;display:none;"></div>
      </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="panel panel-default">
      <div class="panel-heading clearfix">
        <strong>
          <span class="glyphicon glyphicon-th"></span>
          <span>SALE EDIT</span>
        </strong>
      </div>
      <div class="panel-body">
        <!-- This form will submit the sale(s) to this same page -->
        <form method="post" action="add_sale.php">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Date</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="product_info"></tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include_once('layouts/footer.php'); ?>

<!-- jQuery (if your header/footer already includes jQuery, you can remove this line) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Client JS: search suggestions, selecting product, and auto-calc total -->
<script>
$(function(){

  // Prevent form submit for the search form (we use AJAX)
  $("#sug-form").on("submit", function(e){
    e.preventDefault();
  });

  // Live search (auto-suggestion)
  $("#sug_input").on("input", function(){
    var q = $(this).val();
    if (q.length > 0) {
      $.post("ajax.php", { product_name: q }, function(html){
        // Server now returns raw HTML (not JSON) — put it directly
        $("#result").html(html).show();
      }).fail(function(xhr){
        console.error("Search request failed:", xhr.responseText);
      });
    } else {
      $("#result").hide().empty();
    }
  });

  // When user clicks a suggestion, fetch product row
  $("#result").on("click", "li.list-group-item", function(){
    var name = $(this).data('name') || $(this).text();
    fill_product(name);
  });

  // Also hide suggestions if clicked outside
  $(document).on("click", function(e){
    if (!$(e.target).closest("#sug-input, #result, #sug-form").length) {
      $("#result").hide();
    }
  });

  // Fill product into table (calls ajax.php with p_name)
  function fill_product(name) {
    $("#sug_input").val(name);
    $("#result").hide().empty();

    $.post("ajax.php", { p_name: name }, function(html){
      $("#product_info").html(html);
      attachListeners(); // attach events after row added
      recalcAllRows();   // ensure totals are correct on initial load
    }).fail(function(xhr){
      console.error("Fetch product info failed:", xhr.responseText);
    });
  }

  // When qty or price change, recalc that row's total
  function attachListeners(){
    // delegated handler for any dynamically added rows
    $("#product_info").off("input.calc").on("input.calc", "input[name='quantity[]'], input[name='price[]']", function(){
      var row = $(this).closest("tr");
      var qty = parseFloat(row.find("input[name='quantity[]']").val()) || 0;
      var price = parseFloat(row.find("input[name='price[]']").val()) || 0;
      var total = qty * price;
      // Update total field (keep 2 decimals)
      row.find("input[name='total[]']").val(total.toFixed(2));
    });
  }

  // Recalculate all rows (useful immediately after injecting the row)
  function recalcAllRows(){
    $("#product_info tr").each(function(){
      var row = $(this);
      var qty = parseFloat(row.find("input[name='quantity[]']").val()) || 0;
      var price = parseFloat(row.find("input[name='price[]']").val()) || 0;
      row.find("input[name='total[]']").val((qty * price).toFixed(2));
    });
  }

});
</script>

<?php
  $page_title = 'Edit sale';
  require_once('includes/load.php');

  // Check what level user has permission to view this page
  page_require_level(3);
?>

<?php
  // Find sale
  $sale = find_by_id('sales', (int)$_GET['id']);

  if(!$sale){
    $session->msg("d", "Missing sale id.");
    redirect('sales.php');
  }

  // Find product related to this sale
  $product = find_by_id('products', $sale['product_id']);

  if(!$product){
    $session->msg("d", "Product not found.");
    redirect('sales.php');
  }
?>

<?php

  if(isset($_POST['update_sale'])){

    $req_fields = array(
      'title',
      'quantity',
      'price',
      'total',
      'date'
    );

    validate_fields($req_fields);

    if(empty($errors)){

      $p_id = (int)$product['id'];

      $s_qty = (int)$_POST['quantity'];

      $s_total = $db->escape($_POST['total']);

      $date = $db->escape($_POST['date']);

      $s_date = date("Y-m-d", strtotime($date));


      /*
       * ----------------------------------------------------
       * Get old quantity before updating the sale
       * ----------------------------------------------------
       */
      $old_qty = (int)$sale['qty'];

      /*
       * ----------------------------------------------------
       * New quantity entered by user
       * ----------------------------------------------------
       */
      $new_qty = $s_qty;

      /*
       * ----------------------------------------------------
       * Calculate difference
       *
       * Example:
       *
       * Old = 5
       * New = 8
       * Difference = +3
       *
       * Stock decreases by 3
       *
       * Old = 8
       * New = 4
       * Difference = -4
       *
       * Stock increases by 4
       * ----------------------------------------------------
       */
      $difference = $new_qty - $old_qty;


      /*
       * ----------------------------------------------------
       * Update sale
       * ----------------------------------------------------
       */
      $sql  = "UPDATE sales SET ";
      $sql .= "product_id='{$p_id}',";
      $sql .= "qty={$new_qty},";
      $sql .= "price='{$s_total}',";
      $sql .= "date='{$s_date}'";
      $sql .= " WHERE id='{$sale['id']}'";

      $result = $db->query($sql);


      /*
       * ----------------------------------------------------
       * If sale was successfully updated,
       * adjust product stock using ONLY the difference
       * ----------------------------------------------------
       */
      if($result){

        if($difference != 0){

          adjust_product_qty(
            $difference,
            $p_id
          );

        }

        $session->msg(
          's',
          "Sale updated successfully."
        );

        redirect(
          'edit_sale.php?id=' . (int)$sale['id'],
          false
        );

      } else {

        $session->msg(
          'd',
          "Sorry, failed to update sale!"
        );

        redirect(
          'sales.php',
          false
        );
      }

    } else {

      $session->msg(
        "d",
        $errors
      );

      redirect(
        'edit_sale.php?id=' . (int)$sale['id'],
        false
      );
    }
  }

?>

<?php include_once('layouts/header.php'); ?>

<div class="row">

  <div class="col-md-6">

    <?php echo display_msg($msg); ?>

  </div>

</div>


<div class="row">

  <div class="col-md-12">

    <div class="panel">

      <!-- Panel Header -->
      <div class="panel-heading clearfix">

        <strong>

          <span class="glyphicon glyphicon-th"></span>

          <span>Edit Sale</span>

        </strong>

        <div class="pull-right">

          <a
            href="sales.php"
            class="btn btn-primary"
          >
            <span class="glyphicon glyphicon-list"></span>
            Show All Sales
          </a>

        </div>

      </div>


      <!-- Panel Body -->
      <div class="panel-body">

        <table class="table table-bordered">

          <thead>

            <tr>

              <th>Product Title</th>

              <th>Qty</th>

              <th>Price</th>

              <th>Total</th>

              <th>Date</th>

              <th>Action</th>

            </tr>

          </thead>


          <tbody id="product_info">

            <tr>

              <form
                method="post"
                action="edit_sale.php?id=<?php echo (int)$sale['id']; ?>"
              >

                <!-- Product -->
                <td id="s_name">

                  <input
                    type="text"
                    class="form-control"
                    id="sug_input"
                    name="title"
                    value="<?php echo remove_junk($product['name']); ?>"
                  >

                  <div
                    id="result"
                    class="list-group"
                  ></div>

                </td>


                <!-- Quantity -->
                <td id="s_qty">

                  <input
                    type="number"
                    min="1"
                    class="form-control"
                    name="quantity"
                    value="<?php echo (int)$sale['qty']; ?>"
                    required
                  >

                </td>


                <!-- Price -->
                <td id="s_price">

                  <input
                    type="text"
                    class="form-control"
                    name="price"
                    value="<?php echo remove_junk($product['sale_price']); ?>"
                    readonly
                  >

                </td>


                <!-- Total -->
                <td>

                  <input
                    type="text"
                    class="form-control"
                    name="total"
                    value="<?php echo remove_junk($sale['price']); ?>"
                    required
                  >

                </td>


                <!-- Date -->
                <td id="s_date">

                  <input
                    type="date"
                    class="form-control datepicker"
                    name="date"
                    data-date-format=""
                    value="<?php echo remove_junk($sale['date']); ?>"
                    required
                  >

                </td>


                <!-- Action -->
                <td>

                  <button
                    type="submit"
                    name="update_sale"
                    class="btn btn-primary"
                  >

                    <span class="glyphicon glyphicon-ok"></span>

                    Update Sale

                  </button>

                </td>

              </form>

            </tr>

          </tbody>

        </table>

      </div>

    </div>

  </div>

</div>


<?php include_once('layouts/footer.php'); ?>
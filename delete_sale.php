<?php
  require_once('includes/load.php');

  // Check user permission
  page_require_level(3);
?>

<?php

  // Get the sale
  $d_sale = find_by_id(
    'sales',
    (int)$_GET['id']
  );

  // Sale not found
  if(!$d_sale){

    $session->msg(
      "d",
      "Missing sale id."
    );

    redirect('sales.php');
  }


  // Product ID from sale
  $product_id = (int)$d_sale['product_id'];

  // Quantity that was sold
  $sale_qty = (int)$d_sale['qty'];


  /*
   * ----------------------------------------------------
   * Delete the sale
   * ----------------------------------------------------
   */

  $delete_id = delete_by_id(
    'sales',
    (int)$d_sale['id']
  );


  /*
   * ----------------------------------------------------
   * If sale was deleted successfully,
   * restore the sold quantity to inventory.
   * ----------------------------------------------------
   */

  if($delete_id){

    $sql = "UPDATE products
            SET quantity = quantity + {$sale_qty}
            WHERE id = {$product_id}";

    $restore_stock = $db->query($sql);


    if($restore_stock){

      $session->msg(
        "s",
        "Sale deleted successfully and stock restored."
      );

    } else {

      $session->msg(
        "d",
        "Sale deleted, but stock could not be restored."
      );

    }

    redirect('sales.php');

  } else {

    $session->msg(
      "d",
      "Sale deletion failed."
    );

    redirect('sales.php');
  }

?>
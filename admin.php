<?php
  $page_title = 'Admin Home Page';

  require_once('includes/load.php');

  // Check what level user has permission to view this page
  page_require_level(1);


  /* =========================================================
     EXISTING DASHBOARD DATA
  ========================================================= */

  $c_categorie     = count_by_id('categories');
  $c_product       = count_by_id('products');
  $c_sale          = count_by_id('sales');
  $c_user          = count_by_id('users');

  $products_sold   = find_higest_saleing_product('10');
  $recent_products = find_recent_product_added('5');
  $recent_sales    = find_recent_sale_added('5');


  /* =========================================================
     AI INVENTORY DATA
  ========================================================= */

  $ai_products = array();

  $ai_low_stock = 0;
  $ai_reorder = 0;
  $ai_inventory_units = 0;

  $ai_script = __DIR__ .
               DIRECTORY_SEPARATOR .
               'ML' .
               DIRECTORY_SEPARATOR .
               'ml_engine.py';


  /* ---------------------------------------------------------
     Calculate total inventory units from existing products
  --------------------------------------------------------- */

  foreach ($recent_products as $rp) {
      // Existing recent products are already loaded.
      // Total inventory is calculated separately below.
  }


  $all_inventory_products = find_all('products');

  foreach ($all_inventory_products as $inventory_product) {

      $ai_inventory_units +=
          (int)$inventory_product['quantity'];
  }


  /* ---------------------------------------------------------
     Run AI engine for all products
  --------------------------------------------------------- */

  $ai_command = 'python ' .
                escapeshellarg($ai_script) .
                ' 0';

  $ai_output = shell_exec($ai_command);

  if ($ai_output) {

      $ai_result = json_decode(
          $ai_output,
          true
      );

      if (
          $ai_result &&
          isset($ai_result['success']) &&
          $ai_result['success']
      ) {

          if (
              isset($ai_result['products'])
          ) {

              $ai_products =
                  $ai_result['products'];
          }

          if (
              isset($ai_result['summary'])
          ) {

              $ai_low_stock =
                  (int)$ai_result['summary']['low_stock'];

              $ai_reorder =
                  (int)$ai_result['summary']['reorder_products'];
          }
      }
  }


  /* =========================================================
     AI HELPER FUNCTIONS
  ========================================================= */

  function admin_ai_risk_class($risk)
  {
      switch ($risk) {

          case 'Out of Stock':
              return 'admin-risk-out';

          case 'Critical':
              return 'admin-risk-critical';

          case 'Low':
              return 'admin-risk-low';

          case 'Medium':
              return 'admin-risk-medium';

          case 'Healthy':
              return 'admin-risk-healthy';

          default:
              return 'admin-risk-unknown';
      }
  }


  function admin_ai_trend_class($trend)
  {
      switch ($trend) {

          case 'Increasing':
              return 'admin-trend-up';

          case 'Decreasing':
              return 'admin-trend-down';

          default:
              return 'admin-trend-stable';
      }
  }


  function admin_ai_trend_symbol($trend)
  {
      switch ($trend) {

          case 'Increasing':
              return '↑';

          case 'Decreasing':
              return '↓';

          default:
              return '→';
      }
  }


  /* ---------------------------------------------------------
     Sort AI products by risk
  --------------------------------------------------------- */

  $admin_risk_priority = array(
      'Out of Stock' => 1,
      'Critical' => 2,
      'Low' => 3,
      'Medium' => 4,
      'Healthy' => 5,
      'No Data' => 6
  );


  usort(
      $ai_products,
      function ($a, $b)
      use ($admin_risk_priority) {

          $ra = isset(
              $admin_risk_priority[$a['risk']]
          )
              ? $admin_risk_priority[$a['risk']]
              : 99;

          $rb = isset(
              $admin_risk_priority[$b['risk']]
          )
              ? $admin_risk_priority[$b['risk']]
              : 99;

          return $ra - $rb;
      }
  );


  /* ---------------------------------------------------------
     Get top 5 products requiring attention
  --------------------------------------------------------- */

  $ai_attention_products = array();

  foreach ($ai_products as $ai_product) {

      if (
          $ai_product['risk'] != 'Healthy' &&
          $ai_product['risk'] != 'No Data'
      ) {

          $ai_attention_products[] =
              $ai_product;
      }

      if (
          count($ai_attention_products) >= 5
      ) {

          break;
      }
  }


  /* ---------------------------------------------------------
     Get top performing products
  --------------------------------------------------------- */

  $ai_top_products = $ai_products;

  usort(
      $ai_top_products,
      function ($a, $b) {

          return
              $b['total_sales']
              -
              $a['total_sales'];
      }
  );

  $ai_top_products =
      array_slice(
          $ai_top_products,
          0,
          5
      );

?>

<?php include_once('layouts/header.php'); ?>


<style>

/* =========================================================
   AI ADMIN DASHBOARD
========================================================= */

.admin-ai-wrapper {
    margin-top: 5px;
    margin-bottom: 25px;
}


/* Header */

.admin-ai-header {
    background: linear-gradient(
        135deg,
        #16213e,
        #3f68b5
    );

    color: #fff;

    border-radius: 10px;

    padding: 20px;

    margin-bottom: 15px;

    box-shadow:
        0 4px 15px
        rgba(0,0,0,0.10);
}

.admin-ai-header h3 {
    margin: 0 0 5px 0;

    font-weight: 600;
}

.admin-ai-header p {
    margin: 0;

    font-size: 12px;

    opacity: .82;
}

.admin-ai-link {
    display: inline-block;

    margin-top: 12px;

    padding: 7px 13px;

    border-radius: 5px;

    background:
        rgba(255,255,255,.13);

    color: #fff;

    font-size: 12px;

    text-decoration: none;
}

.admin-ai-link:hover {
    color: #fff;

    background:
        rgba(255,255,255,.22);

    text-decoration: none;
}


/* KPI */

.admin-ai-kpi {
    background: #fff;

    border: 1px solid #e8ebef;

    border-radius: 8px;

    padding: 15px;

    min-height: 105px;

    margin-bottom: 15px;

    position: relative;

    overflow: hidden;
}

.admin-ai-kpi-title {
    color: #858d97;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;
}

.admin-ai-kpi-value {
    font-size: 25px;

    font-weight: 700;

    margin-top: 7px;
}

.admin-ai-kpi-sub {
    color: #9299a2;

    font-size: 10px;

    margin-top: 3px;
}

.admin-ai-kpi-icon {
    position: absolute;

    right: 15px;

    top: 15px;

    font-size: 30px;

    opacity: .09;
}


/* Section */

.admin-ai-panel {
    background: #fff;

    border:
        1px solid #e8ebef;

    border-radius: 8px;

    overflow: hidden;

    margin-bottom: 15px;
}

.admin-ai-panel-title {
    padding: 14px 16px;

    border-bottom:
        1px solid #edf0f2;

    font-weight: 600;

    font-size: 13px;
}

.admin-ai-panel-body {
    padding: 0;
}


/* Risk */

.admin-risk {
    display: inline-block;

    padding: 4px 9px;

    border-radius: 15px;

    font-size: 10px;

    font-weight: 600;
}

.admin-risk-out,
.admin-risk-critical {
    background: #f8d7da;

    color: #721c24;
}

.admin-risk-low,
.admin-risk-medium {
    background: #fff3cd;

    color: #856404;
}

.admin-risk-healthy {
    background: #d4edda;

    color: #155724;
}

.admin-risk-unknown {
    background: #e2e3e5;

    color: #383d41;
}


/* Trend */

.admin-trend-up {
    color: #1e9b50;

    font-weight: 600;
}

.admin-trend-down {
    color: #d63c48;

    font-weight: 600;
}

.admin-trend-stable {
    color: #68727d;

    font-weight: 600;
}


/* AI Table */

.admin-ai-table {
    width: 100%;

    margin: 0;
}

.admin-ai-table th {
    background: #f8f9fa;

    color: #707984;

    font-size: 9px;

    text-transform: uppercase;
}

.admin-ai-table td {
    font-size: 11px;

    vertical-align: middle !important;
}


/* Empty */

.admin-ai-empty {
    text-align: center;

    padding: 25px;

    color: #89919b;

    font-size: 11px;
}

.admin-ai-empty-icon {
    font-size: 23px;

    margin-bottom: 8px;
}


/* Mobile */

@media(max-width:767px) {

    .admin-ai-wrapper {
        margin-left: 0;
        margin-right: 0;
    }

    .admin-ai-kpi-value {
        font-size: 22px;
    }

}

</style>


<!-- =========================================================
     EXISTING MESSAGE
========================================================= -->

<div class="row">

  <div class="col-md-12">

    <?php
    echo display_msg($msg);
    ?>

  </div>

</div>


<!-- =========================================================
     EXISTING KPI CARDS
========================================================= -->

<div class="row">


  <div class="col-md-3">

    <a
      href="users.php"
      style="text-decoration:none;"
    >

      <div
        class="panel panel-box clearfix"
      >

        <div
          class="panel-icon pull-left bg-secondary1"
        >

          <i
            class="glyphicon glyphicon-user"
          ></i>

        </div>

        <div
          class="panel-value pull-right"
        >

          <h2 class="margin-top">

            <?php
            echo $c_user['total'];
            ?>

          </h2>

          <p class="text-muted">
            Users
          </p>

        </div>

      </div>

    </a>

  </div>


  <div class="col-md-3">

    <a
      href="categorie.php"
      style="text-decoration:none;"
    >

      <div
        class="panel panel-box clearfix"
      >

        <div
          class="panel-icon pull-left bg-red"
        >

          <i
            class="glyphicon glyphicon-th-large"
          ></i>

        </div>

        <div
          class="panel-value pull-right"
        >

          <h2 class="margin-top">

            <?php
            echo $c_categorie['total'];
            ?>

          </h2>

          <p class="text-muted">
            Categories
          </p>

        </div>

      </div>

    </a>

  </div>


  <div class="col-md-3">

    <a
      href="product.php"
      style="text-decoration:none;"
    >

      <div
        class="panel panel-box clearfix"
      >

        <div
          class="panel-icon pull-left bg-blue2"
        >

          <i
            class="glyphicon glyphicon-shopping-cart"
          ></i>

        </div>

        <div
          class="panel-value pull-right"
        >

          <h2 class="margin-top">

            <?php
            echo $c_product['total'];
            ?>

          </h2>

          <p class="text-muted">
            Products
          </p>

        </div>

      </div>

    </a>

  </div>


  <div class="col-md-3">

    <a
      href="sales.php"
      style="text-decoration:none;"
    >

      <div
        class="panel panel-box clearfix"
      >

        <div
          class="panel-icon pull-left bg-green"
        >

          <i
            class="glyphicon glyphicon-usd"
          ></i>

        </div>

        <div
          class="panel-value pull-right"
        >

          <h2 class="margin-top">

            <?php
            echo $c_sale['total'];
            ?>

          </h2>

          <p class="text-muted">
            Sales
          </p>

        </div>

      </div>

    </a>

  </div>

</div>


<!-- =========================================================
     AI INVENTORY HEALTH
========================================================= -->

<div class="admin-ai-wrapper">


  <div class="admin-ai-header">

    <h3>

      <span
        class="glyphicon glyphicon-stats"
      ></span>

      AI Inventory Health

    </h3>

    <p>

      Real-time inventory intelligence using
      Linear Regression demand forecasting.

    </p>

    <a
      href="ml_dashboard.php"
      class="admin-ai-link"
    >

      <span
        class="glyphicon glyphicon-dashboard"
      ></span>

      Open AI Inventory Analytics

    </a>

  </div>


  <!-- AI KPI -->

  <div class="row">


    <div class="col-md-3 col-sm-6">

      <div class="admin-ai-kpi">

        <div class="admin-ai-kpi-icon">

          <span
            class="glyphicon glyphicon-th-large"
          ></span>

        </div>

        <div class="admin-ai-kpi-title">
          Products Monitored
        </div>

        <div class="admin-ai-kpi-value">

          <?php
          echo count(
              $all_inventory_products
          );
          ?>

        </div>

        <div class="admin-ai-kpi-sub">
          AI-enabled products
        </div>

      </div>

    </div>


    <div class="col-md-3 col-sm-6">

      <div class="admin-ai-kpi">

        <div class="admin-ai-kpi-icon">

          <span
            class="glyphicon glyphicon-inbox"
          ></span>

        </div>

        <div class="admin-ai-kpi-title">
          Inventory Units
        </div>

        <div class="admin-ai-kpi-value">

          <?php
          echo number_format(
              $ai_inventory_units
          );
          ?>

        </div>

        <div class="admin-ai-kpi-sub">
          Current available stock
        </div>

      </div>

    </div>


    <div class="col-md-3 col-sm-6">

      <div class="admin-ai-kpi">

        <div class="admin-ai-kpi-icon">

          <span
            class="glyphicon glyphicon-warning-sign"
          ></span>

        </div>

        <div class="admin-ai-kpi-title">
          Stock Alerts
        </div>

        <div class="admin-ai-kpi-value">

          <?php
          echo $ai_low_stock;
          ?>

        </div>

        <div class="admin-ai-kpi-sub">
          Products needing attention
        </div>

      </div>

    </div>


    <div class="col-md-3 col-sm-6">

      <div class="admin-ai-kpi">

        <div class="admin-ai-kpi-icon">

          <span
            class="glyphicon glyphicon-refresh"
          ></span>

        </div>

        <div class="admin-ai-kpi-title">
          Reorder Required
        </div>

        <div class="admin-ai-kpi-value">

          <?php
          echo $ai_reorder;
          ?>

        </div>

        <div class="admin-ai-kpi-sub">
          Products to replenish
        </div>

      </div>

    </div>

  </div>


  <!-- AI TABLES -->

  <div class="row">


    <!-- TOP PRODUCTS -->

    <div class="col-md-6">

      <div class="admin-ai-panel">

        <div class="admin-ai-panel-title">

          <span
            class="glyphicon glyphicon-star"
          ></span>

          Top Performing Products

        </div>


        <div class="admin-ai-panel-body">

          <?php
          if (
              count($ai_top_products) > 0
          ):
          ?>

          <div class="table-responsive">

            <table
              class="table table-hover admin-ai-table"
            >

              <thead>

                <tr>

                  <th>Product</th>

                  <th>Sales</th>

                  <th>Demand</th>

                  <th>Trend</th>

                </tr>

              </thead>


              <tbody>

              <?php
              foreach (
                  $ai_top_products
                  as $top_product
              ):
              ?>

              <tr>

                <td>

                  <strong>

                    <?php
                    echo htmlspecialchars(
                        $top_product['name']
                    );
                    ?>

                  </strong>

                </td>

                <td>

                  <?php
                  echo number_format(
                      $top_product['total_sales']
                  );
                  ?>

                </td>

                <td>

                  <?php
                  echo number_format(
                      $top_product['average_daily_demand'],
                      2
                  );
                  ?>

                </td>

                <td>

                  <span
                    class="<?php
                    echo admin_ai_trend_class(
                        $top_product['trend']
                    );
                    ?>"
                  >

                    <?php
                    echo admin_ai_trend_symbol(
                        $top_product['trend']
                    );
                    ?>

                    <?php
                    echo htmlspecialchars(
                        $top_product['trend']
                    );
                    ?>

                  </span>

                </td>

              </tr>

              <?php endforeach; ?>

              </tbody>

            </table>

          </div>

          <?php else: ?>

          <div class="admin-ai-empty">

            <div
              class="admin-ai-empty-icon"
            >

              <span
                class="glyphicon glyphicon-info-sign"
              ></span>

            </div>

            No AI product analysis available.

          </div>

          <?php endif; ?>

        </div>

      </div>

    </div>


    <!-- ATTENTION -->

    <div class="col-md-6">

      <div class="admin-ai-panel">

        <div class="admin-ai-panel-title">

          <span
            class="glyphicon glyphicon-warning-sign"
          ></span>

          Inventory Attention

        </div>


        <div class="admin-ai-panel-body">

          <?php
          if (
              count(
                  $ai_attention_products
              ) > 0
          ):
          ?>

          <div class="table-responsive">

            <table
              class="table table-hover admin-ai-table"
            >

              <thead>

                <tr>

                  <th>Product</th>

                  <th>Stock</th>

                  <th>Demand</th>

                  <th>Risk</th>

                </tr>

              </thead>


              <tbody>

              <?php
              foreach (
                  $ai_attention_products
                  as $attention
              ):
              ?>

              <tr>

                <td>

                  <strong>

                    <?php
                    echo htmlspecialchars(
                        $attention['name']
                    );
                    ?>

                  </strong>

                </td>

                <td>

                  <?php
                  echo number_format(
                      $attention['stock']
                  );
                  ?>

                </td>

                <td>

                  <?php
                  echo number_format(
                      $attention['predicted_demand'],
                      2
                  );
                  ?>

                  /day

                </td>

                <td>

                  <span
                    class="admin-risk <?php
                    echo admin_ai_risk_class(
                        $attention['risk']
                    );
                    ?>"
                  >

                    <?php
                    echo htmlspecialchars(
                        $attention['risk']
                    );
                    ?>

                  </span>

                </td>

              </tr>

              <?php endforeach; ?>

              </tbody>

            </table>

          </div>

          <?php else: ?>

          <div class="admin-ai-empty">

            <div
              class="admin-ai-empty-icon"
            >

              <span
                class="glyphicon glyphicon-ok-circle"
              ></span>

            </div>

            <strong>
              Inventory is healthy
            </strong>

            <br>

            No products currently require
            immediate attention.

          </div>

          <?php endif; ?>

        </div>

      </div>

    </div>

  </div>

</div>


<!-- =========================================================
     EXISTING HIGHEST SELLING + SALES
========================================================= -->

<div class="row">

   <div class="col-md-6">

     <div class="panel panel-default">

       <div class="panel-heading">

         <strong>

           <span
             class="glyphicon glyphicon-th"
           ></span>

           <span>
             Highest Selling Products
           </span>

         </strong>

       </div>


       <div class="panel-body">

         <table
           class="table table-bordered table-striped table-hover"
         >

           <thead>

             <tr>

               <th
                 class="text-center"
                 style="width:50px;"
               >
                 #
               </th>

               <th>
                 Product Title
               </th>

               <th
                 class="text-center"
                 style="width:15%;"
               >
                 Total Sold
               </th>

               <th
                 class="text-center"
                 style="width:15%;"
               >
                 Total Quantity
               </th>

             </tr>

           </thead>


           <tbody>

             <?php
             foreach (
                 $products_sold
                 as $product_sold
             ):
             ?>

               <tr>

                 <td class="text-center">

                   <?php
                   echo count_id();
                   ?>

                 </td>

                 <td>

                   <?php
                   echo remove_junk(
                       first_character(
                           $product_sold['name']
                       )
                   );
                   ?>

                 </td>

                 <td class="text-center">

                   <?php
                   echo (int)
                       $product_sold['totalSold'];
                   ?>

                 </td>

                 <td class="text-center">

                   <?php
                   echo (int)
                       $product_sold['totalQty'];
                   ?>

                 </td>

               </tr>

             <?php endforeach; ?>

           </tbody>

         </table>

       </div>

     </div>

   </div>


   <div class="col-md-6">

     <div class="panel panel-default">

       <div class="panel-heading">

         <strong>

           <span
             class="glyphicon glyphicon-th"
           ></span>

           <span>
             Latest Sales
           </span>

         </strong>

       </div>


       <div class="panel-body">

         <table
           class="table table-bordered table-striped table-hover"
         >

           <thead>

             <tr>

               <th
                 class="text-center"
                 style="width:50px;"
               >
                 #
               </th>

               <th>
                 Product Name
               </th>

               <th>
                 Date
               </th>

               <th>
                 Total Sale
               </th>

             </tr>

           </thead>


           <tbody>

             <?php
             foreach (
                 $recent_sales
                 as $recent_sale
             ):
             ?>

               <tr>

                 <td class="text-center">

                   <?php
                   echo count_id();
                   ?>

                 </td>

                 <td>

                   <a
                     href="edit_sale.php?id=<?php
                     echo (int)
                         $recent_sale['id'];
                     ?>"
                   >

                     <?php
                     echo remove_junk(
                         first_character(
                             $recent_sale['name']
                         )
                     );
                     ?>

                   </a>

                 </td>

                 <td>

                   <?php
                   echo date(
                       "Y-m-d",
                       strtotime(
                           $recent_sale['date']
                       )
                   );
                   ?>

                 </td>

                 <td>

                   $<?php
                   echo (int)
                       $recent_sale['price'];
                   ?>

                 </td>

               </tr>

             <?php endforeach; ?>

           </tbody>

         </table>

       </div>

     </div>

   </div>

</div>


<!-- =========================================================
     EXISTING RECENT PRODUCTS
========================================================= -->

<div class="row">

   <div class="col-md-6">

     <div class="panel panel-default">

       <div class="panel-heading">

         <strong>

           <span
             class="glyphicon glyphicon-th"
           ></span>

           <span>
             Recently Added Products
           </span>

         </strong>

       </div>


       <div class="panel-body">

         <div class="list-group">

           <?php
           foreach (
               $recent_products
               as $recent_product
           ):
           ?>

             <a
               class="list-group-item clearfix"
               href="edit_product.php?id=<?php
               echo (int)
                   $recent_product['id'];
               ?>"
             >

               <div
                 style="
                   display:flex;
                   justify-content:space-between;
                   align-items:center;
                   width:100%;
                 "
               >

                 <div>

                   <span
                     class="name"
                     style="font-weight:600;"
                   >

                     <?php
                     echo remove_junk(
                         first_character(
                             $recent_product['name']
                         )
                     );
                     ?>

                   </span>

                   <br>

                   <small
                     class="text-muted"
                   >

                     <?php
                     echo remove_junk(
                         first_character(
                             $recent_product['categorie']
                         )
                     );
                     ?>

                   </small>

                 </div>


                 <span
                   class="label label-warning"
                   style="font-size:14px;"
                 >

                   $<?php
                   echo (int)
                       $recent_product['sale_price'];
                   ?>

                 </span>

               </div>

             </a>

           <?php endforeach; ?>

         </div>

       </div>

     </div>

   </div>

</div>


<?php include_once('layouts/footer.php'); ?>
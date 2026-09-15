<?php
require_once('includes/load.php');
page_require_level(1);

global $db;


/* =========================================================
   PRODUCTS
========================================================= */

$products = find_all('products');

$product_id = isset($_GET['product_id'])
    ? (int)$_GET['product_id']
    : 0;

$forecast_days = isset($_GET['forecast_days'])
    ? (int)$_GET['forecast_days']
    : 7;

if ($forecast_days < 1) {
    $forecast_days = 7;
}

if ($forecast_days > 30) {
    $forecast_days = 30;
}


/* =========================================================
   TOTAL INVENTORY
========================================================= */

$total_inventory_units = 0;

foreach ($products as $p) {
    $total_inventory_units += (int)$p['quantity'];
}

/* =========================================================
   MANAGEMENT KPI DATA
========================================================= */

$total_inventory_value = 0;

foreach ($products as $p) {
    $total_inventory_value +=
        ((float)$p['quantity'] * (float)$p['buy_price']);
}

$fast_moving_products = array();
$slow_moving_products = array();


/* =========================================================
   SELECTED PRODUCT ANALYSIS
========================================================= */

$result = null;
$ml_error = '';

if ($product_id > 0) {

    $script = __DIR__ .
        DIRECTORY_SEPARATOR .
        'ML' .
        DIRECTORY_SEPARATOR .
        'ml_engine.py';

    $command = 'python ' .
        escapeshellarg($script) .
        ' ' .
        intval($product_id) .
        ' ' .
        intval($forecast_days);

    $output = shell_exec($command);

    if ($output) {

        $result = json_decode(
            $output,
            true
        );

        if (
            !$result ||
            !isset($result['success'])
        ) {
            $ml_error =
                'Unable to read ML engine response.';
        }

    } else {

        $ml_error =
            'Python ML engine could not be executed.';
    }
}


/* =========================================================
   ALL PRODUCT ANALYSIS
========================================================= */

$all_products = array();

$summary = array(
    'total_products' => count($products),
    'low_stock' => 0,
    'reorder_products' => 0
);

$script = __DIR__ .
    DIRECTORY_SEPARATOR .
    'ML' .
    DIRECTORY_SEPARATOR .
    'ml_engine.py';

$command_all = 'python ' .
    escapeshellarg($script) .
    ' 0';

$output_all = shell_exec(
    $command_all
);

if ($output_all) {

    $all_result = json_decode(
        $output_all,
        true
    );

    if (
        $all_result &&
        isset($all_result['success']) &&
        $all_result['success']
    ) {

        if (
            isset($all_result['products'])
        ) {
            $all_products =
                $all_result['products'];
        }

        if (
            isset($all_result['summary'])
        ) {
            $summary =
                $all_result['summary'];
        }
    }
}

/* =========================================================
   MANAGEMENT ANALYTICS
========================================================= */

$performance_products = $all_products;

usort(
    $performance_products,
    function ($a, $b) {
        return (float)$b['total_sales'] - (float)$a['total_sales'];
    }
);

$fast_moving_products = array_slice(
    $performance_products,
    0,
    5
);

$slow_sorted_products = $all_products;

usort(
    $slow_sorted_products,
    function ($a, $b) {
        return (float)$a['total_sales'] - (float)$b['total_sales'];
    }
);

$slow_moving_products = array_slice(
    $slow_sorted_products,
    0,
    5
);

$attention_count = 0;

foreach ($all_products as $item) {
    if (
        $item['risk'] != 'Healthy' &&
        $item['risk'] != 'No Data'
    ) {
        $attention_count++;
    }
}


/* =========================================================
   SORT PRODUCTS BY RISK
========================================================= */

$risk_priority = array(
    'Out of Stock' => 1,
    'Critical' => 2,
    'Low' => 3,
    'Medium' => 4,
    'Healthy' => 5,
    'No Data' => 6
);

usort(
    $all_products,
    function ($a, $b) use ($risk_priority) {

        $ra = isset(
            $risk_priority[$a['risk']]
        )
            ? $risk_priority[$a['risk']]
            : 99;

        $rb = isset(
            $risk_priority[$b['risk']]
        )
            ? $risk_priority[$b['risk']]
            : 99;

        return $ra - $rb;
    }
);


/* =========================================================
   LOW STOCK PRODUCTS
========================================================= */

$low_stock_products = array();

foreach ($all_products as $item) {

    if (
        $item['risk'] != 'Healthy' &&
        $item['risk'] != 'No Data'
    ) {

        $low_stock_products[] = $item;
    }
}


/* =========================================================
   REORDER PRODUCTS
========================================================= */

$reorder_products = array();

foreach ($all_products as $item) {

    if (
        $item['recommended_order'] > 0
    ) {

        $reorder_products[] = $item;
    }
}


/* Sort highest order quantity first */

usort(
    $reorder_products,
    function ($a, $b) {

        return
            $b['recommended_order']
            -
            $a['recommended_order'];
    }
);


/* =========================================================
   SELECTED PRODUCT VALUES
========================================================= */

$selected_product = null;

if (
    $result &&
    isset($result['product'])
) {
    $selected_product =
        $result['product'];
}

$selected_stock = 0;
$selected_demand = 0;
$selected_risk = 'Unknown';
$selected_trend = 'Unknown';
$selected_performance = 'Unknown';
$recommended_order = 0;
$reorder_point = 0;
$stock_days = 0;

if (
    $result &&
    isset($result['stock'])
) {

    $selected_stock =
        $result['stock']['current_stock'];

    $selected_demand =
        $result['stock']['predicted_daily_demand'];

    $selected_risk =
        $result['stock']['risk'];

    $recommended_order =
        $result['stock']['recommended_order'];

    $reorder_point =
        $result['stock']['reorder_point'];

    $stock_days =
        $result['stock']['stock_days'];
}

if (
    $result &&
    isset($result['trend'])
) {

    $selected_trend =
        $result['trend']['trend'];
}

if (
    $result &&
    isset($result['performance'])
) {

    $selected_performance =
        $result['performance']['performance'];
}


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function risk_class($risk)
{
    switch ($risk) {

        case 'Out of Stock':
            return 'risk-out';

        case 'Critical':
            return 'risk-critical';

        case 'Low':
            return 'risk-low';

        case 'Medium':
            return 'risk-medium';

        case 'Healthy':
            return 'risk-healthy';

        default:
            return 'risk-unknown';
    }
}


function trend_class($trend)
{
    switch ($trend) {

        case 'Increasing':
            return 'trend-up';

        case 'Decreasing':
            return 'trend-down';

        default:
            return 'trend-stable';
    }
}


function trend_symbol($trend)
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


/* =========================================================
   HEADER
========================================================= */

include_once(
    'layouts/header.php'
);

?>

<style>

/* =========================================================
   MAIN
========================================================= */

.ai-page {
    padding: 18px;
    background: #f5f7fa;
}


/* =========================================================
   HEADER
========================================================= */

.ai-header {
    background: linear-gradient(
        135deg,
        #16213e,
        #3f68b5
    );

    color: #fff;

    border-radius: 12px;

    padding: 25px;

    margin-bottom: 18px;

    box-shadow:
        0 5px 20px rgba(
            0,
            0,
            0,
            0.12
        );
}

.ai-header h2 {
    margin: 0 0 7px 0;
    font-weight: 600;
}

.ai-header p {
    margin: 0;
    opacity: 0.85;
}

.ai-badge {
    display: inline-block;

    margin-top: 12px;

    padding: 6px 13px;

    border-radius: 20px;

    background:
        rgba(255,255,255,0.13);

    border:
        1px solid
        rgba(255,255,255,0.25);

    font-size: 12px;
}


/* =========================================================
   CARDS
========================================================= */

.ai-card {
    background: #fff;

    border-radius: 10px;

    padding: 20px;

    margin-bottom: 18px;

    border: 1px solid #e9edf2;

    box-shadow:
        0 3px 14px
        rgba(0,0,0,0.055);
}


/* =========================================================
   KPI
========================================================= */

.kpi-card {
    position: relative;

    min-height: 125px;

    overflow: hidden;
}

.kpi-title {
    color: #7a8491;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: 0.6px;
}

.kpi-value {
    font-size: 29px;

    font-weight: 700;

    margin-top: 9px;
}

.kpi-sub {
    color: #929aa5;

    font-size: 11px;

    margin-top: 4px;
}

.kpi-icon {
    position: absolute;

    right: 18px;

    top: 18px;

    font-size: 35px;

    opacity: 0.10;
}


/* =========================================================
   ANALYSIS BOX
========================================================= */

.analysis-box {
    background: #fff;

    padding: 20px;

    border-radius: 10px;

    margin-bottom: 18px;

    border: 1px solid #e9edf2;

    box-shadow:
        0 3px 14px
        rgba(0,0,0,0.055);
}

.analysis-box h4 {
    margin-top: 0;

    font-weight: 600;
}


/* =========================================================
   METRIC CARDS
========================================================= */

.metric-card {
    text-align: center;

    min-height: 135px;
}

.metric-label {
    color: #7a8491;

    font-size: 11px;

    text-transform: uppercase;
}

.metric-value {
    font-size: 27px;

    font-weight: 700;

    margin-top: 12px;
}

.metric-small {
    color: #929aa5;

    font-size: 11px;

    margin-top: 5px;
}


/* =========================================================
   RISK
========================================================= */

.risk-badge {
    display: inline-block;

    padding: 6px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 600;
}

.risk-out,
.risk-critical {
    background: #f8d7da;
    color: #721c24;
}

.risk-low,
.risk-medium {
    background: #fff3cd;
    color: #856404;
}

.risk-healthy {
    background: #d4edda;
    color: #155724;
}

.risk-unknown {
    background: #e2e3e5;
    color: #383d41;
}


/* =========================================================
   TREND
========================================================= */

.trend-up {
    color: #1e9b50;

    font-weight: 600;
}

.trend-down {
    color: #d63c48;

    font-weight: 600;
}

.trend-stable {
    color: #68727d;

    font-weight: 600;
}


/* =========================================================
   TABLE
========================================================= */

.ai-table {
    background: #fff;

    border-radius: 10px;

    overflow: hidden;

    border: 1px solid #e9edf2;

    box-shadow:
        0 3px 14px
        rgba(0,0,0,0.055);
}

.ai-table-title {
    padding: 17px 20px;

    border-bottom:
        1px solid #edf0f3;

    font-weight: 600;

    font-size: 14px;
}

.ai-table table {
    margin-bottom: 0;
}

.ai-table th {
    background: #f8f9fb;

    color: #6d7680;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .4px;
}

.ai-table td {
    vertical-align: middle !important;

    font-size: 12px;
}


/* =========================================================
   STOCK BAR
========================================================= */

.stock-bar {
    width: 100%;

    height: 7px;

    background: #edf0f3;

    border-radius: 10px;

    overflow: hidden;

    margin-top: 5px;
}

.stock-bar-inner {
    height: 100%;

    border-radius: 10px;

    background: #4b6cb7;
}


/* =========================================================
   AI EXPLANATION
========================================================= */

.ai-explanation {
    background: #fff;

    border-radius: 10px;

    padding: 20px;

    margin-top: 18px;

    border:
        1px solid #e9edf2;

    box-shadow:
        0 3px 14px
        rgba(0,0,0,0.055);
}

.flow-box {
    text-align: center;

    padding: 15px 5px;
}

.flow-number {
    width: 35px;

    height: 35px;

    line-height: 35px;

    margin: auto;

    border-radius: 50%;

    background: #3f68b5;

    color: #fff;

    font-weight: bold;
}

.flow-title {
    margin-top: 9px;

    font-size: 12px;

    font-weight: 600;
}

.flow-text {
    color: #8a929c;

    font-size: 10px;

    margin-top: 4px;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    text-align: center;

    padding: 30px;

    color: #8a929c;
}

.empty-state-icon {
    font-size: 27px;

    margin-bottom: 10px;
}


/* =========================================================
   ALERT
========================================================= */

.ai-alert {
    padding: 14px;

    border-radius: 8px;

    margin-bottom: 18px;
}

.ai-alert-danger {
    background: #f8d7da;

    color: #721c24;
}

.ai-alert-warning {
    background: #fff3cd;

    color: #856404;
}


/* =========================================================
   BUTTON
========================================================= */

.analyze-btn {
    height: 42px;

    border-radius: 6px;

    font-weight: 600;
}


/* =========================================================
   MANAGEMENT / AI RECOMMENDATION
========================================================= */

.management-card {
    min-height: 118px;
}

.management-value {
    font-size: 23px;
    font-weight: 700;
    margin-top: 9px;
}

.management-label {
    color: #7a8491;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: .5px;
}

.ai-recommendation {
    border-left: 4px solid #3f68b5;
    background: #f7f9fc;
    padding: 18px 20px;
    border-radius: 8px;
}

.ai-recommendation.warning {
    border-left-color: #e0a800;
}

.ai-recommendation.danger {
    border-left-color: #d9534f;
}

.ai-recommendation-title {
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 6px;
}

.ai-recommendation-text {
    color: #68727d;
    font-size: 12px;
    line-height: 1.7;
}

.priority-badge {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
}

.priority-high {
    background: #f8d7da;
    color: #721c24;
}

.priority-medium {
    background: #fff3cd;
    color: #856404;
}

.priority-normal {
    background: #d4edda;
    color: #155724;
}

/* =========================================================
   MOBILE
========================================================= */

@media(max-width:767px) {

    .ai-page {
        padding: 10px;
    }

    .kpi-value {
        font-size: 24px;
    }

    .metric-value {
        font-size: 22px;
    }
}

</style>


<div class="ai-page">


<!-- =====================================================
     HEADER
====================================================== -->

<div class="ai-header">

    <h2>
        <span class="glyphicon glyphicon-stats"></span>
        AI Inventory Analytics
    </h2>

    <p>
        Intelligent demand forecasting, stock monitoring
        and reorder recommendations.
    </p>

    <div class="ai-badge">

        <span class="glyphicon glyphicon-flash"></span>

        Powered by Linear Regression

    </div>

</div>


<!-- =====================================================
     KPI CARDS
====================================================== -->

<div class="row">


    <div class="col-md-3 col-sm-6">

        <div class="ai-card kpi-card">

            <div class="kpi-icon">
                <span class="glyphicon glyphicon-th-large"></span>
            </div>

            <div class="kpi-title">
                Total Products
            </div>

            <div class="kpi-value">
                <?php
                echo count($products);
                ?>
            </div>

            <div class="kpi-sub">
                Products monitored
            </div>

        </div>

    </div>


    <div class="col-md-3 col-sm-6">

        <div class="ai-card kpi-card">

            <div class="kpi-icon">
                <span class="glyphicon glyphicon-inbox"></span>
            </div>

            <div class="kpi-title">
                Inventory Units
            </div>

            <div class="kpi-value">
                <?php
                echo number_format(
                    $total_inventory_units
                );
                ?>
            </div>

            <div class="kpi-sub">
                Current stock quantity
            </div>

        </div>

    </div>


    <div class="col-md-3 col-sm-6">

        <div class="ai-card kpi-card">

            <div class="kpi-icon">
                <span class="glyphicon glyphicon-warning-sign"></span>
            </div>

            <div class="kpi-title">
                Stock Alerts
            </div>

            <div class="kpi-value">
                <?php
                echo (int)$summary['low_stock'];
                ?>
            </div>

            <div class="kpi-sub">
                Products requiring attention
            </div>

        </div>

    </div>


    <div class="col-md-3 col-sm-6">

        <div class="ai-card kpi-card">

            <div class="kpi-icon">
                <span class="glyphicon glyphicon-refresh"></span>
            </div>

            <div class="kpi-title">
                Reorder Required
            </div>

            <div class="kpi-value">
                <?php
                echo (int)$summary['reorder_products'];
                ?>
            </div>

            <div class="kpi-sub">
                Products to replenish
            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     MANAGEMENT KPI ROW
====================================================== -->

<div class="row">

    <div class="col-md-3 col-sm-6">
        <div class="ai-card management-card">
            <div class="management-label">Inventory Value</div>
            <div class="management-value">
                <?php echo number_format($total_inventory_value, 2); ?>
            </div>
            <div class="kpi-sub">Estimated purchase value</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="ai-card management-card">
            <div class="management-label">Fast Moving</div>
            <div class="management-value">
                <?php echo count($fast_moving_products); ?>
            </div>
            <div class="kpi-sub">Top-selling products shown below</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="ai-card management-card">
            <div class="management-label">Slow Moving</div>
            <div class="management-value">
                <?php echo count($slow_moving_products); ?>
            </div>
            <div class="kpi-sub">Products with lowest sales</div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6">
        <div class="ai-card management-card">
            <div class="management-label">Attention Needed</div>
            <div class="management-value">
                <?php echo (int)$attention_count; ?>
            </div>
            <div class="kpi-sub">Non-healthy stock conditions</div>
        </div>
    </div>

</div>


<!-- =====================================================
     ERROR
====================================================== -->

<?php if ($ml_error != ''): ?>

<div class="ai-alert ai-alert-danger">

    <strong>
        <span class="glyphicon glyphicon-remove-circle"></span>
        ML Error:
    </strong>

    <?php
    echo htmlspecialchars(
        $ml_error
    );
    ?>

</div>

<?php endif; ?>


<!-- =====================================================
     ANALYSIS
====================================================== -->

<div class="analysis-box">

    <h4>

        <span class="glyphicon glyphicon-search"></span>

        Product Demand Analysis

    </h4>

    <p style="color:#7f8790;font-size:12px;">

        Select a product to analyze historical demand,
        future demand, stock health and reorder requirements.

    </p>


    <form
        method="get"
        action="ml_dashboard.php"
    >

        <div class="row">


            <div class="col-md-7">

                <label>
                    Product
                </label>

                <select
                    name="product_id"
                    class="form-control"
                    required
                >

                    <option value="">
                        -- Select Product --
                    </option>

                    <?php
                    foreach (
                        $products
                        as $product
                    ):
                    ?>

                    <option
                        value="<?php
                        echo (int)$product['id'];
                        ?>"
                        <?php
                        if (
                            $product_id ==
                            $product['id']
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >

                        <?php
                        echo htmlspecialchars(
                            $product['name']
                        );
                        ?>

                    </option>

                    <?php endforeach; ?>

                </select>

            </div>


            <div class="col-md-3">

                <label>
                    Forecast Period
                </label>

                <select
                    name="forecast_days"
                    class="form-control"
                >

                    <option
                        value="7"
                        <?php
                        if (
                            $forecast_days == 7
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >
                        7 Days
                    </option>

                    <option
                        value="14"
                        <?php
                        if (
                            $forecast_days == 14
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >
                        14 Days
                    </option>

                    <option
                        value="30"
                        <?php
                        if (
                            $forecast_days == 30
                        ) {
                            echo 'selected';
                        }
                        ?>
                    >
                        30 Days
                    </option>

                </select>

            </div>


            <div class="col-md-2">

                <label>
                    &nbsp;
                </label>

                <button
                    type="submit"
                    class="btn btn-primary btn-block analyze-btn"
                >

                    <span
                        class="glyphicon glyphicon-stats"
                    ></span>

                    Analyze

                </button>

            </div>

        </div>

    </form>

</div>


<?php
if (
    $result &&
    isset($result['success']) &&
    $result['success']
):
?>


<!-- =====================================================
     SELECTED PRODUCT
====================================================== -->

<div class="ai-card">

    <h3 style="margin-top:0;">

        <span
            class="glyphicon glyphicon-tag"
        ></span>

        <?php
        echo htmlspecialchars(
            $selected_product['name']
        );
        ?>

    </h3>

    <p
        style="
            color:#7f8790;
            font-size:12px;
            margin-bottom:0;
        "
    >

        Analysis based on the latest

        <strong>
            <?php
            echo (int)
                $result['model']['history_days'];
            ?>
        </strong>

        days of sales history using

        <strong>
            Linear Regression
        </strong>.

    </p>

</div>


<!-- =====================================================
     PRODUCT METRICS
====================================================== -->

<div class="row">


    <div class="col-md-3 col-sm-6">

        <div class="ai-card metric-card">

            <div class="metric-label">
                Current Stock
            </div>

            <div class="metric-value">

                <?php
                echo number_format(
                    $selected_stock
                );
                ?>

            </div>

            <div class="metric-small">
                Units currently available
            </div>

        </div>

    </div>


    <div class="col-md-3 col-sm-6">

        <div class="ai-card metric-card">

            <div class="metric-label">
                Predicted Daily Demand
            </div>

            <div class="metric-value">

                <?php
                echo number_format(
                    $selected_demand,
                    2
                );
                ?>

            </div>

            <div class="metric-small">
                Expected units / day
            </div>

        </div>

    </div>


    <div class="col-md-2 col-sm-6">

        <div class="ai-card metric-card">

            <div class="metric-label">
                Demand Trend
            </div>

            <div class="metric-value <?php
                echo trend_class(
                    $selected_trend
                );
            ?>">

                <?php
                echo trend_symbol(
                    $selected_trend
                );
                ?>

            </div>

            <div class="metric-small">

                <?php
                echo htmlspecialchars(
                    $selected_trend
                );
                ?>

            </div>

        </div>

    </div>


    <div class="col-md-2 col-sm-6">

        <div class="ai-card metric-card">

            <div class="metric-label">
                Performance
            </div>

            <div
                class="metric-value"
                style="font-size:19px;"
            >

                <?php
                echo htmlspecialchars(
                    $selected_performance
                );
                ?>

            </div>

            <div class="metric-small">
                Sales performance
            </div>

        </div>

    </div>


    <div class="col-md-2 col-sm-6">

        <div class="ai-card metric-card">

            <div class="metric-label">
                Stock Health
            </div>

            <div style="margin-top:18px;">

                <span class="risk-badge <?php
                    echo risk_class(
                        $selected_risk
                    );
                ?>">

                    <?php
                    echo htmlspecialchars(
                        $selected_risk
                    );
                    ?>

                </span>

            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     REORDER INFORMATION
====================================================== -->

<div class="ai-card">

    <div class="row">


        <div class="col-md-4">

            <strong>
                <span
                    class="glyphicon glyphicon-map-marker"
                ></span>

                Reorder Point
            </strong>

            <h3>

                <?php
                echo number_format(
                    $reorder_point,
                    2
                );
                ?>

                <small>units</small>

            </h3>

            <span
                style="
                    color:#89919a;
                    font-size:11px;
                "
            >
                Stock level at which replenishment
                should be considered.
            </span>

        </div>


        <div class="col-md-4">

            <strong>
                <span
                    class="glyphicon glyphicon-shopping-cart"
                ></span>

                Recommended Order
            </strong>

            <h3>

                <?php
                echo number_format(
                    $recommended_order
                );
                ?>

                <small>units</small>

            </h3>

            <span
                style="
                    color:#89919a;
                    font-size:11px;
                "
            >
                Suggested replenishment quantity.
            </span>

        </div>


        <div class="col-md-4">

            <strong>
                <span
                    class="glyphicon glyphicon-time"
                ></span>

                Stock Coverage
            </strong>

            <h3>

                <?php
                echo number_format(
                    $stock_days,
                    1
                );
                ?>

                <small>days</small>

            </h3>

            <span
                style="
                    color:#89919a;
                    font-size:11px;
                "
            >
                Estimated days before current stock
                is consumed.
            </span>

        </div>

    </div>

</div>


<!-- =====================================================
     AI RECOMMENDATION
====================================================== -->

<?php
$recommendation_class = '';
$recommendation_title = 'AI Inventory Recommendation';
$recommendation_text = '';

if ($selected_risk == 'Out of Stock' || $selected_risk == 'Critical') {
    $recommendation_class = 'danger';
    $recommendation_title = 'Immediate Action Recommended';
    $recommendation_text =
        'Current stock is at a critical level. Replenishment should be prioritized. ' .
        'The model estimates approximately ' .
        number_format($selected_demand, 2) .
        ' units of daily demand and recommends an order of ' .
        number_format($recommended_order) .
        ' units.';
} elseif ($selected_risk == 'Low' || $selected_risk == 'Medium') {
    $recommendation_class = 'warning';
    $recommendation_title = 'Monitor Inventory Closely';
    $recommendation_text =
        'Inventory is approaching the calculated reorder threshold. Current stock is ' .
        number_format($selected_stock) .
        ' units versus a reorder point of ' .
        number_format($reorder_point, 2) .
        ' units. Prepare replenishment if demand continues at the predicted rate.';
} elseif ($selected_risk == 'Healthy') {
    $recommendation_text =
        'Inventory is currently healthy. Stock covers approximately ' .
        number_format($stock_days, 1) .
        ' days of predicted demand. No immediate replenishment action is required.';
} else {
    $recommendation_text =
        'The system does not have enough reliable demand data to make a strong inventory recommendation.';
}
?>

<div class="ai-card">

    <div class="ai-recommendation <?php echo $recommendation_class; ?>">

        <div class="ai-recommendation-title">
            <span class="glyphicon glyphicon-bullhorn"></span>
            <?php echo htmlspecialchars($recommendation_title); ?>
        </div>

        <div class="ai-recommendation-text">
            <?php echo htmlspecialchars($recommendation_text); ?>
        </div>

        <div style="margin-top:10px;font-size:11px;color:#7f8790;">
            Decision inputs: Linear Regression demand forecast + current stock + reorder point + stock coverage.
        </div>

    </div>

</div>


<!-- =====================================================
     GRAPH
====================================================== -->

<div class="ai-card">

    <h4>

        <span
            class="glyphicon glyphicon-stats"
        ></span>

        Actual vs Predicted Sales

    </h4>

    <p
        style="
            color:#8a929c;
            font-size:11px;
        "
    >
        Historical actual sales are compared with the
        Linear Regression model prediction.
    </p>

    <div style="height:400px;">

        <canvas
            id="salesChart"
        ></canvas>

    </div>

</div>


<?php endif; ?>


<!-- =====================================================
     PRODUCT PERFORMANCE
====================================================== -->

<div class="ai-table">

    <div class="ai-table-title">

        <span
            class="glyphicon glyphicon-dashboard"
        ></span>

        Product Performance Analysis

    </div>


    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Product</th>

                    <th>Total Sales</th>

                    <th>Avg Demand</th>

                    <th>Trend</th>

                    <th>Performance</th>

                    <th>Stock</th>

                </tr>

            </thead>


            <tbody>

            <?php
            foreach (
                $all_products
                as $item
            ):
            ?>

            <tr>

                <td>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $item['name']
                        );
                        ?>
                    </strong>

                </td>


                <td>

                    <?php
                    echo number_format(
                        $item['total_sales']
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo number_format(
                        $item['average_daily_demand'],
                        2
                    );
                    ?>

                    / day

                </td>


                <td>

                    <span class="<?php
                        echo trend_class(
                            $item['trend']
                        );
                    ?>">

                        <?php
                        echo trend_symbol(
                            $item['trend']
                        );
                        ?>

                        <?php
                        echo htmlspecialchars(
                            $item['trend']
                        );
                        ?>

                    </span>

                </td>


                <td>

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $item['performance']
                        );
                        ?>
                    </strong>

                </td>


                <td>

                    <?php
                    echo number_format(
                        $item['stock']
                    );
                    ?>

                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>


<br>


<!-- =====================================================
     LOW STOCK
====================================================== -->

<div class="ai-table">

    <div class="ai-table-title">

        <span
            class="glyphicon glyphicon-warning-sign"
        ></span>

        Intelligent Low-Stock Dashboard

        <span
            class="pull-right"
            style="
                font-size:11px;
                color:#89919a;
            "
        >
            <?php
            echo count(
                $low_stock_products
            );
            ?>

            alerts

        </span>

    </div>


    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Product</th>

                    <th>Current Stock</th>

                    <th>Daily Demand</th>

                    <th>Coverage</th>

                    <th>Reorder Point</th>

                    <th>Risk</th>

                </tr>

            </thead>


            <tbody>

            <?php
            if (
                count(
                    $low_stock_products
                ) > 0
            ):
            ?>

                <?php
                foreach (
                    $low_stock_products
                    as $item
                ):
                ?>

                <tr>

                    <td>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $item['name']
                            );
                            ?>
                        </strong>

                    </td>


                    <td>

                        <?php
                        echo number_format(
                            $item['stock']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo number_format(
                            $item['predicted_demand'],
                            2
                        );
                        ?>

                        / day

                    </td>


                    <td>

                        <?php

                        if (
                            $item['predicted_demand']
                            > 0
                        ) {

                            $coverage =
                                $item['stock']
                                /
                                $item['predicted_demand'];

                        } else {

                            $coverage = 999;
                        }

                        echo number_format(
                            $coverage,
                            1
                        );

                        ?>

                        days

                    </td>


                    <td>

                        <?php
                        echo number_format(
                            $item['reorder_point'],
                            2
                        );
                        ?>

                    </td>


                    <td>

                        <span
                            class="risk-badge <?php
                                echo risk_class(
                                    $item['risk']
                                );
                            ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $item['risk']
                            );
                            ?>

                        </span>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="6">

                        <div class="empty-state">

                            <div
                                class="empty-state-icon"
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
                            immediate stock attention.

                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<br>


<!-- =====================================================
     REORDER
====================================================== -->

<div class="ai-table">

    <div class="ai-table-title">

        <span
            class="glyphicon glyphicon-refresh"
        ></span>

        Reorder Recommendations

        <span
            class="pull-right"
            style="
                font-size:11px;
                color:#89919a;
            "
        >

            <?php
            echo count(
                $reorder_products
            );
            ?>

            recommendations

        </span>

    </div>


    <div class="table-responsive">

        <table class="table table-hover">

            <thead>

                <tr>

                    <th>Priority</th>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Daily Demand</th>
                    <th>Coverage</th>
                    <th>Reorder Point</th>
                    <th>Order Quantity</th>

                </tr>

            </thead>


            <tbody>

            <?php
            if (
                count(
                    $reorder_products
                ) > 0
            ):
            ?>

                <?php
                foreach (
                    $reorder_products
                    as $item
                ):
                ?>

                <tr>

                    <td>
                        <?php
                        if ($item['risk'] == 'Out of Stock' || $item['risk'] == 'Critical') {
                            $priority = 'High';
                            $priority_class = 'priority-high';
                        } elseif ($item['risk'] == 'Low' || $item['risk'] == 'Medium') {
                            $priority = 'Medium';
                            $priority_class = 'priority-medium';
                        } else {
                            $priority = 'Normal';
                            $priority_class = 'priority-normal';
                        }
                        ?>
                        <span class="priority-badge <?php echo $priority_class; ?>">
                            <?php echo $priority; ?>
                        </span>
                    </td>

                    <td>

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                $item['name']
                            );
                            ?>
                        </strong>

                    </td>


                    <td>

                        <?php
                        echo number_format(
                            $item['stock']
                        );
                        ?>

                    </td>


                    <td>

                        <?php
                        echo number_format(
                            $item['predicted_demand'],
                            2
                        );
                        ?>

                    </td>


                    <td>

                        <?php

                        if (
                            $item['predicted_demand']
                            > 0
                        ) {

                            $coverage =
                                $item['stock']
                                /
                                $item['predicted_demand'];

                        } else {

                            $coverage = 999;
                        }

                        echo number_format(
                            $coverage,
                            1
                        );

                        ?>

                        days

                    </td>


                    <td>

                        <?php
                        echo number_format(
                            $item['reorder_point'],
                            2
                        );
                        ?>

                    </td>


                    <td>

                        <strong>

                            <?php
                            echo number_format(
                                $item['recommended_order']
                            );
                            ?>

                            units

                        </strong>

                    </td>

                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>

                    <td colspan="7">

                        <div class="empty-state">

                            <div
                                class="empty-state-icon"
                            >

                                <span
                                    class="glyphicon glyphicon-ok"
                                ></span>

                            </div>

                            <strong>
                                No reorder required
                            </strong>

                            <br>

                            Current inventory levels
                            are above calculated reorder points.

                        </div>

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =====================================================
     HOW IT WORKS
====================================================== -->

<div class="ai-explanation">

    <h4>

        <span
            class="glyphicon glyphicon-cog"
        ></span>

        How AI Inventory Analytics Works

    </h4>

    <p
        style="
            color:#7f8790;
            font-size:11px;
        "
    >

        The system uses historical sales data to estimate
        future demand and support inventory decisions.

    </p>


    <div class="row">


        <div class="col-md-2 col-sm-4 flow-box">

            <div class="flow-number">
                1
            </div>

            <div class="flow-title">
                Sales History
            </div>

            <div class="flow-text">
                Historical product sales are collected.
            </div>

        </div>


        <div class="col-md-2 col-sm-4 flow-box">

            <div class="flow-number">
                2
            </div>

            <div class="flow-title">
                Daily Demand
            </div>

            <div class="flow-text">
                Sales are converted into daily demand.
            </div>

        </div>


        <div class="col-md-2 col-sm-4 flow-box">

            <div class="flow-number">
                3
            </div>

            <div class="flow-title">
                Linear Regression
            </div>

            <div class="flow-text">
                The model learns the demand trend.
            </div>

        </div>


        <div class="col-md-2 col-sm-4 flow-box">

            <div class="flow-number">
                4
            </div>

            <div class="flow-title">
                Demand Forecast
            </div>

            <div class="flow-text">
                Future demand is estimated.
            </div>

        </div>


        <div class="col-md-2 col-sm-4 flow-box">

            <div class="flow-number">
                5
            </div>

            <div class="flow-title">
                Stock Risk
            </div>

            <div class="flow-text">
                Current stock is compared with demand.
            </div>

        </div>


        <div class="col-md-2 col-sm-4 flow-box">

            <div class="flow-number">
                6
            </div>

            <div class="flow-title">
                Reorder
            </div>

            <div class="flow-text">
                Recommended quantity is calculated.
            </div>

        </div>

    </div>

</div>


</div>


<!-- =====================================================
     CHART
====================================================== -->

<?php
if (
    $result &&
    isset($result['success']) &&
    $result['success']
):
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

var historical = <?php
echo json_encode(
    $result['historical']
);
?>;

var future = <?php
echo json_encode(
    $result['future']
);
?>;


var labels = [];

var actualData = [];

var predictedData = [];


/* Historical */

for (
    var i = 0;
    i < historical.length;
    i++
) {

    labels.push(
        historical[i].date
    );

    actualData.push(
        historical[i].actual
    );

    predictedData.push(
        historical[i].predicted
    );
}


/* Future */

for (
    var j = 0;
    j < future.length;
    j++
) {

    labels.push(
        future[j].date
    );

    actualData.push(
        null
    );

    predictedData.push(
        future[j].predicted
    );
}


/* Chart */

var canvas =
    document.getElementById(
        'salesChart'
    );


if (canvas) {

    var ctx =
        canvas.getContext('2d');


    new Chart(
        ctx,
        {

            type: 'line',

            data: {

                labels: labels,

                datasets: [

                    {
                        label:
                            'Actual Sales',

                        data:
                            actualData,

                        borderWidth: 2,

                        tension: 0.3,

                        fill: false
                    },

                    {
                        label:
                            'Linear Regression Prediction',

                        data:
                            predictedData,

                        borderWidth: 2,

                        borderDash:
                            [6, 6],

                        tension: 0.3,

                        fill: false
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                interaction: {

                    mode: 'index',

                    intersect: false
                },

                plugins: {

                    legend: {

                        display: true
                    }

                },

                scales: {

                    x: {

                        ticks: {

                            maxTicksLimit: 12
                        }

                    },

                    y: {

                        beginAtZero: true,

                        title: {

                            display: true,

                            text:
                                'Units Sold'
                        }

                    }

                }

            }

        }
    );
}

</script>

<?php endif; ?>


<?php
include_once(
    'layouts/footer.php'
);
?>
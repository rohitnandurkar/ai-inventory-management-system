<?php
require_once('includes/load.php');
if (!$session->isUserLoggedIn(true)) { redirect('index.php', false); }

$html = '';

/* 1) Auto-suggestion: POST 'product_name' -> returns <li> items (plain HTML) */
if (isset($_POST['product_name']) && strlen($_POST['product_name'])) {
  $q = $_POST['product_name'];
  $products = find_product_by_title($q);
  if ($products) {
    foreach ($products as $product) {
      // escape for HTML attribute and text
      $p_name_attr = htmlspecialchars($product['name'], ENT_QUOTES);
      $p_name_text = htmlspecialchars($product['name'], ENT_NOQUOTES);
      // put data-name attribute so JS can read it; keep plain text visible
      $html .= "<li class=\"list-group-item\" data-name=\"{$p_name_attr}\">{$p_name_text}</li>";
    }
  } else {
    $html .= '<li class="list-group-item">No product found</li>';
  }

  // Return plain HTML (not JSON) — the client places it directly into the DOM
  echo $html;
  exit;
}

/* 2) Get full product info (to fill the sale row): POST 'p_name' -> returns <tr> row(s) */
if (isset($_POST['p_name']) && strlen($_POST['p_name'])) {
  $product_title = remove_junk($db->escape($_POST['p_name']));
  $results = find_all_product_info_by_title($product_title);

  if ($results) {
    foreach ($results as $result) {
      $name = htmlspecialchars($result['name'], ENT_NOQUOTES);
      $sale_price = number_format((float)$result['sale_price'], 2, '.', '');
      $id = (int)$result['id'];

      $html .= "<tr>";
      // Item + hidden id
      $html .= "<td>{$name}<input type=\"hidden\" name=\"s_id[]\" value=\"{$id}\"></td>";
      // Price (editable)
      $html .= "<td><input type=\"text\" class=\"form-control\" name=\"price[]\" value=\"{$sale_price}\"></td>";
      // Quantity (editable)
      $html .= "<td><input type=\"number\" min=\"1\" class=\"form-control\" name=\"quantity[]\" value=\"1\"></td>";
      // Total (auto-calculated; readonly to avoid accidental edits)
      $html .= "<td><input type=\"text\" readonly class=\"form-control\" name=\"total[]\" value=\"{$sale_price}\"></td>";
      // Date (default today)
      $html .= "<td><input type=\"date\" class=\"form-control\" name=\"date[]\" value=\"" . date('Y-m-d') . "\"></td>";
      // Submit button
      $html .= "<td><button type=\"submit\" name=\"add_single_sale\" class=\"btn btn-primary\">Add sale</button></td>";
      $html .= "</tr>";
    }
  } else {
    $html .= '<tr><td colspan="6">Product not registered in database</td></tr>';
  }

  echo $html;
  exit;
}
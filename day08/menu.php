<?php
// 菜單資料：之後會改成從資料庫讀取
$menu = [
    ["id" => 1, "category" => "熱炒", "name" => "三杯雞",   "price" => 180],
    ["id" => 2, "category" => "熱炒", "name" => "鹽酥蝦",   "price" => 220],
    ["id" => 3, "category" => "青菜", "name" => "炒高麗菜", "price" => 100],
    ["id" => 4, "category" => "青菜", "name" => "燙A菜",    "price" => 80],
    ["id" => 5, "category" => "點心", "name" => "蘿蔔糕",   "price" => 50],
];

// 從網址拿分類，沒有就是「全部」
$category = $_GET["category"] ?? "全部";

// 【新增】從網址拿價格上限 max
// - 用 filter_var 檢查是不是 0 以上的整數，例如 max=200
// - 沒有 max、或是亂填（max=abc、max=-5、max=1.5）時得到 null，代表「不限價格」
$max = null;
if (isset($_GET["max"])) {
    $checked = filter_var($_GET["max"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
    if ($checked !== false) {
        $max = $checked;
    }
}

// 篩選菜單（第一步：依分類）
if ($category === "全部") {
    $items = $menu;
} else {
    $items = array_filter($menu, fn($item) => $item["category"] === $category);
}

// 【新增】篩選菜單（第二步：依價格上限）
// 有設定 max 時，只留下價格小於或等於 max 的菜；沒設定就不篩
if ($max !== null) {
    $items = array_filter($items, fn($item) => $item["price"] <= $max);
}

// 計算總價：把傳進來的每道菜價格加起來
function sumPrice(array $items): int {
    $total = 0;
    foreach ($items as $item) {
        $total += $item["price"];
    }
    return $total;
}

// 【新增】產生分類按鈕的網址：一定帶 category；目前有設定 max 時，也把 max 一起帶上
// http_build_query 會自動把中文、特殊符號轉成網址可用的格式（例如 熱炒 → %E7%86%B1%E7%82%92）
function categoryUrl(string $cat, ?int $max): string {
    $params = ["category" => $cat];
    if ($max !== null) {
        $params["max"] = $max;
    }
    return "?" . http_build_query($params);
}

// 【新增】分類按鈕清單，用迴圈產生按鈕，不用每個都手寫網址
$categories = ["全部", "熱炒", "青菜", "點心"];
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PHP 菜單</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container my-4">
  <!-- 頁面標題 -->
  <h1 class="h3">府城小吃菜單</h1>

  <!-- 分類按鈕：點了會重新載入頁面，網址帶上 category 參數 -->
  <!-- 【修改】改用 categoryUrl() 產生網址，有 max 時會一起保留；href 用 htmlspecialchars 跳脫 & 符號 -->
  <div class="mb-3">
    <?php foreach ($categories as $cat): ?>
      <a href="<?= htmlspecialchars(categoryUrl($cat, $max)) ?>" class="btn btn-outline-danger btn-sm"><?= htmlspecialchars($cat) ?></a>
    <?php endforeach; ?>
  </div>

  <!-- 顯示目前分類；用 htmlspecialchars 跳脫，避免 XSS -->
  <p>目前分類：<?= htmlspecialchars($category) ?></p>

  <!-- 【新增】有設定價格上限時才顯示；$max 已確認是整數，可以直接印出 -->
  <?php if ($max !== null): ?>
    <p>價格上限：<?= $max ?> 元以下</p>
  <?php endif; ?>

  <!-- 篩選後沒有任何菜：顯示提示文字；有菜：列出清單與合計 -->
  <?php if (count($items) === 0): ?>
    <p class="text-muted">這個分類沒有餐點</p>
  <?php else: ?>
    <!-- 菜單清單：每道菜一列，左邊菜名、右邊價格 -->
    <ul class="list-group mb-3">
      <?php foreach ($items as $item): ?>
        <li class="list-group-item d-flex justify-content-between">
          <span><?= htmlspecialchars($item["name"]) ?></span>
          <span>$<?= $item["price"] ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <!-- 合計：共幾道菜、全部點的總價 -->
    <p>共 <?= count($items) ?> 道，全點需 $<?= sumPrice($items) ?></p>
  <?php endif; ?>
</body>
</html>

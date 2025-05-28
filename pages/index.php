<?php
include '../includes/connection.php';
include '../includes/sidebar.php';

// ── 1) (Re)connect to your database if not already done ──
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "scms";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Determine which Analytics view to show (default: month)
$analyticsView = $_GET['analytics_view'] ?? 'month';
if (! in_array($analyticsView, ['day','week','month','year'])) {
    $analyticsView = 'month';
}

// ── 2) Fetch your “month” view sales ────────────────────────────
$sql = "
  SELECT
    DATE_FORMAT(`DATE`, '%b %Y') AS label,
    SUM(`GRANDTOTAL`)           AS revenue,
    COUNT(*)                    AS sold
  FROM `transaction`
  GROUP BY YEAR(`DATE`), MONTH(`DATE`)
  ORDER BY `DATE`
";
$result = $conn->query($sql);
$labels  = [];
$revenue = [];
$sold    = [];
while ($row = $result->fetch_assoc()) {
    $labels[]  = $row['label'];
    $revenue[] = (float)$row['revenue'];
    $sold[]    = (int)$row['sold'];
}

// ── 3) JSON‐encode for JavaScript ───────────────────────────────
$salesLabelsJSON = json_encode($labels);
$revenueDataJSON = json_encode($revenue);
$soldDataJSON    = json_encode($sold);

// ── 4A) Trend card: top 1 product ───────────────────────────────
$trendSql = "
  SELECT
    PRODUCT,
    SUM(QTY)             AS total_sold,
    SUM(QTY * PRICE)     AS total_revenue,
    DATE_FORMAT(MAX(`DATE`),'%b %e, %Y') AS date_sold
  FROM transaction_details td
  JOIN `transaction` t ON td.TRANS_ID = t.TRANS_ID
  GROUP BY PRODUCT
  ORDER BY total_sold DESC
  LIMIT 1
";
$trendRes     = $conn->query($trendSql) or die($conn->error);
$trendRow     = $trendRes->fetch_assoc();
$trendProduct = $trendRow['PRODUCT'];
$trendSold    = (int)$trendRow['total_sold'];
$trendRevenue = number_format((float)$trendRow['total_revenue'], 2);
$trendDate    = $trendRow['date_sold'];

// ── Size Distribution by product.unit ────────────────────────────
$sizeSql = "
  SELECT
    p.unit      AS label,
    SUM(td.QTY) AS total
  FROM `transaction` t
  JOIN `transaction_details` td
    ON td.TRANS_ID = t.TRANS_ID
  JOIN `product` p
    ON p.NAME = td.PRODUCT
  GROUP BY p.unit
";
$sizeRes     = $conn->query($sizeSql) or die($conn->error);
$sizeLabels  = [];
$sizeData    = [];
while ($r = $sizeRes->fetch_assoc()) {
    $sizeLabels[] = $r['label'];
    $sizeData[]   = (int)$r['total'];
}
$sizeLabelsJSON = json_encode($sizeLabels);
$sizeDataJSON   = json_encode($sizeData);

$sizeDate = date('F j, Y');



// ── 4C) Product Performance pie chart ───────────────────────────
$ppSql     = "
  SELECT
    PRODUCT,
    COUNT(*) AS total_sold
  FROM transaction_details
  GROUP BY PRODUCT
  ORDER BY total_sold DESC
  LIMIT 10
";
$ppResult      = $conn->query($ppSql) or die($conn->error);
$productNames  = $salesCount = [];
while ($ppRow = $ppResult->fetch_assoc()) {
    $productNames[] = $ppRow['PRODUCT'];
    $salesCount[]   = (int)$ppRow['total_sold'];
}
$productNamesJSON = json_encode($productNames);
$salesCountJSON   = json_encode($salesCount);

// ── Admin verification (unchanged) ──────────────────────────────
$query  = 'SELECT ID, t.TYPE FROM users u JOIN type t ON t.TYPE_ID=u.TYPE_ID WHERE ID=' . $_SESSION['MEMBER_ID'];
$result = mysqli_query($conn, $query) or die(mysqli_error($conn));
while ($row = mysqli_fetch_assoc($result)) {
    if ($row['TYPE'] === 'User') {
        echo "<script>alert('Restricted Page! This is for ADMIN ONLY!');window.location='pos.php';</script>";
        exit;
    }
}
?>



<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    coffee: {
                        light: '#E6D7C3',
                        DEFAULT: '#8B4513',
                        dark: '#5D2906'
                    }
                }
            }
        }
    }
</script>

<div class="p-2">
    <!-- Dashboard Grid - Reduced gap and padding -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- First Column -->
        <div class="space-y-4">
            <!-- Product Quantity Sold -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border-l-4 border-green-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-green-600 uppercase tracking-wide">Product Sold</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">
                                <?php 
                                $query = "SELECT SUM(QTY) FROM transaction_details";
                                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                                while ($row = mysqli_fetch_array($result)) {
                                    echo number_format($row[0]);
                                }
                                ?> 
                                <span class="text-sm font-normal text-gray-600">Units</span>
                            </div>
                        </div>
                        <div class="rounded-full bg-green-100 p-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border-l-4 border-yellow-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-yellow-600 uppercase tracking-wide">Suppliers</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">
                                <?php 
                                $query = "SELECT COUNT(*) FROM supplier";
                                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                                while ($row = mysqli_fetch_array($result)) {
                                    echo number_format($row[0]);
                                }
                                ?>
                                <span class="text-sm font-normal text-gray-600">Partners</span>
                            </div>
                        </div>
                        <div class="rounded-full bg-yellow-100 p-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Column -->
        <div class="space-y-4">
            <!-- Employees Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border-l-4 border-blue-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-blue-600 uppercase tracking-wide">Employees</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">
                                <?php 
                                $query = "SELECT COUNT(*) FROM employee";
                                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                                while ($row = mysqli_fetch_array($result)) {
                                    echo number_format($row[0]);
                                }
                                ?>
                                <span class="text-sm font-normal text-gray-600">Team</span>
                            </div>
                        </div>
                        <div class="rounded-full bg-blue-100 p-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Accounts Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border-l-4 border-red-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-red-600 uppercase tracking-wide">Accounts</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">
                                <?php 
                                $query = "SELECT COUNT(*) FROM users WHERE TYPE_ID=2";
                                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                                while ($row = mysqli_fetch_array($result)) {
                                    echo number_format($row[0]);
                                }
                                ?>
                                <span class="text-sm font-normal text-gray-600">Registered</span>
                            </div>
                        </div>
                        <div class="rounded-full bg-red-100 p-2">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        

        <!-- Third Column -->
        <div class="space-y-4">
            <!-- Products Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border-l-4 border-indigo-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-indigo-600 uppercase tracking-wide">Products</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">
                                <?php 
                                $query = "SELECT COUNT(*) FROM product";
                                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                                while ($row = mysqli_fetch_array($result)) {
                                    echo number_format($row[0]);
                                }
                                ?>
                                <span class="text-sm font-normal text-gray-600">Items</span>
                            </div>
                        </div>
                        <div class="rounded-full bg-indigo-100 p-2">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Earnings Card -->
            <div class="bg-white rounded-lg shadow-md overflow-hidden transition-all duration-300 hover:shadow-lg border-l-4 border-green-500">
                <div class="p-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <div class="text-xs font-bold text-green-600 uppercase tracking-wide">Total Earnings</div>
                            <div class="mt-1 text-2xl font-bold text-gray-800">
                                ₱<?php 
                                $query = "SELECT SUM(CASH) AS total FROM transaction";
                                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo number_format($row['total'], 2);
                                }
                                ?>
                            </div>
                        </div>
                        <div class="rounded-full bg-green-100 p-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fourth Column - Recent Products -->
        <div>
            <div class="bg-white rounded-lg shadow-md overflow-hidden h-full transition-all duration-300 hover:shadow-lg">
                <div class="p-3">
                    <div class="flex items-center mb-2">
                        <svg class="w-5 h-5 text-coffee mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-gray-800">Recent Products</h3>
                    </div>
                    <div class="space-y-1 max-h-80 overflow-y-auto">
                        <?php 
                        $query = "SELECT NAME, PRODUCT_CODE FROM product order by PRODUCT_ID DESC LIMIT 5";
                        $result = mysqli_query($db, $query) or die(mysqli_error($db));
                        while ($row = mysqli_fetch_array($result)) {
                            echo '<div class="flex items-center p-2 rounded-lg transition-colors hover:bg-gray-50">
                                    <span class="w-6 h-6 rounded-full bg-coffee-light flex items-center justify-center mr-2">
                                        <svg class="w-3 h-3 text-coffee" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </span>
                                    <div class="w-full">
                                        <h4 class="text-sm font-medium text-gray-800">' . $row[0] . '</h4>
                                        <p class="text-xs text-gray-500">Code: ' . $row[1] . '</p>
                                    </div>
                                </div>';
                        }
                        ?>
                    </div>
                    <div class="mt-3 pt-2 border-t">
                        <a href="product.php" class="flex items-center justify-center py-1 px-3 bg-coffee text-white rounded-lg text-sm hover:bg-coffee-dark transition-colors">
                            <span>View All Products</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>


<!-- Sales Section Header -->
<div class="row mt-5 mb-5">
  <div class="col-12">
    <div class="bg-white p-4 rounded shadow-sm d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Sales</h4>
      <div id="sales-filters" class="btn-group btn-group-sm">
        <button type="button" class="btn btn-outline-secondary" data-view="day">Day</button>
        <button type="button" class="btn btn-outline-secondary" data-view="week">Week</button>
        <button type="button" class="btn btn-primary"       data-view="month">Month</button>
        <button type="button" class="btn btn-outline-secondary" data-view="year">Year</button>
      </div>
    </div>
  </div>
</div>

<!-- Sales Charts Row -->
<div class="row gx-5 gy-5 mb-5">
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body p-5">
        <h6 class="text-muted mb-4">Total Revenue</h6>
        <div style="height:300px;"><canvas id="chartTotalRevenue"></canvas></div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body p-5">
        <h6 class="text-muted mb-4">Product Sold</h6>
        <div style="height:300px;"><canvas id="chartProductSold"></canvas></div>
      </div>
    </div>
  </div>
</div>

<!-- Another Analytics Header -->
<div class="row mb-3">
  <div class="col-12">
    <div class="bg-white p-4 rounded shadow-sm d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Another Analytics</h4>
      <div id="analytics-filters" class="btn-group btn-group-sm">
        <button type="button" class="btn btn-outline-secondary" data-view="day">Day</button>
        <button type="button" class="btn btn-outline-secondary" data-view="week">Week</button>
        <button type="button" class="btn btn-outline-secondary" data-view="month">Month</button>
        <button type="button" class="btn btn-primary"       data-view="year">Year</button>
      </div>
    </div>
  </div>
</div>

<!-- Another Analytics Cards Row -->
<div class="row gx-5 gy-3">
  <!-- 1) Trends Card -->
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body d-flex p-4">
        <div class="ms-4 text-start">
          <h6 class="text-muted mb-3">Trends</h6>
          <p class="mb-1"><small id="trend-date"><?= $trendDate ?></small></p>
          <p class="mb-1"><strong id="trend-name"><?= htmlspecialchars($trendProduct) ?></strong></p>
          <p class="mb-1">Units Sold: <strong id="trend-sold"><?= number_format($trendSold) ?> pcs</strong></p>
          <p><small>Total Sales: <strong id="trend-revenue">₱<?= $trendRevenue ?></strong></small></p>
        </div>
      </div>
    </div>
  </div>

  <!-- 2) Product Performance Chart -->
  <div class="col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body p-4">
        <h6 class="text-muted text-center mb-4">Product Performance</h6>
        <div style="height:300px;"><canvas id="productPerformanceChart"></canvas></div>
      </div>
    </div>
  </div>

  <!-- 3) Size Distribution Chart -->
  <div class="col-md-3">
    <div class="card shadow-sm h-100">
      <div class="card-body text-center p-4">
        <h6 class="text-muted mb-3">Size Distribution</h6>
        <p class="mb-3"><small id="size-date"><?= $sizeDate ?></small></p>
        <div style="height:250px;"><canvas id="sizeDistributionChart"></canvas></div>
      </div>
    </div>
  </div>
</div>






<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // ─── 1) Sales: Total Revenue ────────────────────────────────────────
  const revenueChart = new Chart(
    document.getElementById('chartTotalRevenue').getContext('2d'),
    {
      type: 'line',
      data: {
        labels: <?= $salesLabelsJSON ?>,
        datasets: [{
          label: 'Revenue',
          data: <?= $revenueDataJSON ?>,
          borderColor: 'rgba(220,53,69,1)',
          backgroundColor: 'rgba(220,53,69,0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    }
  );

  // ─── 2) Sales: Product Sold ─────────────────────────────────────────
  const soldChart = new Chart(
    document.getElementById('chartProductSold').getContext('2d'),
    {
      type: 'line',
      data: {
        labels: <?= $salesLabelsJSON ?>,
        datasets: [{
          label: 'Units Sold',
          data: <?= $soldDataJSON ?>,
          borderColor: 'rgba(255,99,132,1)',
          backgroundColor: 'rgba(255,99,132,0.1)',
          fill: true,
          tension: 0.4
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    }
  );

  // ─── 3) Analytics: Product Performance Pie ──────────────────────────
  const productPerformanceChart = new Chart(
    document.getElementById('productPerformanceChart').getContext('2d'),
    {
      type: 'pie',
      data: {
        labels: <?= $productNamesJSON ?>,
        datasets: [{
          data: <?= $salesCountJSON ?>,
          backgroundColor: [
            '#A0522D','#87CEEB','#D8BFD8','#98FB98',
            '#DAA520','#FFA07A','#66CDAA','#FF69B4',
            '#CD853F','#708090'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'right' } }
      }
    }
  );

  // ─── 4) Analytics: Size Distribution Doughnut ───────────────────────
  const sizeDistributionChart = new Chart(
    document.getElementById('sizeDistributionChart').getContext('2d'),
    {
      type: 'doughnut',
      data: {
        labels: <?= $sizeLabelsJSON ?>,
        datasets: [{
          data: <?= $sizeDataJSON ?>,
          backgroundColor: ['#4169E1','#FF69B4']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } }
      }
    }
  );

  // ─── Helper: Toggle Button Styles ────────────────────────────────
  function activateButton(containerSelector, activeBtn) {
    document.querySelectorAll(containerSelector + ' button').forEach(btn => {
      btn.classList.toggle('btn-primary', btn === activeBtn);
      btn.classList.toggle('btn-outline-secondary', btn !== activeBtn);
    });
  }

  // ─── 5) Sales “View by” AJAX ────────────────────────────────────────
  document.querySelectorAll('#sales-filters button[data-view]')
    .forEach(btn => btn.addEventListener('click', () => {
      activateButton('#sales-filters', btn);
      const view = btn.dataset.view;
      fetch(`sales_data.php?view=${view}`)
        .then(res => res.json())
        .then(data => {
          revenueChart.data.labels           = data.labels;
          revenueChart.data.datasets[0].data = data.revenue;
          revenueChart.update();

          soldChart.data.labels              = data.labels;
          soldChart.data.datasets[0].data    = data.sold;
          soldChart.update();
        })
        .catch(console.error);
    }));

  // ─── 6) Analytics “View by” AJAX ───────────────────────────────────
  document.querySelectorAll('#analytics-filters button[data-view]')
    .forEach(btn => btn.addEventListener('click', () => {
      activateButton('#analytics-filters', btn);
      const view = btn.dataset.view;
      fetch(`another_analytics.php?view=${view}`)
        .then(res => res.json())
        .then(json => {
          // Update Trend card
          document.getElementById('trend-date').textContent    = json.trend.date;
          document.getElementById('trend-name').textContent    = json.trend.name;
          document.getElementById('trend-sold').textContent    = json.trend.sold + ' pcs';
          document.getElementById('trend-revenue').textContent = '₱' + json.trend.revenue.toLocaleString();
          document.getElementById('trend-img').src             = json.trend.imageUrl;

          // Update Product Performance chart
          productPerformanceChart.data.labels    = json.productPerformance.labels;
          productPerformanceChart.data.datasets[0].data = json.productPerformance.data;
          productPerformanceChart.update();

          // Update Size Distribution chart
          document.getElementById('size-date').textContent = json.sizeDistribution.date;
          sizeDistributionChart.data.labels    = json.sizeDistribution.labels;
          sizeDistributionChart.data.datasets[0].data = json.sizeDistribution.data;
          sizeDistributionChart.update();
        })
        .catch(console.error);
    }));

});
</script>







</div>



<!-- Low Stock Items List - Add this after the Sales Chart Section -->
<div class="mt-4 bg-white p-4 rounded-lg shadow-md border-2 border-red-500">
    <div class="flex items-center mb-3">
        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <h2 class="text-lg font-bold text-red-600">CRITICAL LEVEL ITEMS</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="py-2 px-3 text-left">Code</th>
                    <th class="py-2 px-3 text-left">Name</th>
                    <th class="py-2 px-3 text-left">Quantity</th>
                    <th class="py-2 px-3 text-left">Unit</th>
                    <th class="py-2 px-3 text-left">Supplier</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT i.INGREDIENTS_CODE, i.ING_NAME, i.ING_QUANTITY, i.UNIT, s.company_name AS SUPPLIER_NAME
                          FROM ingredients i
                          LEFT JOIN supplier s ON i.supplier_id = s.supplier_id
                          WHERE i.ING_QUANTITY > 0 AND i.ING_QUANTITY <= 5
                          ORDER BY i.ING_QUANTITY ASC
                          LIMIT 5";
                $result = mysqli_query($db, $query) or die(mysqli_error($db));
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr class='border-b hover:bg-gray-50'>";
                        echo "<td class='py-2 px-3'>" . htmlspecialchars($row['INGREDIENTS_CODE']) . "</td>";
                        echo "<td class='py-2 px-3'>" . htmlspecialchars($row['ING_NAME']) . "</td>";
                        echo "<td class='py-2 px-3 font-bold text-red-600'>" . htmlspecialchars($row['ING_QUANTITY']) . "</td>";
                        echo "<td class='py-2 px-3'>" . htmlspecialchars($row['UNIT']) . "</td>";
                        echo "<td class='py-2 px-3'>" . htmlspecialchars($row['SUPPLIER_NAME']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' class='py-3 px-3 text-center'>No critical level items found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="mt-3 text-right">
        <a href="inventory.php" class="inline-flex items-center py-1 px-3 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 transition-colors">
            <span>View All Critical Items</span>
            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
</div>
<?php
// Fetch top-selling products
$query = "SELECT PRODUCT, SUM(QTY) AS total_sales
        FROM transaction_details
        GROUP BY PRODUCT
        ORDER BY total_sales DESC
        LIMIT 10"; // Top 10 products

$result = mysqli_query($db, $query) or die(mysqli_error($db));

// Prepare arrays for chart data
$productNames = [];
$salesData = [];

while ($row = mysqli_fetch_array($result)) {
    $productNames[] = $row['PRODUCT'];
    $salesData[] = (int)$row['total_sales']; // Ensure data is numeric
}

// Encode PHP arrays to JSON for use in JavaScript
$productNamesJSON = json_encode($productNames);
$salesDataJSON = json_encode($salesData);
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script>
    // Get data from PHP
    const productNames = <?php echo $productNamesJSON; ?>;
    const salesData = <?php echo $salesDataJSON; ?>;
    
    // Calculate total for percentages
    const totalSales = salesData.reduce((sum, value) => sum + value, 0);
    
    // Generate colors for the pie slices
    function generateColors(numColors) {
        const baseColors = [
            'rgba(139, 69, 19, 0.7)',   // Brown
            'rgba(54, 162, 235, 0.7)',  // Blue
            'rgba(255, 206, 86, 0.7)',  // Yellow
            'rgba(75, 192, 192, 0.7)',  // Teal
            'rgba(153, 102, 255, 0.7)', // Purple
            'rgba(255, 99, 132, 0.7)',  // Pink
            'rgba(255, 159, 64, 0.7)',  // Orange
            'rgba(46, 204, 113, 0.7)',  // Green
            'rgba(231, 76, 60, 0.7)',   // Red
            'rgba(52, 73, 94, 0.7)'     // Dark blue
        ];
        
        const borderColors = baseColors.map(color => color.replace('0.7', '1'));
        
        return {
            backgroundColor: baseColors.slice(0, numColors),
            borderColor: borderColors.slice(0, numColors)
        };
    }
    
    const colors = generateColors(productNames.length);
    
    // Register the datalabels plugin
    Chart.register(ChartDataLabels);
    
    // Create Pie Chart with enhanced styling
    const ctx = document.getElementById("myPieChart");
    const myPieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: productNames,
            datasets: [{
                data: salesData,
                backgroundColor: colors.backgroundColor,
                borderColor: colors.borderColor,
                borderWidth: 1,
                hoverOffset: 15
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                datalabels: {
                    formatter: (value, ctx) => {
                        const percentage = ((value / totalSales) * 100).toFixed(1);
                        return `${percentage}%`;
                    },
                    color: '#fff',
                    backgroundColor: 'rgba(0, 0, 0, 0.6)',
                    borderRadius: 3,
                    padding: {top: 3, bottom: 3, left: 6, right: 6},
                    font: {weight: 'bold', size: 10},
                    display: function(context) {
                        const value = context.dataset.data[context.dataIndex];
                        const percentage = (value / totalSales) * 100;
                        return percentage > 3; // Only show if bigger than 3%
                    }
                },
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        font: {size: 11}
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const value = context.raw;
                            const percentage = ((value / totalSales) * 100).toFixed(1);
                            return `${context.label}: ${value} units (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                duration: 1200,
                animateRotate: true,
                animateScale: true
            }
        }
    });
</script>

<?php include'../includes/footer.php'; ?>

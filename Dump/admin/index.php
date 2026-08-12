<?php
/**
 * Admin router
 * NOTE: Session auth is not implemented yet.
 */

$currentPage = $_GET['page'] ?? '';

if ($currentPage === '') {
	header('Location: login.php');
	exit;
}

$allowedPages = [
	'dashboard'     => 'dashboard.php',
	'calendar'      => 'calendar.php',
	'all-bookings'  => 'all-bookings.php',
	'research-labs' => 'research-labs.php',
	'csl-labs'      => 'csl-labs.php',
	'pharma-labs'   => 'pharma-labs.php',
	'schedule-block'=> 'schedule-block.php',
	'manage-lab-staff' => 'manage-lab-staff.php',
	'manage-labs'   => 'manage-labs.php',
	'system-report' => 'system-report.php',
	'history'       => 'history.php',
	'notifications' => 'notifications.php',
];

if (!isset($allowedPages[$currentPage])) {
	http_response_code(404);
	$currentPage = 'dashboard';
}

$pageFile = __DIR__ . '/pages/' . $allowedPages[$currentPage];
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Admin Panel - UniKLAB RCMP</title>
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="assets/css/admin.css" />
</head>
<body data-page="<?= htmlspecialchars($currentPage) ?>">

<?php include __DIR__ . '/components/sidebar.php'; ?>

<main class="main">
	<?php include __DIR__ . '/components/header.php'; ?>

	<section class="content">
		<?php
		if (is_file($pageFile)) {
			include $pageFile;
		} else {
			echo '<div class="page-header"><h1>Page Not Found</h1><p>The requested admin page could not be loaded.</p></div>';
		}
		?>
	</section>

	<?php include __DIR__ . '/components/footer.php'; ?>
</main>

<script src="assets/js/admin.js"></script>
</body>
</html>

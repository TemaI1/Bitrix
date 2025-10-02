<?php
session_start();
$count = isset($_SESSION['third-page-count']) ? $_SESSION['third-page-count'] : 0;
?>
<p>Страница 3 была открыта <?php echo $count; ?> раз(а).</p>
<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>

// Handle filter by role
$roleFilter = '';
if (isset($_GET['role'])) {
    $roleFilter = $conn->real_escape_string($_GET['role']);
    $roleCondition = "WHERE Role = '$roleFilter'";
} else {
    $roleCondition = '';
}

// Query to get players sorted by appearances (high to low)
$sql = "SELECT Player, ImageFile, player_id FROM `efootball_stats` $roleCondition ORDER BY Appearances DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Players</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="allplayerpage.css">
    <script>
       function setActiveButton(role) {
    const buttons = document.querySelectorAll('.filter-section .btn');
    buttons.forEach(button => {
        if (role === '') { // If 'All' is selected
            if (button.getAttribute('href').includes('role=')) {
                button.classList.add('btn-secondary');
                button.classList.remove('btn-primary');
            } else {
                button.classList.add('btn-primary');
                button.classList.remove('btn-secondary');
            }
        } else {
            if (button.getAttribute('href').includes(role)) {
                button.classList.add('btn-primary');
                button.classList.remove('btn-secondary');
            } else {
                button.classList.add('btn-secondary');
                button.classList.remove('btn-primary');
            }
        }
    });
}

    </script>
</head>
<body onload="setActiveButton('<?php echo $roleFilter; ?>')">

    <!-- Go Back Button -->
    <div class="go-back-btn-container">
    <a href="MainPage.php" class="go-back-btn">Home</a>
    
</div>

    <!-- Role Filter Section -->
    <div class="filter-section text-center my-4">
        <a href="AllPlayerPage.php" class="btn btn-primary">All</a>
        <a href="AllPlayerPage.php?role=F" class="btn btn-secondary">Forwards</a>
        <a href="AllPlayerPage.php?role=M" class="btn btn-secondary">Midfielders</a>
        <a href="AllPlayerPage.php?role=D" class="btn btn-secondary">Defenders</a>
        <a href="AllPlayerPage.php?role=G" class="btn btn-secondary">Goalkeepers</a>
    </div>

    <div class="container text-center my-5">
    <div class="player-grid row justify-content-center">
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-4 player-card"> <!-- 5 players per row on large screens -->
                    <div class="player-block">
                        <a href="IndividualPlayerPage.php?player_id=<?php echo $row['player_id']; ?>">
                            <img src="get_image.php?id=<?php echo htmlspecialchars($row['player_id']); ?>" 
                                 alt="<?php echo htmlspecialchars($row['Player']); ?>" 
                                 class="img-fluid full-size-image" style="max-height: 250px; width: auto;">
                            <p class="player-name">
                                <strong><?php echo htmlspecialchars($row['Player']); ?></strong>
                            </p>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No players available.</p>
        <?php endif; ?>
    </div>
</div>



    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- Font Awesome -->
</body>
</html>

<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>


// Initialize filters
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'Goals';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$position = isset($_GET['Main_Position']) ? $_GET['Main_Position'] : '';
$role = isset($_GET['Role']) ? $_GET['Role'] : '';
$club = isset($_GET['Club']) ? $_GET['Club'] : '';
$nation = isset($_GET['Nation']) ? $_GET['Nation'] : '';
$appearances_filter = isset($_GET['Appearances']) ? intval($_GET['Appearances']) : 0; // New filter for appearances

// Sanitize inputs to prevent SQL injection
$sort_by = $conn->real_escape_string($sort_by);
$sort_order = $conn->real_escape_string($sort_order);
$position = $conn->real_escape_string($position);
$role = $conn->real_escape_string($role);
$club = $conn->real_escape_string($club);
$nation = $conn->real_escape_string($nation);

// Validate sort_by to prevent SQL injection via column names
$allowed_sort_by = ['Goals', 'Assists', 'G_plus_A', 'Appearances', 'Goal_Ratio', 'Assist_Ratio', 'G_plus_A_Ratio'];
if (!in_array($sort_by, $allowed_sort_by)) {
    $sort_by = 'Goals';
}

// Validate sort_order
$sort_order = strtoupper($sort_order) === 'ASC' ? 'ASC' : 'DESC';

// Construct query based on filters
$query = "SELECT * FROM efootball_stats WHERE 1";

if (!empty($position)) {
    $query .= " AND Main_Position = '$position'";
}

if (!empty($role)) {
    $query .= " AND Role = '$role'";
}

if (!empty($club)) {
    $query .= " AND Club LIKE '%$club%'";
}

if (!empty($nation)) {
    $query .= " AND Nation LIKE '%$nation%'";
}

// Apply the Appearances filter
if ($appearances_filter > 0) {
    $query .= " AND Appearances >= $appearances_filter";
}

$query .= " ORDER BY $sort_by $sort_order";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Stats</title>
    <link rel="stylesheet" href="stats.css">
    <style>
        /* Go Back Button Alignment */
        .go-back-btn-container {
            position: fixed; /* Set to fixed to keep it in view */
            top: 20px; /* Adjust as needed */
            left: 20px; /* Adjust as needed */
            text-align: left; /* Align text to the left */
        }

        .go-back-btn {
            display: inline-block; /* Display button */
            background-color: #ffcc00; /* Same background color */
            color: #000; /* Same text color */
            border: 1px solid #000; /* Adjusted border for consistency */
            font-weight: bold; /* Bold text */
            padding: 10px 20px; /* Padding */
            border-radius: 5px; /* Rounded corners */
            transition: background-color 0.3s ease, transform 0.3s ease; /* Smooth transition */
            text-decoration: none; /* Remove underline */
            font-size: 1.2em; /* Font size */
        }

        .go-back-btn:hover {
            background-color: #ffc107; /* Change background color on hover */
            transform: scale(1.05); /* Scale effect on hover */
        }
    </style>
</head>
<body>

<div class="go-back-btn-container">
    <a href="MainPage.php" class="go-back-btn">Home</a>
</div>

<div class="stats-container">
    <div class="filters-section">
        <h2>Filters</h2>
        <form method="GET">
            <!-- Sort by -->
            <div class="filter-group">
                <label for="sort_by">Sort by:</label>
                <select name="sort_by" id="sort_by">
                    <option value="Goals" <?= $sort_by == 'Goals' ? 'selected' : '' ?>>Goals</option>
                    <option value="Assists" <?= $sort_by == 'Assists' ? 'selected' : '' ?>>Assists</option>
                    <option value="G_plus_A" <?= $sort_by == 'G_plus_A' ? 'selected' : '' ?>>G + A</option>
                    <option value="Appearances" <?= $sort_by == 'Appearances' ? 'selected' : '' ?>>Appearances</option>
                    <option value="Goal_Ratio" <?= $sort_by == 'Goal_Ratio' ? 'selected' : '' ?>>Goal Ratio</option>
                    <option value="Assist_Ratio" <?= $sort_by == 'Assist_Ratio' ? 'selected' : '' ?>>Assist Ratio</option>
                    <option value="G_plus_A_Ratio" <?= $sort_by == 'G_plus_A_Ratio' ? 'selected' : '' ?>>G + A Ratio</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div class="filter-group">
                <label for="sort_order">Sort Order:</label>
                <select name="sort_order" id="sort_order">
                    <option value="DESC" <?= $sort_order == 'DESC' ? 'selected' : '' ?>>High to Low</option>
                    <option value="ASC" <?= $sort_order == 'ASC' ? 'selected' : '' ?>>Low to High</option>
                </select>
            </div>

            <!-- Position Filter -->
            <div class="filter-group">
                <label for="Main_Position">Position:</label>
                <select name="Main_Position" id="Main_Position">
                    <option value="">All Positions</option>
                    <option value="CF" <?= $position == 'CF' ? 'selected' : '' ?>>CF</option>
                    <option value="SS" <?= $position == 'SS' ? 'selected' : '' ?>>SS</option>
                    <option value="LWF" <?= $position == 'LWF' ? 'selected' : '' ?>>LWF</option>
                    <!-- Add other positions as needed -->
                </select>
            </div>

            <!-- Role Filter -->
            <div class="filter-group">
                <label for="Role">Role:</label>
                <select name="Role" id="Role">
                    <option value="">All Roles</option>
                    <option value="F" <?= $role == 'F' ? 'selected' : '' ?>>Forward</option>
                    <option value="M" <?= $role == 'M' ? 'selected' : '' ?>>Midfield</option>
                    <option value="D" <?= $role == 'D' ? 'selected' : '' ?>>Defender</option>
                    <option value="G" <?= $role == 'G' ? 'selected' : '' ?>>Goalkeeper</option>
                </select>
            </div>

            <!-- Club Filter -->
            <div class="filter-group">
                <label for="Club">Club:</label>
                <input type="text" name="Club" id="Club" value="<?= htmlspecialchars($club) ?>" placeholder="Enter club">
            </div>

            <!-- Nation Filter -->
            <div class="filter-group">
                <label for="Nation">Nation:</label>
                <input type="text" name="Nation" id="Nation" value="<?= htmlspecialchars($nation) ?>" placeholder="Enter nation">
            </div>

            <!-- Appearances Filter -->
            <div class="filter-group">
                <label for="Appearances">Appearances (greater than or equal to):</label>
                <input type="number" name="Appearances" id="Appearances" value="<?= $appearances_filter ?>" placeholder="Enter minimum appearances">
            </div>

            <!-- Filter Buttons -->
            <div class="filter-buttons">
                <button type="submit" class="filter-btn">Apply Filters</button>
                <a href="stats.php" class="filter-btn clear-btn">Clear Filters</a>
            </div>
        </form>
    </div>

    <div class="players-section">
        <h1><?= htmlspecialchars($sort_by) ?> Stats</h1> <!-- Dynamically show stat type -->
        <div class="player-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="player-card">
                        <a href="IndividualPlayerPage.php?player_id=<?= $row['player_id'] ?>">
                            <img src="get_image.php?id=<?= $row['player_id'] ?>" alt="<?= htmlspecialchars($row['Player']) ?>" class="player-image">
                            <h2><?= htmlspecialchars($row['Player']) ?> - <?= htmlspecialchars($row[$sort_by]) ?></h2>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-results">No players found with the selected filters.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>

<?php
$conn->close(); // Close the database connection
?>

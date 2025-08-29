<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>


// Array of available statistics
$statTypes = [
    // General Stats
    "Most Appearances" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY Appearances DESC LIMIT 1",
    "Most Goals" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY Goals DESC LIMIT 1",
    "Most Assists" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY Assists DESC LIMIT 1",
    "Most Goal Ratio" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY Goal_Ratio DESC LIMIT 1",
    "Most Assist Ratio" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY Assist_Ratio DESC LIMIT 1",
    "Most G+A Ratio" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY G_plus_A_Ratio DESC LIMIT 1",
    "Newest Entry" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Appearances >= 25 ORDER BY player_id DESC LIMIT 1",
    
    // Midfielder Stats
    "Most Appearances [Midfielder]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'M' AND Appearances >= 25 ORDER BY Appearances DESC LIMIT 1",
    "Most Goals [Midfielder]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'M' AND Appearances >= 25 ORDER BY Goals DESC LIMIT 1",
    "Most Assists [Midfielder]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'M' AND Appearances >= 25 ORDER BY Assists DESC LIMIT 1",
    "Most Goal Ratio [Midfielder]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'M' AND Appearances >= 25 ORDER BY Goal_Ratio DESC LIMIT 1",
    "Most Assist Ratio [Midfielder]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'M' AND Appearances >= 25 ORDER BY Assist_Ratio DESC LIMIT 1",
    "Most G+A Ratio [Midfielder]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'M' AND Appearances >= 25 ORDER BY G_plus_A_Ratio DESC LIMIT 1",
    
    // Forward Stats
    "Most Appearances [Forward]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'F' AND Appearances >= 25 ORDER BY Appearances DESC LIMIT 1",
    "Most Goals [Forward]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'F' AND Appearances >= 25 ORDER BY Goals DESC LIMIT 1",
    "Most Assists [Forward]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'F' AND Appearances >= 25 ORDER BY Assists DESC LIMIT 1",
    "Most Goal Ratio [Forward]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'F' AND Appearances >= 25 ORDER BY Goal_Ratio DESC LIMIT 1",
    "Most Assist Ratio [Forward]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'F' AND Appearances >= 25 ORDER BY Assist_Ratio DESC LIMIT 1",
    "Most G+A Ratio [Forward]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'F' AND Appearances >= 25 ORDER BY G_plus_A_Ratio DESC LIMIT 1",
    
    // Defender Stats
    "Most Appearances [Defender]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'D' AND Appearances >= 25 ORDER BY Appearances DESC LIMIT 1",
    "Most Goals [Defender]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'D' AND Appearances >= 25 ORDER BY Goals DESC LIMIT 1",
    "Most Assists [Defender]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'D' AND Appearances >= 25 ORDER BY Assists DESC LIMIT 1",
    
    // Goalkeeper Stats
    "Most Appearances [Goalkeeper]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'G' AND Appearances >= 25 ORDER BY Appearances DESC LIMIT 1",
    "Most Goals [Goalkeeper]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'G' AND Appearances >= 25 ORDER BY Goals DESC LIMIT 1",
    "Most Assists [Goalkeeper]" => "SELECT Player, ImageFile, player_id FROM `efootball_stats` WHERE Role = 'G' AND Appearances >= 25 ORDER BY Assists DESC LIMIT 1"
];

// Function to pick valid stats
function fetchValidStats($statTypes, $conn, $limit = 3) {
    $statsData = [];
    $triedQueries = [];
    while (count($statsData) < $limit) {
        // Get a random stat type
        $randomKey = array_rand($statTypes);
        $query = $statTypes[$randomKey];

        // Make sure we haven't already tried this query
        if (in_array($randomKey, $triedQueries)) {
            continue; // Skip this query if already tried
        }

        $triedQueries[] = $randomKey; // Mark this query as tried

        // Execute the query
        $result = $conn->query($query);

        // Check if the result is valid and fetch the data
        if ($result && $result->num_rows > 0) {
            $statsData[] = [
                'statType' => $randomKey,
                'data' => $result->fetch_assoc()
            ];
        }

        // If we've tried all stat types and still don't have enough data, stop
        if (count($triedQueries) === count($statTypes)) {
            break;
        }
    }
    return $statsData;
}

// Fetch 3 valid statistics
$statsData = fetchValidStats($statTypes, $conn, 3);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MainPage</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="mainpage.css">
</head>
<body>

    <!-- Middle Section with 3 Image Placeholders -->
    <div class="container text-center my-5">
    <div class="stats-section row justify-content-center" style="padding-top: 1px;">
    <?php if (!empty($statsData)): ?>
        <?php foreach ($statsData as $stat): ?>
            <div class="col-md-4 col-sm-6">
                <div class="stat-block">
                    <a href="IndividualPlayerPage.php?player_id=<?php echo $stat['data']['player_id']; ?>">
                        <img src="get_image.php?id=<?php echo $stat['data']['player_id']; ?>" alt="<?php echo htmlspecialchars($stat['data']['Player']); ?>" class="img-fluid">
                        <p class="stat-text">
                            <strong><?php echo htmlspecialchars($stat['statType']); ?></strong> : 
                            <span class="player-name" style="font-weight: bold;"><?php echo htmlspecialchars($stat['data']['Player']); ?></span>
                        </p>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No statistics available.</p>
    <?php endif; ?>
    </div>
</div>


    <!-- Options Section -->
    <div class="options-section d-flex justify-content-around align-items-center">
        <a href="MainPage.php" class="option">
            <div class="icon match-icon" style="background-image: url('Images/1w.png');"></div>
            <p>Home</p>
        </a>
        <a href="Stats.php" class="option">
            <div class="icon gameplan-icon" style="background-image: url('Images/5w.png');"></div>
            <p>Stats</p>
        </a>
        <a href="AddPlayer.php" class="option">
            <div class="icon myteam-icon" style="background-image: url('Images/3w.png');"></div>
            <p>Add Player</p>
        </a>
        <a href="AllPlayerPage.php" class="option">
            <div class="icon dailygame-icon" style="background-image: url('Images/4w.png');"></div>
            <p>All Players</p>
        </a>
        <a href="IndividualPlayerPage.php" class="option">
            <div class="icon extras-icon" style="background-image: url('Images/2w.png');"></div>
            <p>Update Player</p>
        </a>
    </div>

</body>
</html>

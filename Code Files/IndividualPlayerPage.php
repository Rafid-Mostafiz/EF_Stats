<?php
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>

<?php require __DIR__ . '/db_ro.php'; ?>



// Fetch the player_id from the previous page or default to 13
$player_id = isset($_GET['player_id']) ? intval($_GET['player_id']) : 3;

// Function to fetch player data
function getPlayerData($conn, $player_id) {
    $sql = "SELECT * FROM efootball_stats WHERE player_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $player = $result->fetch_assoc();
    $stmt->close();
    return $player;
}

// Fetch player data
$player = getPlayerData($conn, $player_id);

// If no player found, fetch player with ID 3
if (!$player) {
    $player_id = 3;
    $player = getPlayerData($conn, $player_id);
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $player_name = htmlspecialchars(trim($_POST['Player']));
    $appearances = intval($_POST['Appearances']);
    $goals = intval($_POST['Goals']);
    $assists = intval($_POST['Assists']);
    $role = htmlspecialchars(trim($_POST['Role']));
    $main_position = htmlspecialchars(trim($_POST['Main_Position']));
    $sub_position1 = htmlspecialchars(trim($_POST['Sub_Position1']));
    $sub_position2 = htmlspecialchars(trim($_POST['Sub_Position2']));
    $sub_position3 = htmlspecialchars(trim($_POST['Sub_Position3']));
    $type = htmlspecialchars(trim($_POST['Type']));
    $club = htmlspecialchars(trim($_POST['Club']));
    $nation = htmlspecialchars(trim($_POST['Nation']));

// Handling player deletion
if (isset($_POST['deletePlayer'])) {
    $delete_sql = "DELETE FROM efootball_stats WHERE player_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $player_id);

    if ($delete_stmt->execute()) {
        // Redirect to the main page after deletion
        header("Location: MainPage.php");
        exit;
    } else {
        echo "Error deleting player: " . $delete_stmt->error;
    }
    $delete_stmt->close();
}

    // Calculate automatically based on appearances, goals, and assists
    $goal_ratio = $appearances > 0 ? $goals / $appearances : 0;
    $assist_ratio = $appearances > 0 ? $assists / $appearances : 0;
    $g_plus_a = $goals + $assists;
    $g_plus_a_ratio = $appearances > 0 ? $g_plus_a / $appearances : 0;
if (isset($_POST['uploadImage'])) {
    // Check if a file was uploaded
    if (isset($_FILES['imageUpload']) && $_FILES['imageUpload']['error'] == UPLOAD_ERR_OK) {
        $imageFile = $_FILES['imageUpload']['tmp_name'];
        $imageName = $_FILES['imageUpload']['name'];
        $imagePath = 'uploads/' . basename($imageName);

        // Move the uploaded file to the desired directory
        if (move_uploaded_file($imageFile, $imagePath)) {
            // Update the player's image path in the database
            $update_image_sql = "UPDATE efootball_stats SET ImageFile = ? WHERE player_id = ?";
            $update_image_stmt = $conn->prepare($update_image_sql);
            $update_image_stmt->bind_param("si", $imagePath, $player_id);

            if ($update_image_stmt->execute()) {
                // Refresh the page to reflect changes
                header("Location: IndividualPlayerPage.php?player_id=$player_id");
                exit;
            } else {
                echo "Error updating image record: " . $update_image_stmt->error;
            }

            $update_image_stmt->close();
        } else {
            echo "Failed to upload image.";
        }
    } else {
        echo "No file uploaded or upload error.";
    }
}
    // Prepare update statement
    $update_sql = "UPDATE efootball_stats SET 
    Player = ?, 
    Appearances = ?, 
    Goals = ?, 
    Assists = ?, 
    G_plus_A = ?, 
    G_plus_A_Ratio = ?, 
    Goal_Ratio = ?, 
    Assist_Ratio = ?, 
    Role = ?, 
    Main_Position = ?, 
    Sub_Position1 = ?, 
    Sub_Position2 = ?, 
    Sub_Position3 = ?, 
    Type = ?, 
    Club = ?, 
    Nation = ?
    WHERE player_id = ?";

    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters: include the automatically calculated values
    $update_stmt->bind_param(
        "siiiidddssssssssi", 
        $player_name, 
        $appearances, 
        $goals, 
        $assists, 
        $g_plus_a, 
        $g_plus_a_ratio, 
        $goal_ratio, 
        $assist_ratio, 
        $role, 
        $main_position, 
        $sub_position1, 
        $sub_position2, 
        $sub_position3, 
        $type, 
        $club, 
        $nation, 
        $player_id
    );
    
    // Execute the update
    if ($update_stmt->execute()) {
        // Refresh the page to reflect changes
        header("Location: IndividualPlayerPage.php?player_id=$player_id");
        exit;
    } else {
        echo "Error updating record: " . $update_stmt->error;
    }

    $update_stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($player['Player']); ?>'s Stats</title>
    <link rel="stylesheet" href="individualplayerpage.css">
</head>
<body>
<div class="go-back-btn-container">
    <a href="MainPage.php" class="go-back-btn">Home</a>
    <a href="AllPlayerPage.php" class="go-back-btn2">All Players</a>
    <a href="ChangePicture.php?player_id=<?php echo $player_id; ?>" class="go-back-btn">Change Picture</a>
    <form method="POST" style="display: inline;">
        <button type="submit" name="deletePlayer" class="go-back-btn">Delete</button>
    </form>
</div>

    <div class="player-container">
    <div class="left-section">
    <?php if (!empty($player['ImageFile'])): ?>
        <img src="get_image2.php?player_id=<?php echo $player_id; ?>" alt="Player Image" class="player-image">
    <?php else: ?>
        <img src="placeholder.jpg" alt="No Image Available" class="player-image">
    <?php endif; ?>
    
    <!-- Player's Name -->
    <h1><?php echo htmlspecialchars($player['Player']); ?></h1>
</div>

        <div class="right-section">
            <form method="POST" action="">
                <div class="important-stats">
                <div class="stat-box">
    <label for="Player">Player Name</label>
    <input type="text" id="Player" name="Player" value="<?php echo htmlspecialchars($player['Player']); ?>" required>
</div>
                    <div class="stat-box">
                        <label for="Appearances">Appearances</label>
                        <input type="number" id="Appearances" name="Appearances" value="<?php echo htmlspecialchars($player['Appearances']); ?>" required>
                    </div>
                    <div class="stat-box">
                        <label for="Goals">Goals</label>
                        <input type="number" id="Goals" name="Goals" value="<?php echo htmlspecialchars($player['Goals']); ?>" required>
                    </div>
                    <div class="stat-box">
    <label for="Goal_Ratio">Goal Ratio</label>
    <input type="number" step="0.01" id="Goal_Ratio" name="Goal_Ratio" value="<?php echo number_format($player['Goal_Ratio'], 2); ?>" readonly>
</div>
                    <div class="stat-box">
                        <label for="Assists">Assists</label>
                        <input type="number" id="Assists" name="Assists" value="<?php echo htmlspecialchars($player['Assists']); ?>" required>
                    </div>
                    <div class="stat-box">
    <label for="Assist_Ratio">Assist Ratio</label>
    <input type="number" step="0.01" id="Assist_Ratio" name="Assist_Ratio" value="<?php echo number_format($player['Assist_Ratio'], 2); ?>" readonly>
</div>

<div class="stat-box">
    <label for="G_plus_A">G+A</label>
    <input type="number" id="G_plus_A" name="G_plus_A" value="<?php echo $player['G_plus_A']; ?>" readonly>
</div>


                    
                    
<div class="stat-box">
    <label for="G_plus_A_Ratio">G+A Ratio</label>
    <input type="number" step="0.01" id="G_plus_A_Ratio" name="G_plus_A_Ratio" value="<?php echo number_format($player['G_plus_A_Ratio'], 2); ?>" readonly>
</div>
                    
                </div>

                <div class="other-stats">
                    <div class="stat-box">
                        <label for="Role">Role</label>
                        <input type="text" id="Role" name="Role" value="<?php echo htmlspecialchars($player['Role']); ?>" required>
                    </div>
                    <div class="stat-box">
                        <label for="Main_Position">Main Position</label>
                        <input type="text" id="Main_Position" name="Main_Position" value="<?php echo htmlspecialchars($player['Main_Position']); ?>" required>
                    </div>
                    <div class="stat-box">
                        <label for="Sub_Position1">Sub Position 1</label>
                        <input type="text" id="Sub_Position1" name="Sub_Position1" value="<?php echo htmlspecialchars($player['Sub_Position1']); ?>">
                    </div>
                    <div class="stat-box">
                        <label for="Sub_Position2">Sub Position 2</label>
                        <input type="text" id="Sub_Position2" name="Sub_Position2" value="<?php echo htmlspecialchars($player['Sub_Position2']); ?>">
                    </div>
                    <div class="stat-box">
                        <label for="Sub_Position3">Sub Position 3</label>
                        <input type="text" id="Sub_Position3" name="Sub_Position3" value="<?php echo htmlspecialchars($player['Sub_Position3']); ?>">
                    </div>
                    <div class="stat-box">
                        <label for="Type">Type</label>
                        <input type="text" id="Type" name="Type" value="<?php echo htmlspecialchars($player['Type']); ?>">
                    </div>
                    <div class="stat-box">
                        <label for="Club">Club</label>
                        <input type="text" id="Club" name="Club" value="<?php echo htmlspecialchars($player['Club']); ?>" required>
                    </div>
                    <div class="stat-box">
                        <label for="Nation">Nation</label>
                        <input type="text" id="Nation" name="Nation" value="<?php echo htmlspecialchars($player['Nation']); ?>" required>
                    </div>
                </div>

                <button type="submit" class="update-btn">Update Player</button>
            </form>
        </div>
    </div>
</body>
</html>

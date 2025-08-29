<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>


// Initialize variables
$errors = [];
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate inputs
    $player_name = htmlspecialchars(trim($_POST['Player']));
    $appearances = intval($_POST['Appearances']);
    $goals = intval($_POST['Goals']);
    $assists = intval($_POST['Assists']);
    $role = htmlspecialchars(trim($_POST['Role']));
    $main_position = htmlspecialchars(trim($_POST['Main_Position']));
    $club = htmlspecialchars(trim($_POST['Club']));
    $nation = htmlspecialchars(trim($_POST['Nation']));

    // Validate required fields
    if (empty($player_name)) {
        $errors[] = "Player name is required.";
    }
    if ($appearances < 0) {
        $errors[] = "Appearances cannot be negative.";
    }
    if ($goals < 0) {
        $errors[] = "Goals cannot be negative.";
    }
    if ($assists < 0) {
        $errors[] = "Assists cannot be negative.";
    }
    if (empty($role) || !in_array($role, ['F', 'M', 'D', 'G'])) {
        $errors[] = "Role must be one of F, M, D, G.";
    }
    if (empty($main_position)) {
        $errors[] = "Main Position is required.";
    }
    if (empty($club)) {
        $errors[] = "Club is required.";
    }
    if (empty($nation)) {
        $errors[] = "Nation is required.";
    }

    // Handle image upload
    $imageData = null;
    if (isset($_FILES['imageUpload']) && $_FILES['imageUpload']['error'] == UPLOAD_ERR_OK) {
        // Validate if the uploaded file is an image
        $fileType = mime_content_type($_FILES['imageUpload']['tmp_name']);
        if (strpos($fileType, 'image/') === 0) {
            $imageFile = $_FILES['imageUpload']['tmp_name'];
            $imageData = file_get_contents($imageFile); // Convert image file to binary data
        } else {
            $errors[] = "Uploaded file is not a valid image.";
        }
    } else {
        $errors[] = "Image upload is required.";
    }

    // If no errors, proceed to insert data
    if (empty($errors)) {
        // Calculate automatically based on appearances, goals, and assists
        $g_plus_a = $goals + $assists;
        $goal_ratio = $appearances > 0 ? round($goals / $appearances, 3) : 0;
        $assist_ratio = $appearances > 0 ? round($assists / $appearances, 3) : 0;
        $g_plus_a_ratio = $appearances > 0 ? round($g_plus_a / $appearances, 3) : 0;

        // Prepare the SQL statement
        $insert_sql = "INSERT INTO efootball_stats 
            (Player, Appearances, Goals, Assists, G_plus_A, G_plus_A_Ratio, Goal_Ratio, Assist_Ratio, 
             Role, Main_Position, Club, Nation, ImageFile) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insert_sql);
        if ($stmt) {
            // Bind parameters with the correct data types, including the longblob for the image
            $stmt->bind_param(
                "siiiidddsssss", // Use 'b' for blob (binary data)
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
                $club,
                $nation,
                $imageData // This is the binary data for the image
            );

            // Execute the statement
            if ($stmt->execute()) {
                // Get the last inserted player ID
                $player_id = $conn->insert_id;
                
                // Redirect to IndividualPlayer.php with the player_id
                header("Location: IndividualPlayerPage.php?player_id=" . $player_id);
                exit(); // Ensure no further code is executed
            } else {
                $errors[] = "Error inserting player: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $errors[] = "Prepare failed: " . $conn->error;
        }
    }
}

$conn->close();
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Player</title>
    <link rel="stylesheet" href="addplayer.css">
</head>
<body>
    <div class="go-back-btn-container">
        <a href="MainPage.php" class="go-back-btn">Home</a>
        <a href="AllPlayerPage.php" class="go-back-btn2">All Players</a>
    </div>

    <div class="container">
        <h1>Add New Player</h1>

        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="add-player-form">
            <div class="important-stats">
                <div class="stat-box">
                    <label for="Player">Player Name</label>
                    <input type="text" id="Player" name="Player" value="<?php echo isset($_POST['Player']) ? htmlspecialchars($_POST['Player']) : ''; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="Appearances">Appearances</label>
                    <input type="number" id="Appearances" name="Appearances" min="0" value="<?php echo isset($_POST['Appearances']) ? htmlspecialchars($_POST['Appearances']) : '0'; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="Goals">Goals</label>
                    <input type="number" id="Goals" name="Goals" min="0" value="<?php echo isset($_POST['Goals']) ? htmlspecialchars($_POST['Goals']) : '0'; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="Assists">Assists</label>
                    <input type="number" id="Assists" name="Assists" min="0" value="<?php echo isset($_POST['Assists']) ? htmlspecialchars($_POST['Assists']) : '0'; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="Role">Role</label>
                    <select id="Role" name="Role" required>
                        <option value="">Select Role</option>
                        <option value="F" <?php echo (isset($_POST['Role']) && $_POST['Role'] == 'F') ? 'selected' : ''; ?>>Forward (F)</option>
                        <option value="M" <?php echo (isset($_POST['Role']) && $_POST['Role'] == 'M') ? 'selected' : ''; ?>>Midfielder (M)</option>
                        <option value="D" <?php echo (isset($_POST['Role']) && $_POST['Role'] == 'D') ? 'selected' : ''; ?>>Defender (D)</option>
                        <option value="G" <?php echo (isset($_POST['Role']) && $_POST['Role'] == 'G') ? 'selected' : ''; ?>>Goalkeeper (G)</option>
                    </select>
                </div>
                <div class="stat-box">
                    <label for="Main_Position">Main Position</label>
                    <input type="text" id="Main_Position" name="Main_Position" value="<?php echo isset($_POST['Main_Position']) ? htmlspecialchars($_POST['Main_Position']) : ''; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="Club">Club</label>
                    <input type="text" id="Club" name="Club" value="<?php echo isset($_POST['Club']) ? htmlspecialchars($_POST['Club']) : ''; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="Nation">Nation</label>
                    <input type="text" id="Nation" name="Nation" value="<?php echo isset($_POST['Nation']) ? htmlspecialchars($_POST['Nation']) : ''; ?>" required>
                </div>
                <div class="stat-box">
                    <label for="imageUpload">Player Image</label>
                    <input type="file" id="imageUpload" name="imageUpload" accept="image/*" required>
                </div>
            </div>

            <button type="submit" class="update-btn">Add Player</button>
        </form>
    </div>
</body>
</html>

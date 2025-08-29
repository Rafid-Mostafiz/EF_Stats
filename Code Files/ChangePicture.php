<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>


// Initialize player_id and current image
$player_id = isset($_GET['player_id']) ? intval($_GET['player_id']) : 0;
$current_image = null;
$player_name = "";

// Fetch player's current image and name for display purposes
if ($player_id > 0) {
    $stmt = $conn->prepare("SELECT ImageFile, Player FROM efootball_stats WHERE player_id = ?");
    $stmt->bind_param("i", $player_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_image = $row['ImageFile']; // This will now contain the binary image data or NULL if not available
        $player_name = $row['Player'];
    } else {
        echo "Player not found.";
    }

    $stmt->close();
} else {
    echo "No player ID specified.";
}

// Handle form submission for image update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if a file was uploaded
    if (isset($_FILES['imageUpload']) && $_FILES['imageUpload']['error'] == UPLOAD_ERR_OK) {
        // Validate if the uploaded file is an image
        $fileType = mime_content_type($_FILES['imageUpload']['tmp_name']);
        if (strpos($fileType, 'image/') === 0) {
            $imageFile = $_FILES['imageUpload']['tmp_name'];
            $imageData = file_get_contents($imageFile);

            // Prepare the SQL statement to update the image and increment 'testing'
            $update_image_sql = "UPDATE efootball_stats SET ImageFile = ?, testing = testing + 1 WHERE player_id = ?";
            $update_image_stmt = $conn->prepare($update_image_sql);

            if ($update_image_stmt) {
                // Bind the parameters (s for binary string, i for integer)
                $update_image_stmt->bind_param("si", $imageData, $player_id);

                // Execute the statement
                if ($update_image_stmt->execute()) {
                    // Redirect to the same page after successful upload
                    header("Location: " . $_SERVER['PHP_SELF'] . "?player_id=$player_id");
                    exit;
                } else {
                    echo "Error updating image record: " . $update_image_stmt->error;
                }
                $update_image_stmt->close();
            } else {
                echo "Error preparing statement: " . $conn->error;
            }
        } else {
            echo "Uploaded file is not a valid image.";
        }
    } else {
        echo "No file uploaded.";
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Picture for Player</title>
    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: url('Images/emirates.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            text-align: center;
            background-color: rgba(0, 0, 0, 0.7);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        h1 {
            margin-bottom: 20px;
        }

        .upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .upload-form input[type="file"] {
            margin-bottom: 20px;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }

        .upload-form button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .upload-form button:hover {
            background-color: #218838;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Change Picture for Player: <?php echo htmlspecialchars($player_name); ?></h1>
        <?php if ($current_image): ?>
            <div>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($current_image); ?>" alt="Player Image" style="max-width: 200px; max-height: 200px; margin-bottom: 20px;">
            </div>
        <?php else: ?>
            <p>No image available for this player.</p>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <input type="file" name="imageUpload" accept="image/*" required>
            <button type="submit">Upload New Picture</button>
        </form>
        <a href="IndividualPlayerPage.php?player_id=<?php echo $player_id; ?>" class="back-button">Back to Player Stats</a>
    </div>
</body>
</html>

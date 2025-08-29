<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>


// Check if 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Prepare and execute the query to get the image
    $stmt = $conn->prepare("SELECT ImageFile FROM `efootball_stats` WHERE player_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Set the content type header
        header("Content-Type: image/jpeg");
        
        // Output the image data
        echo $row['ImageFile'];
    } else {
        echo "Image not found.";
    }
    
    $stmt->close();
} else {
    echo "No ID specified.";
}

$conn->close();
?>

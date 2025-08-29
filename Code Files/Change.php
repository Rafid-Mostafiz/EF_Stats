<?php
<?php require __DIR__ . '/db_ro.php'; ?>
<?php
require __DIR__ . '/read_only.php';
if (READ_ONLY && $_SERVER['REQUEST_METHOD'] !== 'GET') {
  http_response_code(403);
  die('⚠️ Site is currently read-only. Updates are disabled.');
}
?>


// Set default player ID (change this based on dynamic input)
$player_id = isset($_GET['player_id']) ? intval($_GET['player_id']) : 12;

// Fetch player name
$name_sql = "SELECT Player FROM efootball_stats WHERE player_id = ?";
$name_stmt = $conn->prepare($name_sql);
$name_stmt->bind_param("i", $player_id);
$name_stmt->execute();
$name_stmt->bind_result($player_name);
$name_stmt->fetch();
$name_stmt->close();

// Fetch change history for the player ordered by change date
$sql = "SELECT * FROM player_stats_history WHERE player_id = ? ORDER BY change_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $player_id);
$stmt->execute();
$result = $stmt->get_result();
$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Player Change Log</title>
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
            flex-direction: column;
        }
        table {
            width: 80%;
            margin-top: 20px;
            border-collapse: collapse;
            background-color: rgba(0, 0, 0, 0.6);
        }
        table th, table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: center;
        }
        table th {
            background-color: #4caf50;
        }
        .home-btn {
            position: absolute; /* Move button to the top left corner */
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background-color: yellow; /* Set button color to yellow */
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: black; /* Set text color for contrast */
            border-radius: 5px;
        }
        .home-btn:hover {
            background-color: #d4d400; /* Darken on hover */
        }
    </style>
</head>
<body>

<h1>Change Log for Player: <?php echo htmlspecialchars($player_name); ?></h1>

<table>
    <tr>
        <th>Change Date</th>
        <th>Previous Appearances</th>
        <th>New Appearances</th>
        <th>Games Change</th>
        <th>Goals Change</th>
        <th>Assists Change</th>
        <th>G + A</th>
        <th>Goal Ratio</th>
        <th>Assist Ratio</th>
        <th>G + A Ratio</th>
    </tr>
    <?php foreach ($history as $entry): ?>
        <tr>
            <td><?php echo htmlspecialchars($entry['change_date']); ?></td>
            <td><?php echo htmlspecialchars($entry['previous_appearances']); ?></td>
            <td><?php echo htmlspecialchars($entry['new_appearances']); ?></td>
            <td><?php echo htmlspecialchars($entry['games_change']); ?></td>
            <td><?php echo htmlspecialchars($entry['goals_change']); ?></td>
            <td><?php echo htmlspecialchars($entry['assists_change']); ?></td>
            <td><?php echo htmlspecialchars($entry['goals_change'] + $entry['assists_change']); ?></td>
            <td><?php echo number_format($entry['g_ratio'], 2); ?></td>
            <td><?php echo number_format($entry['a_ratio'], 2); ?></td>
            <td><?php echo number_format($entry['g_a_ratio'], 2); ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<!-- Home button to navigate back to MainPage.php -->
<button onclick="window.location.href='MainPage.php'" class="home-btn">Home</button>

</body>
</html>

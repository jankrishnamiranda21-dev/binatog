<?php
include 'db_connect.php';

// Handle Add Team
if (isset($_POST['add_team'])) {
    $team_name = $_POST['team_name'];
    $conn->query("INSERT INTO teams (team_name) VALUES ('$team_name')");
}

// Handle Add Match
if (isset($_POST['add_match'])) {
    $team_id = $_POST['team_id'];
    $points = $_POST['points_scored'];
    $date = $_POST['match_date'];
    $conn->query("INSERT INTO matches (team_id, points_scored, match_date) VALUES ('$team_id', '$points', '$date')");
}

// Handle Update Match
if (isset($_POST['update_match'])) {
    $match_id = $_POST['match_id'];
    $team_id = $_POST['team_id'];
    $points = $_POST['points_scored'];
    $date = $_POST['match_date'];
    $conn->query("UPDATE matches SET team_id='$team_id', points_scored='$points', match_date='$date' WHERE match_id='$match_id'");
}

// Handle Delete Match
if (isset($_GET['delete'])) {
    $match_id = $_GET['delete'];
    $conn->query("DELETE FROM matches WHERE match_id='$match_id'");
}

// Fetch Rankings
$rankings_sql = "
SELECT 
    t.team_name,
    (SELECT SUM(m.points_scored) FROM matches m WHERE m.team_id = t.team_id) AS total_points
FROM teams t
ORDER BY total_points DESC
";
$rankings = $conn->query($rankings_sql);

// Fetch Matches for management table
$matches_sql = "SELECT m.match_id, t.team_name, m.points_scored, m.match_date, t.team_id 
                FROM matches m JOIN teams t ON m.team_id = t.team_id
                ORDER BY m.match_date DESC";
$matches = $conn->query($matches_sql);

// Fetch teams for dropdown
$teams = $conn->query("SELECT * FROM teams");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sports Team Rankings</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Sports Team Rankings</h1>
    
    <!-- Add Team Form -->
    <h2>Add New Team</h2>
    <form method="POST">
        <input type="text" name="team_name" placeholder="Team Name" required>
        <button type="submit" name="add_team">Add Team</button>
    </form>

    <!-- Rankings Table -->
    <h2>Team Rankings</h2>
    <table>
        <tr>
            <th>Rank</th>
            <th>Team Name</th>
            <th>Total Points</th>
        </tr>
        <?php
        $rank = 1;
        if ($rankings->num_rows > 0) {
            while($row = $rankings->fetch_assoc()) {
                echo "<tr>";
                echo "<td class='rank'>".$rank."</td>";
                echo "<td>".$row['team_name']."</td>";
                echo "<td>".$row['total_points']."</td>";
                echo "</tr>";
                $rank++;
            }
        }
        ?>
    </table>

    <!-- Manage Matches -->
    <h2>Manage Matches</h2>
    <table>
        <tr>
            <th>Match ID</th>
            <th>Team</th>
            <th>Points</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php
        while($row = $matches->fetch_assoc()) {
            echo "<tr>";
            echo "<td>".$row['match_id']."</td>";
            echo "<td>".$row['team_name']."</td>";
            echo "<td>".$row['points_scored']."</td>";
            echo "<td>".$row['match_date']."</td>";
            echo "<td>
                    <a href='?edit=".$row['match_id']."'>Edit</a> | 
                    <a href='?delete=".$row['match_id']."' onclick='return confirm(\"Delete this match?\")'>Delete</a>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <!-- Add/Edit Match Form -->
    <?php
    if (isset($_GET['edit'])) {
        $match_id = $_GET['edit'];
        $edit_sql = "SELECT * FROM matches WHERE match_id='$match_id'";
        $edit_result = $conn->query($edit_sql)->fetch_assoc();
        $edit_team = $edit_result['team_id'];
        $edit_points = $edit_result['points_scored'];
        $edit_date = $edit_result['match_date'];
        $edit_mode = true;
    } else {
        $edit_mode = false;
        $edit_team = $edit_points = $edit_date = '';
    }
    ?>
    <h2><?php echo $edit_mode ? "Edit Match" : "Add Match"; ?></h2>
    <form method="POST">
        <input type="hidden" name="match_id" value="<?php echo $edit_mode ? $match_id : ''; ?>">
        <label>Team:</label>
        <select name="team_id" required>
            <option value="">Select Team</option>
            <?php
            $teams->data_seek(0);
            while($team = $teams->fetch_assoc()) {
                $selected = ($edit_team == $team['team_id']) ? "selected" : "";
                echo "<option value='".$team['team_id']."' $selected>".$team['team_name']."</option>";
            }
            ?>
        </select>
        <label>Points Scored:</label>
        <input type="number" name="points_scored" value="<?php echo $edit_points; ?>" required>
        <label>Match Date:</label>
        <input type="date" name="match_date" value="<?php echo $edit_date; ?>" required>
        <button type="submit" name="<?php echo $edit_mode ? 'update_match' : 'add_match'; ?>">
            <?php echo $edit_mode ? "Update Match" : "Add Match"; ?>
        </button>
    </form>
</div>
</body>
</html>

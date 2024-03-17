<?php
session_start();
include 'server_connection.php'; // Make sure to use the correct path to your connection script

$connection = connect_to_database();
$query = $_GET['query'] ?? ''; // Use null coalescing operator to handle if query is not set
$groupId = $_GET['groupId'] ?? 0; // Use 0 or any other default for group ID

$memberQuery = "SELECT username, gpermissions, fpermissions FROM group_participants WHERE groupid = ? AND username LIKE ?";
$memberStmt = mysqli_prepare($connection, $memberQuery);
$searchTerm = '%' . $query . '%';
mysqli_stmt_bind_param($memberStmt, "is", $groupId, $searchTerm);
mysqli_stmt_execute($memberStmt);
$memberResult = mysqli_stmt_get_result($memberStmt);

// Begin with the same div structure as the original member list
echo '<div id="memberList">';
while ($row = mysqli_fetch_assoc($memberResult)) {
    // Ensure the div class matches the one used in your original list for consistent styling
    echo '<div class="member">';
    echo htmlspecialchars($row['username']) . ' - ' . htmlspecialchars($row['gpermissions']) . ' - ' . htmlspecialchars($row['fpermissions']);
    echo '</div>';
}
echo '</div>';

mysqli_stmt_close($memberStmt);
mysqli_close($connection);
?>

<?php
/*
	File:		api/get_stats.php
	Created: 	API endpoint for fetching player stats via AJAX
	Info: 		Returns player stats in JSON format
*/

// Suppress any HTML output from errors
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unwanted output
ob_start();

require_once('../globals.php');

// Clean any output that might have been generated
ob_clean();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($userid) || !$userid) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

// Fetch fresh user data
$userData = $db->fetch_row($db->query("SELECT * FROM `users` WHERE `userid` = {$userid}"));

if (!$userData) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

// Fetch user stats from userstats table
$statsData = $db->fetch_row($db->query("SELECT * FROM `userstats` WHERE `userid` = {$userid}"));

// Calculate percentages
$hp_percent = round($userData['hp'] / $userData['maxhp'] * 100);
$energy_percent = round($userData['energy'] / $userData['maxenergy'] * 100);
$will_percent = round($userData['will'] / $userData['maxwill'] * 100);
$brave_percent = round($userData['brave'] / $userData['maxbrave'] * 100);
$xp_percent = round($userData['xp'] / $userData['xp_needed'] * 100);

// Debug log
error_log("API get_stats: userid={$userid}, energy={$userData['energy']}, maxenergy={$userData['maxenergy']}, percent={$energy_percent}");

// Prepare response
$response = [
    'success' => true,
    'stats' => [
        'hp' => $userData['hp'],
        'maxhp' => $userData['maxhp'],
        'hp_percent' => $hp_percent,
        'energy' => $userData['energy'],
        'maxenergy' => $userData['maxenergy'],
        'energy_percent' => $energy_percent,
        'will' => $userData['will'],
        'maxwill' => $userData['maxwill'],
        'will_percent' => $will_percent,
        'brave' => $userData['brave'],
        'maxbrave' => $userData['maxbrave'],
        'brave_percent' => $brave_percent,
        'xp' => $userData['xp'],
        'xp_needed' => $userData['xp_needed'],
        'xp_percent' => $xp_percent,
        'level' => $userData['level'],
        'primary_currency' => $userData['primary_currency'],
        'secondary_currency' => $userData['secondary_currency'],
        'strength' => isset($statsData['strength']) ? $statsData['strength'] : 0,
        'agility' => isset($statsData['agility']) ? $statsData['agility'] : 0,
        'guard' => isset($statsData['guard']) ? $statsData['guard'] : 0,
        'labor' => isset($statsData['labor']) ? $statsData['labor'] : 0,
        'iq' => isset($statsData['IQ']) ? $statsData['IQ'] : 0
    ],
    'timestamp' => time()
];

echo json_encode($response);
?>
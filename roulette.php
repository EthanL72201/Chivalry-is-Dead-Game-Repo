<?php
/*
	File:		roulette.php
	Created: 	6/23/2019 at 6:11PM Eastern Time
	Info: 		A roulette table mini-game.
	Author:		TheMasterGeneral
	Website: 	https://github.com/MasterGeneral156/chivalry-engine
	MIT License

	Copyright (c) 2019 TheMasterGeneral

	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:

	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/
require_once('globals.php');
// Use more entropy for the anti-refresh token
$tresder = random_int(1000, 9999);
$maxbet = $ir['level'] * 250;
$_GET['tresde'] = (isset($_GET['tresde']) && is_numeric($_GET['tresde'])) ? abs($_GET['tresde']) : 0;
if (!isset($_SESSION['tresde'])) {
    $_SESSION['tresde'] = 0;
}
if (($_SESSION['tresde'] == $_GET['tresde']) || $_GET['tresde'] < 100) {
    alert('danger', "Uh Oh!", "Do not refresh while playing Roulette", true, "roulette.php?tresde={$tresder}");
    die($h->endpage());
}
$_SESSION['tresde'] = $_GET['tresde'];
echo "<h3>🎰 Roulette Table</h3><hr />
<div class='alert alert-info'>
    <strong>Game Rules:</strong> Pick a number between 0-36. If the wheel lands on your number, you win 50x your bet!
    <br><small>House edge: 2.7% (European style single-zero roulette)</small>
</div>";
if (isset($_POST['bet']) && is_numeric($_POST['bet'])) {
    $_POST['bet'] = abs($_POST['bet']);
    if (!isset($_POST['number'])) {
        $_POST['number'] = 0;
    }
    $_POST['number'] = abs($_POST['number']);
    if ($_POST['bet'] > $ir['primary_currency']) {
        alert('danger', "Uh Oh!", "You are trying to bet more cash than you currently have.", true, "roulette.php?tresde={$tresder}");
        die($h->endpage());
    } else if ($_POST['bet'] > $maxbet) {
        alert('danger', "Uh Oh!", "You are trying to bet more than you're allowed to at your level.", true, "roulette.php?tresde={$tresder}");
        die($h->endpage());
    } else if ($_POST['number'] > 36 || $_POST['number'] < 0) {
        alert('danger', "Uh Oh!", "You input an invalid guess.", true, "roulette.php?tresde={$tresder}");
        die($h->endpage());
    } else if ($_POST['bet'] < 0) {
        alert('danger', "Uh Oh!", "You cannot bet less than 0 cash.", true, "roulette.php?tresde={$tresder}");
        die($h->endpage());
    }
    $slot = array();
    // Use PHP's native random_int for truly random numbers
    $slot[1] = random_int(0, 36);
    if ($slot[1] == $_POST['number']) {
        $gain = $_POST['bet'] * 50;
        $title = "Success!";
        $alerttype = 'success';
        $win = 1;
        $phrase = " and won! You keep your bet, and pocket an extra " . number_format($gain);
        $api->game->addLog($userid, 'gambling', "Bet {$_POST['bet']} and won {$gain} in roulette.");
    } else {

        $title = "Uh Oh!";
        $alerttype = 'danger';
        $win = 0;
        $gain = -$_POST['bet'];
        $phrase = ". You lose your bet. Sorry man.";
        $api->game->addLog($userid, 'gambling', "Lost {$_POST['bet']} in roulette.");
    }
    // Determine color of the number (for visual effect)
    $color = 'green'; // 0 is green
    if ($slot[1] > 0) {
        $reds = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
        $color = in_array($slot[1], $reds) ? 'red' : 'black';
    }
    $colorEmoji = $color == 'red' ? '🔴' : ($color == 'black' ? '⚫' : '🟢');
    
    alert($alerttype, $title, "You bet on <strong>#{$_POST['number']}</strong> with " . number_format($_POST['bet']) . " " . constant("primary_currency") . ".<br><br>
        The wheel spins... and lands on:<br>
        <span style='font-size: 2em; font-weight: bold;'>{$colorEmoji} {$slot[1]}</span><br><br>
        {$phrase}", true, "roulette.php?tresde={$tresder}");
    $db->query("UPDATE `users` SET `primary_currency` = `primary_currency` + ({$gain}) WHERE `userid` = {$userid}");
    $tresder = random_int(1000, 9999);
    echo "<br />
	<form action='roulette.php?tresde={$tresder}' method='post'>
    	<input type='hidden' name='bet' value='{$_POST['bet']}' />
    	<input type='hidden' name='number' value='{$_POST['number']}' />
    	<input type='submit' class='btn btn-primary' value='Again, Same Bet' />
    </form>
	<a href='roulette.php?tresde={$tresder}'>Again, Different Bet</a><br />
	<a href='explore.php'>I'm Good</a>";
} else {
    echo "
	<form action='?tresde={$tresder}' method='post'>
	<table class='table table-bordered'>
		<tr>
			<th colspan='2'>
				Ready to test your luck? Awesome! Here at the roulette table, the house always wins. To combat players
				losing all their wealth in one go, we've put in a bet restriction. At your level, you can only bet
				" . number_format($maxbet) . " " . constant("primary_currency") . ".
			</th>
		</tr>
		<tr>
			<th>
				Bet
			</th>
			<td>
				<input type='number' class='form-control' name='bet' min='0' max='{$maxbet}' value='5' />
			</td>
		</tr>
		<tr>
			<th>
				Pick #
			</th>
			<td>
				<input type='number' class='form-control' name='number' min='0' max='36' value='<?php echo random_int(0, 36); ?>' />
			</td>
		</tr>
		<tr>
			<td colspan='2'>
				<input class='btn btn-primary' type='submit' value='Place Bet' />
			</td>
		</tr>
	</table>
	</form>";
}
$h->endpage();
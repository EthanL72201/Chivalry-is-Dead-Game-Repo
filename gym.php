<?php
/*
	File:		gym_redesigned.php
	Created: 	Complete gym redesign with better UI
	Info: 		Modern training interface with improved layout
*/
$macropage = ('gym.php');

// Prevent caching of this page
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

require("globals.php");
require_once("includes/vip-benefits.php");

// Initialize VIP benefits
$vipBenefits = getVIPBenefits($db, $userid);

// Check restrictions
if ($api->user->inInfirmary($ir['userid'])) {
    alert("danger", "Unconscious!", "You cannot train while you're in the infirmary.", true, 'index.php');
    die($h->endpage());
}

if ($api->user->inDungeon($ir['userid'])) {
    alert("danger", "Locked Up!", "You cannot train while you're in the dungeon.", true, 'index.php');
    die($h->endpage());
}

$statnames = array("Strength" => "strength", "Agility" => "agility", "Guard" => "guard", "Labor" => "labor");

// Process training if submitted
if (isset($_POST["stat"]) && isset($_POST["amnt"])) {
    $amnt = filter_input(INPUT_POST, 'amnt', FILTER_SANITIZE_NUMBER_INT) ?: 0;
    
    if (!isset($statnames[$_POST['stat']])) {
        alert("danger", "Error!", "Invalid stat selected.", true, 'gym.php');
        die($h->endpage());
    }
    
    if (!isset($_POST['verf']) || !checkCSRF('gym_train', stripslashes($_POST['verf']))) {
        alert('danger', "Security Error!", "Session expired. Please try again.", true, 'gym.php');
        die($h->endpage());
    }
    
    $stat = $statnames[$_POST['stat']];
    
    if ($amnt > $ir['energy']) {
        alert("danger", "Not Enough Energy!", "You need more energy to train that much.", false);
    } else {
        // Store energy before training
        $energyBefore = $ir['energy'];
        $dbEnergyBefore = $db->fetch_single($db->query("SELECT `energy` FROM `users` WHERE `userid` = {$userid}"));
        
        $gain = $api->user->train($userid, $_POST['stat'], $amnt);
        
        // Apply VIP training bonus
        $originalGain = $gain;
        $gain = $vipBenefits->applyTrainingBonus($gain);
        $bonus = $gain - $originalGain;
        
        // Fetch the actual energy from database after train() function
        $actualEnergy = $db->fetch_single($db->query("SELECT `energy` FROM `users` WHERE `userid` = {$userid}"));
        
        $NewStatAmount = $ir[$stat] + $gain;
        $EnergyLeft = $actualEnergy; // Use the actual energy from database
        
        // Update local variable
        $ir['energy'] = $EnergyLeft;
        $ir[$stat] = $NewStatAmount;
        
        // Debug logging
        error_log("GYM DEBUG: Before training - Session energy: {$energyBefore}, DB energy: {$dbEnergyBefore}");
        error_log("GYM DEBUG: After training - DB energy: {$actualEnergy}, Amount used: {$amnt}");
        
        // Log VIP benefit usage if bonus was applied
        // Commented out until table is created
        // if ($bonus > 0) {
        //     $vipBenefits->logBenefitUsage('training_bonus', $bonus);
        // }
        
        $message = "You gained <strong>{$gain}</strong> " . constant("stat_" . $stat) . "! Energy: {$energyBefore} → {$EnergyLeft} (Used: {$amnt})";
        if ($bonus > 0) {
            $message .= " <span class='text-warning'><i class='fas fa-crown'></i> VIP Bonus: +{$bonus}</span>";
        }
        
        // Debug: Check if energy was actually deducted
        if ($energyBefore == $EnergyLeft) {
            $message .= "<br><span class='text-danger'>Warning: Energy was not deducted!</span>";
        }
        
        alert('success', "Training Complete!", $message, false);
    }
}

// Debug: Check initial energy value
$dbEnergy = $db->fetch_single($db->query("SELECT `energy` FROM `users` WHERE `userid` = {$userid}"));
error_log("GYM PAGE LOAD: userid={$userid}, \$ir['energy']={$ir['energy']}, DB energy={$dbEnergy}, maxenergy={$ir['maxenergy']}");

// Calculate percentages and stats
$energyPercent = round($ir['energy'] / $ir['maxenergy'] * 100);
$hpPercent = round($ir['hp'] / $ir['maxhp'] * 100);
$willPercent = round($ir['will'] / $ir['maxwill'] * 100);
$bravePercent = round($ir['brave'] / $ir['maxbrave'] * 100);

// Get stat rankings
$strengthRank = getRank($ir['strength'], 'strength');
$agilityRank = getRank($ir['agility'], 'agility');
$guardRank = getRank($ir['guard'], 'guard');
$laborRank = getRank($ir['labor'], 'labor');
?>
<!-- Load optimized gym styles -->
<link rel="stylesheet" href="css/gym.css">

<div class="gym-container">
    <!-- Hero Section -->
    <div class="gym-hero">
        <h1><i class="fas fa-dumbbell"></i> Training Facility</h1>
        <p>Push your limits and become stronger!</p>
    </div>

    <!-- Resource Overview -->
    <div class="resource-grid">
        <div class="resource-card">
            <div class="resource-header">
                <div>
                    <div class="resource-label">Energy Available</div>
                    <div class="resource-value"><?php echo number_format($ir['energy']); ?>/<?php echo number_format($ir['maxenergy']); ?></div>
                </div>
                <div class="resource-icon energy">
                    <i class="fas fa-bolt"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar energy" style="width: <?php echo $energyPercent; ?>%;"></div>
            </div>
        </div>

        <div class="resource-card">
            <div class="resource-header">
                <div>
                    <div class="resource-label">Health Points</div>
                    <div class="resource-value"><?php echo number_format($ir['hp']); ?>/<?php echo number_format($ir['maxhp']); ?></div>
                </div>
                <div class="resource-icon health">
                    <i class="fas fa-heart"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar health" style="width: <?php echo $hpPercent; ?>%;"></div>
            </div>
        </div>

        <div class="resource-card">
            <div class="resource-header">
                <div>
                    <div class="resource-label">Willpower</div>
                    <div class="resource-value"><?php echo number_format($ir['will']); ?>/<?php echo number_format($ir['maxwill']); ?></div>
                </div>
                <div class="resource-icon will">
                    <i class="fas fa-brain"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar will" style="width: <?php echo $willPercent; ?>%;"></div>
            </div>
        </div>

        <div class="resource-card">
            <div class="resource-header">
                <div>
                    <div class="resource-label">Bravery</div>
                    <div class="resource-value"><?php echo number_format($ir['brave']); ?>/<?php echo number_format($ir['maxbrave']); ?></div>
                </div>
                <div class="resource-icon brave">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar brave" style="width: <?php echo $bravePercent; ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Stats Showcase -->
    <div class="stats-showcase">
        <div class="stat-showcase-card strength">
            <div class="stat-header">
                <div class="stat-info">
                    <h3><?php echo constant("stat_strength"); ?></h3>
                    <div class="stat-value"><?php echo number_format($ir['strength']); ?></div>
                    <span class="stat-rank">Rank #<?php echo $strengthRank; ?></span>
                </div>
                <div class="stat-icon-large strength">
                    <i class="fas fa-fist-raised"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar strength" style="width: <?php echo min(100, $ir['strength'] / 1000 * 100); ?>%;"></div>
            </div>
        </div>

        <div class="stat-showcase-card agility">
            <div class="stat-header">
                <div class="stat-info">
                    <h3><?php echo constant("stat_agility"); ?></h3>
                    <div class="stat-value"><?php echo number_format($ir['agility']); ?></div>
                    <span class="stat-rank">Rank #<?php echo $agilityRank; ?></span>
                </div>
                <div class="stat-icon-large agility">
                    <i class="fas fa-running"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar agility" style="width: <?php echo min(100, $ir['agility'] / 1000 * 100); ?>%;"></div>
            </div>
        </div>

        <div class="stat-showcase-card guard">
            <div class="stat-header">
                <div class="stat-info">
                    <h3><?php echo constant("stat_guard"); ?></h3>
                    <div class="stat-value"><?php echo number_format($ir['guard']); ?></div>
                    <span class="stat-rank">Rank #<?php echo $guardRank; ?></span>
                </div>
                <div class="stat-icon-large guard">
                    <i class="fas fa-shield-alt"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar guard" style="width: <?php echo min(100, $ir['guard'] / 1000 * 100); ?>%;"></div>
            </div>
        </div>

        <div class="stat-showcase-card labor">
            <div class="stat-header">
                <div class="stat-info">
                    <h3><?php echo constant("stat_labor"); ?></h3>
                    <div class="stat-value"><?php echo number_format($ir['labor']); ?></div>
                    <span class="stat-rank">Rank #<?php echo $laborRank; ?></span>
                </div>
                <div class="stat-icon-large labor">
                    <i class="fas fa-hammer"></i>
                </div>
            </div>
            <div class="modern-progress">
                <div class="modern-progress-bar labor" style="width: <?php echo min(100, $ir['labor'] / 1000 * 100); ?>%;"></div>
            </div>
        </div>
    </div>

    <!-- Training Section -->
    <div class="training-section">
        <h2><i class="fas fa-play-circle"></i> Start Training Session</h2>
        
        <?php if ($vipBenefits->isVIP()): ?>
        <div class="alert alert-warning mb-3">
            <i class="fas fa-crown"></i> <strong>VIP Training Bonus:</strong> 
            You receive 25% bonus to all training gains! <?php echo $vipBenefits->getVIPBadge(); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($ir['energy'] < 1): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> You don't have enough energy to train. 
            <a href="temple.php?action=energy" class="alert-link">Refill your energy</a> or wait for it to regenerate.
        </div>
        <?php else: ?>
        
        <form method="post" action="gym.php" class="form-modern">
            <input type="hidden" name="verf" value="<?php echo getCodeCSRF('gym_train'); ?>">
            
            <div class="form-group-modern">
                <label for="stat">Select Training Focus</label>
                <select name="stat" id="stat" class="form-control-modern">
                    <option value="Strength"><?php echo constant("stat_strength"); ?> - Increases melee damage</option>
                    <option value="Agility"><?php echo constant("stat_agility"); ?> - Improves dodge chance</option>
                    <option value="Guard"><?php echo constant("stat_guard"); ?> - Reduces damage taken</option>
                    <option value="Labor"><?php echo constant("stat_labor"); ?> - Boosts job income</option>
                </select>
            </div>
            
            <div class="form-group-modern">
                <label for="amnt">Energy Investment (Available: <?php echo $ir['energy']; ?>)</label>
                <input type="number" name="amnt" id="amnt" class="form-control-modern" 
                       min="1" max="<?php echo $ir['energy']; ?>" 
                       value="<?php echo min(10, $ir['energy']); ?>" required>
            </div>
            
            <div class="quick-actions">
                <button type="button" class="quick-action-btn" data-amount="1">1</button>
                <button type="button" class="quick-action-btn" data-amount="5">5</button>
                <button type="button" class="quick-action-btn" data-amount="10">10</button>
                <button type="button" class="quick-action-btn" data-amount="25">25</button>
                <button type="button" class="quick-action-btn" data-amount="50">50</button>
                <button type="button" class="quick-action-btn" data-amount="100">100</button>
                <button type="button" class="quick-action-btn" data-amount="<?php echo floor($ir['energy'] / 2); ?>">Half</button>
                <button type="button" class="quick-action-btn" data-amount="<?php echo $ir['energy']; ?>">Max</button>
            </div>
            
            <button type="submit" class="btn-train">
                <i class="fas fa-dumbbell"></i> Begin Training
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Info Cards -->
    <div class="info-cards">
        <div class="info-card">
            <h4><i class="fas fa-lightbulb text-warning"></i> Training Tips</h4>
            <p>Higher energy investments yield better training results. Balance your stats for optimal combat performance.</p>
        </div>
        
        <div class="info-card">
            <h4><i class="fas fa-clock text-info"></i> Energy Recovery</h4>
            <p>Energy regenerates over time. You can also refill it instantly at the temple for a small fee.</p>
        </div>
        
        <div class="info-card">
            <h4><i class="fas fa-trophy text-success"></i> Stat Rankings</h4>
            <p>Your rank shows how you compare to other players. Train regularly to climb the leaderboards!</p>
        </div>
    </div>
</div>

<!-- Load gym optimizer for better performance -->
<script src="js/gym-optimizer.js" defer></script>

<?php if (isset($_POST["stat"]) && isset($_POST["amnt"]) && isset($EnergyLeft)): ?>
<script>
// Refresh stats after successful training
// Use window.onload to ensure all scripts are loaded
window.addEventListener('load', function() {
    // Call refreshStats if it exists (from modern-enhancements.js)
    if (typeof window.refreshStats === 'function') {
        console.log('Refreshing stats after training...');
        setTimeout(function() {
            window.refreshStats();
        }, 1000);
    } else {
        console.warn('refreshStats function not found');
    }
});
</script>
<?php endif; ?>

<?php
$h->endpage();
?>
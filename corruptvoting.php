<?php
require('globals.php');
if ($api->UserStatus($userid,'dungeon') || $api->UserStatus($userid,'infirmary'))
{
    alert('danger',"Uh Oh!","You can't view or pay off your politicians...",true,'index.php');
    die($h->endpage());
}
if (!isset($_GET['action'])) {
    $_GET['action'] = '';
}
switch ($_GET['action']) {
    case 'smelt':
        smelt();
        break;
    default:
        home();
        break;
}

function home()
{
    global $db, $userid, $h;
    
    $q = $db->query("SELECT * FROM `corrupt_votes` WHERE `cv_active` = '1' AND `cv_resolved` = '0'");
    
    echo "<div class='card'>
            <div class='card-header'>
                These are votes you may corrupt.
            </div>
            <div class='card-body'>";
    while ($r = $db->fetch_row($q))
    {
        echo "  <div class='row'>
                    <div class='col-12'>
                        <div class='row'>
                            <div class='col-12'>
                                <b>{$r['cv_title']}</b>
                            </div>
                            <div class='col-12'>
                                <i>{$r['cv_desc']}</i>
                            </div>
                        </div>
                    </div>
                </div>";
    }
    echo "</div></div>";
}
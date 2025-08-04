<?php
$menuhide=1;
$nohdr=true;
require_once('../../globals.php');
if (isset($_SERVER['REQUEST_METHOD']) && is_string($_SERVER['REQUEST_METHOD']))
{
    if (strtoupper($_SERVER['REQUEST_METHOD']) !== 'GET')
    {
        // Ignore a GET request
        header('HTTP/1.1 400 Bad Request');
        exit;
    }
}
if (!is_ajax())
{
    header('HTTP/1.1 400 Bad Request');
    exit;
}
switch ($_GET['action'])
{
    case 'del':
        del();
        break;
    case 'delall':
        delall();
        break;
}

function del()
{
    global $db, $userid;
    $_GET['delete'] = abs($_GET['delete']);
    if ($_GET['delete'] > 0) 
    {
        $d_c = $db->query("/*qc=on*/SELECT COUNT(`notif_user`)
                      FROM `notifications`
                      WHERE `notif_id` = {$_GET['delete']}
                      AND `notif_user` = {$userid}");
        if ($db->fetch_single($d_c) == 0) 
        {
            alert('danger', "Uh Oh!", "You cannot delete a notification that doesn't exist, or doesn't belong to you.", false);
        } 
        else 
        {
            $db->query("DELETE FROM `notifications`
                 WHERE `notif_id` = {$_GET['delete']}
                 AND `notif_user` = {$userid}");
            alert('success', "Success!", "Notification has been deleted successfully.", false);
        }
        $db->free_result($d_c);
        echo "<script>document.getElementById('notif{$_GET['delete']}').remove();</script>";
    }
    else
    {
        alert('danger', "Uh Oh!", "Please input a valid notification to delete.", false);
    }
}
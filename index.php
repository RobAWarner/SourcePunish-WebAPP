<?php
    require_once('includes/core.php');
    require_once(DIR_INCLUDE.'class.steam.php');
    $GLOBALS['steam'] = new Steam();
?>
<!DOCTYPE html>
<html>
<head></head>
<body>
<?php
    /* Test SteamID converstion */
    echo $GLOBALS['steam']->Steam64ToID(76561198000905473, true);
    /* Test login/auth */
    /*if(isset($_GET['logout'])) {
        $GLOBALS['auth']->EndSession();
        header('Location: index.php');
    } else if($GLOBALS['auth']->ValidateSession()) {
        echo '<a href="index.php?logout=true">Logout</a><br /><br />';
        $IsAdmin = $GLOBALS['auth']->IsAdmin();
        echo ($IsAdmin?'Is Admin! ':'Is NOT Admin! ');
        $HasBan = $GLOBALS['auth']->HasAdminFlag('b');
        var_dump($HasBan);
    } else if(isset($_GET['openid_signed']) && $_GET['openid_signed'] != "") {
        $Steam64 = $GLOBALS['auth']->ValidateLogin();
        if($Steam64 !== false) {
            $GLOBALS['auth']->SetSession($Steam64);
            header('Location: index.php');
            die('Loggin in');
        }
    } else {
        echo '<a href="'.$GLOBALS['auth']->GetLoginURL().'">Login</a><br /><br />';
    }*/
?>
</body></html>
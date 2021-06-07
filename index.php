<?php

global $c;
include("config.php");

$base_url = "?".explode("?", $_SERVER['REQUEST_URI'])[1];
$base_url = explode("&act=", $base_url)[0];

//Catch any ajax/api calls made to this page
if(isset($_GET['act']) && isset($_SESSION['authed_player_id']) && $_GET['act'] == 'trigger_attack') {
    /*
        Usage:
            Trigger this once for each player's botnet you wish to turn on DDOS attack for.

        Inputs:
            $_POST['player_id'] = Player's botnet you want to turn on DDOS attack for
            $_POST['ip'] = target IP (NOT service IP)
            $_POST['use_all'] = 0/1. If 0, uses botnet only if currently idle. If 1, forces botnet to drop what it is doing and join this ddos.
    */

    if(intval($_POST['use_all']) == 1) {
        /*
            User requested to use ALL resources...
            So first, cancel any existing ddos attacks the current botnet has running
        */
        $url = "http://hackcyber.space/botnet_attack.php?act=cancel_all_ddos_attacks";
        $data = [
            "api_auth_ip" => $_SESSION['authed_ip'],
            "api_auth_root_pw" => $root_pw,
            "api_auth_player_id" => $_POST['player_id']
        ];
        
        $options = array(
            'http' => array( // use key 'http' even if you send the request to https://...
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        if ($result === FALSE) { 
            /* Handle error */ 
        }
        $result_json = json_decode($result, true);
    }

    //Now send the api call to instruct the current botnet to trigger the new ddos attack 

    $url = "http://hackcyber.space/botnet_attack.php?act=start_ddos_attack";
    $data = [
        "api_auth_ip" => $_SESSION['authed_ip'],
        "api_auth_root_pw" => $root_pw,
        "api_auth_player_id" => $_POST['player_id'],
        "ip" => $_POST['ip']
    ];
    
    $options = array(
        'http' => array( // use key 'http' even if you send the request to https://...
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { 
        /* Handle error */ 
    }
    $result_json = json_decode($result, true);
    print '{"status": "success"}';die;
}


//Authenticate the user, and pull their information
$user_stats_api_url = "http://hackcyber.space/service_api.php?act=get_player_info&ip=".urlencode($_GET['ip'])."&player_id=".urlencode($_GET['player_id'])."&root_pw=".$root_pw;
$user_stats_results = @file_get_contents($user_stats_api_url);
$user_data = json_decode($user_stats_results, true);

if(!isset($user_data['player_id']) || !isset($user_data['player_id'])) {
    die("<span style='color:red'>Error - unable to authenticate.</span>");
}

?>
<html>
<head>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<link href="index.css" rel="stylesheet">
<script src='index.js'></script>
<script>
var base_url = "<?=str_replace("&amp;","&",htmlentities($base_url))?>";
</script>
</head>
<body bgcolor='#0C1820'>
<?php

//Authenticate user
//Requires correct hacking group ID and api permissions.

$perm_check = [
    "/botnet.php?act=get_botnet_ddos_status", 
    "/botnet_attack.php?act=start_ddos_attack", 
    "/botnet_attack.php?act=cancel_all_ddos_attacks", 
];
$authenticated = 1;
if ($user_data['player_hacking_group_id'] != $hacking_group_id) {
    $authenticated = 0;
}
foreach($perm_check as $i => $perm) {
    if(!in_array($perm, $user_data['ip_permissions'])) {
        $authenticated = 0;
    }
}


if(!$authenticated) { //User not authenticated
    
?>
    <table width=100%>
        <tr>
            <td width=200px>
                <img src='LOIC.gif'>
            </td>
            <td id='permissions_content'>
                <?php

                    if($user_data['player_hacking_group_id'] != $hacking_group_id) {
                        print "<span style='color:red'>[✘] Valid hacking group membership</span><br>";
                    }
                    else {
                        print "<span style='color:green'>[✔] Valid hacking group membership</span><br>";
                    }

                    $perm_check = [
                        "/botnet.php?act=get_botnet_ddos_status", 
                        "/botnet_attack.php?act=start_ddos_attack", 
                        "/botnet_attack.php?act=cancel_all_ddos_attacks", 
                    ];
                    foreach($perm_check as $i => $perm) {
                        if(!in_array($perm, $user_data['ip_permissions'])) {
                            print "<span style='color:red'>[✘] API Permission: ".$perm."</span>";
                            print "<button  onclick=\"trigger_perm_request('".$perm."')\">Request Permission</button><br>";
                        }
                        else {
                            print "<span style='color:green'>[✔] API Permission: ".$perm."</span><br>";
                        }
                        
                    }

                ?>
            </td>
        </tr>
    </table>
    <?
}
else { 
    //User authenticated
    $ir_q = pg_query_params($c, "SELECT * FROM loic_member WHERE player_id=$1", array($_GET['player_id']));
    if(!pg_num_rows($ir_q)) {
        pg_query_params($c, "INSERT INTO loic_member (player_id, player_name) VALUES ($1, $2)", array($_GET['player_id'], $user_data['player_name']));
    }
    $_SESSION['authed_player_id'] = $_GET['player_id'];
    $_SESSION['authed_ip'] = $_GET['ip'];

?>
<table width=100%>
    <tr>
        <td rowspan=4>
            <img src='LOIC.gif'>
        </td>
        <td width=50%>
            <fieldset style='height: 71px;'>
                <legend>1. Select your target</legend>
                <table width=100% style='padding-top:13px;'>
                    <tr>
                        <td>
                            <label for="ip">IP</label>
                        </td>
                        <td>
                            <input type="text" id="ip" name="ip" style='width: 100%;color:white;'>
                        </td>
                        <td>
                            <button onclick="lock_on()">Lock on</button>
                        </td>
                    </tr>
                </table>
            </fieldset>
        </td>
        <td width=50%>
            <fieldset>
                <legend>3. Ready?</legend>
                <button style='padding-top: 15px; padding-bottom: 15px;font-size:18px;width:100%;' id='lazer_button' onclick="charge_lazer()">IMMA CHARGIN MAH LAZER</button>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan=2>
            <fieldset>
                <legend>Selected target</legend>
                <input type="text" style='font-size: 60px; width: 100%; text-align:center;color:white;font-weight: bold; font-style: italic;padding-top: 6px;padding-bottom: 6px;' id="selected_target" value='N O N E !'>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan=2>
            <fieldset>
                <legend>2. Attack options</legend>
                <input type="radio" id="idle_resources" name="attack_priority_level" value="idle_resources" checked="checked">
                <label for="idle_resources">Use <b>ONLY</b> idle resources</label><br>
                <input type="radio" id="all_resources" name="attack_priority_level" value="all_resources">
                <label for="all_resources" style="color:red">Use <b>ALL</b> resources (Make everyone stop what they are doing and join this attack)</label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <td colspan=2 id='attack_status_cell'>
            <fieldset>
                <legend>Attack status</legend>
                <table style='color:white;text-align:center;' width=100%>
                <tr>
                    <td>
                        Botnet Owner
                    </td>
                    <td>
                        Available Strength
                    </td>
                    <td>
                        Currently Attacking
                    </td>
                </tr>
                <tr>
                    <td colspan=3>
                        <hr>
                    </td>
                </tr>
<?php

//Pull all our current member's botnet status and display it

$all_members_q = pg_query($c, "SELECT player_id, player_name FROM loic_member ORDER BY player_name ASC");
while($member = pg_fetch_assoc($all_members_q)) {

    $url = "http://hackcyber.space/botnet.php?act=get_botnet_ddos_status";
    $data = [
        "api_auth_ip" => $_GET['ip'],
        "api_auth_root_pw" => $root_pw,
        "api_auth_player_id" => $member['player_id']
    ];
    
    $options = array(
        'http' => array( // use key 'http' even if you send the request to https://...
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) { 
        /* 
            Handle error... or at least we should.
            In the future, consider some kind of logic that would let us deleted old / no longer active users from this list.
        */ 
    }
    $result_json = json_decode($result, true);
?>
                <tr>
                    <td style="color:#0f0;">
                        <?=$member['player_name']?>
                    </td>
                    <td>
                        <?=$result_json['ddos_power']?> Mbs
                    </td>
                    <td class='player_row' data-playerid="<?=$member['player_id']?>">
                        <?
                        if(!$result_json['ddos_active_target']) {
                            print "None";
                        }
                        else {
                            print $result_json['ddos_active_target'];
                        }

                        ?>
                    </td>
                </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
<?php
    }
} //End authenticated user content
?>


</body>
</html>
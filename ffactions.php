<?php

/*
 * Followers/Following Plugin for MyBB (v1.0) by EvolSoft
 * Developer: EvolSoft
 * Website: http://www.evolsoft.tk
 * Date: 06/08/2015 10:01 AM (UTC)
 * Copyright & License: (C) 2015 EvolSoft
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'ffsystem.php');

require_once "./global.php";


if($mybb->user['uid'] == 0){
	error_no_permission();
}

if(isset($_GET['action']) && isset($_GET['uid'])){
	if(strtolower($_GET['action']) == "follow"){
		if(get_user($_GET['uid']) == null){
			if(isset($_GET['ajax'])){
				header("Content-type: application/json;");
				echo json_encode(array("status" => "false", "followers" => 0));
			}else{
				error("Can't add this user to your followers list: User not found.");
			}
		}else{
			if(mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $_GET['uid'] . "'")) == 0){
				$db->query("INSERT INTO " . TABLE_PREFIX . "ffplugin (following, follower) VALUES ('" . $mybb->user['uid'] . "', '" . $_GET['uid'] . "')");
				if($mybb->settings['ffplugin_em'] == 1){
					if(function_exists("myalerts_info")){
						$myalertsinfo = myalerts_info();
						if($myalertsinfo['version'] >= "2.0.0"){
							$alertType = MybbStuff_MyAlerts_AlertTypeManager::getInstance()->getByCode('ffplugin_myalerts');
							if ($alertType != null && $alertType->getEnabled()) {
								$alert = new MybbStuff_MyAlerts_Entity_Alert($_GET['uid'], $alertType, 0);
								MybbStuff_MyAlerts_AlertManager::getInstance()->addAlert($alert);
							}
						}
					}
				}
				if(isset($_GET['ajax'])){
					header("Content-type: application/json;");
					echo json_encode(array("status" => "true", "followers" => countf($_GET['uid'], false)));
				}else{
					redirect("member.php?action=profile&uid=" . $_GET['uid'], "You are now following " . getName($_GET['uid']) . ".");
				}
			}else{
				if(isset($_GET['ajax'])){
					header("Content-type: application/json;");
					echo json_encode(array("status" => "true", "followers" => countf($_GET['uid'], false)));
				}else{
					error("You are already following this user.");
				}
			}
		}
	}elseif(strtolower($_GET['action']) == "unfollow"){
		if(mysqli_num_rows($db->query("SELECT * FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $_GET['uid'] . "'")) == 0){
			if(isset($_GET['ajax'])){
				header("Content-type: application/json;");
				echo json_encode(array("status" => "false", "followers" => countf($_GET['uid'], false)));
			}else{
				error("You are not following this user.");
			}
		}else{
			$db->query("DELETE FROM " . TABLE_PREFIX . "ffplugin WHERE following='" . $mybb->user['uid'] . "' AND follower='" . $_GET['uid'] . "'");
			if(isset($_GET['ajax'])){
				header("Content-type: application/json;");
				echo json_encode(array("status" => "false", "followers" => countf($_GET['uid'], false)));
			}else{
				redirect("member.php?action=profile&uid=" . $_GET['uid'], "You are not following " . getName($_GET['uid']) . " anymore.");
			}
		}
	}elseif(strtolower($_GET['action']) == "showfollowinglist"){
		if(get_user($_GET['uid']) == null){
			drawModal("Following", 500, "<tr><td class=\"trow1\" colspan=\"2\"><em>Can't get the following list of this user: User not found.</em></td></tr>");
		}else{
			drawModal("Following", 500, processModalList($_GET['uid'], true));
		}
	}elseif(strtolower($_GET['action']) == "showfollowerslist"){
		if(get_user($_GET['uid']) == null){
			drawModal("Followers", 500, "<tr><td class=\"trow1\" colspan=\"2\"><em>Can't get the followers list of this user: User not found.</em></td></tr>");
		}else{
			drawModal("Followers", 500, processModalList($_GET['uid'], false));
		}
	}else{
		error("You are trying to perform an invalid action.");
	}
}else{
	error("You are trying to perform an invalid action.");
}
?>

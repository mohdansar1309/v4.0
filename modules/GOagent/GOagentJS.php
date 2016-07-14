<?php
namespace creamy;

define('GO_AGENT_DIRECTORY', str_replace($_SERVER['DOCUMENT_ROOT'], "", dirname(__FILE__)));
define('GO_BASE_DIRECTORY', dirname(dirname(dirname(__FILE__))));
define('GO_LANG_DIRECTORY', dirname(__FILE__) . '/lang/');
require_once(GO_BASE_DIRECTORY.'/php/CRMDefaults.php');
require_once(GO_BASE_DIRECTORY.'/php/LanguageHandler.php');
require_once(GO_BASE_DIRECTORY.'/php/DatabaseConnectorFactory.php');
include(GO_BASE_DIRECTORY.'/php/Session.php');
require_once(GO_BASE_DIRECTORY.'/php/goCRMAPISettings.php');
$goAPI = (empty($_SERVER['HTTPS'])) ? str_replace('https:', 'http:', gourl) : str_replace('http:', 'https:', gourl);

$lh = \creamy\LanguageHandler::getInstance();
$lh->addCustomTranslationsFromFile(GO_LANG_DIRECTORY . $lh->getLanguageHandlerLocale());

$US = '_';
$NOW_TIME = date("Y-m-d H:i:s");
$tsNOW_TIME = date("YmdHis");
$StarTtimE = date("U");

$result = get_user_info($_SESSION['userid']);
$default_settings = $result->default_settings;
$agent = $result->user_info;
$phone = $result->phone_info;
$system = $result->system_info;
if (isset($result->camp_info)) {
    $camp_info = $result->camp_info;
}
$_SESSION['is_logged_in'] = $result->is_logged_in;

if (!isset($_REQUEST['action']) && !isset($_REQUEST['module_name'])) {
    header('Content-Type: text/javascript');

    echo "// Session Variables\n";
    $sess_vars = "|";
    foreach ($_SESSION as $idx => $val) {
        if (!preg_match("/^(userrole|avatar)/", $idx)) {
            if ($idx == 'is_logged_in')
                $val = ($val) ? 1 : 0;
            ${$idx} = $val;
            $sess_vars .= "{$idx}|";
            //if ($idx == 'is_logged_in') {
            //    $val = ($val) ? 1 : 0;
            //    echo "var {$idx} = {$val};\n";
            //} else {
            //    echo "var {$idx} = '{$val}';\n";
            //}
        }
    }
    echo "// {$sess_vars}\n";
?>

// Settings
var is_logged_in = <?=$is_logged_in?>;
var logoutWarn = true;
var use_webrtc = <?=$use_webrtc?>;
var NOW_TIME = '<?=$NOW_TIME?>';
var SQLdate = '<?=$NOW_TIME?>';
var StarTtimE = '<?=$StarTtimE?>';
var UnixTime = '<?=$StarTtimE?>';
var UnixTimeMS = 0;
var t = new Date();
var c = new Date();
var refresh_interval = 1000;
var SIPserver = '<?=$SIPserver?>';
<?php
    foreach ($default_settings as $idx => $val) {
        if (is_numeric($val) && !preg_match("/^(conf_exten|session_id)$/", $idx)) {
            if ($idx == 'xfer_group_count') {
                echo "var XFgroupCOUNT = {$val};\n";
            }
            if ($idx == 'alt_phone_dialing') {
                echo "var starting_alt_phone_dialing = {$val};\n";
            }
            echo "var {$idx} = {$val};\n";
        } else if (is_array($val)) {
            echo "    {$idx} = new Array('','','','','','');\n";
        } else if (is_object($val)) {
            $valList  = "";
            $valList2 = "";
            $valName  = $idx;
            foreach ($val as $idz => $valz) {
                $valList  .= "'{$idz}',";
                $valList2 .= "'{$valz}',";
            }
            $valList  = preg_replace("/,$/", "", $valList);
            $valList2 = preg_replace("/,$/", "", $valList2);
            
            if ($idx == 'xfer_groups') {
                $valName = 'VARxferGroups';
            } else if ($idx == 'xfer_group_names') {
                $valName = 'VARxferGroupsNames';
            }
            
            if ($idx == 'statuses') {
                echo "var statuses_names = new Array({$valList2});\n";
            }
            echo "var {$valName} = new Array({$valList});\n";
        } else {
            echo "var {$idx} = '{$val}';\n";
            if ($idx == 'callback_statuses_list') {
                echo "var VARCBstatusesLIST = '{$val}';\n";
            }
        }
    }
    echo "\n";
    
    echo "// User Settings\n";    
    foreach ($agent as $idx => $val) {
        if (preg_match("/^(vicidial_recording|vicidial_recording_override)$/", $idx)) {
            ${$idx} = $val;
            echo "var {$idx} = '{$val}';\n";
        } else {
            if ($idx == 'user') {
                echo "var {$idx} = '{$val}';\n";
                echo "var uName = '{$val}';\n";
            } else if ($idx == 'pass') {
                echo "var {$idx} = '{$val}';\n";
                echo "var uPass = '{$val}';\n";
            } else if ($idx == 'phone_login') {
                ${$idx} = $val;
                echo "var {$idx} = '{$val}';\n";
                echo "var pExten = '{$val}';\n";
                echo "var original_{$idx} = '{$val}';\n";
            } else if ($idx == 'phone_pass') {
                ${$idx} = $val;
                echo "var {$idx} = '{$val}';\n";
                echo "var pPass = '{$val}';\n";
            } else if ($idx == 'full_name') {
                echo "var {$idx} = '{$val}';\n";
                echo "var fName = '{$val}';\n";
                echo "var LOGfullname = '{$val}';\n";
            } else if (preg_match("/^(custom_)/", $idx)) {
                echo "var user_{$idx} = '{$val}';\n";
            } else {
                echo "var {$idx} = '{$val}';\n";
            }
        }
    }
    //echo "// ".$result['user_group']."\n";
    
    $phone_login = (isset($_SESSION['phone_login'])) ? $_SESSION['phone_login'] : $phone_login;
    $phone_pass = (isset($_SESSION['phone_pass'])) ? $_SESSION['phone_pass'] : $phone_pass;
    echo "\n// Phone Settings\n";

    foreach ($phone as $idx => $val) {
        echo "var {$idx} = '{$val}';\n";
    }
    
    echo "\n// System Settings\n";
    
    foreach ($system as $idx => $val) {
        if (preg_match("/^(vdc_)/", $idx)) {
            $idx_ = str_replace('vdc_', '', $idx);
            echo "var {$idx_} = '{$val}';\n";
        } else {
            if ($idx == 'allow_emails') {
                echo "var email_enabled = '{$val}';\n";
            } else if ($idx == 'qc_features_active') {
                echo "var qc_enabled = '{$val}';\n";
            } else {
                echo "var {$idx} = '{$val}';\n";
            }
        }
    }
    
    if (isset($camp_info->campaign_id)) {
        echo "\n// Campaign Settings\n";
        $dial_prefix = '';
?>
var campaign = '<?=$camp_info->campaign_id?>';      // put here the selected campaign upon login
var group = '<?=$camp_info->campaign_id?>';         // same value as campaign variable
<?php
        foreach ($camp_info as $idx => $val) {
            if (preg_match("/^(timer_action)/", $idx)) {
                echo "var campaign_{$idx} = '{$val}';\n";
            } else {
                if ($idx == 'dial_prefix')
                    {$dial_prefix = $val;}
                if ($idx == 'manual_dial_prefix')
                    {$val = (strlen($val) < 1) ? $dial_prefix : $val;}
                if ($idx == 'pause_after_each_call') {
                    $idx = 'dispo_check_all_pause';
                    $val = ($val == 'Y') ? 1 : 0;
                }
                if (preg_match("/^(campaign_rec_filename|default_group_alias)$/", $idx)) {
                    echo "var LIVE_{$idx} = '{$val}';\n";
                }
        
                if (!preg_match("/^(disable_dispo_screen|disable_dispo_status|campaign_recording)$/", $idx)) {
                    if (preg_match("/^(web_form_address)/", $idx)) {
                        echo "var {$idx} = '{$val}';\n";
                        echo "var VDIC_{$idx} = '{$val}';\n";
                        echo "var TEMP_VDIC_{$idx} = '{$val}';\n";
                    } else {
                        echo "var {$idx} = '{$val}';\n";
                        if ($idx == 'auto_dial_level') {
                            echo "var starting_dial_level = '{$val}';\n";
                        }
                        if ($idx == 'api_manual_dial') {
                            $AllowManualQueueCalls = 1;
                            $AllowManualQueueCallsChoice = 0;
                            if ($val == 'QUEUE') {
                                $AllowManualQueueCalls = 0;
                                $AllowManualQueueCallsChoice = 1;
                            }
                            echo "var AllowManualQueueCalls = '{$AllowManualQueueCalls}';\n";
                            echo "var AllowManualQueueCallsChoice = '{$AllowManualQueueCallsChoice}';\n";
                        }
                        if ($idx == 'manual_preview_dial') {
                            $manual_dial_preview = 1;
                            if ($val == 'DISABLED')
                                {$manual_dial_preview = 0;}
                            echo "var manual_dial_preview = '{$manual_dial_preview}';\n";
                        }
                        if ($idx == 'manual_dial_override') {
                            if ($val == 'ALLOW_ALL')
                                {echo "    agentcall_manual = '1';\n";}
                            if ($val == 'DISABLE_ALL')
                                {echo "    agentcall_manual = '0';\n";}
                        }
                        if ($idx == 'agent_clipboard_copy') {
                            echo "var Copy_to_Clipboard = '{$val}';\n";
                        }
                        if (preg_match("/^(xferconf_)/", $idx)) {
                            echo "var ".preg_replace(array('/xferconf/', '/number/', '/dtmf/'), array('Call_XC', 'Number', 'DTMF'), $idx)." = '{$val}';\n";
                        }
                        if ($idx == 'view_calls_in_queue_launch') {
                            echo "var view_calls_in_queue_active = '{$val}';\n";
                        }
                    }
                } else {
                    ${$idx} = $val;
                }
            }
        }
        
        if (($disable_dispo_screen == 'DISPO_ENABLED') || ($disable_dispo_screen == 'DISPO_SELECT_DISABLED') || (strlen($disable_dispo_status) < 1)) {
            if ($disable_dispo_screen == 'DISPO_SELECT_DISABLED') {
                echo "var hide_dispo_list = '1';\n";
            } else {
                echo "var hide_dispo_list = '0';\n";
            }
            echo "var disable_dispo_screen = '0';\n";
            echo "var disable_dispo_status = '';\n";
        }
        if (($disable_dispo_screen == 'DISPO_DISABLED') && (strlen($disable_dispo_status) > 0)) {
            echo "var hide_dispo_list = '0';\n";
            echo "var disable_dispo_screen = '1';\n";
            echo "var disable_dispo_status = '{$disable_dispo_status}';\n";
        }
        
        if ((!preg_match('/DISABLED/', $vicidial_recording_override)) && ($vicidial_recording > 0))
            {$campaign_recording = $vicidial_recording_override;}
        if ($vicidial_recording == '0')
            {$campaign_recording = 'NEVER';}
        echo "var campaign_recording = '{$campaign_recording}';\n";
        echo "var LIVE_campaign_recording = '{$campaign_recording}';\n";
    }
?>

$(document).ready(function() {
    $(window).load(function() {
        var refreshId = setInterval(function() {
            if (is_logged_in) {
                //Start of checking for live calls
                //if (live_customer_call == 1) {
                //    live_call_seconds++;
                //    //$("input[name='SecondS']").val(live_call_seconds);
                //    //$("div:contains('CALL LENGTH:') > span").html(live_call_seconds);
                //    //$("div:contains('SESSION ID:') > span").html(session_id);
                //    toggleButton('DialHangup', 'hangup');
                //    toggleButton('ResumePause', 'off');
                //    
                //    if (CheckDEADcall > 0) {
                //        if (CheckDEADcallON < 1) {
                //            toggleStatus('DEAD');
                //            toggleButton('ParkCall', 'off');
                //            toggleButton('TransferCall', 'off');
                //            CheckDEADcallON = 1;
                //            
                //            if (xfer_in_call > 0 && customer_3way_hangup_logging == 'ENABLED') {
                //                customer_3way_hangup_counter_trigger = 1;
                //                customer_3way_hangup_counter = 1;
                //            }
                //        }
                //    }
                //}
                
                if (live_customer_call < 1) {
                    $("#edit-profile").addClass('hidden');
                    //toggleStatus('NOLIVE');
                    
                    if (dialingINprogress < 1) {
                        //toggleButton('DialHangup', 'dial');
                        //toggleButton('ResumePause', 'on');
                    }
                }
                //End of checking for live calls
    
                $("#LeadLookUP").prop('checked', true);
    
                check_r++;
                WaitingForNextStep = 0;
                if ( (CloserSelecting==1) || (TerritorySelecting==1) )	{WaitingForNextStep=1;}
                
                if (open_dispo_screen == 1) {
                    wrapup_counter = 0;
                    if (wrapup_seconds > 0) {
                        //showDiv('WrapupBox');
                        //$("#WrapupTimer").html(wrapup_seconds);
                        wrapup_waiting = 1;
                    }
    
                    CustomerData_update();
                    //if (hide_gender < 1)
                    //{
                    //    $("#GENDERhideFORie").html('');
                    //    $("#GENDERhideFORieALT").html('<select size="1" name="gender_list" class="cust_form" id="gender_list"><option value="U">U - <?=$lh->translationFor('undefined')?></option><option value="M">M - <?=$lh->translationFor('male')?></option><option value="F">F - <?=$lh->translationFor('female')?></option></select>');
                    //}
    
                    DispoSelectBox();
                    //DispoSelectContent_create('','ReSET');
                    WaitingForNextStep = 1;
                    open_dispo_screen = 0;
                    LIVE_default_xfer_group = default_xfer_group;
                    LIVE_campaign_recording = campaign_recording;
                    LIVE_campaign_rec_filename = campaign_rec_filename;
                    if (disable_alter_custphone != 'HIDE')
                        {$("#DispoSelectPhone").html(dialed_number);}
                    else
                        {$("#DispoSelectPhone").html('');}
                    if (auto_dial_level == 0) {
                        if ($("#DialALTPhone").is(':checked') == true) {
                            reselect_alt_dial = 1;
                            toggleButton('DialHangup', 'dial');
    
                            $("#MainStatusSpan").html("<b><?=$lh->translationFor('dial_next_call')?></b>");
                        } else {
                            reselect_alt_dial = 0;
                        }
                    }
    
                    // Submit custom form if it is custom_fields_enabled
                    if (custom_fields_enabled > 0) {
                        //alert("IFRAME submitting!");
                        //vcFormIFrame.document.form_custom_fields.submit();
                    }
                }
                
                if (AgentDispoing > 0) {
                    WaitingForNextStep = 1;
                    CheckForConfCalls(session_id, '0');
                    AgentDispoing++;
                }
                
                if (agent_choose_ingroups_skip_count > 0) {
                    agent_choose_ingroups_skip_count--;
                    if (agent_choose_ingroups_skip_count == 0)
                        {CloserSelectSubmit();}
                }
                if (agent_select_territories_skip_count > 0) {
                    agent_select_territories_skip_count--;
                    if (agent_select_territories_skip_count == 0)
                        {TerritorySelectSubmit();}
                }
                if (logout_stop_timeouts == 1)	{WaitingForNextStep = 1;}
                if ( (custchannellive < -30) && (lastcustchannel.length > 3) && (no_empty_session_warnings < 1) ) {CustomerChannelGone();}
                if ( (custchannellive < -10) && (lastcustchannel.length > 3) ) {ReCheckCustomerChan();}
                if ( (nochannelinsession > 16) && (check_r > 15) && (no_empty_session_warnings < 1) ) {NoneInSession();}
                if (external_transferconf_count > 0) {external_transferconf_count = (external_transferconf_count - 1);}
                if (manual_auto_hotkey == 1) {
                    manual_auto_hotkey = 0;
                    ManualDialNext('','','','','','0');
                }
                if (manual_auto_hotkey > 1) {manual_auto_hotkey = (manual_auto_hotkey - 1);}

                if (WaitingForNextStep == 0) {
                    if (trigger_ready > 0) {
                        trigger_ready = 0;
                        if (auto_resume_precall == 'Y')
                            {AutoDial_Resume_Pause("VDADready");}
                    }
                    
                    // check for live channels in conference room and get current datetime
                    CheckForConfCalls(session_id, '0');
                    
                    // refresh agent status view
                    if (agent_status_view_active > 0) {
                        //refresh_agents_view('AgentViewStatus', agent_status_view);
                    }
                    if (view_calls_in_queue_active > 0) {
                        //refresh_calls_in_queue(view_calls_in_queue);
                    }
                    if (xfer_select_agents_active > 0) {
                        //refresh_agents_view('AgentXferViewSelect', agent_status_view);
                    }
                    if (agentonly_callbacks == '1')
                        {CB_count_check++;}

                    if (AutoDialWaiting == 1) {
                        CheckForIncoming();
                    }

                    if (MD_channel_look == 1) {
                        ManualDialCheckChannel();
                    }
                    
                    if ( (CB_count_check > 19) && (agentonly_callbacks == '1') ) {
                        //CalLBacKsCounTCheck();
                        CB_count_check = 0;
                    }
                    if ( (even > 0) && (agent_display_dialable_leads > 0) ) {
                        //DiaLableLeaDsCounT();
                    }
                    if (live_customer_call == 1) {
                        live_call_seconds++;
                        $(".formMain input[name='seconds']").val(live_call_seconds);
                        $("#SecondsDISP").html(live_call_seconds);
                        $("#edit-profile").removeClass('hidden');
                    }
                    if (XD_live_customer_call == 1) {
                        XD_live_call_seconds++;
                        $("#xferlength").val(XD_live_call_seconds);
                        $("#edit-profile").removeClass('hidden');
                    }
                    if (customerparked == 1) {
                        customerparkedcounter++;
                        var parked_mm = Math.floor(customerparkedcounter/60);  // The minutes
                        var parked_ss = customerparkedcounter % 60;            // The balance of seconds
                        if (parked_ss < 10)
                            {parked_ss = "0" + parked_ss;}
                        var parked_mmss = parked_mm + ":" + parked_ss;
                        $("#ParkCounterSpan").html("<?=$lh->translationFor('time_on_park')?>: " + parked_mmss);
					}
                    if (customer_3way_hangup_counter_trigger > 0) {
                        if (customer_3way_hangup_counter > customer_3way_hangup_seconds) {
                            var customer_3way_timer_seconds = (XD_live_call_seconds - customer_3way_hangup_counter);
                            //customer_3way_hangup_process('DURING_CALL',customer_3way_timer_seconds);
    
                            customer_3way_hangup_counter = 0;
                            customer_3way_hangup_counter_trigger = 0;
    
                            if (customer_3way_hangup_action == 'DISPO') {
                                customer_3way_hangup_dispo_message = '<?=$lh->translationFor('customer_hangup_3way')?>';
                                BothCallHangup();
                            }
						} else {
                            customer_3way_hangup_counter++;
                            //document.getElementById("debugbottomspan").innerHTML = "<?=$lang['customer_3way_hangup']?> " + customer_3way_hangup_counter;
						}
					}
                    if ( (update_fields > 0) && (update_fields_data.length > 2) ) {
                        UpdateFieldsData();
					}
                    if ( (timer_action != 'NONE') && (timer_action.length > 3) && (timer_action_seconds <= live_call_seconds) && (timer_action_seconds >= 0) ) {
                        TimerActionRun('', '');
                    }
                    if (HKdispo_display > 0) {
                        if ( (HKdispo_display == 3) && (HKfinish == 1) ) {
                            HKfinish = 0;
                            DispoSelectSubmit();
                            //AutoDialWaiting = 1;
                            //AutoDial_Resume_Pause("VDADready");
                        }
                        if (HKdispo_display == 1) {
                            //if (hot_keys_active == 1)
                            //	{showDiv('HotKeyEntriesBox');}
                            //hideDiv('HotKeyActionBox');
                        }
                        HKdispo_display--;
                    }
                    if (all_record == 'YES') {
                        if (all_record_count < allcalls_delay)
                            {all_record_count++;}
                        else {
                            //ConfSendRecording('MonitorConf', session_id , '');
                            all_record = 'NO';
                            all_record_count = 0;
                        }
                    }
    
    
                    if (active_display == 1) {
                        check_s = check_r.toString();
                        if ( (check_s.match(/00$/)) || (check_r < 2) )  {
                            //check_for_conf_calls();
                        }
                    }
                    if (check_r < 2) {
                        // nothing to see here... move along...
                    } else {
                        //check_for_live_calls();
                        check_s = check_r.toString();
                    }
                    if ( (blind_monitoring_now > 0) && ( (blind_monitor_warning == 'ALERT') || (blind_monitor_warning == 'NOTICE') ||  (blind_monitor_warning == 'AUDIO') || (blind_monitor_warning == 'ALERT_NOTICE') || (blind_monitor_warning == 'ALERT_AUDIO') || (blind_monitor_warning == 'NOTICE_AUDIO') || (blind_monitor_warning == 'ALL') ) ) {
                        if ( (blind_monitor_warning == 'NOTICE') || (blind_monitor_warning == 'ALERT_NOTICE') || (blind_monitor_warning == 'NOTICE_AUDIO') || (blind_monitor_warning == 'ALL') ) {
                            //document.getElementById("blind_monitor_notice_span_contents").innerHTML = blind_monitor_message + "<br />";
                            //showDiv('blind_monitor_notice_span');
                        }
                        if (blind_monitoring_now_trigger > 0) {
                            if ( (blind_monitor_warning == 'ALERT') || (blind_monitor_warning == 'ALERT_NOTICE') || (blind_monitor_warning == 'ALERT_AUDIO') || (blind_monitor_warning == 'ALL') ) {
                                //document.getElementById("blind_monitor_alert_span_contents").innerHTML = blind_monitor_message;
                                //showDiv('blind_monitor_alert_span');
                            }
                            if ( (blind_monitor_filename.length > 0) && ( (blind_monitor_warning == 'AUDIO') || (blind_monitor_warning == 'ALERT_AUDIO')|| (blind_monitor_warning == 'NOTICE_AUDIO') || (blind_monitor_warning == 'ALL') ) ) {
                                BasicOriginateCall(blind_monitor_filename, 'NO', 'YES', session_id, 'YES', '', '1', '0', '1');
                            }
                            blind_monitoring_now_trigger = 0;
                        }
                    } else {
                        //hideDiv('blind_monitor_notice_span');
                        //document.getElementById("blind_monitor_notice_span_contents").innerHTML = '';
                        //hideDiv('blind_monitor_alert_span');
                    }
                    if (wrapup_seconds > 0) {
                        //document.getElementById("WrapupTimer").innerHTML = (wrapup_seconds - wrapup_counter);
                        wrapup_counter++;
                        if ( (wrapup_counter > wrapup_seconds) && ($("#WrapupBox").is(':visible')) ) {
                            wrapup_waiting = 0;
                            //hideDiv('WrapupBox');
                            if (DispoSelectStop) {
                                if (auto_dial_level != '0') {
                                    AutoDialWaiting = 0;
                                    //alert('wrapup pause');
                                    AutoDial_Resume_Pause("VDADpause");
                                    //document.getElementById("DiaLControl").innerHTML = DiaLControl_auto_HTML;
                                }
                                pause_calling = 1;
                                if (dispo_check_all_pause != '1') {
                                    DispoSelectStop = false;
                                    $("#DispoSelectStop").prop('checked', false);
                                    //alert("unchecking PAUSE");
                                }
                            } else {
                                if (auto_dial_level != '0') {
                                    AutoDialWaiting = 1;
                                    //alert('wrapup ready');
                                    AutoDial_Resume_Pause("VDADready", "NEW_ID", "WRAPUP");
                                    //document.getElementById("DiaLControl").innerHTML = DiaLControl_auto_HTML_ready;
                                }
                            }
                        }
                    }
                    if (consult_custom_wait > 0) {
                        //if (consult_custom_wait == '1')
                        //    {vcFormIFrame.document.form_custom_fields.submit();}
                        if (consult_custom_wait >= consult_custom_delay)
                            {SendManualDial('YES');}
                        else
                            {consult_custom_wait++;}
                    }
                }
            } else {
                updateButtons();
                
                if (DefaultALTDial == 1) {
                    $("#DiaLAltPhonE").prop('checked', true);
                }
                
                $("#LeadLookUP").prop('checked', true);
                
                if (INgroupCOUNT > 0) {
                    if (closer_default_blended == 1)
                        {$("#closerSelectBlended").prop('checked', true);}
                    //CloserSelectContent_create();
                    //showDiv('CloserSelectBox');
                    CloserSelecting = 1;
                    //CloserSelectContent_create();
                    if (agent_choose_ingroups_DV == "MGRLOCK")
                        {agent_choose_ingroups_skip_count = 4;}
                } else {
                    //hideDiv('CloserSelectBox');
                    //MainPanelToFront();
                    CloserSelecting = 0;
                    if (dial_method == "INBOUND_MAN") {
                        dial_method = "MANUAL";
                        auto_dial_level = 0;
                        starting_dial_level = 0;
                        toggleButton('DialHangup', 'dial');
                    }
                }
            }
        }, refresh_interval);
        
        if (is_logged_in) {
            $("aside.control-sidebar").addClass('control-sidebar-open');
        }
        
        if (!opener) {
            //<?=GO_AGENT_DIRECTORY?>/jsSIP.php
            //var GOagentWebRTC = window.open('http://google.com','GOagentWebRTC', 'toolbar=no,status=no,menubar=no,scrollbars=no,resizable=yes,left=100000, top=100000, width=10, height=10');
            //opener = GOagentWebRTC;
            console.log('a new window is opened', opener);
        } else {
            console.log('window is still open');
        }
    });

    var logoutRegX = new RegExp("logout\.php", "ig");
    $("#cream-agent-logout").click(function(event) {
        var hRef = $(this).attr('href');
        var loggedOut = 0;
        if (hRef.match(logoutRegX)) {
            event.preventDefault();
            var sureToLogout = confirm("<?=$lh->translationFor('sure_to_logout')?>");
            if (sureToLogout) {
                if (is_logged_in) {
                    logoutWarn = false;
                    btnLogMeOut();
                    loggedOut++;
                }
                if (phone.isConnected()) {
                    phone.stop();
                    loggedOut++;
                }
                if (loggedOut > 0) {
                    console.log('<?=$lh->translationFor('logging_out_phones')?>...');
                    $("div.preloader center").append('<br><br><span style="font-size: 24px; color: white;"><?=$lh->translationFor('logging_out_phones')?>...</span>');
                    $("div.preloader").fadeIn("slow");
                    setTimeout(
                        function() {
                            window.location.href = hRef;
                        },
                        2500
                    );
                }
            }
        }
    });

    <?php
    $GOmodule = false;
    if ($GOmodule) {
    ?>
    var $navBar = $("<div id='go_nav_bar'></div>");
    $navBar.css({
        'left' : '0px',
        'bottom' : '0px',
        'width' : '100%',
        'height' : '0px',
        'padding' : '0 5px',
        'position' : 'fixed',
        'zIndex' : '1001',
        'backgroundColor' : 'white',
        'border-top' : 'solid 1px black'
    });
    
    if ($("footer").length < 1) {
        $("body").append('<footer></footer>');
    }

    $("footer").append($navBar);
    var $vtFooter = jQuery("footer");
    $vtFooter.css({
        'border-top' : '0',
        'margin-left' : '230px',
        'zIndex' : '995'
    });
    var circleButton = jQuery(".circle-button").css('bottom');
    var favDivButton = jQuery("#fab-div-area").css('margin-bottom');
    
    $("footer").prepend($('<div id="go_nav_tab" title="<?=$lh->translationFor('open_tab')?>"> <i class="fa fa-chevron-up"></i> </div>'));
    $("#go_nav_tab").css({
        'left' : '3px',
        'bottom' : '0px',
        'padding' : '0 5px',
        'backgroundColor' : 'white',
        'border-style' : 'solid',
        'border-width' : '1px 1px 0',
        'border-color' : 'black',
        'cursor' : 'pointer',
        'position' : 'fixed',
        'zIndex' : '1002',
        'height' : '15px',
    });

    var resized = true;
    $("#go_nav_tab").click(function() {
        var barHeight = 37;
        $("#go_nav_tab").stop(true, false).animate({
            bottom: resized ? (barHeight - 1) : -1
        }, function() {
            if (resized) {
                $(this).attr('title', '<?=$lh->translationFor('open_tab')?>');
            } else {
                $(this).attr('title', '<?=$lh->translationFor('close_tab')?>');
            }
        });
        
        $("#go_nav_bar").stop(true, false).animate({
            height: resized ? barHeight : 0
        });
        
        $("footer").stop(true, false).animate({
            paddingBottom : resized ? barHeight : 15,
        });
        
        $(".circle-button").stop(true, false).animate({
            bottom : resized ? (barHeight + parseInt(circleButton)) : circleButton,
        });
        
        $("#fab-div-area").stop(true, false).animate({
            marginBottom : resized ? (barHeight + parseInt(favDivButton)) : favDivButton,
        });
        
        if (($(window).scrollTop() + document.body.clientHeight) == $(document).height()) {
            $("html, body").animate({ scrollTop: $(document).height() }, 'slow');
        }
        
        $("#go_nav_tab i").attr({
            class: resized ? 'fa fa-chevron-down' : 'fa fa-chevron-up'
        });
        
        resized = !resized;
    });
    <?php
    }
    ?>

    // buttons
    $("#go_agent_dialpad").append("<li id='go_nav_btn' style='margin-top: 15px;'></li>");
    //$("#go_nav_btn").append("<div id='go_btn_group' class='btn-group dropup pull-right' style='margin: 0 1px;'>");
    //$("#go_btn_group").append("<button id='dropdownMenuAgent' type='button' data-toggle='dropdown' class='btn btn-default dropdown-toggle' style='margin: 5px 0;'><i class='fa fa-navicon'></i></button>");
    //$("#go_btn_group").append("<ul id='go_dropdown' class='dropdown-menu'>");
    //$("#go_dropdown").append("<li id='manual-dial'><a><?=$lh->translationFor('manual_dial')?> <span class='fa fa-phone pull-right'></span></a></li>");
    //$("#go_dropdown").append("<li><a><?=$lh->translationFor('available_hot_keys')?> <span class='fa fa-keyboard-o pull-right'></span></a></li>");
    //$("#go_dropdown").append("<li><a><?=$lh->translationFor('active_callbacks')?> <span class='badge pull-right bg-green'>0</span></a></li>");
    //$("#go_dropdown").append("<li><a><?=$lh->translationFor('callbacks_for_today')?> <span class='badge pull-right bg-red'>0</span></a></li>");
    //$("#go_dropdown").append("<li><a><?=$lh->translationFor('enter_pause_code')?> <span class='fa fa-pause-circle-o pull-right'></span></a></li>");
    //$("#go_dropdown").append("<li><a><?=$lh->translationFor('lead_search')?> <span class='fa fa-search pull-right'></span></a></li>");
    //$("#go_dropdown").append("<li id='btnLogMeOut'><a><?=$lh->translationFor('logout_from_phone')?> <span class='fa fa-sign-out pull-right'></span></a></li>");
    //$("#go_btn_group").append("</ul>");
    //$("#go_nav_btn").append("</div>");
    $("#go_nav_btn").append("<div id='livecall' class='center-block'><h3 class='nolivecall' title=''><?=$lh->translationFor('no_live_call')?></h3></div>");
    $("#go_nav_btn").append("<div id='go_btn_div' class='center-block' style='text-align: center;'></div>");
    $("#go_btn_div").append("<button id='btnDialHangup' title='<?=$lh->translationFor('dial_next_call')?>' class='btn btn-danger btn-lg' style='margin: 0 5px 5px 0; font-size: 16px;'><i class='fa fa-phone'></i></button>");
    $("#go_btn_div").append("<button id='btnResumePause' title='<?=$lh->translationFor('resume_dialing')?>' class='btn btn-success btn-lg' style='margin: 0 5px 5px 0; font-size: 16px;'><i class='fa fa-play'></i></button>");
    $("#go_btn_div").append("<button id='btnParkCall' title='<?=$lh->translationFor('park_call')?>' class='btn btn-warning btn-lg' style='margin: 0 5px 5px 0; font-size: 15px; padding-bottom: 11px;'><i class='fa fa-music'></i></button>");
    $("#go_btn_div").append("<button id='btnTransferCall' title='<?=$lh->translationFor('transfer_call')?>' class='btn btn-default btn-lg' style='margin: 0 5px 5px 0;'><i class='fa fa-random'></i></button>");
    $("#go_btn_div").append("<button id='btnIVRParkCall' title='<?=$lh->translationFor('ivr_park_call')?>' class='btn btn-default btn-lg' style='margin: 0 5px 5px 0;'><i class='fa fa-tty'></i></button>");
    $("#go_btn_div").append("<button id='btnReQueueCall' title='<?=$lh->translationFor('requeue_call')?>' class='btn btn-default btn-lg' style='margin: 0 5px 5px 0; padding-right: 21px;'><i class='fa fa-refresh'></i></button>");
    //$("#go_nav_btn").append("<div id='cust-info' class='center-block' style='text-align: center; line-height: 35px;'><i class='fa fa-user'></i> <span id='cust-name' style='padding-right: 20px;'></span> <i class='fa fa-phone-square'></i> <span id='cust-phone'></span><input type='hidden' id='cust-phone-number' /></div>");
    $("#go_agent_status").append("<li><div id='MainStatusSpan' class='center-block' style='line-height: 35px;'>&nbsp;</div></li>");
    
    <?php
    $padPx = (preg_match("/Chrome/", $_SERVER['HTTP_USER_AGENT'])) ? "4px" : "3px";
    ?>
    $("#go_agent_manualdial").append("<li><div class='btn-group pull-right'><a href='javascript:void(0)' class='btn btn-success' style='padding: 4px 3px <?=$padPx?> 5px;' id='manual-dial-now'><?=$lh->translationFor('dial')?></a><a href='#' data-target='#' class='btn btn-success dropdown-toggle' data-toggle='dropdown' style='padding: 11px 6px 12px;' id='manual-dial-dropdown'><span class='caret'></span></a><ul class='dropdown-menu'><li><a href='javascript:void(0)' id='manual-dial-now'><?=$lh->translationFor('dial_now')?></a></li><li><a href='javascript:void(0)' id='manual-dial-preview'><?=$lh->translationFor('preview_call')?></a></li></ul></div><input type='hidden' size='7' maxlength='10' name='MDDiaLCodE' id='MDDiaLCodE' class='digits-only' value='1' /><input type='text' size='18' maxlength='18' name='MDPhonENumbeR' id='MDPhonENumbeR' class='phonenumbers-only' value='' placeholder='<?=$lh->translationFor('enter_phone_number')?>' onkeyup='activateLinks();' onchange='activateLinks();' style='padding: 4px 2px 3px; color: #222;' /><br><small>(<?=$lh->translationFor('digits_only_please')?>)</small><input type='hidden' name='MDPhonENumbeRHiddeN' id='MDPhonENumbeRHiddeN' value='' /><input type='hidden' name='MDLeadID' id='MDLeadID' value='' /><input type='hidden' name='MDType' id='MDType' value='' /><input type='checkbox' name='LeadLookUP' id='LeadLookUP' size='1' value='0' class='hidden' disabled /><input type='hidden' size='24' maxlength='20' name='MDDiaLOverridE' id='MDDiaLOverridE' class='cust_form' value='' /></li>");

    $("#go_agent_login").append("<li><button id='btnLogMeIn' class='btn btn-warning center-block' style='margin-top: 2px; padding: 5px 12px;'><i class='fa fa-sign-in'></i> <?=$lh->translationFor('login_on_phone')?></button></li>");
    $("#go_agent_logout").append("<li><button id='btnLogMeOut' class='btn btn-warning center-block' style='margin-top: 2px; padding: 5px 12px;'><i class='fa fa-sign-out'></i> <?=$lh->translationFor('logout_from_phone')?></button></li>");
    
    var paddingHB = 100;
    var navConBar = $("ul.control-sidebar-tabs").innerHeight();
    var minusThis = (parseInt(navConBar) + parseInt(paddingHB));
    var newHeight = parseInt($("body").innerHeight()) - minusThis;
    $("aside.control-sidebar div.tab-content").css({
        'height': newHeight
    });

    $("button[id^='btn']").click(function() {
        var btnID = $(this).attr('id').replace('btn', '');
        switch (btnID) {
            case "DialHangup":
                btnDialHangup();
                break;
            case "ResumePause":
                btnResumePause();
                break;
            case "LogMeIn":
                btnLogMeIn();
                break;
            case "LogMeOut":
                btnLogMeOut();
                break;
        }
    });
    
    $("li[id^='btn']").click(function() {
        var btnID = $(this).attr('id').replace('btn', '');
        switch (btnID) {
            case "LogMeOut":
                btnLogMeOut();
                break;
        }
    });

    var showInfo = false;
    $("#cust-info").click(function() {
        if (!showInfo) {
            $("#dialog-custinfo").modal({
                backdrop: 'static',
                show: true
            });
            showInfo = true;
        } else {
            $("#dialog-custinfo").modal('hide');
            showInfo = false;
        }
    });

    $("#manual-dial").click(function() {
        $("#manual-dial-box").modal({
            backdrop: 'static',
            show: true
        });
    });
    
    $("#dialog-custinfo div.modal-dialog").css({'width': '750px'});
    
    $("a[id^='manual-dial-']").click(function() {
        //$('#manual-dial-box').modal('hide');
        var btnID = $(this).attr('id').replace('manual-dial-', '');
        if (btnID != 'dropdown' && ! $(this).hasClass('disabled')) {
            NewManualDialCall(btnID.toUpperCase());
            activateLinks();
        }
    });
    
    setInterval(function() {
        if (!$('#go_dropdown').is(':visible')) {
            $('.circle-button').show();
        } else {
            $('.circle-button').hide();
        }
    }, 500);
    
    $('#dropdownMenuAgent').click(function() {
        $('.circle-button').hide();
    });
    
    //$(".modal").on("show.bs.modal", function() {
    //    var curModal;
    //    curModal = this;
    //    $(".modal").each(function() {
    //        if (this !== curModal) {
    //            $(this).modal("hide");
    //            if ($(this).attr('id').match(/custinfo$/)) {
    //                showInfo = false;
    //            }
    //        }
    //    });
    //});

    updateButtons();
    toggleButtons(dial_method);
    toggleStatus('NOLIVE');
    activateLinks();

    window.addEventListener("beforeunload", function (e) {
        if (is_logged_in) {
            var confirmationMessage = "<?=$lh->translationFor('currently_in_call')?>";
        
            (e || window.event).returnValue = confirmationMessage;     //Gecko + IE
            return confirmationMessage;                                //Webkit, Safari, Chrome etc.
        } else {
            //GOagentWebRTC.close();
        }
    });

    $("#notSelectedINB, #selectedINB").sortable({
        connectWith: ".connectedINB",
        placeholder: "ui-state-highlight",
        receive: function(event, ui) {
            if ($(this).attr('id') == 'notSelectedINB' && $(this).text() != "") {
                $("#scButton").html('<?=$lh->translationFor('select_all')?>');
            }
        },
        remove: function(event, ui) {
            if ($(this).attr('id') == 'notSelectedINB' && $(this).text() == "") {
                $("#scButton").html('<?=$lh->translationFor('remove_all')?>');
            } else if ($(this).attr('id') == 'selectedINB' && $(this).text() == "") {
                $("#scButton").html('<?=$lh->translationFor('select_all')?>');
            }
        }
    }).disableSelection();
    
    $("#select_camp").change(function() {
        var camp = $(this).val();
        $("#inboundSelection, #scButton, #selectionNote").addClass('hidden');
        $("#closerSelectBlended").closest('p').addClass('hidden');
        if (camp.length > 0) {
            $("#logSpinner").removeClass('hidden');
            $("#scSubmit").removeClass('disabled');
            var postData = {
                goAction: 'goGetInboundGroups',
                goUser: user,
                goPass: pass,
                goCampaign: $(this).val(),
                responsetype: 'json'
            };
        
            $.ajax({
                type: 'POST',
                url: '<?=$goAPI?>/goAgent/goAPI.php',
                processData: true,
                data: postData,
                dataType: "json"
            })
            .done(function (data) {
                var result = data.result;
                $("#logSpinner").addClass('hidden');
                if (result != 'error') {
                    var inb_list = '';
                    $.each(data.data.inbound_groups, function(idx, inbg) {
                        inb_list += "<li class='ui-state-default'><abbr title='"+inbg+"'>"+idx+"</abbr></li>";
                    });
                    $("#selectedINB").empty();
                    $("#notSelectedINB").empty().append(inb_list);
                    $("#inboundSelection, #scButton, #selectionNote").removeClass('hidden');
                    $("#closerSelectBlended").closest('p').removeClass('hidden');
                } else {
                    //alert(data.message);
                    $("#inboundSelection, #scButton, #selectionNote").addClass('hidden');
                    $("#closerSelectBlended").closest('p').addClass('hidden');
                }
            });
        } else {
            $("#scSubmit").addClass('disabled');
        }
    });

    $("#scButton").click(function() {
        var content = $(this).text();
        if (content == '<?=$lh->translationFor('select_all')?>') {
            content = '<?=$lh->translationFor('remove_all')?>';
            var divContent = $("#notSelectedINB").html();
            $("#notSelectedINB").empty();
            $("#selectedINB").append(divContent);
        } else {
            content = '<?=$lh->translationFor('select_all')?>';
            var divContent = $("#selectedINB").html();
            $("#selectedINB").empty();
            $("#notSelectedINB").append(divContent);
        }
        $(this).text(content);
    });
    
    $("#scSubmit").click(function(e) {
        e.preventDefault();
        var inbArray = [];
        $("#scSubmit").addClass('disabled');
        $("#selectedINB").find('abbr').each(function(index) {
            inbArray.push($(this).text());
        });
        var postData = {
            goAction: 'goLoginUser',
            goUser: user,
            goPass: pass,
            goCampaign: $("#select_camp").val(),
            goIngroups: inbArray,
            responsetype: 'json',
            closer_blended: $("#closerSelectBlended").is(':checked'),
            goUseWebRTC: use_webrtc
        };

        $.ajax({
            type: 'POST',
            url: '<?=$goAPI?>/goAgent/goAPI.php',
            processData: true,
            data: postData,
            dataType: "json"
        })
        .done(function (result) {
            if (result.result != 'error') {
                $("#select-campaign").modal('hide');
                
                refresh_interval = 1000;
                is_logged_in = 1;
                logout_stop_timeouts = 0;
                
                $.each(result.data, function(key, value) {
                    if (key == 'campaign_settings') {
                        $.each(value, function(cKey, cValue) {
                            var patt = /^timer_action/g;
                            if (patt.test(cKey)) {
                                $.globalEval("var campaign_"+cKey+" = '"+cValue+"';");
                            } else {
                                if (cKey == 'campaign_id') {
                                    $.post("<?=$module_dir?>GOagentJS.php", {'module_name': 'GOagent', 'action': 'SessioN', 'campaign_id': cValue});
                                }
                                
                                if (cKey == 'dial_prefix') {
                                    var dial_prefix = cValue;
                                }
                            
                                if (cKey == 'manual_dial_prefix') {
                                    cValue = (cValue.length < 1) ? dial_prefix : cValue;
                                }
                                
                                if (cKey == 'pause_after_each_call') {
                                    cKey = 'dispo_check_all_pause';
                                    cValue = (cValue == 'Y') ? 1 : 0;
                                }
                                
                                var rec_patt = /^(campaign_rec_filename|default_group_alias)$/g;
                                if (rec_patt.test(cKey)) {
                                    $.globalEval("var LIVE_"+cKey+" = '"+cValue+"';");
                                }
                                
                                var dispo_patt = /^(disable_dispo_screen|disable_dispo_status|campaign_recording)$/g;
                                if (!dispo_patt.test(cKey)) {
                                    if (cKey == 'web_form_address' || cKey == 'web_form_address_two') {
                                        $.globalEval("var "+cKey+" = '"+cValue+"';");
                                        $.globalEval("var VDIC_"+cKey+" = '"+cValue+"';");
                                        $.globalEval("var TEMP_VDIC_"+cKey+" = '"+cValue+"';");
                                    } else {
                                        $.globalEval("var "+cKey+" = '"+cValue+"';");
                                        if (cKey == 'auto_dial_level') {
                                            $.globalEval("var starting_dial_level = '"+cValue+"';");
                                        }
                                        if (cKey == 'api_manual_dial') {
                                            var amqc = 1;
                                            var amqcc = 0;
                                            if (cValue == 'QUEUE') {
                                                amqc = 0;
                                                amqcc = 1;
                                            }
                                            $.globalEval("var AllowManualQueueCalls = '"+amqc+"';");
                                            $.globalEval("var AllowManualQueueCallsChoice = '"+amqcc+"';");
                                        }
                                        if (cKey == 'manual_preview_dial') {
                                            var mdp = 1;
                                            if (cValue == 'DISABLED')
                                                {mdp = 0;}
                                            $.globalEval("var manual_dial_preview = '"+mdp+"';");
                                        }
                                        if (cKey == 'manual_dial_override') {
                                            if (cValue == 'ALLOW_ALL')
                                                {agentcall_manual = '1';}
                                            if (cValue == 'DISABLE_ALL')
                                                {agentcall_manual = '0';}
                                        }
                                    }
                                } else {
                                    $.globalEval("var "+cKey+" = '"+cValue+"';");
                                }
                            }
                        });
                    } else {
                        var patt = /^(user|pass|statuses|statuses_count)$/g;
                        //console.log("var "+key+" = '"+value+"';");
                        if (!patt.test(key)) {
                            if (key == 'now_time') {
                                $.globalEval("var NOW_TIME = '"+value+"';");
                                $.globalEval("var SQLdate = '"+value+"';");
                            } else if (key == 'start_time') {
                                $.globalEval("var StarTtimE = '"+value+"';");
                                $.globalEval("var UnixTime = '"+value+"';");
                            } else if (key == 'VARxferGroups' || key == 'VARxferGroupsNames') {
                                $.globalEval("var "+key+" = new Array("+value+");");
                            } else if (key == 'session_name') {
                                $.globalEval("var "+key+" = '"+value+"';");
                                $.globalEval("var webform_session = '&"+key+"="+value+"';");
                            } else if (key == 'alt_phone_dialing') {
                                $.globalEval("var "+key+" = "+value+";");
                                $.globalEval("var starting_"+key+" = "+value+";");
                            } else {
                                $.globalEval("var "+key+" = '"+value+"';");
                            }
                        }
                    }
                });
    
                if ((disable_dispo_screen == 'DISPO_ENABLED') || (disable_dispo_screen == 'DISPO_SELECT_DISABLED') || (disable_dispo_status.length < 1)) {
                    if (disable_dispo_screen == 'DISPO_SELECT_DISABLED') {
                        $.globalEval("var hide_dispo_list = '1';");
                    } else {
                        $.globalEval("var hide_dispo_list = '0';");
                    }
                    $.globalEval("var disable_dispo_screen = '0';");
                    $.globalEval("var disable_dispo_status = '';");
                }
                if ((disable_dispo_screen == 'DISPO_DISABLED') && (disable_dispo_status.length > 0)) {
                    $.globalEval("var hide_dispo_list = '0';");
                    $.globalEval("var disable_dispo_screen = '1';");
                    $.globalEval("var disable_dispo_status = '"+disable_dispo_status+"';");
                }
                
                var vro_patt = /DISABLED/;
                if ((!vro_patt.test(vicidial_recording_override)) && (vicidial_recording > 0))
                    {var camp_rec = vicidial_recording_override;}
                if (vicidial_recording == '0')
                    {var camp_rec = 'NEVER';}
                $.globalEval("var campaign_recording = '"+camp_rec+"';");
                $.globalEval("var LIVE_campaign_recording = '"+camp_rec+"';");
                
                updateButtons();
            } else {
                refresh_interval = 730000;
                is_logged_in = 0;
                alert(result.message);
                $("#scSubmit").removeClass('disabled');
            }
        })
        .fail(function() {
            refresh_interval = 730000;
            is_logged_in = 0;
            $("#scSubmit").removeClass('disabled');
        });
    });
    
    $(".digits-only, .phonenumbers-only").keypress(function (e) {
        var thisOne = $(this)[0];
        //if the letter is not digit then display error and don't type anything
        if (thisOne.className == 'digits-only' && e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
            //display error message
            return false;
        } else if (thisOne.className == 'phonenumbers-only' && e.which != 8 && e.which != 0 && (e.which != 40 && e.which != 41 && e.which != 43 && e.which != 45) && (e.which < 48 || e.which > 57)) {
            //display error message
            return false;
        }
    });
    
    $('#manual-dial-box').on('hidden.bs.modal', function () {
        $("#MDPhonENumbeR").val('');
        $("#MDPhonENumbeRHiddeN").val('');
        $("#MDLeadID").val('');
        $("#MDType").val('');
    });
    
    $("#btn-dispo-submit").click(function() {
        DispoSelectSubmit();
    });
    
    $("#btn-dispo-reset").click(function() {
        DispoSelectContent_create('', 'ReSET');
    });
});

function btnLogMeIn () {
    var postData = {
        goAction: 'goGetAllowedCampaigns',
        goUser: user,
        goPass: pass,
        responsetype: 'json'
    };

    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
        if (result.result == 'success') {
            var camp_list = result.data.allowed_campaigns;
            var camp_options = "<option value=''><?=$lh->translationFor('select_a_campaign')?></option>";
            $.each(camp_list, function(idx, camp) {
                camp_options += "<option value='"+idx+"'>"+camp+"</option>";
            });
            $("#select-campaign select#select_camp").html(camp_options);
            $("#inboundSelection, #scButton, #selectionNote").addClass('hidden');
            $("#closerSelectBlended").closest('p').addClass('hidden');
            $("#select-campaign").modal({
                keyboard: false,
                backdrop: 'static',
                show: true
            });
        } else {
            alert("<?=$lh->translationFor('error')?>: "+result.message+".\n\n<?=$lh->translationFor('contact_admin')?>");
        }
    });
}

function btnLogMeOut () {
    refresh_interval = 730000;
    var logMeOut = true;
    if (logoutWarn) {
        logMeOut = confirm("<?=$lh->translationFor('sure_to_logout')?>");
    }
    if (logMeOut) {
        var postData = {
            goAction: 'goLogoutUser',
            goUser: user,
            goPass: pass,
            goSIPserver: SIPserver,
            goNoDeleteSession: no_delete_sessions,
            goLogoutKickAll: LogoutKickAll,
            goServerIP: server_ip,
            goSessionName: session_name,
            goExtContext: ext_context,
            goAgentLogID: agent_log_id,
            responsetype: 'json'
        };
    
        $.ajax({
            type: 'POST',
            url: '<?=$goAPI?>/goAgent/goAPI.php',
            processData: true,
            data: postData,
            dataType: "json"
        })
        .done(function (result) {
            if (result.result == 'success') {
                is_logged_in = 0;
                alert('SUCCESS: You have been logged out of the dialer.');
            } else {
                refresh_interval = 1000;
                alert('ERROR: ' + result.message);
            }
        });
        logoutWarn = true;
        logout_stop_timeouts = 1;
    } else {
        refresh_interval = 1000;
    }
}

function btnDialHangup () {
    //console.log(live_customer_call + ' ' + toggleButton('DialHangup'));
    if (live_customer_call == 1) {
        if (toggleButton('DialHangup')) {
            //toggleButton('DialHangup', 'off');
            //AgentDispoing = 1;
            
            //Pause
            //if ($("#DispoSelectStop").is(':checked')) {
            //    //sendToAPI('PAUSE');
            //    toggleButton('ResumePause', 'resume');
            //} else {
            //    toggleButton('ResumePause', 'on');
            //}
            
            //Hangup
            //live_customer_call = 0;
            DialedCallHangup();
            //delay(sendToAPI('HANGUP'), 500);
            
            //Dispose
            //DispoSelectBox();
            //delay(sendToAPI('STATUS', 'A'), 1000);
        }
    } else {
        toggleButton('DialHangup', 'hangup', false);
        toggleButton('ResumePause', 'off');
        //live_customer_call = 1;
        //toggleStatus('LIVE');
        
        ManualDialNext('','','','','','0');
    }
}

function btnResumePause () {
    if (live_customer_call < 1) {
        var btnClass = $('#btnResumePause').children('i').attr('class');
        if (/pause$/.test(btnClass)) {
            //toggleButton('ResumePause', 'resume');
            AutoDial_Resume_Pause("VDADpause");
        } else {
            //toggleButton('ResumePause', 'pause');
            AutoDial_Resume_Pause("VDADready");
        }
    }
}

function activateLinks() {
    if (AutoDialReady > 0 || live_customer_call > 0) {
        $('#MDPhonENumbeR').val('');
        $('#MDPhonENumbeR').prop('readonly', true);
    } else {
        $('#MDPhonENumbeR').prop('readonly', false);
    }
    var phoneNumber = $('#MDPhonENumbeR').val();

    if (phoneNumber.length > 5) {
        $("a[id^='manual-dial-']").removeClass('disabled');
    } else {
        $("a[id^='manual-dial-']").addClass('disabled');
    }
}

function toggleButton (taskname, taskaction, taskenable, toupperfirst, tolowerelse) {
    if (tolowerelse) {taskname = taskname.toLowerCase();}
    if (toupperfirst) {taskname = taskname.toUpperFirst();}
    
    var actClass = '';
    var actTitle = '';
    var isEnabled = (taskenable != null) ? taskenable : true;
    var isHidden = false;
    
    if (taskaction != null && taskaction.length > 0) {
        switch (taskaction.toLowerCase()) {
            case "dial":
                actClass = "fa fa-phone";
                actTitle = "<?=$lh->translationFor('dial_next_call')?>";
                break;
            case "hangup":
                actClass = "fa fa-stop";
                actTitle = "<?=$lh->translationFor('hangup_call')?>";
                break;
            case "resume":
                actClass = "fa fa-play";
                actTitle = "<?=$lh->translationFor('resume_dialing')?>";
                break;
            case "pause":
                actClass = "fa fa-pause";
                actTitle = "<?=$lh->translationFor('pause_dialing')?>";
                break;
            case "on":
                actClass = "";
                isEnabled = true;
                break;
            case "off":
                actClass = "";
                isEnabled = false;
                break;
            case "hide":
                actClass = "";
                isHidden = true;
                break;
            default:
                actClass = "";
        }
        
        if (actClass.length > 0) {
            if (!isEnabled) {
                $("#btn"+taskname).addClass('disabled');
            } else {
                $("#btn"+taskname).removeClass('disabled');
            }
            
            $("#btn"+taskname+" i").attr('class', actClass);
            if (actTitle != '') {
                $("#btn"+taskname).attr('title', actTitle);
            }
        } else {
            if (!isEnabled) {
                $("#btn"+taskname).addClass('disabled');
            } else {
                $("#btn"+taskname).removeClass('disabled');
            }
        }
        
        if (isHidden) {
            $("#btn"+taskname).addClass('hidden');
        }
    } else {
        var returnVal = ($("#btn"+taskname).hasClass('disabled')) ? false : true;
        return returnVal;
    }
}

function toggleButtons (taskaction, taskivr, taskrequeue) {
    if (taskaction != null && taskaction.length > 0) {
        var btnIVR = 'hide';
        if (taskivr == 'ENABLED' || taskivr == 'ENABLED_PARK_ONLY') {
            var btnIVR = 'off';
        }
        var btnRequeue = (taskrequeue == 'Y') ? 'off' : 'hide';
        
        switch (taskaction.toLowerCase()) {
            case "manual":
                toggleButton('DialHangup', 'dial');
                toggleButton('ResumePause', 'hide');
                break;
            default:
                toggleButton('DialHangup', 'dial');
                toggleButton('ResumePause', 'resume');
        }
        
        //console.log("btnIVR = "+btnIVR+"; btnRequeue = "+btnRequeue);
        toggleButton('TransferCall', 'off');
        toggleButton('ParkCall', 'off');
        toggleButton('IVRParkCall', btnIVR);
        toggleButton('ReQueueCall', btnRequeue);
        
        if (btnIVR == 'hide' && btnRequeue == 'hide') {
            $("#btn_spacer").addClass('hidden');
        } else {
            $("#btn_spacer").removeClass('hidden');
        }
    }
}

function updateButtons () {
    if (is_logged_in) {
        $("#go_nav_btn").removeClass('hidden');
        $("#go_agent_login").addClass('hidden');
        $("#go_agent_logout").removeClass('hidden');
        $("#go_agent_status").removeClass('hidden');
        $("#go_agent_manualdial").removeClass('hidden');
        $("#go_agent_manualdial").prev().removeClass('hidden');
    } else {
        $("#go_nav_btn").addClass('hidden');
        $("#go_agent_login").removeClass('hidden');
        $("#go_agent_logout").addClass('hidden');
        $("#go_agent_status").addClass('hidden');
        $("#go_agent_manualdial").addClass('hidden');
        $("#go_agent_manualdial").prev().addClass('hidden');
    }
}

function toggleStatus (status) {
    var statusClass = '';
    var statusLabel = '';
    switch (status) {
        case "DEAD":
            statusClass = 'deadcall';
            statusLabel = '<?=$lh->translationFor('dead_call')?>';
            break;
        case "LIVE":
            statusClass = 'livecall';
            statusLabel = '<?=$lh->translationFor('live_call')?>';
            break;
        case "HANGUP":
            statusClass = 'callhangup';
            statusLabel = '<?=$lh->translationFor('call_hangup')?>';
            break;
        default:
            statusClass = 'nolivecall';
            statusLabel = '<?=$lh->translationFor('no_live_call')?>';
    }

    $("#livecall h3").attr({'class': statusClass, 'title': statusLabel}).html(statusLabel);
}

function CheckForConfCalls (confnum, force) {
    if (confnum.length < 1) {
        confnum = conf_exten;
    }
    
    custchannellive--;
    if ( (agentcallsstatus == '1') || (callholdstatus == '1') ) {
        campagentstatct++;
        if (campagentstatct > campagentstatctmax) {
            campagentstatct = 0;
            var campagentstdisp = 'YES';
        } else {
            var campagentstdisp = 'NO';
        }
    } else {
        var campagentstdisp = 'NO';
    }

    var postData = {
        goAction: 'goCheckConference',
        goUser: uName,
        goPass: uPass,
        goSessionName: session_name,
        goClient: "vdc",
        goConfExten: confnum,
        goAutoDialLevel: auto_dial_level,
        goCampAgentDisp: campagentstdisp,
        responsetype: 'json'
    };

    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
        if (result.result == 'success') {
            var LMAforce = force;
            var confArray = result.data.conf_output;
                UnixTime = confArray.unixtime;
                UnixTime = parseInt(UnixTime);
                UnixTimeMS = (UnixTime * 1000);
                t.setTime(UnixTimeMS);
            if ( (callholdstatus == '1') || (agentcallsstatus == '1') || (vicidial_agent_disable != 'NOT_ACTIVE') ) {
                var AGLogin = confArray.logged_in;
                var CampCalls = confArray.camp_calls;
                var DialCalls = confArray.dial_calls;
                if (AGLogin != 'N') {
                    $("#AgentStatusStatus").html(AGLogin);
                }
                if (CampCalls != 'N') {
                    $("#AgentStatusCalls").html(CampCalls);
                }
                if (DialCalls != 'N') {
                    $("#AgentStatusDials").html(DialCalls);
                }
                if ( (AGLogin == 'DEAD_VLA') && ( (vicidial_agent_disable == 'LIVE_AGENT') || (vicidial_agent_disable == 'ALL') ) ) {
                    //showDiv('AgenTDisablEBoX');
                    refresh_interval = 7300000;
                }
                if ( (AGLogin == 'DEAD_EXTERNAL') && ( (vicidial_agent_disable == 'EXTERNAL') || (vicidial_agent_disable == 'ALL') ) ) {
                    //showDiv('AgenTDisablEBoX');
                    refresh_interval = 7300000;
                }
                if ( (AGLogin == 'TIME_SYNC') && (vicidial_agent_disable == 'ALL') ) {
                    //showDiv('SysteMDisablEBoX');
                }
                if (AGLogin == 'SHIFT_LOGOUT') {
                    shift_logout_flag = 1;
                }
                if (AGLogin == 'API_LOGOUT') {
                    api_logout_flag = 1;
                    //if ( (MD_channel_look < 1) && (live_customer_call < 1) && (alt_dial_status_display < 1) )
                    //    {LogouT('API');}
                }
            }
            
            var VLAStatus = confArray.status;
            if ( (VLAStatus == 'PAUSED') && (AutoDialWaiting == 1) ) {
                if (PauseNotifyCounter > 10) {
                    alert('<?=$lh->translationFor('session_paused')?>');
                    AutoDial_Resume_Pause('VDADpause');
                    PauseNotifyCounter = 0;
                } else {
                    PauseNotifyCounter++;
                }
            } else {
                PauseNotifyCounter = 0;
            }
            
            var APIhangup = confArray.api_hangup;
            var APIstatus = confArray.api_status;
            var APIpause = confArray.api_pause;
            var APIdial = confArray.api_dial;
                APIManualDialQueue = confArray.api_manual_dial_queue;
            var CheckDEADcall = confArray.dead_call;
            var InGroupChange_array = confArray.ingroup_change.split("|");
            var InGroupChange = InGroupChange_array[0];
            var InGroupChangeBlend = InGroupChange_array[1];
            var InGroupChangeUser = InGroupChange_array[2];
            var InGroupChangeName = InGroupChange_array[3];
                update_fields = confArray.api_fields;
                update_fields_data = confArray.api_fields_data;
                api_timer_action = confArray.api_timer_action;
                api_timer_action_message = confArray.api_timer_message;
                api_timer_action_seconds = confArray.api_timer_seconds;
                api_timer_action_destination = confArray.api_timer_destination;
            var api_recording = confArray.api_recording;
                api_dtmf = confArray.api_dtmf;
            if (confArray.api_transferconf.length > 0) {
                var api_transferconf_values_array = confArray.api_transferconf.split("---");
                    api_transferconf_function = api_transferconf_values_array[0];
                    api_transferconf_group = api_transferconf_values_array[1];
                    api_transferconf_number = api_transferconf_values_array[2];
                    api_transferconf_consultative = api_transferconf_values_array[3];
                    api_transferconf_override = api_transferconf_values_array[4];
                    api_transferconf_group_alias = api_transferconf_values_array[5];
                    api_transferconf_cid_number = api_transferconf_values_array[6];
            }
                api_parkcustomer = confArray.api_park;
                
            if (api_recording=='START') {
                //ConfSendRecording('MonitorConf', session_id,'','1');
                //sendToAPI('recording', 'START');
            }
            if (api_recording=='STOP') {
                //ConfSendRecording('StopMonitorConf', session_id, recording_filename,'1');
                //sendToAPI('recording', 'STOP');
            }
            if (api_transferconf_function.length > 0) {
                if (api_transferconf_function == 'HANGUP_XFER')
                    {XFerCallHangup();}
                if (api_transferconf_function == 'HANGUP_BOTH')
                    {BothCallHangup();}
                if (api_transferconf_function == 'LEAVE_VM')
                    {mainxfer_send_redirect('XfeRVMAIL',lastcustchannel,lastcustserverip);}
                if (api_transferconf_function == 'LEAVE_3WAY_CALL')
                    {leave_3way_call('FIRST');}
                if (api_transferconf_function == 'BLIND_TRANSFER') {
                    $(".formXFER input[name='xfernumber']").val(api_transferconf_number);
                    mainxfer_send_redirect('XfeRBLIND',lastcustchannel,lastcustserverip);
                }
                if (external_transferconf_count < 1) {
                    if (api_transferconf_function == 'LOCAL_CLOSER') {
                        API_selected_xfergroup = api_transferconf_group;
                        $(".formXFER input[name='xfernumber']").val(api_transferconf_number);
                        //mainxfer_send_redirect('XfeRLOCAL',lastcustchannel,lastcustserverip);
                    }
                    if (api_transferconf_function == 'DIAL_WITH_CUSTOMER') {
                        if (api_transferconf_consultative=='YES')
                            {$(".formXFER input[name='consultativexfer']").is(':checked') = true;}
                        if (api_transferconf_consultative=='NO')
                            {$(".formXFER input[name='consultativexfer']").is(':checked') = false;}
                        if (api_transferconf_override=='YES')
                            {$(".formXFER input[name='xferoverride']").is(':checked') = true;}
                        API_selected_xfergroup = api_transferconf_group;
                        $(".formXFER input[name='xfernumber']").val(api_transferconf_number);
                        active_group_alias = api_transferconf_group_alias;
                        cid_choice = api_transferconf_cid_number;
                        SendManualDial('YES');
                    }
                    if (api_transferconf_function == 'PARK_CUSTOMER_DIAL') {
                        if (api_transferconf_consultative == 'YES')
                            {$(".formXFER input[name='consultativexfer']").is(':checked') = true;}
                        if (api_transferconf_consultative == 'NO')
                            {$(".formXFER input[name='consultativexfer']").is(':checked') = false;}
                        if (api_transferconf_override == 'YES')
                            {$(".formXFER input[name='xferoverride']").is(':checked') = true;}
                        API_selected_xfergroup = api_transferconf_group;
                        $(".formXFER input[name='xfernumber']").val(api_transferconf_number);
                        active_group_alias = api_transferconf_group_alias;
                        cid_choice = api_transferconf_cid_number;
                        xfer_park_dial();
                    }
                    external_transferconf_count = 3;
                }
                Clear_API_Field('external_transferconf');
            }
            if (api_parkcustomer == 'PARK_CUSTOMER')
                {mainxfer_send_redirect('ParK', lastcustchannel, lastcustserverip);}
            if (api_parkcustomer == 'GRAB_CUSTOMER')
                {mainxfer_send_redirect('FROMParK', lastcustchannel, lastcustserverip);}
            if (api_parkcustomer == 'PARK_IVR_CUSTOMER')
                {mainxfer_send_redirect('ParKivr', lastcustchannel, lastcustserverip);}
            if (api_parkcustomer == 'GRAB_IVR_CUSTOMER')
                {mainxfer_send_redirect('FROMParKivr', lastcustchannel, lastcustserverip);}
            if (api_dtmf.length > 0) {
                var REGdtmfPOUND = new RegExp("P","g");
                var REGdtmfSTAR = new RegExp("S","g");
                var REGdtmfQUIET = new RegExp("Q","g");
                api_dtmf = api_dtmf.replace(REGdtmfPOUND, '#');
                api_dtmf = api_dtmf.replace(REGdtmfSTAR, '*');
                api_dtmf = api_dtmf.replace(REGdtmfQUIET, ',');
                $("#conf_dtmf").val(api_dtmf);
                //SendConfDTMF(session_id);
            }
    
            if (api_timer_action.length > 2) {
                timer_action = api_timer_action;
                timer_action_message = api_timer_action_message;
                timer_action_seconds = api_timer_action_seconds;
                timer_action_destination = api_timer_action_destination;
                //alert("TIMER_API:" + timer_action + '|' + timer_action_message + '|' + timer_action_seconds + '|' + timer_action_destination + '|');
            }
            
            //API catcher for hanging up calls
            if (APIhangup == 1 && (live_customer_call == 1 || MD_channel_look == 1)) {
                WaitingForNextStep = 0;
                custchannellive = 0;
                
                DialedCallHangup();
            }
            
            //API catcher for Call Dispositions
            if ( (APIstatus.length < 1000) && (APIstatus.length > 0) && (AgentDispoing > 1) && (APIstatus != '::::::::::') ) {
                var regCBmatch = new RegExp('!',"g");
                if (APIstatus.match(regCBmatch)) {
                    var APIcbSTATUS_array = APIstatus.split("!");
                    var APIcbSTATUS =	APIcbSTATUS_array[0];
                    var APIcbDATETIME =	APIcbSTATUS_array[1];
                    var APIcbTYPE =		APIcbSTATUS_array[2];
                    var APIcbCOMMENTS =	APIcbSTATUS_array[3];
                    var APIqmCScode =	APIcbSTATUS_array[4];
    
                    if ( (APIcbDATETIME.length > 10) && (APIcbTYPE.length > 5) ) {
                        CallBackDateTime =   APIcbDATETIME;
                        CallBackRecipient =  APIcbTYPE;
                        CallBackLeadStatus = APIcbSTATUS;
                        CallBackComments =   APIcbCOMMENTS;
                        $("#DispoSelection").val('CBHOLD');
                    } else {
                        $("#DispoSelection").val(APIcbSTATUS);
                    }
                    
                    if (APIqmCScode.length > 0) {
                        DispoQMcsCODE = APIqmCScode;
                    }
    
                    DispoSelectSubmit();
                } else {
                    $("#DispoSelection").val(APIstatus);
                    DispoSelectSubmit();
                }
            }
    
            if (APIpause.length > 4) {
                var APIpause_array = APIpause.split("!");
                if (APIpause_ID != APIpause_array[1]) {
                    APIpause_ID = APIpause_array[1];
                    if (APIpause_array[0] == 'PAUSE') {
                        if (live_customer_call == 1) {
                            // set to pause on next dispo
                            $("#DispoSelectStop").prop('checked', true);
                            DispoSelectStop = true;
                            //console.log("Setting agent status to PAUSE on next dispo");
                        } else {
                            if (AutoDialReady == 1) {
                                if (auto_dial_level != '0') {
                                    AutoDialWaiting = 0;
                                    AutoDial_Resume_Pause("VDADpause");
                                }
                                pause_calling = 1;
                            }
                        }
                    }
                    
                    if ( (APIpause_array[0] == 'RESUME') && (AutoDialReady < 1) && (auto_dial_level > 0) ) {
                        AutoDialWaiting = 1;
                        AutoDial_Resume_Pause("VDADready");
                        //console.log("Setting agent status to RESUME");
                    }
                }
            }
            
            //API catcher for Manual Dial
            if (APIdial.length > 9 && AllowManualQueueCalls == '0') {
                APIManualDialQueue++;
            }
            if (APIManualDialQueue != APIManualDialQueue_last) {
                APIManualDialQueue_last = APIManualDialQueue;
                //console.info('Manual Queue: '+APIManualDialQueue);
            }
            
            if (APIdial.length > 9 && WaitingForNextStep == '0' && AllowManualQueueCalls == '1' && check_r > 2) {
                var APIdial_array_detail = APIdial.split("!");
                if (APIdial_ID != APIdial_array_detail[6]) {
                    APIdial_ID = APIdial_array_detail[6];
                    $('#inputphone_code').val(APIdial_array_detail[1]);
                    $('#cust-phone-number').val(APIdial_array_detail[0]);
                    $('#inputvendor_lead_code').val(APIdial_array_detail[5]);
                    prefix_choice = APIdial_array_detail[7];
                    active_group_alias = APIdial_array_detail[8];
                    cid_choice = APIdial_array_detail[9];
                    vtiger_callback_id = APIdial_array_detail[10];
                    $("input[name='lead_id']").val(APIdial_array_detail[11]);
                    $("input[name='uniqueid']").val(APIdial_array_detail[12]);
                    
                    if (active_group_alias.length > 1)
                        {var sending_group_alias = 1;}
                    
                    //console.log('Dialing '+APIdial_array_detail[0]+'...');
                    if (APIdial_array_detail[2] == 'YES')  // lookup lead in system
                        {$("#LeadLookUP").prop('checked', true);}
                    else
                        {$("#LeadLookUP").prop('checked', false);}
                    if (APIdial_array_detail[4] == 'YES') { // focus on vicidial agent screen
                        window.focus();
                        alert("<?=$lh->translationFor('placing_call_to')?>:" + APIdial_array_detail[1] + " " + APIdial_array_detail[0]);
                    }
                    if (APIdial_array_detail[3] == 'YES')  // call preview
                        {NewManualDialCall('PREVIEW');}
                    else
                        {NewManualDialCall('NOW');}
                }
            }

            if ( (CheckDEADcall > 0) && (live_customer_call == 1) ) {
                if (CheckDEADcallON < 1) {
                    toggleStatus('DEAD');
                    toggleButton('ParkCall', 'off');
                    toggleButton('TransferCall', 'off');
                    CheckDEADcallON = 1;

                    if ( (xfer_in_call > 0) && (customer_3way_hangup_logging=='ENABLED') ) {
                        customer_3way_hangup_counter_trigger = 1;
                        customer_3way_hangup_counter = 1;
                    }
                }
            }
            
            if (InGroupChange > 0) {
                var external_blended = InGroupChangeBlend;
                var external_igb_set_user = InGroupChangeUser;
                external_igb_set_name = InGroupChangeName;
                manager_ingroups_set = 1;
    
                if ( (external_blended == '1') && (dial_method != 'INBOUND_MAN') )
                    {closer_blended = '1';}
    
                if (external_blended == '0')
                    {closer_blended = '0';}
            }
            
            var live_conf_calls = result.data.channels_list;
            var conf_chan_array = result.data.count_echo.split(" ~");
            if ( (conf_channels_xtra_display == 1) || (conf_channels_xtra_display == 0) ) {
                if (live_conf_calls > 0) {
                    loop_ct = 0;
                    var temp_blind_monitors = 0;
                    var ARY_ct = 0;
                    var LMAalter = 0;
                    var LMAcontent_change = 0;
                    var LMAcontent_match = 0;
                    agentphonelive = 0;
                    var conv_start = -1;
                    var live_conf_HTML = '<font face="Arial,Helvetica"><b><?=$lh->translationFor('live_calls_in_your_session')?>:</b></font><br /><table width="340px"><tr><td><font class="log_title">#</font></td><td><font class="log_title"><?=$lh->translationFor('remote_channel')?></font></td><td><font class="log_title"><?=$lh->translationFor('hangup')?></font></td></tr>';
                    if ( (LMAcount > live_conf_calls)  || (LMAcount < live_conf_calls) || (LMAforce > 0)) {
                        LMAe[0] = '';
                        LMAe[1] = '';
                        LMAe[2] = '';
                        LMAe[3] = '';
                        LMAe[4] = '';
                        LMAe[5] = ''; 
                        LMAcount = 0;
                        LMAcontent_change++;
                    }
    
                    while (loop_ct < live_conf_calls) {
                        loop_ct++;
                        loop_s = loop_ct.toString();
                        if (loop_s.match(/1$|3$|5$|7$|9$/)) 
                            {var row_color = '#DDDDFF';}
                        else
                            {var row_color = '#CCCCFF';}
                        var conv_ct = (loop_ct + conv_start);
                        var channelfieldA = conf_chan_array[conv_ct];
                        var regXFcred = new RegExp(flag_string,"g");
                        var regRNnolink = new RegExp('Local/5' + confnum,"g")
                        if ( (channelfieldA.match(regXFcred)) && (flag_channels>0) ) {
                            var chan_name_color = 'log_text_red';
                        } else {
                            var chan_name_color = 'log_text';
                        }
                        if ( (HideMonitorSessions==1) && (channelfieldA.match(/ASTblind/)) ) {
                            var hide_channel=1;
                            blind_monitoring_now++;
                            temp_blind_monitors++;
                            if (blind_monitoring_now == 1)
                                {blind_monitoring_now_trigger = 1;}
                        } else {
                            if (channelfieldA.match(regRNnolink)) {
                                // do not show hangup or volume control links for recording channels
                                live_conf_HTML = live_conf_HTML + '<tr bgcolor="' + row_color + '"><td><font class="log_text">' + loop_ct + '</font></td><td><font class="' + chan_name_color + '">' + channelfieldA + '</font></td><td><font class="log_text"><?=$lh->translationFor('recording')?></font></td></tr>';
                            } else {
                                if (volumecontrol_active!=1) {
                                    live_conf_HTML = live_conf_HTML + '<tr bgcolor="' + row_color + '"><td><font class="log_text">' + loop_ct + '</font></td><td><font class="' + chan_name_color + '">' + channelfieldA + '</font></td><td><font class="log_text"><a href="#" onclick="livehangup_send_hangup(\"' + channelfieldA + '\");return false;"><?=$lh->translationFor('hangup')?></a></font></td></tr>';
                                } else {
                                    live_conf_HTML = live_conf_HTML + '<tr bgcolor="' + row_color + '"><td><font class="log_text">' + loop_ct + '</font></td><td><font class="' + chan_name_color + '">' + channelfieldA + '</font></td><td><font class="log_text"><a href="#" onclick="livehangup_send_hangup(\"' + channelfieldA + '\");return false;"><?=$lh->translationFor('hangup')?></a></font></td><td><a href="#" onclick="volume_control(\"UP\",\"' + channelfieldA + '\",\"\");return false;"><img src="./images/vdc_volume_up.gif" border="0" /></a> &nbsp; <a href="#" onclick="volume_control(\"DOWN\",\"' + channelfieldA + '\",\"\");return false;"><img src="./images/vdc_volume_down.gif" border="0" /></a> &nbsp; &nbsp; &nbsp; <a href="#" onclick="volume_control(\"MUTING\",\"' + channelfieldA + '\",\"\");return false;"><img src="./images/vdc_volume_MUTE.gif" border="0" /></a> &nbsp; <a href="#" onclick="volume_control(\"UNMUTE\",\"' + channelfieldA + '\",\"\");return false;"><img src="./images/vdc_volume_UNMUTE.gif" border="0" /></a></td></tr>';
                                }
                            }
                        }
                        //var debugspan = document.getElementById("debugbottomspan").innerHTML;
    
                        if (channelfieldA == lastcustchannel) {custchannellive++;}
                        else {
                            if(customerparked == 1)
                                {custchannellive++;}
                            // allow for no customer hungup errors if call from another server
                            if(server_ip == lastcustserverip)
                                {var nothing='';}
                            else
                                {custchannellive++;}
                        }
    
                        if (volumecontrol_active > 0) {
                            if ( (protocol != 'EXTERNAL') && (protocol != 'Local') ) {
                                var regAGNTchan = new RegExp(protocol + '/' + extension,"g");
                                if  ( (channelfieldA.match(regAGNTchan)) && (agentchannel != channelfieldA) ) {
                                    agentchannel = channelfieldA;
    
                                    //$("#AgentMuteSpan").html("<a href='#CHAN-" + agentchannel + "' onclick='volume_control(\"MUTING\",\"" + agentchannel + "\",\"AgenT\");return false;'><img src='./images/vdc_volume_MUTE.gif' border='0' /></a>");
                                }
                            } else {
                                if (agentchannel.length < 3) {
                                    agentchannel = channelfieldA;
    
                                    //$("#AgentMuteSpan").html("<a href='#CHAN-" + agentchannel + "' onclick='volume_control(\"MUTING\",\"" + agentchannel + "\",\"AgenT\");return false;'><img src='./images/vdc_volume_MUTE.gif' border='0' /></a>");
                                }
                            }
                            //document.getElementById("agentchannelSPAN").innerHTML = agentchannel;
                        }
    
                        //document.getElementById("debugbottomspan").innerHTML = debugspan + '<br />' + channelfieldA + '|' + lastcustchannel + '|' + custchannellive + '|' + LMAcontent_change + '|' + LMAalter;
    
                        if (!LMAe[ARY_ct]) {
                            LMAe[ARY_ct] = channelfieldA;
                            LMAcontent_change++;
                            LMAalter++;
                        } else {
                            if (LMAe[ARY_ct].length < 1) {
                                LMAe[ARY_ct] = channelfieldA;
                                LMAcontent_change++;
                                LMAalter++;
                            } else {
                                if (LMAe[ARY_ct] == channelfieldA) {LMAcontent_match++;}
                                else {
                                    LMAcontent_change++;
                                    LMAe[ARY_ct] = channelfieldA;
                                }
                            }
                        }
                        if (LMAalter > 0) {LMAcount++;}
                            
                        if (agentchannel == channelfieldA) {agentphonelive++;}
    
                        ARY_ct++;
                    }
                    //var debug_LMA = LMAcontent_match+"|"+LMAcontent_change+"|"+LMAcount+"|"+live_conf_calls+"|"+LMAe[0]+LMAe[1]+LMAe[2]+LMAe[3]+LMAe[4]+LMAe[5];
                    //$("#confdebug").html(debug_LMA + "<br />");
    
                    if (agentphonelive < 1) {agentchannel = '';}
    
                    live_conf_HTML = live_conf_HTML + "</table>";
    
                    if (LMAcontent_change > 0) {
                        if (conf_channels_xtra_display == 1)
                            {$("#outboundcallsspan").html(live_conf_HTML);}
                    }
                    nochannelinsession = 0;
                    if (temp_blind_monitors < 1) {
                        no_blind_monitors++;
                        if (no_blind_monitors > 2)
                            {blind_monitoring_now = 0;}
                    }
                } else {
                    LMAe[0]='';
                    LMAe[1]='';
                    LMAe[2]='';
                    LMAe[3]='';
                    LMAe[4]='';
                    LMAe[5]='';
                    LMAcount=0;
                    if (conf_channels_xtra_display == 1) {
                        if ($("#outboundcallsspan").html().length > 2) {
                            $("#outboundcallsspan").html('');
                        }
                    }
                    custchannellive = -99;
                    nochannelinsession++;
    
                    no_blind_monitors++;
                    if (no_blind_monitors > 2)
                        {blind_monitoring_now = 0;}
                }
            }
            
            //$('#debug').html('<b>DEBUG:</b> ' + result);
        } else {
            alert(result.message);
        }
    });
}

function CheckForIncoming () {
    all_record = 'NO';
    all_record_count = 0;

    var postData = {
        goAction: 'goVDADCheckIncoming',
        goServerIP: server_ip,
        goSessionName: session_name,
        goUser: uName,
        goPass: uPass,
        goCampaign: campaign,
        goAgentLogID: agent_log_id,
        responsetype: 'json'
    };

    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
        if (live_customer_call == 1) {
            //console.log(result);
            forTestingOnly = '';
        }

        var this_VDIC_data = result.data;
        
        if (this_VDIC_data.has_call == '1') {
            AutoDialWaiting = 0;
            QUEUEpadding = 0;
            
            VDIC_web_form_address = web_form_address
            VDIC_web_form_address_two = web_form_address_two
            var VDIC_fronter = '';
            
            if (this_VDIC_data.group_web.length > 5)
                {VDIC_web_form_address = this_VDIC_data.group_web;}
            var VDCL_group_name                         = this_VDIC_data.group_name;
            var VDCL_group_color                        = this_VDIC_data.group_color;
            var VDCL_fronter_display                    = this_VDIC_data.fronter_display;
                VDCL_group_id                           = this_VDIC_data.channel_group;
                Call_Script_id                          = this_VDIC_data.ingroup_script;
                Call_Auto_Launch                        = this_VDIC_data.get_call_launch;
                Call_XC_a_DTMF                          = this_VDIC_data.xferconf_a_dtmf;
                Call_XC_a_Number                        = this_VDIC_data.xferconf_a_number;
                Call_XC_b_DTMF                          = this_VDIC_data.xferconf_b_dtmf;
                Call_XC_b_Number                        = this_VDIC_data.xferconf_b_number;
            if ( (this_VDIC_data.default_xfer_group.length > 1) && (this_VDIC_data.default_xfer_group != '---NONE---') )
                {LIVE_default_xfer_group = this_VDIC_data.default_xfer_group;}
            else
                {LIVE_default_xfer_group = default_xfer_group;}

            if ( (this_VDIC_data.ingroup_recording_override.length > 1) && (this_VDIC_data.ingroup_recording_override != 'DISABLED') )
                {LIVE_campaign_recording = this_VDIC_data.ingroup_recording_override;}
            else
                {LIVE_campaign_recording = campaign_recording;}

            if ( (this_VDIC_data.ingroup_rec_filename.length > 1) && (this_VDIC_data.ingroup_rec_filename != 'NONE') )
                {LIVE_campaign_rec_filename = this_VDIC_data.ingroup_rec_filename;}
            else
                {LIVE_campaign_rec_filename = campaign_rec_filename;}

            if ( (this_VDIC_data.default_group_alias.length > 1) && (this_VDIC_data.default_group_alias != 'NONE') )
                {LIVE_default_group_alias = this_VDIC_data.default_group_alias;}
            else
                {LIVE_default_group_alias = default_group_alias;}

            if ( (this_VDIC_data.caller_id_number.length > 1) && (this_VDIC_data.caller_id_number != 'NONE') )
                {LIVE_caller_id_number = this_VDIC_data.caller_id_number;}
            else
                {LIVE_caller_id_number = default_group_alias_cid;}

            if (this_VDIC_data.group_web_vars.length > 0)
                {LIVE_web_vars = this_VDIC_data.group_web_vars;}
            else
                {LIVE_web_vars = default_web_vars;}

            if (this_VDIC_data.group_web_two.length > 5)
                {VDIC_web_form_address_two = this_VDIC_data.group_web_two;}

            var call_timer_action                       = this_VDIC_data.timer_action;

            if ( (call_timer_action == 'NONE') || (call_timer_action.length < 2) ) {
                timer_action = campaign_timer_action;
                timer_action_message = campaign_timer_action_message;
                timer_action_seconds = campaign_timer_action_seconds;
                timer_action_destination = campaign_timer_action_destination;
            } else {
                var call_timer_action_message           = this_VDIC_data.timer_action_message;
                var call_timer_action_seconds           = this_VDIC_data.timer_action_seconds;
                var call_timer_action_destination       = this_VDIC_data.timer_action_destination;
                timer_action = call_timer_action;
                timer_action_message = call_timer_action_message;
                timer_action_seconds = call_timer_action_seconds;
                timer_action_destination = call_timer_action_destination;
            }

            Call_XC_c_Number                            = this_VDIC_data.xferconf_c_number;
            Call_XC_d_Number                            = this_VDIC_data.xferconf_d_number;
            Call_XC_e_Number                            = this_VDIC_data.xferconf_e_number;
            uniqueid_status_display                     = this_VDIC_data.uniqueid_status_display;
            uniqueid_status_prefix                      = this_VDIC_data.uniqueid_status_prefix;
            did_id                                      = this_VDIC_data.did_id;
            did_extension                               = this_VDIC_data.did_extension;
            did_pattern                                 = this_VDIC_data.did_pattern;
            did_description                             = this_VDIC_data.did_description;
            closecallid                                 = this_VDIC_data.closecallid;
            xfercallid                                  = this_VDIC_data.xfercallid;

            if ( (this_VDIC_data.fronter_full_name.length > 1) && (VDCL_fronter_display == 'Y') )
                {VDIC_fronter = "  Fronter: " + this_VDIC_data.fronter_full_name + " - " + this_VDIC_data.tsr;}
            
            $(".formMain input[name='lead_id']").val(this_VDIC_data.lead_id);
            $(".formMain input[name='uniqueid']").val(this_VDIC_data.uniqueid);
            CIDcheck                                    = this_VDIC_data.callerid;
            CallCID                                     = this_VDIC_data.callerid;
            LastCallCID                                 = this_VDIC_data.callerid;
            $("#callchannel").html(this_VDIC_data.channel);
            lastcustchannel                             = this_VDIC_data.channel;
            $("#callserverip").val(this_VDIC_data.call_server_ip);
            lastcustserverip                            = this_VDIC_data.call_server_ip;

            toggleStatus('LIVE');

            $(".formMain input[name='seconds']").val(0);
            $("#SecondsDISP").html('0');

            if (uniqueid_status_display=='ENABLED')
                {custom_call_id = " Call ID " + this_VDIC_data.uniqueid;}
            if (uniqueid_status_display=='ENABLED_PREFIX')
                {custom_call_id = " Call ID " + uniqueid_status_prefix + "" + this_VDIC_data.uniqueid;}
            if (uniqueid_status_display=='ENABLED_PRESERVE')
                {custom_call_id = " Call ID " + this_VDIC_data.custom_call_id;}

            live_customer_call = 1;
            live_call_seconds = 0;
            
            activateLinks();

            // INSERT VICIDIAL_LOG ENTRY FOR THIS CALL PROCESS
            // DialLog("start");

            custchannellive = 1;

            LastCID                                     = this_VDIC_data.MqueryCID;
            LeadPrevDispo                               = this_VDIC_data.dispo;
            fronter                                     = this_VDIC_data.tsr;
            $(".formMain input[name='vendor_lead_code']").val(this_VDIC_data.vendor_id);
            $(".formMain input[name='list_id']").val(this_VDIC_data.list_id);
            $(".formMain input[name='gmt_offset_now']").val(this_VDIC_data.gmt_offset_now);
            $(".formMain input[name='phone_code']").val(this_VDIC_data.phone_code);
            $(".formMain input[name='phone_number']").val(this_VDIC_data.phone_number);
            $(".formMain input[name='title']").val(this_VDIC_data.title);
            $(".formMain input[name='first_name']").val(this_VDIC_data.first_name);
            $(".formMain input[name='middle_initial']").val(this_VDIC_data.middle_initial);
            $(".formMain input[name='last_name']").val(this_VDIC_data.last_name);
            $(".formMain input[name='address1']").val(this_VDIC_data.address1);
            $(".formMain input[name='address2']").val(this_VDIC_data.address2);
            $(".formMain input[name='address3']").val(this_VDIC_data.address3);
            $(".formMain input[name='city']").val(this_VDIC_data.city);
            $(".formMain input[name='state']").val(this_VDIC_data.state);
            $(".formMain input[name='province']").val(this_VDIC_data.province);
            $(".formMain input[name='postal_code']").val(this_VDIC_data.postal_code);
            $(".formMain input[name='country_code']").val(this_VDIC_data.country_code);
            $(".formMain input[name='gender']").val(this_VDIC_data.gender);
            $(".formMain input[name='date_of_birth']").val(this_VDIC_data.date_of_birth);
            $(".formMain input[name='alt_phone']").val(this_VDIC_data.alt_phone);
            $(".formMain input[name='email']").val(this_VDIC_data.email);
            $(".formMain input[name='security_phrase']").val(this_VDIC_data.security);
            var REGcommentsNL = new RegExp("!N","g");
            var thisComments = this_VDIC_data.comments.replace(REGcommentsNL, "\n");
            $(".formMain input[name='comments']").val(thisComments);
            $(".formMain input[name='called_count']").val(this_VDIC_data.called_count);
            CBentry_time                                = this_VDIC_data.CBentry_time;
            CBcallback_time                             = this_VDIC_data.CBcallback_time;
            CBuser                                      = this_VDIC_data.CBuser;
            CBcomments                                  = this_VDIC_data.CBcomments;
            dialed_number                               = this_VDIC_data.dialed_number;
            dialed_label                                = this_VDIC_data.dialed_label;
            source_id                                   = this_VDIC_data.source_id;
            EAphone_code                                = this_VDIC_data.alt_phone_code;
            EAphone_number                              = this_VDIC_data.alt_phone_number;
            EAalt_phone_notes                           = this_VDIC_data.alt_phone_note;
            EAalt_phone_active                          = this_VDIC_data.alt_phone_active;
            EAalt_phone_count                           = this_VDIC_data.alt_phone_count;
            $(".formMain input[name='rank']").val(this_VDIC_data.rank);
            $(".formMain input[name='owner']").val(this_VDIC_data.owner);
            script_recording_delay                      = this_VDIC_data.script_recording_delay;
            $(".formMain input[name='entry_list_id']").val(this_VDIC_data.entry_list_id);
            custom_field_names                          = this_VDIC_data.custom_field_names;
            custom_field_values                         = this_VDIC_data.custom_field_values;
            custom_field_types                          = this_VDIC_data.custom_field_types;
            //Added By Poundteam for Audited Comments (Manual Dial Section Only)
            //if (qc_enabled > 0)
            //{
            //    $(".formMain input[name='ViewCommentButton']").val(check_VDIC_array[53]);
            //    $(".formMain input[name='audit_comments_button']").val(check_VDIC_array[53]);
            //    var REGACcomments = new RegExp("!N","g");
            //    check_VDIC_array[54] = check_VDIC_array[54].replace(REGACcomments, "\n");
            //    $(".formMain input[name='audit_comments']").val(check_VDIC_array[54]);
            //}
            //END section Added By Poundteam for Audited Comments
            // Add here for AutoDial (VDADcheckINCOMING in vdc_db_query)

            //if (hide_gender > 0)
            //{
            //    $(".formMain input[name='gender_list']").val(check_VDIC_array[25]);
            //} else {
            //    var gIndex = 0;
            //    if ($(".formMain input[name='gender']").val() == 'M') {var gIndex = 1;}
            //    if ($(".formMain input[name='gender']").val() == 'F') {var gIndex = 2;}
            //    document.getElementById("gender_list").selectedIndex = gIndex;
            //}

            lead_dial_number = $(".formMain input[name='phone_number']").val();
            dispnum = $(".formMain input[name='phone_number']").val();
            var status_display_number = phone_number_format(dispnum);
            var callnum = dialed_number;
            var dial_display_number = phone_number_format(callnum);
            
            //$("#cust-name").html(this_VDIC_data.first_name+" "+this_VDIC_data.last_name);
            //$("#cust-phone").html(phone_number_format(dispnum));

            var status_display_content = '';
            if (status_display_CALLID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('uid')?>: " + LastCID;}
            if (status_display_LEADID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('lead_id')?>: " + $("#formMain input[name='lead_id'").val();}
            if (status_display_LISTID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('list_id')?>: " + $("#formMain input[name='list_id'").val();}

            $("#MainStatusSpan").html("<b><?=$lh->translationFor('incoming_call')?>:</b> " + dial_display_number + " " + custom_call_id + " " + status_display_content + "<br>" + VDIC_fronter);

            //if (CBentry_time.length > 2)
            //{
            //    $("#CustInfoSpan").html(" <b> PREVIOUS CALLBACK </b>");
            //    $("#CustInfoSpan").css('background', CustCB_bgcolor);
            //    $("#CBcommentsBoxA").html("<b>Last Call: </b>" + CBentry_time);
            //    $("#CBcommentsBoxB").html("<b>CallBack: </b>" + CBcallback_time);
            //    $("#CBcommentsBoxC").html("<b>Agent: </b>" + CBuser);
            //    $("#CBcommentsBoxD").html("<b>Comments: </b><br />" + CBcomments);
            //    //showDiv('CBcommentsBox');
            //}
            //if (dialed_label == 'ALT')
            //    {$("#CustInfoSpan").html(" <b> ALT DIAL NUMBER: ALT </b>");}
            //if (dialed_label == 'ADDR3')
            //    {$("#CustInfoSpan").html(" <b> ALT DIAL NUMBER: ADDRESS3 </b>");}
            //var REGalt_dial = new RegExp("X","g");
            //if (dialed_label.match(REGalt_dial))
            //{
            //    $("#CustInfoSpan").html(" <b> ALT DIAL NUMBER: " + dialed_label + "</b>");
            //    $("#EAcommentsBoxA").html("<b>Phone Code and Number: </b>" + EAphone_code + " " + EAphone_number);
            //
            //    var EAactive_link = '';
            //    if (EAalt_phone_active == 'Y') 
            //        {EAactive_link = "<a href=\"#\" onclick=\"alt_phone_change('" + EAphone_number + "','" + EAalt_phone_count + "','" + $("#formMain input[name='lead_id']").val() + "','N');\">Change this phone number to INACTIVE</a>";}
            //    else
            //        {EAactive_link = "<a href=\"#\" onclick=\"alt_phone_change('" + EAphone_number + "','" + EAalt_phone_count + "','" + $("#formMain input[name='lead_id']").val() + "','Y');\">Change this phone number to ACTIVE</a>";}
            //
            //    $("#EAcommentsBoxB").html("<b>Active: </b>" + EAalt_phone_active + "<br />" + EAactive_link);
            //    $("#EAcommentsBoxC").html("<b>Alt Count: </b>" + EAalt_phone_count);
            //    $("#EAcommentsBoxD").html("<b>Notes: </b>" + EAalt_phone_notes);
            //    //showDiv('EAcommentsBox');
            //}

            if (this_VDIC_data.group_name.length > 0) {
                inOUT = 'IN';
                if (this_VDIC_data.group_color.length > 2) {
                    $("#MainStatusSpan").css('background', this_VDIC_data.group_color);
                }
                dispnum = $("#cust-phone-number").val();
                var status_display_number = phone_number_format(dispnum);
                var callnum = dialed_number;
                var dial_display_number = phone_number_format(callnum);

                var status_display_content = '';
                if (status_display_CALLID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('uid')?>: " + CIDcheck;}
                if (status_display_LEADID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('lead_id')?>: " + $("#formMain input[name='lead_id']").val();}
                if (status_display_LISTID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('list_id')?>: " + $("#formMain input[name='list_id']").val();}

                $("#MainStatusSpan").html("<b><?=$lh->translationFor('incoming_call')?>:</b> " + dial_display_number + " " + custom_call_id + " <?=$lh->translationFor('group')?>- " + this_VDIC_data.group_name + " &nbsp; " + VDIC_fronter + " " + status_display_content); 
            }

            toggleButton('DialHangup','hangup');
            
            //document.getElementById("ParkControl").innerHTML ="<a href=\"#\" onclick=\"mainxfer_send_redirect('ParK','" + lastcustchannel + "','" + lastcustserverip + "');disableButton('XFER');return false;\"><img src=\"./images/callpark.png\" border=\"0\" title=\"Park Call\" alt=\"Park Call\" /></a>";
            //if ( (ivr_park_call=='ENABLED') || (ivr_park_call=='ENABLED_PARK_ONLY') )
            //{
            //    document.getElementById("ivrParkControl").innerHTML ="<a href=\"#\" onclick=\"mainxfer_send_redirect('ParKivr','" + lastcustchannel + "','" + lastcustserverip + "');return false;\"><img src=\"./images/ivrcallpark.png\" style=\"padding-bottom:3px;\" border=\"0\" title=\"IVR Park Call\" alt=\"IVR Park Call\" /></a>";
            //}
            //
            //document.getElementById("HangupControl").innerHTML = "<a href=\"#\" onclick=\"dialedcall_send_hangup();\"><img src=\"./images/hangup.png\" border=\"0\" title=\"Hangup Customer\" alt=\"Hangup Customer\" /></a>";
            //
            //document.getElementById("XferControl").innerHTML = "<a href=\"#\" onclick=\"ShoWTransferMain('ON');disableButton('PARK');\"><img src=\"./images/transfer.png\" border=\"0\" title=\"Transfer - Conference\" alt=\"Transfer - Conference\" /></a>";
            //
            //document.getElementById("LocalCloser").innerHTML = "<a href=\"#\" onclick=\"mainxfer_send_redirect('XfeRLOCAL','" + lastcustchannel + "','" + lastcustserverip + "');return false;\"><img src=\"./images/vdc_XB_localcloser.gif\" border=\"0\" alt=\"LOCAL CLOSER\" style=\"vertical-align:middle\" /></a>";
            //
            //document.getElementById("DialBlindTransfer").innerHTML = "<input type=\"button\" onclick=\"mainxfer_send_redirect('XfeRBLIND','" + lastcustchannel + "','" + lastcustserverip + "');return false;\" value=\" BLIND TRANSFER \" style=\"font-size:10px;width:150px;vertical-align:middle;\" />";
            //
            //document.getElementById("DialBlindVMail").innerHTML = "<a href=\"#\" onclick=\"mainxfer_send_redirect('XfeRVMAIL','" + lastcustchannel + "','" + lastcustserverip + "');return false;\"><img src=\"./images/vdc_XB_ammessage.gif\" border=\"0\" alt=\"Blind Transfer VMail Message\" style=\"vertical-align:middle\" /></a>";

            if ( (quick_transfer_button == 'IN_GROUP') || (quick_transfer_button == 'LOCKED_IN_GROUP') ) {
                if (quick_transfer_button_locked > 0)
                    {quick_transfer_button_orig = default_xfer_group;}

                //$("#QuickXfer").html("<a href=\"#\" onclick=\"mainxfer_send_redirect('XfeRLOCAL','" + lastcustchannel + "','" + lastcustserverip + "','','','" + quick_transfer_button_locked + "');return false;\"><img src=\"./images/quicktransfer.png\" style=\"padding-bottom:3px;\" border=\"0\" title=\"Quick Transfer\" alt=\"QUICK TRANSFER\" /></a>");
            }
            if (prepopulate_transfer_preset_enabled > 0) {
                if ( (prepopulate_transfer_preset == 'PRESET_1') || (prepopulate_transfer_preset == 'LOCKED_PRESET_1') )
                    {$("#xfernumber").val(Call_XC_a_Number);   $("#xfername").val('D1');}
                if ( (prepopulate_transfer_preset == 'PRESET_2') || (prepopulate_transfer_preset == 'LOCKED_PRESET_2') )
                    {$("#xfernumber").val(Call_XC_b_Number);   $("#xfername").val('D2');}
                if ( (prepopulate_transfer_preset == 'PRESET_3') || (prepopulate_transfer_preset == 'LOCKED_PRESET_3') )
                    {$("#xfernumber").val(Call_XC_c_Number);   $("#xfername").val('D3');}
                if ( (prepopulate_transfer_preset == 'PRESET_4') || (prepopulate_transfer_preset == 'LOCKED_PRESET_4') )
                    {$("#xfernumber").val(Call_XC_d_Number);   $("#xfername").val('D4');}
                if ( (prepopulate_transfer_preset == 'PRESET_5') || (prepopulate_transfer_preset == 'LOCKED_PRESET_5') )
                    {$("#xfernumber").val(Call_XC_e_Number);   $("#xfername").val('D5');}
            }
            if ( (quick_transfer_button == 'PRESET_1') || (quick_transfer_button == 'PRESET_2') || (quick_transfer_button == 'PRESET_3') || (quick_transfer_button == 'PRESET_4') || (quick_transfer_button == 'PRESET_5') || (quick_transfer_button == 'LOCKED_PRESET_1') || (quick_transfer_button == 'LOCKED_PRESET_2') || (quick_transfer_button == 'LOCKED_PRESET_3') || (quick_transfer_button == 'LOCKED_PRESET_4') || (quick_transfer_button == 'LOCKED_PRESET_5') ) {
                if ( (quick_transfer_button == 'PRESET_1') || (quick_transfer_button == 'LOCKED_PRESET_1') )
                    {$("#xfernumber").val(Call_XC_a_Number);   $("#xfername").val('D1');}
                if ( (quick_transfer_button == 'PRESET_2') || (quick_transfer_button == 'LOCKED_PRESET_2') )
                    {$("#xfernumber").val(Call_XC_b_Number);   $("#xfername").val('D2');}
                if ( (quick_transfer_button == 'PRESET_3') || (quick_transfer_button == 'LOCKED_PRESET_3') )
                    {$("#xfernumber").val(Call_XC_c_Number);   $("#xfername").val('D3');}
                if ( (quick_transfer_button == 'PRESET_4') || (quick_transfer_button == 'LOCKED_PRESET_4') )
                    {$("#xfernumber").val(Call_XC_d_Number);   $("#xfername").val('D4');}
                if ( (quick_transfer_button == 'PRESET_5') || (quick_transfer_button == 'LOCKED_PRESET_5') )
                    {$("#xfernumber").val(Call_XC_e_Number);   $("#xfername").val('D5');}
                if (quick_transfer_button_locked > 0)
                    {quick_transfer_button_orig = $("#xfernumber").val();}
                
                //$("#QuickXfer").html("<a href=\"#\" onclick=\"mainxfer_send_redirect('XfeRBLIND','" + lastcustchannel + "','" + lastcustserverip + "','','','" + quick_transfer_button_locked + "');return false;\"><img src=\"./images/quicktransfer.png\" style=\"padding-bottom:3px;\" border=\"0\" title=\"Quick Transfer\" alt=\"QUICK TRANSFER\" /></a>");
            }

            //if (custom_3way_button_transfer_enabled > 0)
            //{
            //    $("#CustomXfer").html("<a href=\"#\" onclick=\"custom_button_transfer();return false;\"><img src=\"./images/vdc_LB_customxfer.gif\" border=\"0\" alt=\"Custom Transfer\" /></a>");
            //}

            if (call_requeue_button > 0) {
                var CloserSelectChoices = $("#CloserSelectList").val();
                var regCRB = new RegExp("AGENTDIRECT","ig");
                if ( (CloserSelectChoices.match(regCRB)) || (closer_campaigns.match(regCRB)) ) {
                    toggleButton('ReQueueCall', 'on');
                } else {
                    toggleButton('ReQueueCall', 'off');
                }
            }

            // Build transfer pull-down list
            loop_ct = 0;
            live_Xfer_HTML = '';
            Xfer_Select = '';
            while (loop_ct < XFgroupCOUNT) {
                if (VARxferGroups[loop_ct] == LIVE_default_xfer_group)
                    {Xfer_Select = 'selected ';}
                else {Xfer_Select = '';}
                live_Xfer_HTML = live_Xfer_HTML + "<option " + Xfer_Select + "value=\"" + VARxferGroups[loop_ct] + "\">" + VARxferGroups[loop_ct] + " - " + VARxferGroupsNames[loop_ct] + "</option>\n";
                loop_ct++;
            }
            //$("#XferGroupList").html("<select size='1' name='XfeRGrouP' class='cust_form' id='XferGroup' onChange='XferAgentSelectLink();return false;'>" + live_Xfer_HTML + "</select>");

            if (lastcustserverip == server_ip) {
                //$("#VolumeUpSpan").html("<a onclick=\"volume_control('UP','" + lastcustchannel + "','');return false;\"><img src='./images/vdc_volume_up.gif' border='0' /></a>");
                //$("#VolumeDownSpan").html("<a onclick=\"volume_control('DOWN','" + lastcustchannel + "','');return false;\"><img src='./images/vdc_volume_down.gif' border='0' /></a>");
            }

            if (dial_method == "INBOUND_MAN") {
                //$("#DiaLControl").html("<img src=\"./images/pause_OFF.png\" border=\"0\" title=\"Pause\" alt=\" Pause \" /><br /><img src=\"./images/resume_OFF.png\" border=\"0\" title=\"Resume\" alt=\"Resume\" /><small>&nbsp;</small><img src=\"./images/dialnext_OFF.png\" border=\"0\" title=\"Dial Next Number\" alt=\"Dial Next Number\" />");
                toggleButton('ResumePause', 'pause', false);
            } else {
                //$("#DiaLControl").html(DiaLControl_auto_HTML_OFF);
                toggleButton('ResumePause', 'pause', false);
            }

            if (VDCL_group_id.length > 1)
                {var group = VDCL_group_id;}
            else
                {var group = campaign;}
            if ( (dialed_label.length < 2) || (dialed_label=='NONE') ) {dialed_label='MAIN';}

            //if (hide_gender < 1)
            //{
            //    var genderIndex = document.getElementById("gender_list").selectedIndex;
            //    var genderValue =  document.getElementById('gender_list').options[genderIndex].value;
            //    $(".formMain input[name='gender']").val(genderValue);
            //}

            LeadDispo = '';

            var regWFAcustom = new RegExp("^VAR","ig");
            if (VDIC_web_form_address.match(regWFAcustom)) {
                TEMP_VDIC_web_form_address = URLDecode(VDIC_web_form_address,'YES','CUSTOM');
                TEMP_VDIC_web_form_address = TEMP_VDIC_web_form_address.replace(regWFAcustom, '');
            } else {
                TEMP_VDIC_web_form_address = URLDecode(VDIC_web_form_address,'YES','DEFAULT','1');
            }

            if (VDIC_web_form_address_two.match(regWFAcustom)) {
                TEMP_VDIC_web_form_address_two = URLDecode(VDIC_web_form_address_two,'YES','CUSTOM');
                TEMP_VDIC_web_form_address_two = TEMP_VDIC_web_form_address_two.replace(regWFAcustom, '');
            } else {
                TEMP_VDIC_web_form_address_two = URLDecode(VDIC_web_form_address_two,'YES','DEFAULT','2');
            }


            if (TEMP_VDIC_web_form_address.length > 0) {
                //$("#WebFormSpan").html("<a href=\"" + TEMP_VDIC_web_form_address + "\" target=\"" + web_form_target + "\" onMouseOver=\"WebFormRefresH();\"><img src=\"./images/vdc_LB_webform.gif\" border=\"0\" alt=\"Web Form\" /></a>\n");
            }

            if (enable_second_webform > 0) {
                //$("#WebFormSpanTwo").html("<a href=\"" + TEMP_VDIC_web_form_address_two + "\" target=\"" + web_form_target + "\" onMouseOver=\"WebFormTwoRefresH();\"><img src=\"./images/vdc_LB_webform_two.gif\" border=\"0\" alt=\"Web Form 2\" /></a>\n");
            }

            if ( (LIVE_campaign_recording == 'ALLCALLS') || (LIVE_campaign_recording == 'ALLFORCE') )
                {all_record = 'YES';}

            if ( (view_scripts == 1) && (Call_Script_ID.length > 0) ) {
                var SCRIPT_web_form = "http://"+hostURL+"/testing.php";
                var TEMP_SCRIPT_web_form = URLDecode(SCRIPT_web_form,'YES','DEFAULT','1');
                //$("#ScriptButtonSpan").html("<A HREF=\"#\" onClick=\"ScriptPanelToFront();\"><IMG SRC=\"./images/script_tab.png\" ALT=\"SCRIPT\" WIDTH=143 HEIGHT=27 BORDER=0></A>");

                if ( (script_recording_delay > 0) && ( (LIVE_campaign_recording == 'ALLCALLS') || (LIVE_campaign_recording == 'ALLFORCE') ) ) {
                    delayed_script_load = 'YES';
                    //RefresHScript('CLEAR');
                } else {
                    //load_script_contents();
                }
            }

            if (custom_fields_enabled > 0) {
                $("#CustomFormSpan").html("<a href=\"#\" onclick=\"FormPanelToFront();\"><img src=\"./images/custom_form_tab.png\" alt=\"FORM\" width=\"143px\" height=\"27px\" border=\"0\" /></a>");
                //FormContentsLoad();
            }
            // JOEJ 082812 - new for email feature
            if (email_enabled > 0) {
                //EmailContentsLoad();
            }
            if (Call_Auto_Launch == 'SCRIPT') {
                if (delayed_script_load == 'YES') {
                    //load_script_contents();
                }
                //ScriptPanelToFront();
            }
            if (Call_Auto_Launch == 'FORM') {
                //FormPanelToFront();
            }
            if (Call_Auto_Launch == 'EMAIL') {
                //EmailPanelToFront();
            }

            if (Call_Auto_Launch == 'WEBFORM') {
                window.open(TEMP_VDIC_web_form_address, web_form_target, 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=640,height=450');
            }
            if (Call_Auto_Launch == 'WEBFORMTWO') {
                window.open(TEMP_VDIC_web_form_address_two, web_form_target, 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=640,height=450');
            }

            if (useIE > 0) {
                var regCTC = new RegExp("^NONE","ig");
                if (Copy_to_Clipboard.match(regCTC))
                    {var nothing = 1;}
                else {
                    var tmp_clip = $(Copy_to_Clipboard);
                    //alert_box("Copy to clipboard SETTING: |" + useIE + "|" + Copy_to_Clipboard + "|" + tmp_clip.value + "|");
                    window.clipboardData.setData('Text', tmp_clip.value)
                    //alert_box("Copy to clipboard: |" + tmp_clip.value + "|" + Copy_to_Clipboard + "|");
                }
            }

            if (alert_enabled == 'ON') {
                var callnum = dialed_number;
                var dial_display_number = phone_number_format(callnum);
                alert("<?=$lh->translationFor('incoming')?>: " + dial_display_number + "\n <?=$lh->translationFor('group')?>- " + VDIC_data_VDIG[1] + " &nbsp; " + VDIC_fronter);
            }
        } else if (email_enabled > 0 && EMAILgroupCOUNT > 0 && AutoDialWaiting == 1) {
            // JOEJ check for EMAIL
            // QUEUEpadding is needed to allow inbound calls to get through QUEUE status
            QUEUEpadding++;
            if (QUEUEpadding == 5)  {
                QUEUEpadding = 0;
                //check_for_incoming_email();
            }
        }
    });
}


// ################################################################################
// Update Agent screen with values from vicidial_list record
function UpdateFieldsData() {
    var fields_list = update_fields_data + ',';
    update_fields = 0;
    update_fields_data = '';
    
    var postData = {
        goAction: 'goUpdateFields',
        goUser: uName,
        goPass: uPass,
        goSessionName: session_name,
        goServerIP: server_ip,
        goConfExten: session_id,
        goStage: update_fields_data,
        responsetype: 'json'
    };

    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
        var UDfieldsResponse = null;
        UDfieldsData = result.data;
        
        if (UDfieldsData.status == 'GOOD') {
            var regUDvendor_lead_code = new RegExp("vendor_lead_code,","ig");
            if (fields_list.match(regUDvendor_lead_code))
                {$("#name_form input[name='vendor_lead_code']").val(UDfieldsData.vendor_id);}
            var regUDsource_id = new RegExp("source_id,","ig");
            if (fields_list.match(regUDsource_id))
                {source_id = UDfieldsData.source_id;}
            var regUDgmt_offset_now = new RegExp("gmt_offset_now,","ig");
            if (fields_list.match(regUDgmt_offset_now))
                {$("#name_form input[name='gmt_offset_now']").val(UDfieldsData.gmt_offset);}
            var regUDphone_code = new RegExp("phone_code,","ig");
            if (fields_list.match(regUDphone_code))
                {$("#name_form input[name='phone_code']").val(UDfieldsData.phone_code);}
            var regUDphone_number = new RegExp("phone_number,","ig");
            if (fields_list.match(regUDphone_number)) {
                if ( (disable_alter_custphone == 'Y') || (disable_alter_custphone == 'HIDE') ) {
                    var tmp_pn = $("#phone_numberDISP");
                    if (disable_alter_custphone == 'Y') {
                        tmp_pn.html(UDfieldsData.phone_number);
                    }
                }
                $("#name_form input[name='phone_number']").val(UDfieldsData.phone_number);
            }
            var regUDtitle = new RegExp("title,","ig");
            if (fields_list.match(regUDtitle))
                {$("#name_form input[name='title']").val(UDfieldsData.title);}
            var regUDfirst_name = new RegExp("first_name,","ig");
            if (fields_list.match(regUDfirst_name))
                {$("#name_form input[name='first_name']").val(UDfieldsData.first_name);}
            var regUDmiddle_initial = new RegExp("middle_initial,","ig");
            if (fields_list.match(regUDmiddle_initial))
                {$("#name_form input[name='middle_initial']").val(UDfieldsData.middle_initial);}
            var regUDlast_name = new RegExp("last_name,","ig");
            if (fields_list.match(regUDlast_name))
                {$("#name_form input[name='last_name']").val(UDfieldsData.last_name);}
            var regUDaddress1 = new RegExp("address1,","ig");
            if (fields_list.match(regUDaddress1))
                {$("#name_form input[name='address1']").val(UDfieldsData.address1);}
            var regUDaddress2 = new RegExp("address2,","ig");
            if (fields_list.match(regUDaddress2))
                {$("#name_form input[name='address2']").val(UDfieldsData.address2);}
            var regUDaddress3 = new RegExp("address3,","ig");
            if (fields_list.match(regUDaddress3))
                {$("#name_form input[name='address3']").val(UDfieldsData.address3);}
            var regUDcity = new RegExp("city,","ig");
            if (fields_list.match(regUDcity))
                {$("#name_form input[name='city']").val(UDfieldsData.city);}
            var regUDstate = new RegExp("state,","ig");
            if (fields_list.match(regUDstate))
                {$("#name_form input[name='state']").val(UDfieldsData.state);}
            var regUDprovince = new RegExp("province,","ig");
            if (fields_list.match(regUDprovince))
                {$("#name_form input[name='province']").val(UDfieldsData.province);}
            var regUDpostal_code = new RegExp("postal_code,","ig");
            if (fields_list.match(regUDpostal_code))
                {$("#name_form input[name='postal_code']").val(UDfieldsData.postal_code);}
            var regUDcountry_code = new RegExp("country_code,","ig");
            if (fields_list.match(regUDcountry_code))
                {$("#name_form input[name='country_code']").val(UDfieldsData.country_code);}
            var regUDgender = new RegExp("gender,","ig");
            if (fields_list.match(regUDgender)) {
                $("#name_form select[name='gender']").val(UDfieldsData.gender);
                if (hide_gender > 0) {
                    //document.vicidial_form.gender_list.value		= UDfieldsResponse_array[18];
                } else {
                    var gIndex = 0;
                    //if (document.vicidial_form.gender.value == 'M') {var gIndex = 1;}
                    //if (document.vicidial_form.gender.value == 'F') {var gIndex = 2;}
                    //document.getElementById("gender_list").selectedIndex = gIndex;
                    //var genderIndex = document.getElementById("gender_list").selectedIndex;
                    //var genderValue =  document.getElementById('gender_list').options[genderIndex].value;
                    //document.vicidial_form.gender.value = genderValue;
                }
            }
            var regUDdate_of_birth = new RegExp("date_of_birth,","ig");
            if (fields_list.match(regUDdate_of_birth))
                {$("#name_form input[name='date_of_birth']").val(UDfieldsData.date_of_birth);}
            var regUDalt_phone = new RegExp("alt_phone,","ig");
            if (fields_list.match(regUDalt_phone))
                {$("#name_form input[name='alt_phone']").val(UDfieldsData.alt_phone);}
            var regUDemail = new RegExp("email,","ig");
            if (fields_list.match(regUDemail))
                {$("#name_form input[name='email']").val(UDfieldsData.email);}
            var regUDsecurity_phrase = new RegExp("security_phrase,","ig");
            if (fields_list.match(regUDsecurity_phrase))
                {$("#name_form input[name='security_phrase']").val(UDfieldsData.security);}
            var regUDcomments = new RegExp("comments,","ig");
            if (fields_list.match(regUDcomments)) {
                var REGcommentsNL = new RegExp("!N","g");
                var UDfieldComments = UDfieldsData.comments;
                UDfieldComments = UDfieldComments.replace(REGcommentsNL, "\n");
                $("#name_form input[name='comments']").val(UDfieldComments);
            }
            var regUDrank = new RegExp("rank,","ig");
            if (fields_list.match(regUDrank))
                {$("#name_form input[name='rank']").val(UDfieldsData.rank);}
            var regUDowner = new RegExp("owner,","ig");
            if (fields_list.match(regUDowner))
                {$("#name_form input[name='owner']").val(UDfieldsData.owner);}
            var regUDformreload = new RegExp("formreload,","ig");
            if (fields_list.match(regUDformreload))
                {FormContentsLoad();}

            // JOEJ 082812 - new for email feature
            //var regUDemailreload = new RegExp("emailreload,","ig");
            //if (fields_list.match(regUDemailreload))
            //    {EmailContentsLoad();}

            var VDIC_web_form_address = web_form_address;
            var VDIC_web_form_address_two = web_form_address_two;
            var regWFAcustom = new RegExp("^VAR","ig");
            if (VDIC_web_form_address.match(regWFAcustom)) {
                TEMP_VDIC_web_form_address = URLDecode(VDIC_web_form_address, 'YES', 'CUSTOM');
                TEMP_VDIC_web_form_address = TEMP_VDIC_web_form_address.replace(regWFAcustom, '');
            } else {
                TEMP_VDIC_web_form_address = URLDecode(VDIC_web_form_address, 'YES', 'DEFAULT', '1');
            }

            if (VDIC_web_form_address_two.match(regWFAcustom)) {
                TEMP_VDIC_web_form_address_two = URLDecode(VDIC_web_form_address_two, 'YES', 'CUSTOM');
                TEMP_VDIC_web_form_address_two = TEMP_VDIC_web_form_address_two.replace(regWFAcustom, '');
            } else {
                TEMP_VDIC_web_form_address_two = URLDecode(VDIC_web_form_address_two, 'YES', 'DEFAULT', '2');
            }

            if (TEMP_VDIC_web_form_address.length > 0) {
                //document.getElementById("WebFormSpan").innerHTML = "<a href=\"" + TEMP_VDIC_web_form_address + "\" target=\"" + web_form_target + "\" onMouseOver=\"WebFormRefresH();\" style=\"font-size:13px;color:white;text-decoration:none;\"><?=$lang['web_form']?></a>";
            }
                                            
            if (enable_second_webform > 0) {
                //document.getElementById("WebFormSpanTwo").innerHTML = "<a href=\"" + TEMP_VDIC_web_form_address_two + "\" target=\"" + web_form_target + "\" onMouseOver=\"WebFormTwoRefresH();\" style=\"font-size:13px;color:white;text-decoration:none;\"><?=$lang['web_form_two']?></a>";
            }
        } else {
            alert("<?=$lh->translationFor('update_fields_error')?>: " + result.message);
        }
    });
}


// ################################################################################
// Refresh the FORM content
function FormContentsLoad() {
    var form_list_id = $("#name_form input[name='list_id']").val();
    var form_entry_list_id = $("#name_form input[name='entry_list_id']").val();
    if (form_entry_list_id.length > 2)
        {form_list_id = form_entry_list_id}
    //document.getElementById('vcFormIFrame').src='./vdc_form_display.php?lead_id=' + document.vicidial_form.lead_id.value + '&list_id=' + form_list_id + '&user=' + user + '&pass=' + pass + '&campaign=' + campaign + '&server_ip=' + server_ip + '&session_id=' + '&uniqueid=' + document.vicidial_form.uniqueid.value + '&stage=DISPLAY' + "&campaign=" + campaign + "&phone_login=" + phone_login + "&original_phone_login=" + original_phone_login +"&phone_pass=" + phone_pass + "&fronter=" + fronter + "&closer=" + user + "&group=" + group + "&channel_group=" + group + "&SQLdate=" + SQLdate + "&epoch=" + UnixTime + "&uniqueid=" + document.vicidial_form.uniqueid.value + "&customer_zap_channel=" + lastcustchannel + "&customer_server_ip=" + lastcustserverip +"&server_ip=" + server_ip + "&SIPexten=" + extension + "&session_id=" + session_id + "&phone=" + document.vicidial_form.phone_number.value + "&parked_by=" + document.vicidial_form.lead_id.value +"&dispo=" + LeaDDispO + '' +"&dialed_number=" + dialed_number + '' +"&dialed_label=" + dialed_label + '' +"&camp_script=" + campaign_script + '' +"&in_script=" + CalL_ScripT_id + '' +"&script_width=" + script_width + '' +"&script_height=" + script_height + '' +"&fullname=" + LOGfullname + '' +"&recording_filename=" + recording_filename + '' +"&recording_id=" + recording_id + '' +"&user_custom_one=" + VU_custom_one + '' +"&user_custom_two=" + VU_custom_two + '' +"&user_custom_three=" + VU_custom_three + '' +"&user_custom_four=" + VU_custom_four + '' +"&user_custom_five=" + VU_custom_five + '' +"&preset_number_a=" + CalL_XC_a_NuMber + '' +"&preset_number_b=" + CalL_XC_b_NuMber + '' +"&preset_number_c=" + CalL_XC_c_NuMber + '' +"&preset_number_d=" + CalL_XC_d_NuMber + '' +"&preset_number_e=" + CalL_XC_e_NuMber + '' +"&preset_dtmf_a=" + CalL_XC_a_Dtmf + '' +"&preset_dtmf_b=" + CalL_XC_b_Dtmf + '' +"&did_id=" + did_id + '' +"&did_extension=" + did_extension + '' +"&did_pattern=" + did_pattern + '' +"&did_description=" + did_description + '' +"&closecallid=" + closecallid + '' +"&xfercallid=" + xfercallid + '' + "&agent_log_id=" + agent_log_id + "&call_id=" + LasTCID + "&user_group=" + VU_user_group + '' +"&web_vars=" + LIVE_web_vars + '';
    form_list_id = '';
    form_entry_list_id = '';
}


// clear api field
function Clear_API_Field(temp_field) {
    var postData = {
        goServerIP: server_ip,
        goSessionName: session_name,
        goAction: "goClearAPIField",
        goComments: temp_field,
        goUser: uName,
        goPass: uPass,
        responsetype: 'json'
    };
        
    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
		//alert(result.result);
	});
}

function ManualDialCheckChannel(taskCheckOR) {
    var CIDcheck = MDnextCID;
    if (taskCheckOR == 'YES') {
        var CIDcheck = XDnextCID;
    } else {
        var CIDcheck = MDnextCID;
    }
    var postData = {
        goServerIP: server_ip,
        goSessionName: session_name,
        goAction: "goManualDialLookCall",
        goConfExten: session_id,
        goUser: uName,
        goPass: uPass,
        goMDnextCID: CIDcheck,
        goAgentLogID: agent_log_id,
        goLeadID: $(".formMain input[name='lead_id']").val(),
        goDialSeconds: MD_ring_seconds,
        goStage: taskCheckOR,
        responsetype: 'json'
    };
        
    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
        var this_MD_data = result.data;
        var MDlookCID = result.lookCID;
        var regMDL = new RegExp("^Local","ig");
        
        if (MDlookCID == "NO") {
            MD_ring_seconds++;
            dispnum = lead_dial_number;
            var status_display_number = phone_number_format(dispnum);
            
            var status_display_content = '';
            if (alt_dial_status_display == 0) {
                if (status_display_CALLID > 0) {status_display_content += "<br><?=$lh->translationFor('uid')?>: " + CIDcheck;}
                if (status_display_LEADID > 0) {status_display_content += "<br><?=$lh->translationFor('lead_id')?>: " + $(".formMain input[name='lead_id']").val();}
                if (status_display_LISTID > 0) {status_display_content += "<br><?=$lh->translationFor('list_id')?>: " + $(".formMain input[name='list_id']").val();}
                
                $("#MainStatusSpan").html("<b><?=$lh->translationFor('calling')?>:</b> " + status_display_number + " " + status_display_content + " <br><?=$lh->translationFor('waiting_for_ring')?>... " + MD_ring_seconds + " <?=$lh->translationFor('seconds')?>");
            }
        } else {
            MDuniqueid = this_MD_data.uniqueid;
            MDchannel = this_MD_data.channel;
            var MDalert = this_MD_data.MDalert;
            
            if (MDalert == "ERROR") {
                var MDerrorDesc = this_MD_data.MDerrorDesc;
                var MDerrorDescSIP = this_MD_data.MDerrorDescSIP;
                alert("<?=$lh->translationFor('call_rejected')?>: " + MDchannel + "\n" + MDerrorDesc + "\n" + MDerrorDescSIP);
            }
            
            if ( (MDchannel.match(regMDL)) && (asterisk_version != '1.0.8') && (asterisk_version != '1.0.9') ) {
                // bad grab of Local channel, try again
                MD_ring_seconds++;
            } else {
                custchannellive = 1;
                
                $(".formMain input[name='uniqueid']").val(this_MD_data.uniqueid);
                $("#callchannel").html(this_MD_data.channel);
                lastcustchannel = this_MD_data.channel;
                
                toggleStatus('LIVE');
                $("#call_length").html("0");
                $("#session_id").html(session_id);
                
                dispnum = lead_dial_number;
                var status_display_number = phone_number_format(dispnum);
                
                live_customer_call = 1;
                live_call_seconds = 0;
                MD_channel_look = 0;
                var status_display_content = '';
                if (status_display_CALLID > 0) {status_display_content += "<br><?=$lh->translationFor('uid')?>: " + CIDcheck;}
                if (status_display_LEADID > 0) {status_display_content += "<br><?=$lh->translationFor('lead_id')?>: " + $(".formMain input[name='lead_id']").val();}
                if (status_display_LISTID > 0) {status_display_content += "<br><?=$lh->translationFor('list_id')?>: " + $(".formMain input[name='list_id']").val();}
                
                $("#MainStatusSpan").html("<b><?=$lh->translationFor('called')?>:</b> " + status_display_number + " " + status_display_content);
                
                toggleButton('DialHangup', 'hangup');
                activateLinks();
                
                lastcustserverip = '';
            }
        }
    });
    
    if ( (MD_ring_seconds > 49) && (MD_ring_seconds > dial_timeout) ) {
        MD_channel_look = 0;
        MD_ring_seconds = 0;
        
        toggleButtons(dial_method);
        alert("<?=$lh->translationFor('dial_timeout')?>.");
    }
}

// ################################################################################
// Insert the new manual dial as a lead and go to manual dial screen
function NewManualDialCall(tempDiaLnow) {
    if (waiting_on_dispo > 0) {
        alert("<?=$lh->translationFor('system_delay_try_again')?>\n<?=$lh->translationFor('code')?>:" + agent_log_id + " - " + waiting_on_dispo);
    } else {
        //hideDiv('NeWManuaLDiaLBox');

        var sending_group_alias = 0;
        var MDDiaLCodEform = $("#MDDiaLCodE").val();
        var MDPhonENumbeRform = $("#MDPhonENumbeR").val();
        var MDLeadIDform = $("#MDLeadID").val();
        var MDTypeform = $("#MDType").val();
        var MDDiaLOverridEform = $("#MDDiaLOverridE").val();
        var MDVendorLeadCode = $(".formMain input[name='vendor_lead_code']").val();
        var MDLookuPLeaD = 'new';
        if ($("#LeadLookUP").is(':checked'))
            {MDLookuPLeaD = 'lookup';}

        if (MDPhonENumbeRform == 'XXXXXXXXXX')
            {MDPhonENumbeRform = $("#MDPhonENumbeRHiddeN").val();}

        if (MDDiaLCodEform.length < 1)
            {MDDiaLCodEform = $(".formMain input[name='phone_code']").val();}

        if ( (MDDiaLOverridEform.length > 0) && (active_ingroup_dial.length < 1) ) {
            agent_dialed_number = 1;
            agent_dialed_type = 'MANUAL_OVERRIDE';
            BasicOriginateCall(session_id,'NO','YES',MDDiaLOverridEform,'YES','','1','0');
        } else {
            if (active_ingroup_dial.length < 1) {
                auto_dial_level = 0;
                manual_dial_in_progress = 1;
                agent_dialed_number = 1;
            }
            //MainPanelToFront();

            if ( (tempDiaLnow == 'PREVIEW') && (active_ingroup_dial.length < 1) ) {
                //alt_phone_dialing = 1;
                agent_dialed_type='MANUAL_PREVIEW';
                buildDiv('DiaLLeaDPrevieW');
                if (alt_phone_dialing == 1)
                    {buildDiv('DiaLDiaLAltPhonE');}
                $("#LeadPreview").prop('checked', true);
                //$("#DialALTPhone").prop('checked', true);
            } else {
                agent_dialed_type = 'MANUAL_DIALNOW';
                $("#LeadPreview").prop('checked', false);
                $("#DialALTPhone").prop('checked', false);
            }
            if (active_group_alias.length > 1)
                {var sending_group_alias = 1;}

            ManualDialNext("",MDLeadIDform,MDDiaLCodEform,MDPhonENumbeRform,MDLookuPLeaD,MDVendorLeadCode,sending_group_alias,MDTypeform);
        }

        $("#MDPhonENumbeR").val('');
        $("#MDDiaLOverridE").val('');
        $("#MDLeadID").val('');
        $("#MDType").val('');
        $("#MDPhonENumbeRHiddeN").val('');
    }
}

// ################################################################################
// Fast version of manual dial
function NewManualDialCallFast() {
    var MDDiaLCodEform = $(".formMain input[name='phone_code']").val();
    var MDPhonENumbeRform = $(".formMain input[name='phone_number']").val();
    var MDVendorLeadCode = $(".formMain input[name='vendor_lead_code']").val();

    if ( (MDDiaLCodEform.length < 1) || (MDPhonENumbeRform.length < 5) ) {
        alert("<?=$lh->translationFor('must_enter_number_to_fdial')?>");
    } else {
        if (waiting_on_dispo > 0) {
            alert("<?=$lh->translationFor('system_delay_try_again')?>\n<?=$lh->translationFor('code')?>:" + agent_log_id + " - " + waiting_on_dispo);
        } else {
            var MDLookuPLeaD = 'new';
            if ($("#LeadLookUP").is(':checked'))
                {MDLookuPLeaD = 'lookup';}
        
            agent_dialed_number = 1;
            agent_dialed_type = 'MANUAL_DIALFAST';
            //alt_phone_dialing = 1;
            auto_dial_level = 0;
            manual_dial_in_progress = 1;
            //MainPanelToFront();
            //buildDiv('DiaLLeaDPrevieW');
            //if (alt_phone_dialing == 1)
            //    {buildDiv('DiaLDiaLAltPhonE');}
            $("#LeadPreview").prop('checked', false);
            //$("#DialALTPhone").prop('checked', true);
            ManualDialNext("","",MDDiaLCodEform,MDPhonENumbeRform,MDLookuPLeaD,MDVendorLeadCode,'0');
        }
    }
}

// ################################################################################
// Finish Callback and go back to original screen
function ManualDialFinished() {
    alt_phone_dialing = starting_alt_phone_dialing;
    auto_dial_level = starting_dial_level;
    //MainPanelToFront();
    //CalLBacKsCounTCheck();
    manual_dial_in_progress = 0;
}


// Hangup Calls
function DialedCallHangup(dispowindow, hotkeysused, altdispo, nodeletevdac) {
    if (VDCL_group_id.length > 1)
        {var group = VDCL_group_id;}
    else
        {var group = campaign;}
    var form_cust_channel = $("#callchannel").html();
    var form_cust_serverip = $("#callserverip").val();
    var customer_channel = lastcustchannel;
    var customer_server_ip = lastcustserverip;
    AgainHangupChannel = lastcustchannel;
    AgainHangupServer = lastcustserverip;
    AgainCallSeconds = live_call_seconds;
    AgainCallCID = CallCID;
    var process_post_hangup = 0;
    if ( (RedirectXFER < 1) && ( (MD_channel_look == 1) || (auto_dial_level == 0) ) ) {
        MD_channel_look = 0;
        //DialTimeHangup('MAIN');
    }
    if (form_cust_channel.length > 3) {
        var queryCID = "HLvdcW" + epoch_sec + user_abb;
        var hangupvalue = customer_channel;
        var postData = {
            goServerIP: server_ip,
            goSessionName: session_name,
            goAction: 'goHangupCall',
            goChannel: hangupvalue,
            goUser: uName,
            goPass: uPass,
            goCallServerIP: customer_server_ip,
            goAutoDialLevel: auto_dial_level,
            goQueryCID: queryCID,
            goCallCID: CallCID,
            goSeconds: live_call_seconds,
            goExten: session_id,
            goCampaign: group,
            goNoDeleteVDAC: nodeletevdac,
            goLogCampaign: campaign,
            goQMExtension: qm_extension,
            responsetype: 'json'
        };
        
        $.ajax({
            type: 'POST',
            url: '<?=$goAPI?>/goAgent/goAPI.php',
            processData: true,
            data: postData,
            dataType: "json"
        })
        .done(function (result) {
            NActiveExt = null;
            NActiveExt = result;
        });
        process_post_hangup = 1;
    } else {process_post_hangup = 1;}
    
    if (process_post_hangup == 1) {
        live_customer_call = 0;
        live_call_seconds = 0;
        MD_ring_seconds = 0;
        CallCID = '';
        MDnextCID = '';

        //UPDATE VICIDIAL_LOG ENTRY FOR THIS CALL PROCESS
        //DialLog("end",nodeletevdac);
        conf_dialed = 0;
        if (dispowindow == 'NO') {
            open_dispo_screen = 0;
        } else {
            if (auto_dial_level == 0) {
                if ($("#DialALTPhone").is(':checked')) {
                    reselect_alt_dial = 1;
                    open_dispo_screen = 0;
                } else {
                    reselect_alt_dial = 0;
                    open_dispo_screen = 1;
                }
            } else {
                if ($("#DialALTPhone").is(':checked')) {
                    reselect_alt_dial = 1;
                    open_dispo_screen = 0;
                    auto_dial_level = 0;
                    manual_dial_in_progress = 1;
                    auto_dial_alt_dial = 1;
                } else {
                    reselect_alt_dial = 0;
                    open_dispo_screen = 1;
                }
            }
        }

        //DEACTIVATE CHANNEL-DEPENDANT BUTTONS AND VARIABLES
        $("#callchannel").html('');
        $("#callserverip").val('');
        lastcustchannel = '';
        lastcustserverip = '';
        MDchannel = '';
        if (post_phone_time_diff_alert_message.length > 10) {
            $("#post_phone_time_diff_span_contents").html("");
            //hideDiv('post_phone_time_diff_span');
            post_phone_time_diff_alert_message = '';
        }

        toggleStatus('NOLIVE');
        
        //document.getElementById("WebFormSpan").innerHTML = "<a href=\"#\" style=\"font-size:13px;color:grey;text-decoration:none;\" /><?=$lang['web_form']?></a>";
        if (enable_second_webform > 0) {
            //document.getElementById("WebFormSpanTwo").innerHTML = "<a href=\"#\" style=\"font-size:13px;color:grey;text-decoration:none;\" /><?=$lang['web_form_two']?></a>";
        }
        //document.getElementById("ScriptButtonSpan").innerHTML = "<a href=\"#\" id=\"ScriptButtonSpan\" style=\"font-size:13px;color:grey;text-decoration:none;\"><?=ucwords($lang['script'])?></a>";
        //document.getElementById("CustomFormSpan").innerHTML = "<a href=\"#\" id=\"CustomFormSpan\" style=\"font-size:13px;color:grey;text-decoration:none;\" /><?=ucwords($lang['custom_form'])?></a>";
        
        toggleButton('ParkCall', 'off');
        //document.getElementById("ParkControl").innerHTML = "<img src=\"./images/callpark_OFF.png\" border=\"0\" title=\"<?=$lang['park_call']?>\" alt=\"<?=$lang['park_call']?>\" />";
        if ( (ivr_park_call=='ENABLED') || (ivr_park_call=='ENABLED_PARK_ONLY') ) {
            toggleButton('IVRParkCall', 'off');
            //document.getElementById("ivrParkControl").innerHTML = "<img src=\"./images/ivrcallpark_OFF.png\" style=\"padding-bottom:3px;\" border=\"0\" title=\"<?=$lang['ivr_park_call']?>\" alt=\"<?=$lang['ivr_park_call']?>\" />";
        }
        
        toggleButton('DialHangup', 'dial');
        toggleButton('TransferCall', 'off');
    
        //document.getElementById("HangupControl").innerHTML = "<img src=\"./images/hangup_OFF.png\" border=\"0\" title=\"<?=$lang['hangup_customer']?>\" alt=\"<?=$lang['hangup_customer']?>\" />";
        //document.getElementById("XferControl").innerHTML = "<img src=\"./images/transfer_OFF.png\" border=\"0\" title=\"<?=$lang['transfer_conference']?>\" alt=\"<?=$lang['transfer_conference']?>\" />";
        //document.getElementById("LocalCloser").innerHTML = "<img src=\"./images/vdc_XB_localcloser_OFF.gif\" border=\"0\" alt=\"<?=$lang['local_closer']?>\" style=\"vertical-align:middle\" />";
        //document.getElementById("DialBlindTransfer").innerHTML = "<input type=\"button\" value=\" <?=$lang['blind_transfer']?> \" style=\"font-size:10px;width:150px;vertical-align:middle;\" disabled />";
        //document.getElementById("DialBlindVMail").innerHTML = "<img src=\"./images/vdc_XB_ammessage_OFF.gif\" border=\"0\" alt=\"<?=$lang['blind_trasfer_vmail']?>\" style=\"vertical-align:middle\" />";
        //document.getElementById("VolumeUpSpan").innerHTML = "<img src=\"./images/vdc_volume_up_off.gif\" border=\"0\" />";
        //document.getElementById("VolumeDownSpan").innerHTML = "<img src=\"./images/vdc_volume_down_off.gif\" border=\"0\" />";
        
        if ($("#DialALTPhone").is(':checked')) {
            $("#MainStatusSpan").html("&nbsp;");
        }

        if (quick_transfer_button_enabled > 0) {
            //document.getElementById("QuickXfer").innerHTML = "<img src=\"./images/quicktransfer_OFF.png\" style=\"padding-bottom:3px;\" border=\"0\" alt=\"<?=$lang['quick_transfer']?>\" />";
        }

        if (custom_3way_button_transfer_enabled > 0) {
            //document.getElementById("CustomXfer").innerHTML = "<img src=\"./images/vdc_LB_customxfer_OFF.gif\" border=\"0\" alt=\"<?=$lang['custom_transfer']?>\" />";
        }

        if (call_requeue_button > 0) {
            toggleButton('ReQueueCall', 'off');
            //document.getElementById("ReQueueCall").innerHTML =  "<img src=\"./images/requeuecall_OFF.png\" border=\"0\" title=\"<?=$lang['re_queue_call']?>\" alt=\"<?=$lang['re_queue_call']?>\" />";
        }

        $("#custdatetime").html(' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ');

        if ( (auto_dial_level == 0) && (dial_method != 'INBOUND_MAN') ) {
            if ($("#DialALTPhone").is(':checked')) {
                reselect_alt_dial = 1;
                if (altdispo == 'ALTPH2') {
                    //ManualDialOnly('ALTPhonE');
                } else {
                    if (altdispo == 'ADDR3') {
                        //ManualDialOnly('AddresS3');
                    } else {
                        if (hotkeysused == 'YES') {
                            alt_dial_active = 0;
                            alt_dial_status_display = 0;
                            reselect_alt_dial = 0;
                            manual_auto_hotkey = 2;
                        }
                    }
                }
            } else {
                if (hotkeysused == 'YES') {
                    alt_dial_active = 0;
                    alt_dial_status_display = 0;
                    manual_auto_hotkey = 2;
                } else {
                    toggleButton('DialHangup', 'dial');
                    //document.getElementById("DiaLControl").innerHTML = "<a href=\"#\" onclick=\"ManualDialNext('','','','','','0');\"><img src=\"./images/dialnext.png\" border=\"0\" title=\"<?=$lang['dial_next']?>\" alt=\"<?=$lang['dial_next']?>\" /></a>";
                }
                reselect_alt_dial = 0;
            }
        } else {
            if ($("#DialALTPhone").is(':checked')) {
                reselect_alt_dial = 1;
                if (altdispo == 'ALTPH2') {
                    //ManualDialOnly('ALTPhonE');
                } else {
                    if (altdispo == 'ADDR3') {
                        //ManualDialOnly('AddresS3');
                    } else {
                        if (hotkeysused == 'YES') {
                            manual_auto_hotkey = 2;
                            alt_dial_active = 0;
                            alt_dial_status_display = 0;

                            //$("#MainStatusSpan").style.background = panel_bgcolor;
                            $("#MainStatusSpan").html('&nbsp;');
                            if (dial_method == "INBOUND_MAN") {
                                toggleButton('ResumePause', 'resume', false);
                                //document.getElementById("DiaLControl").innerHTML = "<img src=\"./images/pause_OFF.png\" border=\"0\" title=\"<?=$lang['pause']?>\" alt=\" <?=$lang['pause']?> \" /><br /><img src=\"./images/resume_OFF.png\" border=\"0\" title=\"<?=$lang['resume']?>\" alt=\"<?=$lang['resume']?>\" /><small>&nbsp;</small><img src=\"./images/dialnext_OFF.png\" border=\"0\" title=\"<?=$lang['dial_next']?>\" alt=\"<?=$lang['dial_next']?>\" />";
                            } else {
                                toggleButton('ResumePause', 'resume', false);
                                //document.getElementById("DiaLControl").innerHTML = DiaLControl_auto_HTML_OFF;
                            }
                            reselect_alt_dial = 0;
                        }
                    }
                }
            } else {
                //$("#MainStatusSpan").style.background = panel_bgcolor;
                if (dial_method == "INBOUND_MAN") {
                    toggleButton('ResumePause', 'resume', false);
                    //document.getElementById("DiaLControl").innerHTML = "<img src=\"./images/pause_OFF.png\" border=\"0\" title=\"<?=$lang['pause']?>\" alt=\" <?=$lang['pause']?> \" /><br /><img src=\"./images/resume_OFF.png\" border=\"0\" title=\"<?=$lang['resume']?>\" alt=\"<?=$lang['resume']?>\" /><small>&nbsp;</small><img src=\"./images/dialnext_OFF.png\" border=\"0\" title=\"<?=$lang['dial_next']?>\" alt=\"<?=$lang['dial_next']?>\" />";
                } else {
                    toggleButton('ResumePause', 'resume', false);
                    //document.getElementById("DiaLControl").innerHTML = DiaLControl_auto_HTML_OFF;
                }
                reselect_alt_dial = 0;
            }
        }

        //ShoWTransferMain('OFF');
        activateLinks();
    }
}


function DispoSelectBox() {
    $("#select-disposition").modal({
        keyboard: false,
        backdrop: 'static'
    });
    DispoSelectContent_create('','ReSET');
}

function DispoSelectContent_create(taskDSgrp,taskDSstage) {
    if (disable_dispo_screen > 0) {
        $("#DispoSelection").val(disable_dispo_status);
        DispoSelectSubmit();
    } else {
        if (customer_3way_hangup_dispo_message.length > 1) {
            $("#Dispo3wayMessage").html("<b>" + customer_3way_hangup_dispo_message + "</b>");
        }
        if (APIManualDialQueue > 0) {
            $("#DispoManualQueueMessage").html("<b><?=$lh->translationFor('manual_dial_queue_calls_waiting')?>: " + APIManualDialQueue + "</b>");
        }
        if (per_call_notes == 'ENABLED') {
            var test_notes = $("#call_notes_dispo").val();
            if (test_notes.length > 0)
                {$(".formMain input[name='call_notes']").val(test_notes);}
            $("#PerCallNotesContent").html("<br /><b><font size='3'><?=$lh->translationFor('call_notes')?>: </font></b><br /><textarea name='call_notes_dispo' id='call_notes_dispo' rows='2' cols='100' class='cust_form_text' value=''>" + $(".formMain input[name='call_notes']").val() + "</textarea>");
        } else {
            $("#PerCallNotesContent").html("<input type='hidden' name='call_notes_dispo' id='call_notes_dispo' value='' />");
        }

        AgentDispoing = 1;
        var CBflag = '';
        var statuses_ct_half = parseInt(statuses_count / 2);
        var dispo_HTML = "<script>";
            dispo_HTML = dispo_HTML + "$(function() {";
            dispo_HTML = dispo_HTML + "    $('[id^=dispo-add-]').click(function() {";
            dispo_HTML = dispo_HTML + "        var dispoID = $(this).attr('id');";
            dispo_HTML = dispo_HTML + "        DispoSelectContent_create(dispoID.replace('dispo-add-', ''), 'ADD');";
            //dispo_HTML = dispo_HTML + "alert($('#DispoSelection').val());";
            dispo_HTML = dispo_HTML + "    });";
            dispo_HTML = dispo_HTML + "    $('[id^=dispo-sel-]').click(function() {";
            dispo_HTML = dispo_HTML + "        DispoSelectSubmit();";
            dispo_HTML = dispo_HTML + "    });";
            dispo_HTML = dispo_HTML + "});";
            dispo_HTML = dispo_HTML + "</script>";
            dispo_HTML = dispo_HTML + "<table cellpadding='5' cellspacing='5' width='500px' style='-webkit-touch-callout: none; -webkit-user-select: none; -khtml-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; margin: 0 auto;'><tr><td colspan='2'>&nbsp; <b><?=$lh->translationFor('call_dispositions')?></b><br><br></td></tr><tr><td bgcolor='#FFFFFF' height='300px' width='240px' valign='top' class='DispoSelectA'>";
        var loop_ct = 0;
        if (hide_dispo_list < 1) {
            while (loop_ct < statuses_count) {
                var regCBstatus = new RegExp(' ' + statuses[loop_ct] + ' ', "ig");
                if (VARCBstatusesLIST.match(regCBstatus))
                    {CBflag = '*';}
                else
                    {CBflag = '';}
                //console.log(statuses[loop_ct], taskDSgrp);
                if (taskDSgrp == statuses[loop_ct]) {
                    dispo_HTML = dispo_HTML + "<span id='dispo-sel-"+statuses[loop_ct]+"' style='background-color:#99FF99;cursor:pointer;color:#77a30a;'>&nbsp; " + statuses[loop_ct] + " - " + statuses_names[loop_ct] + " " + CBflag + " &nbsp;</span><br /><br />";
                } else {
                    dispo_HTML = dispo_HTML + "<span id='dispo-add-"+statuses[loop_ct]+"' style='cursor:pointer;color:#77a30a;'>&nbsp; " + statuses[loop_ct] + " - " + statuses_names[loop_ct] + "</span> " + CBflag + " &nbsp;<br /><br />";
                }
                if (loop_ct == statuses_ct_half) 
                    {dispo_HTML = dispo_HTML + "</td><td bgcolor='#FFFFFF' height='300px' width='240px' valign='top' class='DispoSelectB'>";}
                loop_ct++;
            }
        } else {
            dispo_HTML = dispo_HTML + "<?=$lh->translationFor('dispo_status_list_hidden')?><br /><br />";
        }
        dispo_HTML = dispo_HTML + "</td></tr></table>";

        if (taskDSstage == 'ReSET') {$("#DispoSelection").val('');}
        else {$("#DispoSelection").val(taskDSgrp);}

        $("#DispoSelectContent").html(dispo_HTML);
        if (focus_blur_enabled == 1) {
            //document.inert_form.inert_button.focus();
            //document.inert_form.inert_button.blur();
        }
        if (my_callback_option == 'CHECKED')
            {$("#CallBackOnlyMe").prop('checked', true);}
    }
}


function DispoSelectSubmit() {
    console.log('Disposing call...');
    if (VDCL_group_id.length > 1) {var group = VDCL_group_id;}
    else {var group = campaign;}

    leaving_threeway = 0;
    blind_transfer = 0;
    CheckDEADcallON = 0;
    currently_in_email = 0;
    customer_3way_hangup_counter = 0;
    customer_3way_hangup_counter_trigger = 0;
    waiting_on_dispo = 1;
    callchannel = '';
    callserverip = '';
    xferchannel = '';
    var dispo_error = 0;

    //$("#DialWithCustomer").html('');
    //$("#ParkCustomerDial").html('');
    //$("#HangupBothLines").html('');

    var DispoChoice = $("#DispoSelection").val();

    if (DispoChoice.length < 1) {
     	alert("<?=$lh->translationFor('must_select_disposition')?>.");
        console.log("Dispo Choice: Must select disposition.");
    } else {
        if (DialALTPhone == true) {
            var man_status = "";
            alt_dial_status_display = 0;
        }
    
        var regCBstatus = new RegExp(' ' + DispoChoice + ' ',"ig");
        if ((VARCBstatusesLIST.match(regCBstatus)) && (DispoChoice.length > 0) && (scheduled_callbacks > 0) && (DispoChoice != 'CBHOLD')) {
            console.info("Open Callback Selection Box");
        } else {
            var postData = {
                goServerIP: server_ip,
                goSessionName: session_name,
                goAction: 'goUpdateDispo',
                goUser: uName,
                goPass: uPass,
                goDispoChoice: DispoChoice,
                goLeadID: lead_id,
                goCampaign: campaign,
                goAutoDialLevel: auto_dial_level,
                goAgentLogID: agent_log_id,
                goCallBackDateTime: CallBackDateTime,
                goListID: list_id,
                goRecipient: CallBackRecipient,
                goUseInternalDNC: use_internal_dnc,
                goUseCampaignDNC: use_campaign_dnc,
                goMDnextCID: LastCID,
                goStage: group,
                goPhoneNumber: cust_phone_number,
                goPhoneCode: cust_phone_code,
                goDialMethod: dial_method,
                goUniqueid: uniqueid,
                goCallBackLeadStatus: CallBackLeadStatus,
                goComments: encodeURIComponent(CallBackComments),
                goCustomFieldNames: custom_field_names,
                goCallNotes: encodeURIComponent(call_notes_dispo),
                goQMDispoCode: DispoQMcsCODE,
                goEmailEnabled: email_enabled,
                responsetype: 'json'
            };
    
            $.ajax({
                type: 'POST',
                url: '<?=$goAPI?>/goAgent/goAPI.php',
                processData: true,
                data: postData,
                dataType: "json",
            })
            .done(function (result) {
                if (auto_dial_level < 1) {
                    if (result.result == 'success') {
                        agent_log_id = result.data.agent_log_id;
                    } else {
                        dispo_error++;
                        alert('<?=$lh->translationFor('dispo_leadid_not_valid')?>');
                    }
                }
    
                waiting_on_dispo = 0;
            });
            
            //CLEAR ALL FORM VARIABLES
            $(".formMain input[name='lead_id']").val('');
            $(".formMain input[name='vendor_lead_code']").val('');
            $(".formMain input[name='list_id']").val('');
            $(".formMain input[name='entry_list_id']").val('');
            $(".formMain input[name='gmt_offset_now']").val('');
            $(".formMain input[name='phone_code']").val('');
            if ( (disable_alter_custphone == 'Y') || (disable_alter_custphone == 'HIDE') ) {
                var tmp_pn = $("#phone_numberDISP");
                tmp_pn.html(' &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ');
            }
            $(".formMain input[name='phone_number']").val('');
            $(".formMain input[name='title']").val('');
            $(".formMain input[name='first_name']").val('');
            $(".formMain input[name='middle_initial']").val('');
            $(".formMain input[name='last_name']").val('');
            $(".formMain input[name='address1']").val('');
            $(".formMain input[name='address2']").val('');
            $(".formMain input[name='address3']").val('');
            $(".formMain input[name='city']").val('');
            $(".formMain input[name='state']").val('');
            $(".formMain input[name='province']").val('');
            $(".formMain input[name='postal_code']").val('');
            $(".formMain input[name='country_code']").val('');
            $(".formMain input[name='gender']").val('');
            $(".formMain input[name='date_of_birth']").val('');
            $(".formMain input[name='alt_phone']").val('');
            $(".formMain input[name='email']").val('');
            $(".formMain input[name='security_phrase']").val('');
            $(".formMain input[name='comments']").val('');
            $(".formMain input[name='audit_comments']").val('');
            if (qc_enabled > 0) {
                $(".formMain input[name='ViewCommentButton']").val('');
                $(".formMain input[name='audit_comments_button']").val('');
            }
            $(".formMain input[name='called_count']").val('');
            $(".formMain input[name='call_notes']").val('');
            $(".formMain input[name='call_notes_dispo']").val('');
            VDCL_group_id = '';
            fronter = '';
            inOUT = 'OUT';
            vtiger_callback_id = '0';
            recording_filename = '';
            recording_id = '';
            MDuniqueid = '';
            XDuniqueid = '';
            tmp_vicidial_id = '';
            EAphone_code = '';
            EAphone_number = '';
            EAalt_phone_notes = '';
            EAalt_phone_active = '';
            EAalt_phone_count = '';
            XDnextCID = '';
            XDcheck = '';
            MDnextCID = '';
            XD_live_customer_call = 0;
            XD_live_call_seconds = 0;
            xfer_in_call = 0;
            MD_channel_look = 0;
            MD_ring_secondS = 0;
            uniqueid_status_display = '';
            uniqueid_status_prefix = '';
            custom_call_id = '';
            API_selected_xfergroup = '';
            API_selected_callmenu = '';
            timer_action = '';
            timer_action_seconds = '';
            timer_action_mesage = '';
            timer_action_destination = '';
            did_pattern = '';
            did_id = '';
            did_extension = '';
            did_description = '';
            closecallid = '';
            xfercallid = '';
            custom_field_names = '';
            custom_field_values = '';
            custom_field_types = '';
            customerparked = 0;
            customerparkedcounter = 0;
            consult_custom_wait = 0;
            consult_custom_go = 0;
            consult_custom_sent = 0;
            xfername = '';
            //$("#xfernumhidden").val('');
            //$("#debugbottomspan").html('');
            customer_3way_hangup_dispo_message = '';
            Dispo3wayMessage = '';
            DispoManualQueueMessage = '';
            //$("#ManualQueueNotice").html('');
            APIManualDialQueue_last = 0;
            $(".formMain input[name='FORM_LOADED']").val('0');
            CallBackLeadStatus = '';
            CallBackDateTime = '';
            CallBackRecipient = '';
            CallBackComments = '';
            DispoQMcsCODE = '';
            active_ingroup_dial = '';
            nocall_dial_flag = 'DISABLED';
            
            $("#SecondsDISP").html('0');
    
            //CLEAR ALL SUB FORM VARIABLES
            //$("#subForm").find(':input').each(function()
            //{
            //    var pattID = /(callchannel|xferchannel|LeadLookUP)/;
            //    var testInput = pattID.test($(this).attr('id'));
            //    if ( ! testInput ) {
            //        $(this).val('');
            //    }
            //});
            inbound_lead_search = 0;
    
            if (post_phone_time_diff_alert_message.length > 10) {
                $("#post_phone_time_diff_span_contents").html("");
                //hideDiv('post_phone_time_diff_span');
                post_phone_time_diff_alert_message = '';
            }
    
            if (manual_dial_in_progress == 1) {
                ManualDialFinished();
            }
    
            $("#select-disposition").modal('hide');
            AgentDispoing = 0;
    
            if ( (shift_logout_flag < 1) && (api_logout_flag < 1) ) {
                if (wrapup_waiting == 0) {
                    if (DispoSelectStop) {
                        if (auto_dial_level != '0') {
                            AutoDialWaiting = 0;
                            QUEUEpadding = 0;
                            AutoDial_Resume_Pause("VDADpause");
                        }
                        pause_calling = 1;
                        if (dispo_check_all_pause != '1') {
                            DispoSelectStop = false;
                            $("#DispoSelectStop").prop('checked', false);
                        }
                    } else {
                        if (auto_dial_level != '0') {
                            AutoDialWaiting = 1;
                            agent_log_id = AutoDial_Resume_Pause("VDADready","NEW_ID");
                        } else {
                            // trigger HotKeys manual dial automatically go to next lead
                            if (manual_auto_hotkey > 0) {
                                manual_auto_hotkey = 0;
                                ManualDialNext('','','','','','0');
                            }
                        }
                    }
                }
            } else {
                //if (shift_logout_flag > 0)
                //    {LogMeOut('SHIFT');}
                //else
                //    {LogMeOut('API');}
            }
            if (focus_blur_enabled == 1) {
                //$("#inert_button").focus();
                //$("#inert_button").blur();
            }
        }
    }
}


// ################################################################################
// Update vicidial_list lead record with all altered values from form
function CustomerData_update() {
    var REGcommentsAMP = new RegExp('&',"g");
    var REGcommentsQUES = new RegExp("\\?","g");
    var REGcommentsPOUND = new RegExp("\\#","g");
    var REGcommentsRESULT = $(".formMain textarea[name='comments']").val();
        REGcommentsRESULT = REGcommentsRESULT.replace(REGcommentsAMP, "--AMP--");
        REGcommentsRESULT = REGcommentsRESULT.replace(REGcommentsQUES, "--QUES--");
        REGcommentsRESULT = REGcommentsRESULT.replace(REGcommentsPOUND, "--POUND--");

    var postData = {
        goAction: 'goUpdateLead',
        goUser: uName,
        goPass: uPass,
        goCampaign: campaign,
        goServerIP: server_ip,
        goSessionName: session_name,
        goLeadID: $(".formMain input[name='lead_id']").val(),
        goVendorLeadCode: $(".formMain input[name='vendor_lead_code']").val(),
        goPhoneNumber: $(".formMain input[name='phone_number']").val(),
        goTitle: $(".formMain input[name='title']").val(),
        goFirstName: $(".formMain input[name='first_name']").val(),
        goMiddleInitial: $(".formMain input[name='middle_initial']").val(),
        goLastName: $(".formMain input[name='last_name']").val(),
        goAddress1: $(".formMain input[name='address1']").val(),
        goAddress2: $(".formMain input[name='address2']").val(),
        goAddress3: $(".formMain input[name='address3']").val(),
        goCity: $(".formMain input[name='city']").val(),
        goState: $(".formMain input[name='state']").val(),
        goProvince: $(".formMain input[name='province']").val(),
        goPostalCode: $(".formMain input[name='postal_code']").val(),
        goCountryCode: $(".formMain input[name='country_code']").val(),
        goGender: $(".formMain input[name='gender']").val(),
        goDateOfBirth: $(".formMain input[name='date_of_birth']").val(),
        goALTPhone: $(".formMain input[name='alt_phone']").val(),
        goEmail: $(".formMain input[name='email']").val(),
        goSecurity: $(".formMain input[name='security_phrase']").val(),
        goComments: REGcommentsRESULT,
        responsetype: 'json'
    };

    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json",
    })
    .done(function (result) {
        console.log('Customer data updated...');
    });
}


// ################################################################################
// Send Originate command to manager to place a phone call
function BasicOriginateCall(tasknum, taskprefix, taskreverse, taskdialvalue, tasknowait, taskconfxfer, taskcid, taskusegroupalias, taskalert, taskpresetname, taskvariables) {
    if (taskalert == '1') {
        var TAqueryCID = tasknum;
        tasknum = '83047777777777';
        taskdialvalue = '7' + taskdialvalue;
        var alertCID = 1;
    } else {
        var alertCID = 0;
    }
    var usegroupalias = 0;
    var consultativexfer_checked = 0;
    if ($("#consultativexfer").is(':checked'))
        {consultativexfer_checked = 1;}
    var regCXFvars = new RegExp("CXFER","g");
    var tasknum_string = tasknum.toString();
    if ( (tasknum_string.match(regCXFvars)) || (consultativexfer_checked > 0) ) {
        if (tasknum_string.match(regCXFvars)) {
            var Ctasknum = tasknum_string.replace(regCXFvars, '');
            if (Ctasknum.length < 2)
                {Ctasknum = '90009';}
            var agentdirect = '';
        } else {
            Ctasknum = '90009';
            var agentdirect = tasknum_string;
        }
        var XFERSelect = $("#XFERGroup");
        var XFER_Group = XFERSelect.val();
        if (API_selected_xfergroup.length > 1)
            {var XFER_Group = API_selected_xfergroup;}
        tasknum = Ctasknum + "*" + XFER_Group + '*CXFER*' + $("#name_form input[name='lead_id']").val() + '**' + dialed_number + '*' + user + '*' + agentdirect + '*' + live_call_seconds + '*';

        if (consult_custom_sent < 1)
            {CustomerData_update();}
    }
    var regAXFvars = new RegExp("AXFER","g");
    if (tasknum_string.match(regAXFvars)) {
        var Ctasknum = tasknum_string.replace(regAXFvars, '');
        if (Ctasknum.length < 2)
            {Ctasknum = '83009';}
        var closerxfercamptail = '_L';
        if (closerxfercamptail.length < 3)
            {closerxfercamptail = 'IVR';}
        tasknum = Ctasknum + '*' + $("#name_form input[name='phone_number']").val() + '*' + $("#name_form input[name='lead_id']").val() + '*' + campaign + '*' + closerxfercamptail + '*' + user + '**' + live_call_seconds + '*';

        if (consult_custom_sent < 1)
            {CustomerData_update();}
    }

    if (taskprefix == 'NO') {var call_prefix = '';}
    else {var call_prefix = agc_dial_prefix;}

    if (prefix_choice.length > 0)
        {var call_prefix = prefix_choice;}

    if (taskreverse == 'YES') {
        if (taskdialvalue.length < 2)
            {var dialnum = dialplan_number;}
        else
            {var dialnum = taskdialvalue;}
        var call_prefix = '';
        var originatevalue = "Local/" + tasknum + "@" + ext_context;
    } else {
        var dialnum = tasknum;
        if ( (protocol == 'EXTERNAL') || (protocol == 'Local') ) {
            var protodial = 'Local';
            var extendial = extension;
            //var extendial = extension + "@" + ext_context;
        } else {
            var protodial = protocol;
            var extendial = extension;
        }
        var originatevalue = protodial + "/" + extendial;
    }

    var leadCID = $("#name_form input[name='lead_id']").val();
    var epochCID = epoch_sec;
    if (leadCID.length < 1)
        {leadCID = user_abb;}
    leadCID = set_length(leadCID,'10', 'left');
    epochCID = set_length(epochCID,'6', 'right');
    if (taskconfxfer == 'YES')
        {var queryCID = "DC" + epochCID + 'W' + leadCID + 'W';}
    else
        {var queryCID = "DV" + epochCID + 'W' + leadCID + 'W';}

    //if (taskconfxfer == 'YES')
    //	{var queryCID = "DCagcW" + epoch_sec + user_abb;}
    //else
    //	{var queryCID = "DVagcW" + epoch_sec + user_abb;}

    if (taskalert == '1') {
        queryCID = TAqueryCID;
    }

    if (cid_choice.length > 3) {
        var call_cid = cid_choice;
        usegroupalias=1;
    } else {
        if (taskcid.length > 3) 
            {var call_cid = taskcid;}
        else 
            {var call_cid = campaign_cid;}
    }
    
    var postData = {
        goAction: 'goOriginate',
        goUser: uName,
        goPass: uPass,
        goServerIP: server_ip,
        goSessionName: session_name,
        goOutboundCID: call_cid,
        goUserGroupAlias: usergroupalias,
        goPresetName: taskpresetname,
        goCampaign: campaign,
        goAccount: active_group_alias,
        goAgentDialedNumber: agent_dialed_number,
        goAgentDialedType: agent_dialed_type,
        goLeadID: $("#name_form input[name='lead_id']").val(),
        goStage: CheckDEADcallON,
        goAlertCID: alertCID,
        goCallVariables: taskvariables,
        responsetype: 'json'
    };

    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json",
    })
    .done(function (result) {
        var BOresponse = result;
        if (BOresponse.result == 'error') {
            alert(BOresponse.message);
        }

        if ((taskdialvalue.length > 0) && (tasknowait != 'YES')) {
            XDnextCID = queryCID;
            MD_channel_look = 1;
            XDcheck = 'YES';

            //document.getElementById("HangupXferLine").innerHTML ="<a href=\"#\" onclick=\"xfercall_send_hangup();return false;\"><img src=\"./images/vdc_XB_hangupxferline.gif\" border=\"0\" alt=\"Hangup Xfer Line\" /></a>";
        }
        
        active_group_alias = '';
        cid_choice = '';
        prefix_choice = '';
        agent_dialed_number = '';
        agent_dialed_type = '';
        Call_Script_ID = '';
        call_variables = '';
    });
}


function ManualDialNext(mdnCBid, mdnBDleadid, mdnDiaLCodE, mdnPhonENumbeR, mdnStagE, mdVendorid, mdgroupalias, mdtype) {
    //dialingINprogress = 1;
    if (waiting_on_dispo > 0) {
        //dialingINprogress = 0;
        alert("<?=$lh->translationFor('system_delay_try_again')?>\n\n<?=$lh->translationFor('code')?>: " + agent_log_id + " - " + waiting_on_dispo);
    } else {
        inOUT = 'OUT';
        all_record = 'NO';
        all_record_count = 0;
        if (dial_method == "INBOUND_MAN") {
            auto_dial_level = 0;

            if (VDRP_stage != 'PAUSED') {
                agent_log_id = AutoDial_Resume_Pause("VDADpause",'','','',"DIALNEXT",'1','NXDIAL');
            } else {
                auto_dial_level = starting_dial_level;
            }
            //document.getElementById("DiaLControl").innerHTML = "<img src=\"./images/pause_OFF.png\" border=\"0\" alt=\" <?=$lang['pause']?> \" /><br /><img src=\"./images/resume_OFF.png\" border=\"0\" title=\"<?=$lang['resume']?>\" alt=\"<?=$lang['resume']?>\" /><small>&nbsp;</small><img src=\"./images/dialnext_OFF.png\" border=\"0\" title=\"<?=$lang['dial_next']?>\" alt=\"<?=$lang['dial_next']?>\" />";
            toggleButton('ResumePause', 'off');
            toggleButton('DialHangup', 'off');
        } else {
            if (active_ingroup_dial.length < 1) {
                //document.getElementById("DiaLControl").innerHTML = "<img src=\"./images/dialnext_OFF.png\" border=\"0\" title=\"<?=$lang['dial_next']?>\" alt=\"<?=$lang['dial_next']?>\" />";
                toggleButton('DialHangup', 'off');
                toggleButton('ResumePause', 'off');
            }
        }

        var manual_dial_only_type_flag = '';
        if ( (mdtype == 'ALT') || (mdtype == 'ADDR3') ) {
            agent_dialed_type = mdtype;
            agent_dialed_number = mdnPhonENumbeR;
            if (mdtype == 'ALT')
                {manual_dial_only_type_flag = 'ALTPhone';}
            if (mdtype == 'ADDR3')
                {manual_dial_only_type_flag = 'Address3';}
        }

        if ( ($("#LeadPreview").prop('checked')) && (active_ingroup_dial.length < 1) ) {
            reselect_preview_dial = 1;
            in_lead_preview_state = 1;
            var man_preview = 'YES';
        
            var man_status = "<a href=\"#\" onclick=\"ManualDialOnly('" + manual_dial_only_type_flag + "')\">&nbsp;<blink><?=$lh->translationFor('dial_lead')?></blink>&nbsp;</a> or <a href=\"#\" onclick=\"ManualDialSkip()\">&nbsp;<blink><?=$lh->translationFor('skip_lead')?></blink>&nbsp;</a>";
            if (manual_preview_dial=='PREVIEW_ONLY') {
                var man_status = "<a href=\"#\" onclick=\"ManualDialOnly('" + manual_dial_only_type_flag + "')\">&nbsp;<blink><?=$lh->translationFor('dial_lead')?></blink>&nbsp;</a>";
            }
        } else {
            reselect_preview_dial = 0;
            var man_preview = 'NO';
            var man_status = "<?=$lh->translationFor('waiting_for_ring')?>...";
        }

        if (cid_choice.length > 0)
            {var call_cid = cid_choice;}
        else
            {var call_cid = campaign_cid;}

        if (prefix_choice.length > 0)
            {var call_prefix = prefix_choice;}
        else
            {var call_prefix = manual_dial_prefix;}
        
        var postData = {
            goAction: 'goManualDialNext',
            goUser: uName,
            goPass: uPass,
            goCampaign: campaign,
            goPreview: man_preview,
            goCallbackID: mdnCBid,
            goLeadID: mdnBDleadid,
            goPhoneCode: mdnDiaLCodE,
            goPhoneNumber: mdnPhonENumbeR,
            goListID: manual_dial_list_id,
            goStage: mdnStagE,
            goVendorLeadCode: mdVendorid,
            goUserGroupAlias: mdgroupalias,
            account: active_group_alias,
            goAgentDialedNumber: mdnPhonENumbeR,
            goAgentDialedType: mdtype,
            qm_extension: qm_extension,
            goDialIngroup: active_ingroup_dial,
            goNoCallDialFlag: nocall_dial_flag,
            goSIPserver: SIPserver,
            goVTCallbackID: vtiger_callback_id,
            responsetype: 'json'
        };

        $.ajax({
            type: 'POST',
            url: '<?=$goAPI?>/goAgent/goAPI.php',
            processData: true,
            data: postData,
            dataType: "json",
        })
        .done(function (result) {
            //dialingINprogress = 0;

            if (active_ingroup_dial.length > 0) {
                AutoDial_Resume_Pause("VDADready",'','','NO_STATUS_CHANGE');
                AutoDialWaiting = 1;
            } else {
                var ERR_MSG = "";
                if (result.result == 'error') {
                    ERR_MSG = result.message;
                }
                //$('#dialerOutput').html('<b>DIALER:</b> ' + result);

                var regMNCvar = new RegExp("HOPPER EMPTY","ig");
                var regMDFvarDNC = new RegExp("DNC","ig");
                var regMDFvarCAMP = new RegExp("CAMPLISTS","ig");
                var regMDFvarTIME = new RegExp("OUTSIDE","ig");
                if ( (ERR_MSG.match(regMNCvar)) || (ERR_MSG.match(regMDFvarDNC)) || (ERR_MSG.match(regMDFvarCAMP)) || (ERR_MSG.match(regMDFvarTIME)) ) {
                    var alert_displayed = 0;
                    trigger_ready = 1;
                    live_customer_call = 0;
                    MD_channel_look = 0;
                    alt_phone_dialing = starting_alt_phone_dialing;
                    auto_dial_level = starting_dial_level;

                    if (ERR_MSG.match(regMNCvar)) {
                        alert("<?=$lh->translationFor('no_leads_on_hopper')?>.");
                        alert_displayed = 1;
                    }
                    if (ERR_MSG.match(regMDFvarDNC)) {
                        alert("<?=$lh->translationFor('phone_number_on_dnc')?>.");
                        alert_displayed = 1;
                    }
                    if (ERR_MSG.match(regMDFvarCAMP)) {
                        alert("<?=$lh->translationFor('phone_number_not_on_list')?>.");
                        alert_displayed = 1;
                    }
                    if (ERR_MSG.match(regMDFvarDNC)) {
                        alert("<?=$lh->translationFor('phone_number_outside_time')?>.");
                        alert_displayed = 1;
                    }
                    if (alert_displayed == 0) {
                        alert("<?=$lh->translationFor('unspecified_error')?>:\n" + mdnPhonENumbeR + " | " + MDnextCID);
                        alert_displayed = 1;
                    }

                    if (starting_dial_level > 0) {
                        if (dial_method == "INBOUND_MAN") {
                            auto_dial_level = starting_dial_level;

                            toggleButton('DialHangup', 'dial');
                            toggleButton('ResumePause', 'resume');
                        } else {
                            toggleButton('DialHangup', 'dial');
                            toggleButton('ResumePause', 'resume');
                        }
                    } else {
                        toggleButton('DialHangup', 'dial');
                    }
                } else {
                    var MDnextResponse_array = [];
                    for(var x in result.data){
                        MDnextResponse_array.push(result.data[x]);
                    }
                    MDnextCID = MDnextResponse_array[0];
                    
                    fronter                                 = uName;
                    LastCID                                 = MDnextResponse_array[0];
                    lead_id                                 = MDnextResponse_array[1];
                    LeadPrevDispo                           = MDnextResponse_array[2];
                    $(".formMain input[name='vendor_lead_code']").val(MDnextResponse_array[4]);
                    list_id                                 = MDnextResponse_array[5];
                    $(".formMain input[name='gmt_offset_now']").val(MDnextResponse_array[6]);
                    cust_phone_code                         = MDnextResponse_array[7];
                    $(".formMain input[name='phone_code']").val(cust_phone_code);
                    cust_phone_number                       = MDnextResponse_array[8];
                    $(".formMain input[name='phone_number']").val(cust_phone_number);
                    $(".formMain input[name='title']").val(MDnextResponse_array[9]);
                    cust_first_name                         = MDnextResponse_array[10];
                    $(".formMain input[name='first_name']").val(cust_first_name);
                    cust_middle_initial                     = MDnextResponse_array[11];
                    $(".formMain input[name='middle_initial']").val(cust_middle_initial);
                    cust_last_name                          = MDnextResponse_array[12];
                    $(".formMain input[name='last_name']").val(cust_last_name);
                    $(".formMain input[name='address1']").val(MDnextResponse_array[13]);
                    $(".formMain input[name='address2']").val(MDnextResponse_array[14]);
                    $(".formMain input[name='address3']").val(MDnextResponse_array[15]);
                    $(".formMain input[name='city']").val(MDnextResponse_array[16]);
                    $(".formMain input[name='state']").val(MDnextResponse_array[17]);
                    $(".formMain input[name='province']").val(MDnextResponse_array[18]);
                    $(".formMain input[name='postal_code']").val(MDnextResponse_array[19]);
                    $(".formMain input[name='country_code']").val(MDnextResponse_array[20]);
                    $(".formMain input[name='gender']").val(MDnextResponse_array[21]);
                    $(".formMain input[name='date_of_birth']").val(MDnextResponse_array[22]);
                    $(".formMain input[name='alt_phone']").val(MDnextResponse_array[23]);
                    cust_email                              = MDnextResponse_array[24];
                    $(".formMain input[name='email']").val(cust_email);
                    $(".formMain input[name='security_phrase']").val(MDnextResponse_array[25]);
                    var REGcommentsNL = new RegExp("!N","g");
                    MDnextResponse_array[26] = MDnextResponse_array[26].replace(REGcommentsNL, "\n");
                    $(".formMain input[name='comments']").val(MDnextResponse_array[26]);
                    called_count                            = MDnextResponse_array[27];
                    previous_called_count                   = MDnextResponse_array[27];
                    previous_dispo                          = MDnextResponse_array[2];
                    CBentry_time                            = MDnextResponse_array[28];
                    CBcallback_time                         = MDnextResponse_array[29];
                    CBuser                                  = MDnextResponse_array[30];
                    CBcomments                              = MDnextResponse_array[31];
                    dialed_number                           = MDnextResponse_array[32];
                    dialed_label                            = MDnextResponse_array[33];
                    source_id                               = MDnextResponse_array[34];
                    $(".formMain input[name='rank']").val(MDnextResponse_array[35]);
                    $(".formMain input[name='owner']").val(MDnextResponse_array[36]);
                    Call_Script_ID                          = MDnextResponse_array[37];
                    script_recording_delay                  = MDnextResponse_array[38];
                    Call_XC_a_Number                        = MDnextResponse_array[39];
                    Call_XC_b_Number                        = MDnextResponse_array[40];
                    Call_XC_c_Number                        = MDnextResponse_array[41];
                    Call_XC_d_Number                        = MDnextResponse_array[42];
                    Call_XC_e_Number                        = MDnextResponse_array[43];
                    entry_list_id                           = MDnextResponse_array[44];
                    custom_field_names                      = MDnextResponse_array[45];
                    custom_field_values                     = MDnextResponse_array[46];
                    custom_field_types                      = MDnextResponse_array[47];
                    list_webform                            = MDnextResponse_array[48];
                    list_webform_two                        = MDnextResponse_array[49];
                    post_phone_time_diff_alert_message      = MDnextResponse_array[50];

                    timer_action = campaign_timer_action;
                    timer_action_message = campaign_timer_action_message;
                    timer_action_seconds = campaign_timer_action_seconds;
                    timer_action_destination = campaign_timer_action_destination;
                    
                    lead_dial_number = dialed_number;
                    dispnum = dialed_number;
                    var status_display_number = phone_number_format(dispnum);
                    var status_display_content = '';
                    if (status_display_CALLID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('uid')?>: " + MDnextCID;}
                    if (status_display_LEADID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('lead_id')?>: " + $(".formMain input[name='lead_id']").val();}
                    if (status_display_LISTID > 0) {status_display_content = status_display_content + "<br><?=$lh->translationFor('list_id')?>: " + $(".formMain input[name='list_id']").val();}
                    
                    $("#MainStatusSpan").html("<b><?=$lh->translationFor('calling')?>:</b> " + status_display_number + " " + status_display_content + "<br>" + man_status);
                    
                    LeadDispo = '';
                    
                    VDIC_web_form_address = web_form_address
                    VDIC_web_form_address_two = web_form_address_two
                    if (list_webform.length > 5) {VDIC_web_form_address = list_webform;}
                    if (list_webform_two.length > 5) {VDIC_web_form_address_two = list_webform_two;}

                    var regWFAcustom = new RegExp("^VAR","ig");
                    if (VDIC_web_form_address.match(regWFAcustom)) {
                        TEMP_VDIC_web_form_address = URLDecode(VDIC_web_form_address,'YES','CUSTOM');
                        TEMP_VDIC_web_form_address = TEMP_VDIC_web_form_address.replace(regWFAcustom, '');
                    } else {
                        TEMP_VDIC_web_form_address = URLDecode(VDIC_web_form_address,'YES','DEFAULT','1');
                    }

                    if (VDIC_web_form_address_two.match(regWFAcustom)) {
                        TEMP_VDIC_web_form_address_two = URLDecode(VDIC_web_form_address_two,'YES','CUSTOM');
                        TEMP_VDIC_web_form_address_two = TEMP_VDIC_web_form_address_two.replace(regWFAcustom, '');
                    } else {
                        TEMP_VDIC_web_form_address_two = URLDecode(VDIC_web_form_address_two,'YES','DEFAULT','2');
                    }
                    
                    if (TEMP_VDIC_web_form_address.length > 0) {
                        //document.getElementById("WebFormSpan").innerHTML = "<a href=\"" + TEMP_VDIC_web_form_address + "\" target=\"" + web_form_target + "\" onMouseOver=\"WebFormRefresH();\" style=\"font-size:13px;color:white;text-decoration:none;\"><?=$lang['web_form']?></a>";
                    }
                    
                    if (enable_second_webform > 0) {
                        //document.getElementById("WebFormSpanTwo").innerHTML = "<a href=\"" + TEMP_VDIC_web_form_address_two + "\" target=\"" + web_form_target + "\" onMouseOver=\"WebFormTwoRefresH();\" style=\"font-size:13px;color:white;text-decoration:none;\" /><?=$lang['web_form_two']?></a>";
                    }

                    if (CBentry_time.length > 2) {
                        //document.getElementById("CusTInfOSpaN").innerHTML = " <b> <?=$lang['previous_callback']?> </b>";
                        //document.getElementById("CusTInfOSpaN").style.background = CusTCB_bgcolor;
                        //document.getElementById("CBcommentsBoxA").innerHTML = "<b><?=$lang['last_call']?>: </b>" + CBentry_time;
                        //document.getElementById("CBcommentsBoxB").innerHTML = "<b><?=$lang['callback']?>: </b>" + CBcallback_time;
                        //document.getElementById("CBcommentsBoxC").innerHTML = "<b><?=$lang['agent']?>: </b>" + CBuser;
                        //document.getElementById("CBcommentsBoxD").innerHTML = "<b><?=$lang['comments']?>: </b><br />" + CBcomments;
                        //showDiv('CBcommentsBox');
                    }

                    if (post_phone_time_diff_alert_message.length > 10) {
                        //document.getElementById("post_phone_time_diff_span_contents").innerHTML = " &nbsp; &nbsp; " + post_phone_time_diff_alert_message + "<br />";
                        //showDiv('post_phone_time_diff_span');
                    }

                    if ($("#LeadPreview").prop('checked') == false) {
                        reselect_preview_dial = 0;
                        MD_channel_look = 1;
                        custchannellive = 1;

                        toggleButton('DialHangup', 'hangup');

                        if ( (LIVE_campaign_recording == 'ALLCALLS') || (LIVE_campaign_recording == 'ALLFORCE') )
                            {all_record = 'YES';}

                        if ( (view_scripts == 1) && (campaign_script.length > 0) ) {
                            var SCRIPT_web_form = 'http://127.0.0.1/testing.php';
                            var TEMP_SCRIPT_web_form = URLDecode(SCRIPT_web_form,'YES','DEFAULT','1');
                            //$("#ScriptButtonSpan").html("<a href=\"#\" id=\"ScriptButtonSpan\" onClick=\"ScriptPanelToFront();\" style=\"font-size:13px;color:white;text-decoration:none;\"><?=ucwords($lh->translationFor('script'))?></a> <!-- <A HREF=\"#\" onClick=\"ScriptPanelToFront();\"><IMG SRC=\"./images/script_tab.png\" ALT=\"<?=$lh->translationFor('script')?>\" WIDTH=143 HEIGHT=27 BORDER=0></A>-->");

                            if ( (script_recording_delay > 0) && ( (LIVE_campaign_recording == 'ALLCALLS') || (LIVE_campaign_recording == 'ALLFORCE') ) ) {
                                delayed_script_load = 'YES';
                                //RefresHScript('CLEAR');
                            } else {
                                //load_script_contents();
                            }
                        }

                        if (custom_fields_enabled > 0) {
                            $("#CustomFormSpan").html(" <a href=\"#\" id=\"CustomFormSpan\" onclick=\"FormPanelToFront();\"  style=\"font-size:13px;color:white;text-decoration:none;\" /><?=ucwords($lh->translationFor('custom_form'))?></a>");  
                            //FormContentsLoad();
                        }

                        if (email_enabled > 0 && EMAILgroupCOUNT > 0) {
                            EmailContentsLoad();
                        }
                        if (get_call_launch == 'SCRIPT') {
                            if (delayed_script_load == 'YES') {
                                //load_script_contents();
                            }
                            //ScriptPanelToFront();
                        }

                        if (get_call_launch == 'FORM') {
                            //FormPanelToFront();
                        }

                        if (get_call_launch == 'EMAIL') {
                            EmailPanelToFront();
                        }

                        if (get_call_launch == 'WEBFORM') {
                            //window.open(TEMP_VDIC_web_form_address, web_form_target, 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=640,height=450');
                        }
                        if (get_call_launch == 'WEBFORMTWO') {
                            //window.open(TEMP_VDIC_web_form_address_two, web_form_target, 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=640,height=450');
                        }
                    } else {
                        if (custom_fields_enabled > 0) {
                            $("#CustomFormSpan").html(" <a href=\"#\" id=\"CustomFormSpan\" onclick=\"FormPanelToFront();\"  style=\"font-size:13px;color:white;text-decoration:none;\" /><?=ucwords($lh->translationFor('custom_form'))?></a>");
                            //FormContentsLoad();
                        }
                        if ( (view_scripts == 1) && (campaign_script.length > 0) ) {
                            var SCRIPT_web_form = 'http://127.0.0.1/testing.php';
                            var TEMP_SCRIPT_web_form = URLDecode(SCRIPT_web_form,'YES','DEFAULT','1');
                            //RefresHScript();
                        }
                        reselect_preview_dial = 1;
                    }
                }
            }
        });
    }
}

function AutoDial_Resume_Pause(taskaction, taskagentlog, taskwrapup, taskstatuschange, temp_reason, temp_auto, temp_auto_code) {
    var add_pause_code = '';
    if (taskaction == 'VDADready') {
        VDRP_stage = 'READY';
        var APIaction = 'RESUME';
        if (INgroupCOUNT > 0) {
            if (closer_blended == 0)
                {VDRP_stage = 'CLOSER';}
            else
                {VDRP_stage = 'READY';}
        }
        AutoDialReady = 1;
        AutoDialWaiting = 1;
        if (dial_method == "INBOUND_MAN") {
            auto_dial_level = starting_dial_level;

            toggleButton('ResumePause', 'pause');
            toggleButton('DialHangup', 'dial');
        } else {
            toggleButton('ResumePause', 'pause');
            toggleButton('DialHangup', 'dial', false);
        }
    } else {
        VDRP_stage = 'PAUSED';
        var APIaction = 'PAUSE';
        AutoDialReady = 0;
        AutoDialWaiting = 0;
        pause_code_counter = 0;
        if (dial_method == "INBOUND_MAN") {
            auto_dial_level = starting_dial_level;

            toggleButton('ResumePause', 'resume');
            toggleButton('DialHangup', 'dial');
        } else {
            toggleButton('ResumePause', 'resume');
            toggleButton('DialHangup', 'dial');
        }

        if ( (agent_pause_codes_active=='FORCE') && (temp_reason != 'LOGOUT') && (temp_reason != 'REQUEUE') && (temp_reason != 'DIALNEXT') && (temp_auto != '1') ) {
            //PauseCodeSelectContent_create();
        }

        if (temp_auto == '1') {
            add_pause_code = temp_auto_code;
        }
    }
    
    activateLinks();
    
    var postData = {
        goAction: 'goAutodialResumePause',
        goUser: uName,
        goPass: uPass,
        goCampaign: campaign,
        goAgentLogID: agent_log_id,
        goServerIP: server_ip,
        goSessionName: session_name,
        goTask: taskaction,
        goStage: VDRP_stage,
        goAgentLog: taskagentlog,
        goWrapUp: taskwrapup,
        goDialMethod: dial_method,
        goComments: taskstatuschange,
        goSubStatus: add_pause_code,
        goQMExtension: qm_extension,
        responsetype: 'json'
    };
        
    $.ajax({
        type: 'POST',
        url: '<?=$goAPI?>/goAgent/goAPI.php',
        processData: true,
        data: postData,
        dataType: "json"
    })
    .done(function (result) {
        if (result.result == 'error') {
            return 0;
        } else {
            agent_log_id = result.data.agent_log_id;
        }
    });

    waiting_on_dispo = 0;
    return agent_log_id;
}

function ManualDialCall(TVfast, TVphone_code, TVphone_number, TVlead_id, TVtype) {
    var move_on = 1;
    if ( (AutoDialWaiting == 1) || (live_customer_call == 1) || (alt_dial_active == 1) || (MD_channel_look == 1) || (in_lead_preview_state == 1) ) {
        if ((auto_pause_precall == 'Y') && ( (agent_pause_codes_active == 'Y') || (agent_pause_codes_active == 'FORCE') ) && (AutoDialWaiting == 1) && (live_customer_call != 1) && (alt_dial_active != 1) && (MD_channel_look != 1) && (in_lead_preview_state != 1) ) {
            agent_log_id = AutoDial_Resume_Pause("VDADpause", '', '', '', '', '1', auto_pause_precall_code);
        } else {
            move_on = 0;
            alert("<?=$lh->translationFor('error')?>: <?=$lh->translationFor('must_be_paused_to_dial_manually')?>.");
        }
    }
    if (move_on == 1) {
        if (TVfast == 'FAST') {
            NewManualDialCallFast();
        } else {
            if (TVfast == 'CALLLOG') {
                //hideDiv('CalLLoGDisplaYBox');
                //hideDiv('SearcHForMDisplaYBox');
                //hideDiv('SearcHResultSDisplaYBox');
                //hideDiv('LeaDInfOBox');
                $("#MDDiaLCodE").val(TVphone_code);
                $("#MDPhonENumbeR").val(TVphone_number);
                $("#MDPhonENumbeRHiddeN").val(TVphone_number);
                $("#MDLeadID").val(TVlead_id);
                $("#MDType").val(TVtype);
                if (disable_alter_custphone == 'HIDE')
                    {$("#MDPhonENumbeR").val('XXXXXXXXXX');}
            }
            if (TVfast == 'LEADSEARCH') {
                //hideDiv('SearcHForMDisplaYBox');
                //hideDiv('SearcHResultSDisplaYBox');
                //hideDiv('LeaDInfOBox');
                $("#MDDiaLCodE").val(TVphone_code);
                $("#MDPhonENumbeR").val(TVphone_number);
                $("#MDLeadID").val(TVlead_id);
                $("#MDType").val(TVtype);
            }
            if (agent_allow_group_alias == 'Y') {
                $("#ManuaLDiaLGrouPSelecteD").html('Group Alias: ' + active_group_alias);
                $("#ManuaLDiaLGrouP").html('<a href="#" onclick="GroupAliasSelectContent_create(0);"><?=$lh->translationFor('choose_group_alias')?></a>');
            }
            if (in_group_dial_display > 0) {
                $("#ManuaLDiaLInGrouPSelecteD").html('Dial Ingroup: ' + active_ingroup_dial);
                $("#ManuaLDiaLInGrouP").html('<a href="#" onclick="ManuaLDiaLInGrouPSelectContent_create(0);"><?=$lh->translationFor('choose_dial_ingroup')?></a>');
            }
            if ( (in_group_dial == 'BOTH') || (in_group_dial == 'NO_DIAL') ) {
                nocall_dial_flag = 'DISABLED';
                $("#NoDiaLSelecteD").html('<?=$lh->translationFor('no_call_dial')?>: ' + nocall_dial_flag + ' &nbsp; &nbsp; <a href="#" onclick="NoDiaLSwitcH();"><?=$lh->translationFor('click_to_activate')?></a>');
            }
            //showDiv('NeWManuaLDiaLBox');

            $("#search_phone_number").val('');
            $("#search_lead_id").val('');
            $("#search_vendor_lead_code").val('');
            $("#search_first_name").val('');
            $("#search_last_name").val('');
            $("#search_city").val('');
            $("#search_state").val('');
            $("#search_postal_code").val('');
        }
    }
}


// ################################################################################
// Send Hangup command for 3rd party call connected to the conference now to Manager
function XFerCallHangup() {
    var xferchannel = $("#xferchannel").val();
    var xfer_channel = lastxferchannel;
    var process_post_hangup = 0;
    xfer_in_call = 0;
    if ( (MD_channel_look == 1) && (leaving_threeway < 1) ) {
        MD_channel_look=0;
        DialTimeHangup('XFER');
    }
    if (xferchannel.length > 3) {
        var queryCID = "HXvdcW" + epoch_sec + user_abb;
        var hangupvalue = xfer_channel;
 
        var postData = {
            goAction: 'goHangupCall',
            goUser: uName,
            goPass: uPass,
            goCampaign: campaign,
            goLogCampaign: campaign,
            goChannel: hangupvalue,
            goServerIP: server_ip,
            goSessionName: session_name,
            goQueryCID: queryCID,
            goQMExtension: qm_extension,
            responsetype: 'json'
        };
            
        $.ajax({
            type: 'POST',
            url: '<?=$goAPI?>/goAgent/goAPI.php',
            processData: true,
            data: postData,
            dataType: "json"
        })
        .done(function (result) {
                //alert(result.message);
        });
        process_post_hangup = 1;
    } else {
        process_post_hangup = 1;
    }
    
    if (process_post_hangup == 1) {
        XD_live_customer_call = 0;
        XD_live_call_seconds = 0;
        MD_ring_seconds = 0;
        MD_channel_look = 0;
        XDnextCID = '';
        XDcheck = '';
        xferchannellive = 0;
        consult_custom_wait = 0;
        consult_custom_go = 0;
        consult_custom_sent = 0;


    //  DEACTIVATE CHANNEL-DEPENDANT BUTTONS AND VARIABLES
        $("#xferchannel").val('');
        lastxferchannel = '';

        //document.getElementById("Leave3WayCall").innerHTML = "<input type=\"button\" value=\" <?=$lang['leave_3way_call']?> \" style=\"font-size:10px;width:150px;vertical-align:middle;\" disabled />";

        //document.getElementById("DialWithCustomer").innerHTML ="<input type=\"button\" onclick=\"SendManualDial('YES');return false;\" value=\" <?=$lang['dial_with_customer']?> \" style=\"font-size:10px;width:150px;vertical-align:middle;\" />";

        //document.getElementById("ParkCustomerDial").innerHTML ="<input type=\"button\" onclick=\"xfer_park_dial();return false;\" value=\" <?=$lang['park_customer_dial']?> \" style=\"font-size:10px;width:150px;vertical-align:middle;\" />";

        //document.getElementById("HangupXferLine").innerHTML ="<input type=\"button\" value=\" <?=$lang['hangup_xfer_lines']?> \" style=\"font-size:10px;width:150px;vertical-align:middle;\" disabled />";

        //document.getElementById("HangupBothLines").innerHTML ="<input type=\"button\" onclick=\"bothcall_send_hangup();return false;\" value=\" <?=$lang['hangup_both_lines']?> \" style=\"font-size:10px;width:150px;vertical-align:middle;\" />";
        
        activateLinks();
    }
}


// ################################################################################
// Send Hangup command for any Local call that is not in the quiet(7) entry - used to stop manual dials even if no connect
function DialTimeHangup(tasktypecall) {
    if ( (RedirectXFER < 1) && (leaving_threeway < 1) ) {
        //alert("RedirecTxFEr|" + RedirecTxFEr);
        var queryCID = "HTvdcW" + epoch_sec + user_abb;
        var postData = {
            goAction: 'goHangupConfDial',
            goUser: uName,
            goPass: uPass,
            goCampaign: campaign,
            goExten: session_id,
            goServerIP: server_ip,
            goSessionName: session_name,
            goExtContext: ext_context,
            goQueryCID: queryCID,
            goQMExtension: qm_extension,
            responsetype: 'json'
        };
            
        $.ajax({
            type: 'POST',
            url: '<?=$goAPI?>/goAgent/goAPI.php',
            processData: true,
            data: postData,
            dataType: "json"
        })
        .done(function (result) {
            //alert(result.message + "\n" + tasktypecall + "\n" + leaving_threeway);
        });
    }
}


// ################################################################################
// Start Hangup Functions for both 
function BothCallHangup() {
    if (lastcustchannel.length > 3)
        {DialedCallHangup();}
    if (lastxferchannel.length > 3)
        {XFerCallHangup();}
}



// ################################################################################
// Finish the wrapup timer early
function TimerActionRun(taskaction, taskdialalert) {
    var next_action = 0;
    if (taskaction == 'DialAlert') {
        //document.getElementById("TimerContentSpan").innerHTML = "<b><?=$lh->translationFor('dial_alert')?>:<br /><br />" + taskdialalert.replace("\n","<br />") + "</b>";

        //showDiv('TimerSpan');
    } else {
        if ( (timer_action_message.length > 0) || (timer_action == 'MESSAGE_ONLY') ) {
            //document.getElementById("TimerContentSpan").innerHTML = "<b><?=$lh->translationFor('timer_notification')?>: " + timer_action_seconds + " <?=$lang['seconds']?><br /><br />" + timer_action_message + "</b>";

            //showDiv('TimerSpan');
        }

        if (timer_action == 'WEBFORM') {
            //WebFormRefresH('NO','YES');
            //window.open(TEMP_VDIC_web_form_address, web_form_target, 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=640,height=450');
        }
        if (timer_action == 'WEBFORM2') {
            //WebFormTwoRefresH('NO','YES');
            //window.open(TEMP_VDIC_web_form_address_two, web_form_target, 'toolbar=1,scrollbars=1,location=1,statusbar=1,menubar=1,resizable=1,width=640,height=450');
        }
        if (timer_action == 'D1_DIAL') {
            //DtMf_PreSet_a_DiaL();
        }
        if (timer_action == 'D2_DIAL') {
            //DtMf_PreSet_b_DiaL();
        }
        if (timer_action == 'D3_DIAL') {
            //DtMf_PreSet_c_DiaL();
        }
        if (timer_action == 'D4_DIAL') {
            //DtMf_PreSet_d_DiaL();
        }
        if (timer_action == 'D5_DIAL') {
            //DtMf_PreSet_e_DiaL();
        }
        if (timer_action == 'D1_DIAL_QUIET') {
            //DtMf_PreSet_a_DiaL('YES');
        }
        if (timer_action == 'D2_DIAL_QUIET') {
            //DtMf_PreSet_b_DiaL('YES');
        }
        if (timer_action == 'D3_DIAL_QUIET') {
            //DtMf_PreSet_c_DiaL('YES');
        }
        if (timer_action == 'D4_DIAL_QUIET') {
            //DtMf_PreSet_d_DiaL('YES');
        }
        if (timer_action == 'D5_DIAL_QUIET') {
            //DtMf_PreSet_e_DiaL('YES');
        }
        if ( (timer_action == 'HANGUP') && (live_customer_call == 1) ) {
            //hangup_timer_xfer();
        }
        if ( (timer_action == 'EXTENSION') && (live_customer_call == 1) && (timer_action_destination.length > 0) ) {
            //extension_timer_xfer();
        }
        if ( (timer_action == 'CALLMENU') && (live_customer_call == 1) && (timer_action_destination.length > 0) ) {
            //callmenu_timer_xfer();
        }
        if ( (timer_action == 'IN_GROUP') && (live_customer_call == 1) && (timer_action_destination.length > 0) ) {
            //ingroup_timer_xfer();
        }
        if (timer_action_destination.length > 0) {
            var regNS = new RegExp("nextstep---","ig");
            if (timer_action_destination.match(regNS)) {
                next_action = 1;
                timer_action = 'NONE';
                var next_action_array = timer_action_destination.split("nextstep---");
                var next_action_details_array = next_action_array[1].split("--");
                timer_action = next_action_details_array[0];
                timer_action_seconds = parseInt(next_action_details_array[1]);
                timer_action_seconds = (timer_action_seconds + live_call_seconds);
                timer_action_destination = next_action_details_array[2];
                timer_action_message = next_action_details_array[3];
                //alert("NEXT: " + timer_action + '|' + timer_action_message + '|' + timer_action_seconds + '|' + timer_action_destination + '|');
            }
        }
    }

    if (next_action < 1)
        {timer_action = 'NONE';}	
}

function NoneInSession() {
    //still on development
}


// ################################################################################
// zero-pad numbers or chop them to get to the desired length
function set_length(SLnumber, SLlength_goal, SLdirection) {
	var SLnumber = SLnumber + '';
	var begin_point = 0;
	var number_length = SLnumber.length;
	if (number_length > SLlength_goal) {
		if (SLdirection == 'right') {
			begin_point = (number_length - SLlength_goal);
			SLnumber = SLnumber.substr(begin_point, SLlength_goal);
		} else {
			SLnumber = SLnumber.substr(0,SLlength_goal);
        }
    }
    //alert(SLnumber + '|' + SLlength_goal + '|' + begin_point + '|' + SLdirection + '|' + SLnumber.length + '|' + number_length);
	var result = SLnumber + '';
	while(result.length < SLlength_goal) {
		result = "0" + result;
	}
	return result;
}


// decode the scripttext and scriptname so that it can be displayed
function URLDecode(encodedvar, scriptformat, urlschema, webformnumber) {
    // Replace %ZZ with equivalent character
    // Put [ERR] in output if %ZZ is invalid.
	var HEXCHAR = '0123456789ABCDEFabcdef'; 
	var encoded = encodedvar;
	var decoded = '';
	var web_form_varsX = '';
	var i = 0;
	var RGnl = new RegExp("[\\r]\\n","g");
	var RGtab = new RegExp("\t","g");
	var RGplus = new RegExp(" |\\t|\\n","g");
	var RGiframe = new RegExp("iframe","gi");
	var regWF = new RegExp("\\`|\\~|\\:|\\;|\\#|\\'|\\\"|\\{|\\}|\\(|\\)|\\*|\\^|\\%|\\$|\\!|\\%|\\r|\\t|\\n","ig");

	var xtest;
	xtest = unescape(encoded);
	encoded = utf8_decode(xtest);

	if (urlschema == 'DEFAULT') {
		web_form_varsX = 
		"&lead_id=" + $(".formMain input[name='lead_id']").val() + 
		"&vendor_id=" + $(".formMain input[name='vendor_lead_code']").val() + 
		"&list_id=" + $(".formMain input[name='list_id']").val() + 
		"&gmt_offset_now=" + $(".formMain input[name='gmt_offset_now']").val() + 
		"&phone_code=" + $(".formMain input[name='phone_code']").val() + 
		"&phone_number=" + $(".formMain input[name='phone_number']").val() + 
		"&title=" + $(".formMain input[name='title']").val() + 
		"&first_name=" + $(".formMain input[name='first_name']").val() + 
		"&middle_initial=" + $(".formMain input[name='middle_initial']").val() + 
		"&last_name=" + $(".formMain input[name='last_name']").val() + 
		"&address1=" + $(".formMain input[name='address1']").val() + 
		"&address2=" + $(".formMain input[name='address2']").val() + 
		"&address3=" + $(".formMain input[name='address3']").val() + 
		"&city=" + $(".formMain input[name='city']").val() + 
		"&state=" + $(".formMain input[name='state']").val() + 
		"&province=" + $(".formMain input[name='province']").val() + 
		"&postal_code=" + $(".formMain input[name='postal_code']").val() + 
		"&country_code=" + $(".formMain input[name='country_code']").val() + 
		"&gender=" + $(".formMain input[name='gender']").val() + 
		"&date_of_birth=" + $(".formMain input[name='date_of_birth']").val() + 
		"&alt_phone=" + $(".formMain input[name='alt_phone']").val() + 
		"&email=" + $(".formMain input[name='email']").val() + 
		"&security_phrase=" + $(".formMain input[name='security_phrase']").val() + 
		"&comments=" + $(".formMain input[name='comments']").val() + 
		"&user=" + user + 
		"&pass=" + pass + 
		"&campaign=" + campaign + 
		"&phone_login=" + phone_login + 
		"&original_phone_login=" + original_phone_login +
		"&phone_pass=" + phone_pass + 
		"&fronter=" + fronter + 
		"&closer=" + user + 
		"&group=" + group + 
		"&channel_group=" + group + 
		"&SQLdate=" + SQLdate + 
		"&epoch=" + UnixTime + 
		"&uniqueid=" + $(".formMain input[name='uniqueid']").val() + 
		"&customer_zap_channel=" + lastcustchannel + 
		"&customer_server_ip=" + lastcustserverip +
		"&server_ip=" + server_ip + 
		"&SIPexten=" + extension + 
		"&session_id=" + session_id + 
		"&phone=" + $(".formMain input[name='phone_number']").val() + 
		"&parked_by=" + $(".formMain input[name='lead_id']").val() +
		"&dispo=" + LeadDispo + '' +
		"&dialed_number=" + dialed_number + '' +
		"&dialed_label=" + dialed_label + '' +
		"&source_id=" + source_id + '' +
		"&rank=" + $(".formMain input[name='rank']").val() + '' +
		"&owner=" + $(".formMain input[name='owner']").val() + '' +
		"&camp_script=" + campaign_script + '' +
		"&in_script=" + Call_Script_ID + '' +
		"&fullname=" + LOGfullname + '' +
		"&recording_filename=" + recording_filename + '' +
		"&recording_id=" + recording_id + '' +
		"&user_custom_one=" + user_custom_one + '' +
		"&user_custom_two=" + user_custom_two + '' +
		"&user_custom_three=" + user_custom_three + '' +
		"&user_custom_four=" + user_custom_four + '' +
		"&user_custom_five=" + user_custom_five + '' +
		"&preset_number_a=" + Call_XC_a_Number + '' +
		"&preset_number_b=" + Call_XC_b_Number + '' +
		"&preset_number_c=" + Call_XC_c_Number + '' +
		"&preset_number_d=" + Call_XC_d_Number + '' +
		"&preset_number_e=" + Call_XC_e_Number + '' +
		"&preset_dtmf_a=" + Call_XC_a_DTMF + '' +
		"&preset_dtmf_b=" + Call_XC_b_DTMF + '' +
		"&did_id=" + did_id + '' +
		"&did_extension=" + did_extension + '' +
		"&did_pattern=" + did_pattern + '' +
		"&did_description=" + did_description + '' +
		"&closecallid=" + closecallid + '' +
		"&xfercallid=" + xfercallid + '' +
		"&agent_log_id=" + agent_log_id + '' +
		"&entry_list_id=" + $(".formMain input[name='entry_list_id']").val() + '' +
		"&call_id=" + LastCID + '' +
		"&user_group=" + user_group + '' +
		"&web_vars=" + LIVE_web_vars + '' +
		webform_session;
		
		if (custom_field_names.length > 2) {
			var url_custom_field = '';
			var CFN_array = custom_field_names.split('|');
			var CFN_count = CFN_array.length;
			var CFN_tick = 0;
			while (CFN_tick < CFN_count) {
				var CFN_field = CFN_array[CFN_tick];
				if (CFN_field.length > 0) {
					url_custom_field = url_custom_field + "&" + CFN_field + "=--A--" + CFN_field + "--B--";
				}
				CFN_tick++;
			}
			if (url_custom_field.length > 10) {
				url_custom_field = '&CF_uses_custom_fields=Y' + url_custom_field;
			}
			web_form_varsX = web_form_varsX + '' + url_custom_field;
			scriptformat = 'YES';
		}

		web_form_varsX = web_form_varsX.replace(RGplus, '+');
		web_form_varsX = web_form_varsX.replace(RGnl, '+');
		web_form_varsX = web_form_varsX.replace(regWF, '');

		var regWFAvars = new RegExp("\\?","ig");
		if (encoded.match(regWFAvars))
			{web_form_varsX = '&' + web_form_varsX}
		else
			{web_form_varsX = '?' + web_form_varsX}

		var TEMPX_VDIC_web_form_address = encoded + "" + web_form_varsX;

		var regWFAqavars = new RegExp("\\?&","ig");
		var regWFAaavars = new RegExp("&&","ig");
		TEMPX_VDIC_web_form_address = TEMPX_VDIC_web_form_address.replace(regWFAqavars, '?');
		TEMPX_VDIC_web_form_address = TEMPX_VDIC_web_form_address.replace(regWFAaavars, '&');
		encoded = TEMPX_VDIC_web_form_address;
	}
	if (scriptformat == 'YES') {
		// custom fields populate if lead information is sent with custom field names
		if (custom_field_names.length > 2) {
			var CFN_array = custom_field_names.split('|');
			var CFV_array = custom_field_values.split('----------');
			var CFT_array = custom_field_types.split('|');
			var CFN_count = CFN_array.length;
			var CFN_tick = 0;
			var CFN_debug = '';
			var CF_loaded = $(".formMain input[name='FORM_LOADED']").val();
		//	alert(custom_field_names + "\n" + custom_field_values + "\n" + CFN_count + "\n" + CF_loaded);
			while (CFN_tick < CFN_count) {
				var CFN_field = CFN_array[CFN_tick];
				var RG_CFN_field = new RegExp("--A--" + CFN_field + "--B--","g");
				if ( (CFN_field.length > 0) && (encoded.match(RG_CFN_field)) ) {
					if (CF_loaded == '1') {
						var CFN_value = '';
						var field_parsed=0;
						if ( (CFT_array[CFN_tick]=='TIME') && (field_parsed < 1) ) {
							var CFN_field_hour = 'HOUR_' + CFN_field;
							var cIndex_hour = vcFormIFrame.document.form_custom_fields[CFN_field_hour].selectedIndex;
							var CFN_value_hour =  vcFormIFrame.document.form_custom_fields[CFN_field_hour].options[cIndex_hour].value;
							var CFN_field_minute = 'MINUTE_' + CFN_field;
							var cIndex_minute = vcFormIFrame.document.form_custom_fields[CFN_field_minute].selectedIndex;
							var CFN_value_minute =  vcFormIFrame.document.form_custom_fields[CFN_field_minute].options[cIndex_minute].value;
							var CFN_value = CFN_value_hour + ':' + CFN_value_minute + ':00'
							field_parsed=1;
						}
						if ( (CFT_array[CFN_tick]=='SELECT') && (field_parsed < 1) ) {
							var cIndex = vcFormIFrame.document.form_custom_fields[CFN_field].selectedIndex;
							var CFN_value =  vcFormIFrame.document.form_custom_fields[CFN_field].options[cIndex].value;
							field_parsed=1;
						}
						if ( (CFT_array[CFN_tick]=='MULTI') && (field_parsed < 1) ) {
							var chosen = '';
							var CFN_field = CFN_field + '[]';
							for (i=0; i < vcFormIFrame.document.form_custom_fields[CFN_field].options.length; i++) {
								if (vcFormIFrame.document.form_custom_fields[CFN_field].options[i].selected) {
									chosen = chosen + '' + vcFormIFrame.document.form_custom_fields[CFN_field].options[i].value + ',';
                                }
                            }
							var CFN_value = chosen;
							if (CFN_value.length > 0) {CFN_value = CFN_value.slice(0,-1);}
							field_parsed=1;
							}
						if ( ( (CFT_array[CFN_tick]=='RADIO') || (CFT_array[CFN_tick]=='CHECKBOX') ) && (field_parsed < 1) )
							{
							var chosen = '';
							var CFN_field = CFN_field + '[]';
							var len = vcFormIFrame.document.form_custom_fields[CFN_field].length;
							for (i = 0; i < len; i++) {
								if (vcFormIFrame.document.form_custom_fields[CFN_field][i].checked) {
									chosen = chosen + '' + vcFormIFrame.document.form_custom_fields[CFN_field][i].value + ',';
                                }
                            }
							var CFN_value = chosen;
							if (CFN_value.length > 0) {CFN_value = CFN_value.slice(0,-1);}
							field_parsed = 1;
						}
						if (field_parsed < 1) {
							var CFN_value = vcFormIFrame.document.form_custom_fields[CFN_field].value;
							field_parsed=1;
                        }
                    } else {
						var CFN_value = CFV_array[CFN_tick];
					}
					CFN_value = CFN_value.replace(RGnl,'+');
					CFN_value = CFN_value.replace(RGtab,'+');
					CFN_value = CFN_value.replace(RGplus,'+');
					encoded = encoded.replace(RG_CFN_field, CFN_value);
					web_form_varsX = web_form_varsX.replace(RG_CFN_field, CFN_value);
					CFN_debug = CFN_debug + '|' + CFN_field + '-' + CFN_value;
				}
				CFN_tick++;
			}
//			document.getElementById("debugbottomspan").innerHTML = CFN_debug;
		}

		if (webformnumber == '1')
			{web_form_vars = web_form_varsX;}
		if (webformnumber == '2')
			{web_form_vars_two = web_form_varsX;}

		var SCvendor_lead_code = $(".formMain input[name='vendor_lead_code']").val();
		var SCsource_id = source_id;
		var SClist_id = $(".formMain input[name='list_id']").val();
		var SCgmt_offset_now = $(".formMain input[name='gmt_offset_now']").val();
		var SCcalled_since_last_reset = "";
		var SCphone_code = $(".formMain input[name='phone_code']").val();
		var SCphone_number = $(".formMain input[name='phone_number']").val();
		var SCtitle = $(".formMain input[name='title']").val();
		var SCfirst_name = $(".formMain input[name='first_name']").val();
		var SCmiddle_initial = $(".formMain input[name='middle_initial']").val();
		var SClast_name = $(".formMain input[name='last_name']").val();
		var SCaddress1 = $(".formMain input[name='address1']").val();
		var SCaddress2 = $(".formMain input[name='address2']").val();
		var SCaddress3 = $(".formMain input[name='address3']").val();
		var SCcity = $(".formMain input[name='city']").val();
		var SCstate = $(".formMain input[name='state']").val();
		var SCprovince = $(".formMain input[name='province']").val();
		var SCpostal_code = $(".formMain input[name='postal_code']").val();
		var SCcountry_code = $(".formMain input[name='country_code']").val();
		var SCgender = $(".formMain input[name='gender']").val();
		var SCdate_of_birth = $(".formMain input[name='date_of_birth']").val();
		var SCalt_phone = $(".formMain input[name='alt_phone']").val();
		var SCemail = $(".formMain input[name='email']").val();
		var SCsecurity_phrase = $(".formMain input[name='security_phrase']").val();
		var SCcomments = $(".formMain input[name='comments']").val();
		var SCfullname = LOGfullname;
		var SCfronter = fronter;
		var SCuser = user;
		var SCpass = pass;
		var SClead_id = $(".formMain input[name='lead_id']").val();
		var SCcampaign = campaign;
		var SCphone_login = phone_login;
		var SCoriginal_phone_login = original_phone_login;
		var SCgroup = group;
		var SCchannel_group = group;
		var SCSQLdate = SQLdate;
		var SCepoch = UnixTime;
		var SCuniqueid = $(".formMain input[name='uniqueid']").val();
		var SCcustomer_zap_channel = lastcustchannel;
		var SCserver_ip = server_ip;
		var SCSIPexten = extension;
		var SCsession_id = session_id;
		var SCdispo = LeadDispo;
		var SCdialed_number = dialed_number;
		var SCdialed_label = dialed_label;
		var SCrank = $(".formMain input[name='rank']").val();
		var SCowner = $(".formMain input[name='owner']").val();
		var SCcamp_script = campaign_script;
		var SCin_script = Call_Script_ID;
		var SCrecording_filename = recording_filename;
		var SCrecording_id = recording_id;
		var SCuser_custom_one = user_custom_one;
		var SCuser_custom_two = user_custom_two;
		var SCuser_custom_three = user_custom_three;
		var SCuser_custom_four = user_custom_four;
		var SCuser_custom_five = user_custom_five;
		var SCpreset_number_a = Call_XC_a_Number;
		var SCpreset_number_b = Call_XC_b_Number;
		var SCpreset_number_c = Call_XC_c_Number;
		var SCpreset_number_d = Call_XC_d_Number;
		var SCpreset_number_e = Call_XC_e_Number;
		var SCpreset_dtmf_a = Call_XC_a_DTMF;
		var SCpreset_dtmf_b = Call_XC_b_DTMF;
		var SCdid_id = did_id;
		var SCdid_extension = did_extension;
		var SCdid_pattern = did_pattern;
		var SCdid_description = did_description;
		var SCclosecallid = closecallid;
		var SCxfercallid = xfercallid;
		var SCcall_id = LastCID;
		var SCuser_group = user_group;
		var SCagent_log_id = agent_log_id;
		var SCweb_vars = LIVE_web_vars;

		if (encoded.match(RGiframe)) {
			SCvendor_lead_code = SCvendor_lead_code.replace(RGplus,'+');
			SCsource_id = SCsource_id.replace(RGplus,'+');
			SClist_id = SClist_id.replace(RGplus,'+');
			SCgmt_offset_now = SCgmt_offset_now.replace(RGplus,'+');
			SCcalled_since_last_reset = SCcalled_since_last_reset.replace(RGplus,'+');
			SCphone_code = SCphone_code.replace(RGplus,'+');
			SCphone_number = SCphone_number.replace(RGplus,'+');
			SCtitle = SCtitle.replace(RGplus,'+');
			SCfirst_name = SCfirst_name.replace(RGplus,'+');
			SCmiddle_initial = SCmiddle_initial.replace(RGplus,'+');
			SClast_name = SClast_name.replace(RGplus,'+');
			SCaddress1 = SCaddress1.replace(RGplus,'+');
			SCaddress2 = SCaddress2.replace(RGplus,'+');
			SCaddress3 = SCaddress3.replace(RGplus,'+');
			SCcity = SCcity.replace(RGplus,'+');
			SCstate = SCstate.replace(RGplus,'+');
			SCprovince = SCprovince.replace(RGplus,'+');
			SCpostal_code = SCpostal_code.replace(RGplus,'+');
			SCcountry_code = SCcountry_code.replace(RGplus,'+');
			SCgender = SCgender.replace(RGplus,'+');
			SCdate_of_birth = SCdate_of_birth.replace(RGplus,'+');
			SCalt_phone = SCalt_phone.replace(RGplus,'+');
			SCemail = SCemail.replace(RGplus,'+');
			SCsecurity_phrase = SCsecurity_phrase.replace(RGplus,'+');
			SCcomments = SCcomments.replace(RGplus,'+');
			SCfullname = SCfullname.replace(RGplus,'+');
			SCfronter = SCfronter.replace(RGplus,'+');
			SCuser = SCuser.replace(RGplus,'+');
			SCpass = SCpass.replace(RGplus,'+');
			SClead_id = SClead_id.replace(RGplus,'+');
			SCcampaign = SCcampaign.replace(RGplus,'+');
			SCphone_login = SCphone_login.replace(RGplus,'+');
			SCoriginal_phone_login = SCoriginal_phone_login.replace(RGplus,'+');
			SCgroup = SCgroup.replace(RGplus,'+');
			SCchannel_group = SCchannel_group.replace(RGplus,'+');
			SCSQLdate = SCSQLdate.replace(RGplus,'+');
			SCuniqueid = SCuniqueid.replace(RGplus,'+');
			SCcustomer_zap_channel = SCcustomer_zap_channel.replace(RGplus,'+');
			SCserver_ip = SCserver_ip.replace(RGplus,'+');
			SCSIPexten = SCSIPexten.replace(RGplus,'+');
			SCdispo = SCdispo.replace(RGplus,'+');
			SCdialed_number = SCdialed_number.replace(RGplus,'+');
			SCdialed_label = SCdialed_label.replace(RGplus,'+');
			SCrank = SCrank.replace(RGplus,'+');
			SCowner = SCowner.replace(RGplus,'+');
			SCcamp_script = SCcamp_script.replace(RGplus,'+');
			SCin_script = SCin_script.replace(RGplus,'+');
			SCrecording_filename = SCrecording_filename.replace(RGplus,'+');
			SCrecording_id = SCrecording_id.replace(RGplus,'+');
			SCuser_custom_one = SCuser_custom_one.replace(RGplus,'+');
			SCuser_custom_two = SCuser_custom_two.replace(RGplus,'+');
			SCuser_custom_three = SCuser_custom_three.replace(RGplus,'+');
			SCuser_custom_four = SCuser_custom_four.replace(RGplus,'+');
			SCuser_custom_five = SCuser_custom_five.replace(RGplus,'+');
			SCpreset_number_a = SCpreset_number_a.replace(RGplus,'+');
			SCpreset_number_b = SCpreset_number_b.replace(RGplus,'+');
			SCpreset_number_c = SCpreset_number_c.replace(RGplus,'+');
			SCpreset_number_d = SCpreset_number_d.replace(RGplus,'+');
			SCpreset_number_e = SCpreset_number_e.replace(RGplus,'+');
			SCpreset_dtmf_a = SCpreset_dtmf_a.replace(RGplus,'+');
			SCpreset_dtmf_b = SCpreset_dtmf_b.replace(RGplus,'+');
			SCdid_id = SCdid_id.replace(RGplus,'+');
			SCdid_extension = SCdid_extension.replace(RGplus,'+');
			SCdid_pattern = SCdid_pattern.replace(RGplus,'+');
			SCdid_description = SCdid_description.replace(RGplus,'+');
			SCcall_id = SCcall_id.replace(RGplus,'+');
			SCuser_group = SCuser_group.replace(RGplus,'+');
			SCweb_vars = SCweb_vars.replace(RGplus,'+');
		}

		var RGvendor_lead_code = new RegExp("--A--vendor_lead_code--B--","g");
		var RGsource_id = new RegExp("--A--source_id--B--","g");
		var RGlist_id = new RegExp("--A--list_id--B--","g");
		var RGgmt_offset_now = new RegExp("--A--gmt_offset_now--B--","g");
		var RGcalled_since_last_reset = new RegExp("--A--called_since_last_reset--B--","g");
		var RGphone_code = new RegExp("--A--phone_code--B--","g");
		var RGphone_number = new RegExp("--A--phone_number--B--","g");
		var RGtitle = new RegExp("--A--title--B--","g");
		var RGfirst_name = new RegExp("--A--first_name--B--","g");
		var RGmiddle_initial = new RegExp("--A--middle_initial--B--","g");
		var RGlast_name = new RegExp("--A--last_name--B--","g");
		var RGaddress1 = new RegExp("--A--address1--B--","g");
		var RGaddress2 = new RegExp("--A--address2--B--","g");
		var RGaddress3 = new RegExp("--A--address3--B--","g");
		var RGcity = new RegExp("--A--city--B--","g");
		var RGstate = new RegExp("--A--state--B--","g");
		var RGprovince = new RegExp("--A--province--B--","g");
		var RGpostal_code = new RegExp("--A--postal_code--B--","g");
		var RGcountry_code = new RegExp("--A--country_code--B--","g");
		var RGgender = new RegExp("--A--gender--B--","g");
		var RGdate_of_birth = new RegExp("--A--date_of_birth--B--","g");
		var RGalt_phone = new RegExp("--A--alt_phone--B--","g");
		var RGemail = new RegExp("--A--email--B--","g");
		var RGsecurity_phrase = new RegExp("--A--security_phrase--B--","g");
		var RGcomments = new RegExp("--A--comments--B--","g");
		var RGfullname = new RegExp("--A--fullname--B--","g");
		var RGfronter = new RegExp("--A--fronter--B--","g");
		var RGuser = new RegExp("--A--user--B--","g");
		var RGpass = new RegExp("--A--pass--B--","g");
		var RGlead_id = new RegExp("--A--lead_id--B--","g");
		var RGcampaign = new RegExp("--A--campaign--B--","g");
		var RGphone_login = new RegExp("--A--phone_login--B--","g");
		var RGoriginal_phone_login = new RegExp("--A--original_phone_login--B--","g");
		var RGgroup = new RegExp("--A--group--B--","g");
		var RGchannel_group = new RegExp("--A--channel_group--B--","g");
		var RGSQLdate = new RegExp("--A--SQLdate--B--","g");
		var RGepoch = new RegExp("--A--epoch--B--","g");
		var RGuniqueid = new RegExp("--A--uniqueid--B--","g");
		var RGcustomer_zap_channel = new RegExp("--A--customer_zap_channel--B--","g");
		var RGserver_ip = new RegExp("--A--server_ip--B--","g");
		var RGSIPexten = new RegExp("--A--SIPexten--B--","g");
		var RGsession_id = new RegExp("--A--session_id--B--","g");
		var RGdispo = new RegExp("--A--dispo--B--","g");
		var RGdialed_number = new RegExp("--A--dialed_number--B--","g");
		var RGdialed_label = new RegExp("--A--dialed_label--B--","g");
		var RGrank = new RegExp("--A--rank--B--","g");
		var RGowner = new RegExp("--A--owner--B--","g");
		var RGcamp_script = new RegExp("--A--camp_script--B--","g");
		var RGin_script = new RegExp("--A--in_script--B--","g");
		var RGrecording_filename = new RegExp("--A--recording_filename--B--","g");
		var RGrecording_id = new RegExp("--A--recording_id--B--","g");
		var RGuser_custom_one = new RegExp("--A--user_custom_one--B--","g");
		var RGuser_custom_two = new RegExp("--A--user_custom_two--B--","g");
		var RGuser_custom_three = new RegExp("--A--user_custom_three--B--","g");
		var RGuser_custom_four = new RegExp("--A--user_custom_four--B--","g");
		var RGuser_custom_five = new RegExp("--A--user_custom_five--B--","g");
		var RGpreset_number_a = new RegExp("--A--preset_number_a--B--","g");
		var RGpreset_number_b = new RegExp("--A--preset_number_b--B--","g");
		var RGpreset_number_c = new RegExp("--A--preset_number_c--B--","g");
		var RGpreset_number_d = new RegExp("--A--preset_number_d--B--","g");
		var RGpreset_number_e = new RegExp("--A--preset_number_e--B--","g");
		var RGpreset_dtmf_a = new RegExp("--A--preset_dtmf_a--B--","g");
		var RGpreset_dtmf_b = new RegExp("--A--preset_dtmf_b--B--","g");
		var RGdid_id = new RegExp("--A--did_id--B--","g");
		var RGdid_extension = new RegExp("--A--did_extension--B--","g");
		var RGdid_pattern = new RegExp("--A--did_pattern--B--","g");
		var RGdid_description = new RegExp("--A--did_description--B--","g");
		var RGclosecallid = new RegExp("--A--closecallid--B--","g");
		var RGxfercallid = new RegExp("--A--xfercallid--B--","g");
		var RGagent_log_id = new RegExp("--A--agent_log_id--B--","g");
		var RGcall_id = new RegExp("--A--call_id--B--","g");
		var RGuser_group = new RegExp("--A--user_group--B--","g");
		var RGweb_vars = new RegExp("--A--web_vars--B--","g");

		encoded = encoded.replace(RGvendor_lead_code, SCvendor_lead_code);
		encoded = encoded.replace(RGsource_id, SCsource_id);
		encoded = encoded.replace(RGlist_id, SClist_id);
		encoded = encoded.replace(RGgmt_offset_now, SCgmt_offset_now);
		encoded = encoded.replace(RGcalled_since_last_reset, SCcalled_since_last_reset);
		encoded = encoded.replace(RGphone_code, SCphone_code);
		encoded = encoded.replace(RGphone_number, SCphone_number);
		encoded = encoded.replace(RGtitle, SCtitle);
		encoded = encoded.replace(RGfirst_name, SCfirst_name);
		encoded = encoded.replace(RGmiddle_initial, SCmiddle_initial);
		encoded = encoded.replace(RGlast_name, SClast_name);
		encoded = encoded.replace(RGaddress1, SCaddress1);
		encoded = encoded.replace(RGaddress2, SCaddress2);
		encoded = encoded.replace(RGaddress3, SCaddress3);
		encoded = encoded.replace(RGcity, SCcity);
		encoded = encoded.replace(RGstate, SCstate);
		encoded = encoded.replace(RGprovince, SCprovince);
		encoded = encoded.replace(RGpostal_code, SCpostal_code);
		encoded = encoded.replace(RGcountry_code, SCcountry_code);
		encoded = encoded.replace(RGgender, SCgender);
		encoded = encoded.replace(RGdate_of_birth, SCdate_of_birth);
		encoded = encoded.replace(RGalt_phone, SCalt_phone);
		encoded = encoded.replace(RGemail, SCemail);
		encoded = encoded.replace(RGsecurity_phrase, SCsecurity_phrase);
		encoded = encoded.replace(RGcomments, SCcomments);
		encoded = encoded.replace(RGfullname, SCfullname);
		encoded = encoded.replace(RGfronter, SCfronter);
		encoded = encoded.replace(RGuser, SCuser);
		encoded = encoded.replace(RGpass, SCpass);
		encoded = encoded.replace(RGlead_id, SClead_id);
		encoded = encoded.replace(RGcampaign, SCcampaign);
		encoded = encoded.replace(RGphone_login, SCphone_login);
		encoded = encoded.replace(RGoriginal_phone_login, SCoriginal_phone_login);
		encoded = encoded.replace(RGgroup, SCgroup);
		encoded = encoded.replace(RGchannel_group, SCchannel_group);
		encoded = encoded.replace(RGSQLdate, SCSQLdate);
		encoded = encoded.replace(RGepoch, SCepoch);
		encoded = encoded.replace(RGuniqueid, SCuniqueid);
		encoded = encoded.replace(RGcustomer_zap_channel, SCcustomer_zap_channel);
		encoded = encoded.replace(RGserver_ip, SCserver_ip);
		encoded = encoded.replace(RGSIPexten, SCSIPexten);
		encoded = encoded.replace(RGsession_id, SCsession_id);
		encoded = encoded.replace(RGdispo, SCdispo);
		encoded = encoded.replace(RGdialed_number, SCdialed_number);
		encoded = encoded.replace(RGdialed_label, SCdialed_label);
		encoded = encoded.replace(RGrank, SCrank);
		encoded = encoded.replace(RGowner, SCowner);
		encoded = encoded.replace(RGcamp_script, SCcamp_script);
		encoded = encoded.replace(RGin_script, SCin_script);
		encoded = encoded.replace(RGrecording_filename, SCrecording_filename);
		encoded = encoded.replace(RGrecording_id, SCrecording_id);
		encoded = encoded.replace(RGuser_custom_one, SCuser_custom_one);
		encoded = encoded.replace(RGuser_custom_two, SCuser_custom_two);
		encoded = encoded.replace(RGuser_custom_three, SCuser_custom_three);
		encoded = encoded.replace(RGuser_custom_four, SCuser_custom_four);
		encoded = encoded.replace(RGuser_custom_five, SCuser_custom_five);
		encoded = encoded.replace(RGpreset_number_a, SCpreset_number_a);
		encoded = encoded.replace(RGpreset_number_b, SCpreset_number_b);
		encoded = encoded.replace(RGpreset_number_c, SCpreset_number_c);
		encoded = encoded.replace(RGpreset_number_d, SCpreset_number_d);
		encoded = encoded.replace(RGpreset_number_e, SCpreset_number_e);
		encoded = encoded.replace(RGpreset_dtmf_a, SCpreset_dtmf_a);
		encoded = encoded.replace(RGpreset_dtmf_b, SCpreset_dtmf_b);
		encoded = encoded.replace(RGdid_id, SCdid_id);
		encoded = encoded.replace(RGdid_extension, SCdid_extension);
		encoded = encoded.replace(RGdid_pattern, SCdid_pattern);
		encoded = encoded.replace(RGdid_description, SCdid_description);
		encoded = encoded.replace(RGclosecallid, SCclosecallid);
		encoded = encoded.replace(RGxfercallid, SCxfercallid);
		encoded = encoded.replace(RGagent_log_id, SCagent_log_id);
		encoded = encoded.replace(RGcall_id, SCcall_id);
		encoded = encoded.replace(RGuser_group, SCuser_group);
		encoded = encoded.replace(RGweb_vars, SCweb_vars);
	}
	decoded = encoded; // simple no ?
	decoded = decoded.replace(RGnl, '+');
	decoded = decoded.replace(RGplus,'+');
	decoded = decoded.replace(RGtab,'+');

	//	   while (i < encoded.length) {
	//		   var ch = encoded.charAt(i);
	//		   if (ch == "%") {
	//				if (i < (encoded.length-2) 
	//						&& HEXCHAR.indexOf(encoded.charAt(i+1)) != -1 
	//						&& HEXCHAR.indexOf(encoded.charAt(i+2)) != -1 ) {
	//					decoded += unescape( encoded.substr(i,3) );
	//					i += 3;
	//				} else {
	//					alert( 'Bad escape combo near ...' + encoded.substr(i) );
	//					decoded += "%[ERR]";
	//					i++;
	//				}
	//			} else {
	//			   decoded += ch;
	//			   i++;
	//			}
	//		} // while
    //      decoded = decoded.replace(RGnl, "<br />");
	//
	return decoded;
}

function utf8_decode(utftext) {
    var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) {

        c = utftext.charCodeAt(i);

        if (c < 128) {
            string += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224)) {
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        }
        else {
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }

    }

    return string;
}


// ################################################################################
// phone number format
function phone_number_format(formatphone) {
    // customer_local_time, status date display 9999999999
    //	header_phone_format
    //  US_DASH 000-000-0000 - USA dash separated phone number
    //  US_PARN (000)000-0000 - USA dash separated number with area code in parenthesis
    //  UK_DASH 00 0000-0000 - UK dash separated phone number with space after city code
    //  AU_SPAC 000 000 000 - Australia space separated phone number
    //  IT_DASH 0000-000-000 - Italy dash separated phone number
    //  FR_SPAC 00 00 00 00 00 - France space separated phone number

    var regUS_DASHphone = new RegExp("US_DASH","g");
    var regUS_PARNphone = new RegExp("US_PARN","g");
    var regUK_DASHphone = new RegExp("UK_DASH","g");
    var regAU_SPACphone = new RegExp("AU_SPAC","g");
    var regIT_DASHphone = new RegExp("IT_DASH","g");
    var regFR_SPACphone = new RegExp("FR_SPAC","g");
    var status_display_number = formatphone;
    var dispnumber = formatphone;
    if (disable_alter_custphone == 'HIDE') {
        var status_display_number = 'XXXXXXXXXX';
        dispnumber = 'XXXXXXXXXX';
    }
    if (header_phone_format.match(regUS_DASHphone)) {
        var status_display_number = dispnumber.substring(0,3) + '-' + dispnumber.substring(3,6) + '-' + dispnumber.substring(6,10);
    }
    if (header_phone_format.match(regUS_PARNphone)) {
        var status_display_number = '(' + dispnumber.substring(0,3) + ')' + dispnumber.substring(3,6) + '-' + dispnumber.substring(6,10);
    }
    if (header_phone_format.match(regUK_DASHphone)) {
        var status_display_number = dispnumber.substring(0,2) + ' ' + dispnumber.substring(2,6) + '-' + dispnumber.substring(6,10);
    }
    if (header_phone_format.match(regAU_SPACphone)) {
        var status_display_number = dispnumber.substring(0,3) + ' ' + dispnumber.substring(3,6) + ' ' + dispnumber.substring(6,9);
    }
    if (header_phone_format.match(regIT_DASHphone)) {
        var status_display_number = dispnumber.substring(0,4) + '-' + dispnumber.substring(4,7) + '-' + dispnumber.substring(8,10);
    }
    if (header_phone_format.match(regFR_SPACphone)) {
        var status_display_number = dispnumber.substring(0,2) + ' ' + dispnumber.substring(2,4) + ' ' + dispnumber.substring(4,6) + ' ' + dispnumber.substring(6,8) + ' ' + dispnumber.substring(8,10);
    }

    return status_display_number;
};

String.prototype.toUpperFirst = function() {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
<?php
} else {
    if ($_REQUEST['module_name'] == 'GOagent') {
        $campaign = $_REQUEST['campaign_id'];
        
        switch ($_REQUEST['action']) {
            case "SessioN":
                $_SESSION['campaign_id'] = (strlen($campaign) > 0) ? $campaign : $_SESSION['campaign_id'];
                break;
        }
    } else {
        echo "ERROR: Module '{$_REQUEST['module_name']}' not found.";
    }
}

function get_user_info($user_id) {
    //set variables
    $camp = (isset($_SESSION['campaign_id'])) ? $_SESSION['campaign_id'] : null;
    $url = gourl.'/goAgent/goAPI.php';
    $fields = array(
        'goAction' => 'goGetLoginInfo',
        'goUser' => goUser,
        'goPass' => goPass,
        'responsetype' => responsetype,
        'goUserID' => $user_id,
        'goCampaign' => $camp
    );
    
    //url-ify the data for the POST
    foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
    rtrim($fields_string, '&');
    
    //open connection
    $ch = curl_init();
    
    //set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, count($fields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    
    //execute post
    $result = json_decode(curl_exec($ch));
    
    //close connection
    curl_close($ch);
    
    return $result->data;
}
?>

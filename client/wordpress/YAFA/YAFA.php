<?php

/**
 * @package YAFA Plugin Admin
 * @version 1.0
 * Created by Gamer Network.
 * Developer: @lukeinage
 */
/*
Plugin Name: YAFA Client Plugin
Description: Yet another adserver. The most basic possible adserver, with a very fast and light json API. This client plugin calls the YAFA server and pulls in ad requests for caching.
Author: Gamer Network
Version: 0.8
Author URI: http://gamer.network
*/

defined( 'ABSPATH' ) or die( 'Error' );
include dirname( __FILE__ ) . '/YAFAAdmin.php';

global $yafa;
global $yafa_db_version;
$yafa = null;
$yafa_db_version = '1.0';

class YAFA
{
    private $ad_pool = [];
    private $queried_ads = false;

    /**
     * Sets up the YAFA database default being wp_yafa
     */
    public function install_yafa()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . "yafa";

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
          id mediumint(9) PRIMARY KEY AUTO_INCREMENT,
          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          zone varchar(64) NOT NULL,
          obfuscated_name varchar(64) NOT NULL,
          image text DEFAULT '' NOT NULL,
          click text DEFAULT '' NOT NULL
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta( $sql );
    }

    public function schedule_cron()
    {
        if(wp_get_schedule('yafa_cron_schedule') == false)
        {
            wp_schedule_event( time(), 'every_minute', 'yafa_cron_schedule' );
        }
    }

    public function clear_cron()
    {
        wp_clear_scheduled_hook('yafa_cron_schedule');
    }

    /**
     * @param $api_server
     * @param $site_name
     * @throws Exception
     *
     * Calls YAFA API and adds entrys to DB
     */
    public function call_yafa($api_server, $site_name)
    {

        global $wpdb;
        $table_name = $wpdb->prefix . "yafa";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $api_server.$site_name);
        $result = curl_exec($ch);
        if(curl_errno($ch))
        {
            throw new Exception("Curl Error: ".curl_error($ch));
        }
        curl_close($ch);

        $zones = json_decode($result);

        if ($zones === null && json_last_error() !== JSON_ERROR_NONE)
        {
            throw new Exception("JSON Error: JASON! JASON! JASON! (json syntax) url ".$api_server.$site_name);
        }

        $raw_ad_list = $this->get_ads();
        $sorted_ad_list = [];
        $removal_list = [];
        foreach($raw_ad_list as $ad)
        {
            //in case we have twin hashes :(
            if(array_key_exists($ad->obfuscated_name, $sorted_ad_list))
            {
                $removal_list[] = $ad->obfuscated_name;
            }
            $sorted_ad_list[$ad->obfuscated_name] = $ad;
        }
        foreach($removal_list as $obfuscated_name)
        {
            unset($sorted_ad_list[$obfuscated_name]);
            array_values($sorted_ad_list);
            $wpdb->delete($table_name, array('obfuscated_name' => $obfuscated_name));
        }

        $email = "";

        foreach($zones as $zone => $ad)
        {
            if(array_key_exists($ad->obfuscated_name, $sorted_ad_list))
            {
                $cached_ad = $sorted_ad_list[$ad->obfuscated_name];
                unset($sorted_ad_list[$ad->obfuscated_name]);
                if($cached_ad->zone == $zone && $cached_ad->image == $ad->image && $cached_ad->click == $ad->click)
                {
                    $email .= "\nSkipped Update: ".$ad->image;
                    continue;
                }
                else
                {
                    $wpdb->update(
                        $table_name,
                        array(
                            'time' => current_time('mysql', 1),
                            'zone' => $zone,
                            'image' => $ad->image,
                            'click' => $ad->click
                        ),
                        array('obfuscated_name' => $ad->obfuscated_name)
                    );
                    $email .= "\nUpdated: ".$ad->image;
                }
            }
            else
            {
                $wpdb->insert(
                    $table_name,
                    array(
                        'time' => current_time('mysql', 1),
                        'zone' => $zone,
                        'obfuscated_name' => $ad->obfuscated_name,
                        'image' => $ad->image,
                        'click' => $ad->click
                    )
                );
                $email .= "\nInserted: ".$ad->image;
            }
            if ($wpdb->last_error)
            {
                throw new Exception("WPDB Error: ".$wpdb->last_error);
            }
        }

        foreach($sorted_ad_list as $ad)
        {
            $wpdb->delete($table_name, array('obfuscated_name' => $ad->obfuscated_name));
            $email .= "\nDeleted: ".$ad->image;
        }

        //mail("luke.reed@gamer-network.net", "YAFA SQL Operations", $email);
    }

    /**
     * @param $zone
     * @return string
     * This method should be called by the template, gets a random ad from a zone
     */
    public function get_ad($zone)
    {
        if($this->queried_ads == false)
        {
            $this->filter_ads();
        }

        if(array_key_exists($zone, $this->ad_pool))
        {
            $amount = count($this->ad_pool[$zone]);
            if($amount > 0)
            {
                $key = rand(0, $amount-1);
                $ad_selected = $this->ad_pool[$zone][$key];
                array_splice($this->ad_pool[$zone], $key, 1);
                return '<div class="'.$ad_selected->obfuscated_name.'" data-yafa-img="'.$ad_selected->image.'" data-yafa-click="'.$ad_selected->click.'"></div>';
            }
        }

        return "";
    }

    /**
     * Filters ads into an array of zones
     */
    private function filter_ads()
    {
        $ad_list = $this->get_ads();
        foreach($ad_list as $ad)
        {
            $this->ad_pool[$ad->zone][] = $ad;
        }
        $this->queried_ads = true;
    }

    /**
     * Gets all ads as an array of objects
     */
    public function get_ads()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . "yafa";
        return $wpdb->get_results("SELECT zone, obfuscated_name, image, click FROM $table_name ORDER BY zone LIMIT 20");
    }
}

function get_yafa() {
    global $yafa;
    if($yafa == null)
    {
        $yafa = new YAFA();
    }
    return $yafa;
}

/**
 * Cron point of entry, currently emails on error
 */
function yafa_cron()
{
    try {
        $options = get_option('yafa_admin', ["api_server" => "https://yafa.gamer-network.net/v1/site/", "site_name" => "", "fully_setup" => false]);
        if($options["fully_setup"] == true)
        {
            get_yafa()->call_yafa($options['api_server'], $options['site_name']);
        }
    }
    catch(Exception $e)
    {
        echo "Caught Exception During Cron - ", $e->getMessage();
        mail("luke.reed@gamer-network.net", "WP YAFA Plugin Cron Error", "Caught Exception During Cron - ".$e->getMessage());
        die();
    }
}

function yafa_activated() {
    global $yafa_db_version;
    $stored_db_version = get_option('yafa_db_version', "0");
    if ($stored_db_version != $yafa_db_version)
    {
        get_yafa()->install_yafa();
        $stored_db_version = $yafa_db_version;
        update_option("yafa_db_version", $stored_db_version);
    }
    get_yafa()->schedule_cron();
}

function yafa_deactivated() {
    get_yafa()->clear_cron();
}

function add_minute_cron( $schedules ) {
    // add a 'every_minute' schedule to the existing set
    $schedules['every_minute'] = array(
        'interval' => 60,
        'display' => __('Every Minute')
    );
    return $schedules;
}

function yafa_admin()
{
    if(is_admin())
    {
        new YAFAAdmin();
    }
}

// Plugin Hooks
add_action('init', 'yafa_admin');
add_filter('cron_schedules', 'add_minute_cron');
add_action('yafa_cron_schedule', 'yafa_cron');
register_activation_hook(__FILE__, 'yafa_activated');
register_deactivation_hook(__FILE__, 'yafa_deactivated');
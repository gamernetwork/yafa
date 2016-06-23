<?php

/**
 * @package YAFA Plugin Admin
 * @version 1.0
 * Created by Gamer Network.
 * Developer: @lukeinage
 */
class YAFAAdmin
{
    private $options;

    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'YAFA Admin',
            'YAFA Admin',
            'manage_options',
            'yafa-settings-admin',
            array( $this, 'create_admin_page' )
        );
    }

    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option('yafa_admin', ["api_server" => "https://yafa.gamer-network.net/v1/site/", "site_name" => "", "fully_setup" => false]);
        ?>
        <div class="wrap">
            <h2>YAFA Admin -
            <?php
            if($this->options["fully_setup"] == false)
            {
                ?>
                <span style="color: red;"> Feed needs setup</span>
                <?php
            }
            else
            {
                ?>
                <span style="color: green;"> Feed running</span>
                <?php
            }
            ?>
            </h2>
            <h3>YAFA Cached Feed</h3>
            <style>

                #yafa-table {
                    color: #333; /* Lighten up font color */
                    font-family: Helvetica, Arial, sans-serif; /* Nicer font */
                    border-collapse: collapse; border-spacing: 0;
                    width: 80%;}
                #yafa-table td, #yafa-table th { border: 1px solid #CCC; height: 30px; text-align: left; padding: 5px; }
                #yafa-table th { background: #F3F3F3; font-weight: bold; }
                #yafa-table td { background: #FAFAFA; }
            </style>
            <table id="yafa-table">
                <tr>
                    <th>Ad Zone</th>
                    <th>Obfuscated Name</th>
                    <th>Image</th>
                    <th>Click</th>
                </tr>
                <tr>
                    <?php
                    $ads = get_yafa()->get_ads();
                    foreach($ads as $ad)
                    {
                        echo "<tr>";
                        echo "<td>".$ad->zone."</td>";
                        echo "<td>".$ad->obfuscated_name."</td>";
                        echo "<td><a target='_blank' href='".$ad->image."'>".$ad->image."</a>";
                        echo "<td><a target='_blank' href='".$ad->click."'>".$ad->click."</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tr>
            </table>
            <form method="post" action="options.php">
                <?php
                // This prints out all hidden setting fields
                settings_fields( 'yafa_options' );
                do_settings_sections( 'yafa-settings-admin' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init()
    {
        register_setting(
            'yafa_options', // Option group
            'yafa_admin', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'yafa_setting_section_id', // ID
            'YAFA Server API Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'yafa-settings-admin' // Page
        );

        add_settings_field(
            'api_server',
            'API Server',
            array( $this, 'title_callback' ),
            'yafa-settings-admin',
            'yafa_setting_section_id'
        );

        add_settings_field(
            'site_name',
            'Site Name',
            array( $this, 'site_callback' ),
            'yafa-settings-admin',
            'yafa_setting_section_id'
        );
    }

    public function sanitize( $input )
    {
        $new_input = array();

        $new_input['fully_setup'] = false;

        if( isset( $input['api_server'] ) )
            $new_input['api_server'] = sanitize_text_field( $input['api_server'] );

        if( isset( $input['site_name'] ) )
            $new_input['site_name'] = sanitize_text_field( $input['site_name'] );

        if($new_input['site_name'] == "")
        {
            add_settings_error("yafa_admin", "missing-site-name", "Missing site name", "error");
            return $new_input;
        }

        try {
            get_yafa()->call_yafa($new_input['api_server'], $new_input['site_name']);
        }
        catch(Exception $e)
        {
            add_settings_error("yafa_admin", "pull-results-error", "Error whilst pulling results - ".$e->getMessage(), "error");
            return $new_input;
        }

        $new_input['fully_setup'] = true;

        return $new_input;
    }

    public function print_section_info()
    {
        print "Only edit these if you know what you're doing!";
    }

    public function title_callback()
    {
        printf(
            '<input type="text" id="title" name="yafa_admin[api_server]" size="35" value="%s" />',
            isset( $this->options['api_server'] ) ? esc_attr( $this->options['api_server']) : ''
        );
    }

    public function site_callback()
    {
        printf(
            '<input type="text" id="title" name="yafa_admin[site_name]" value="%s" />',
            isset( $this->options['site_name'] ) ? esc_attr( $this->options['site_name']) : ''
        );
    }
}
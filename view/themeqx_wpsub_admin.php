<?php
global $wpdb;
$get_all_lookup = $wpdb->get_results("select * from {$wpdb->prefix}wpsub_lookup_item");

$status = $wpdb->get_row("select * from {$wpdb->prefix}wpsub_lookup_status where lookup_item_id = 14 ORDER BY ID DESC ");

$get_all_up_happend = $wpdb->get_var("select COUNT(id) from {$wpdb->prefix}wpsub_lookup_status where status = 'up'");
$get_all_down_happend = $wpdb->get_var("select COUNT(id) from {$wpdb->prefix}wpsub_lookup_status where status = 'down'");
$get_all_server_response = $wpdb->get_results("select * from {$wpdb->prefix}wpsub_lookup_status");
?>

<div class="wpsub_main_wrap">
    <h1><i class="dashicons dashicons-dashboard"></i> <?php _e('Dashboard', 'wp-server-uptime-bot'); ?></h1>
    <p><?php _e('All of your monitoring will be here, simple', 'wp-server-uptime-bot'); ?></p>
    <p><?php _e('Notification will be sent to', 'wp-server-uptime-bot'); ?> <strong><?php echo get_option('admin_email'); ?></strong> </p>

    <div class="wpsub-lookup-wrap">

        <div class="wpsub-lookup-ad">

            <form id="wpsub-lookup-add-form" method="post" action="">
                <p class="wpsub-lookup-add-box">
                    <input type="text" value="" name="wpsub_add_lookup_name" placeholder="<?php _e('Lookup Name', 'wp-server-uptime-bot'); ?>" required>
                    <input type="url" value="" name="wpsub_add_lookup_url" placeholder="<?php _e('Lookup URL', 'wp-server-uptime-bot'); ?>" required>
                    <input type="submit" value="<?php _e('Add Lookup', 'wp-server-uptime-bot'); ?>" class="button" id="wpsub-add-lookup-btn">
                </p>
            </form>

            <p id="wpsub-status-msg"></p>


        </div>


        <div id="wpsub-all-lookup">

            <?php if ( $get_all_lookup){ ?>


            <table class="widefat striped">
                <tr>
                    <th><?php _e('Lookup Name', 'wp-server-uptime-bot'); ?></th>
                    <th><?php _e('Lookup URL', 'wp-server-uptime-bot'); ?></th>
                    <th> <span class="wpsub-squire-success"></span> <?php _e('Up', 'wp-server-uptime-bot'); ?></th>
                    <th> <span class="wpsub-squire-danger"></span> <?php _e('Down', 'wp-server-uptime-bot'); ?></th>
                    <th><?php _e('Created at', 'wp-server-uptime-bot'); ?></th>
                    <th>#</th>
                </tr>

                <?php foreach ($get_all_lookup as $lookup_res):
                    $individual_up_total = $wpdb->get_var("select COUNT(id) from {$wpdb->prefix}wpsub_lookup_status where status = 'up' AND lookup_item_id= {$lookup_res->ID}");
                    $individual_down_total = $wpdb->get_var("select COUNT(id) from {$wpdb->prefix}wpsub_lookup_status where status = 'down' AND lookup_item_id= {$lookup_res->ID}");

                    ?>
                    <tr>
                        <td><?php echo $lookup_res->lookup_name; ?> </td>
                        <td><?php echo $lookup_res->lookup_url; ?> </td>
                        <td><?php echo $individual_up_total; ?></td>
                        <td><?php echo $individual_down_total; ?></td>
                        <td>
                            <?php
                            $added_date = date(get_option('date_format'), strtotime($lookup_res->created_at));
                            $added_time = date(get_option('time_format'), strtotime($lookup_res->created_at));
                            echo $added_date." ".$added_time;
                            ?>
                        </td>
                        <td>
                            <a href="javascript:;" class="wpsub-lookup-trasn-btn" data-lookup-id="<?php echo $lookup_res->ID; ?>"> <i class="dashicons dashicons-trash"></i> </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <?php } else{ ?>
                <h3><?php _e('No lookup here', 'wp-server-uptime-bot'); ?></h3>
            <?php } ?>

        </div>



    </div>


    <hr />




    <div class="wpsub-stats-wrap">
        <h4><?php _e('At a glance stats', 'wp-server-uptime-bot') ?></h4>

        <div class="wpsub-circle-wrap">
            <p> <span class="wpsub-squire-danger"></span> <?php _e('Lookup Down', 'wp-server-uptime-bot'); ?> </p>
            <p> <span class="wpsub-squire-success"></span> <?php _e('Lookup Up', 'wp-server-uptime-bot'); ?> </p>

            <div class="wpsub-circle wpsub-circle-success">
                <span>  <?php echo $get_all_up_happend; ?>  </span>
            </div>
            <div class="wpsub-circle wpsub-circle-danger">
                <span><?php echo $get_all_down_happend; ?> </span>
            </div>
        </div>

    </div>


    <?php if ($get_all_server_response){ ?>

        <h4><?php _e('All response from server', 'wp-server-uptime-bot') ?></h4>

    <table class="widefat striped">

        <tr>
            <th><?php _e('Lookup Name', 'wp-server-uptime-bot'); ?></th>
            <th><?php _e('Status', 'wp-server-uptime-bot'); ?> name</th>
            <th><?php _e('Looked Time', 'wp-server-uptime-bot'); ?></th>
            <th><?php _e('Time Duration', 'wp-server-uptime-bot'); ?></th>
        </tr>

        <?php foreach ($get_all_server_response as $s_response){ ?>

            <tr>
                <td><?php echo $s_response->lookup_item_name; ?></td>
                <td>
                    <?php
                    if ($s_response->status == 'up'){
                        echo '<span class="wpsub-label wpsub-label-success"> <i class="dashicons dashicons-arrow-up"></i> Ok (UP)  </span>';
                    } elseif($s_response->status == 'down'){
                        echo ' <span class="wpsub-label wpsub-label-danger"> <i class="dashicons dashicons-arrow-down"></i> Error (Down) </span>';
                    }
                    ?>
                </td>
                <td>
                    <?php echo date(get_option('date_format'), strtotime($s_response->state_datetime) )." ".date(get_option('time_format'), strtotime($s_response->state_datetime) ); ?>
                </td>
                <td>
                    <?php
                    $end_time = time();
                    if (strtotime($s_response->end_datetime) > 3600){
                        $end_time = strtotime($s_response->end_datetime);
                    }
                        echo human_time_diff( strtotime($s_response->state_datetime), $end_time );
                    ?>
                </td>
            </tr>

        <?php } ?>

    </table>

    <?php } else{
        echo "<h3>".__('LookUp has not been started yet', 'wp-server-uptime-bot')."</h3>";
    } ?>

</div>

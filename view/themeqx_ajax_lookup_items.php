<?php
global $wpdb;
$get_all_lookup = $wpdb->get_results("select * from {$wpdb->prefix}wpsub_lookup_item");
?>

<table class="widefat striped">
    <tr>
        <th><?php _e('Lookup Name', 'wp-server-uptime-bot'); ?></th>
        <th><?php _e('Lookup URL', 'wp-server-uptime-bot'); ?></th>
        <th><?php _e('Created at', 'wp-server-uptime-bot'); ?></th>
        <th>#</th>
    </tr>

    <?php foreach ($get_all_lookup as $lookup_res): ?>
        <tr>
            <td><?php echo $lookup_res->lookup_name; ?> </td>
            <td><?php echo $lookup_res->lookup_url; ?> </td>
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
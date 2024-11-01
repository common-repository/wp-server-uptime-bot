/**
 * Main Javascript Script For WP Server UpTime Bot
 */

jQuery(document).ready(function($){
    $('#wpsub-lookup-add-form').on('submit',function(e){
        e.preventDefault();
        var form_input = $(this).serialize()+'&action=wpsub_add_lookup';

        $.ajax({
            type : 'POST',
            url : ajaxurl,
            data : form_input,
            success : function(data){
                var success_data = JSON.parse(data);
                if (success_data.status == 1){
                    $('#wpsub-status-msg').html('<span class="wpsub-msg-success">'+success_data.msg+'</span>');

                    $.post( ajaxurl, {action : 'wpsub_load_lookup_items' }, function( data ) {
                        $('#wpsub-all-lookup').html(data);
                    });
                    $('#wpsub-lookup-add-form').trigger("reset");
                }else{
                    $('#wpsub-status-msg').html('<span class="wpsub-msg-danger">'+success_data.msg+'</span>');
                }
            }
        })

    });

    $('body').on('click', '.wpsub-lookup-trasn-btn', function(e){
        e.preventDefault();
        if ( ! confirm('Are you sure?')){
            return false;
        }
        var lookup_id = $(this).data('lookup-id');
        var current_selector = $(this);
        $.ajax({
            type : 'POST',
            url : ajaxurl,
            data : {lookup_id : lookup_id, action : 'wpsub_lookup_delete'},
            success : function(data){
                var success_data = JSON.parse(data);
                if (success_data.status == 1){
                    $('#wpsub-status-msg').html('<span class="wpsub-msg-success">'+success_data.msg+'</span>');
                    current_selector.closest('tr').hide('slow');
                }
            }
        })


    })

});

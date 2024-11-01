<?php
wp_enqueue_style('bootstrapCss', plugins_url('/bootstrap5.css', __FILE__));
wp_enqueue_style('dataTableCss', plugins_url('/dataTableStyle.css', __FILE__));
wp_enqueue_script('jquery');
wp_enqueue_script('dataTableJS', plugins_url('/dataTableScript.js', __FILE__));
?>

<h4 class="py-3 px-2">XEPPT Error Logs</h4>

<!-- Table -->
<div class="container-fluid">
    <table id="logstable" class="table table-striped table-responsive table-bordered table-hover nowrap" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>OrderID</th>
                <th>Request</th>
                <th>Response</th>
                <th>Message</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            global $wpdb;
            $chlist = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "xeppt_error_logs WHERE 1=1 ORDER BY id DESC LIMIT 100");
            $pCount = 1;

            foreach ($chlist as $value) {
                $dt = new DateTime($value->date_added);
                $date = $dt->format('Y-m-d');
            ?>
                <tr>
                    <td><?php echo esc_html( $pCount ); ?></td>
                    <td><?php echo esc_html( $value->order_id ); ?></td>
                    <td><?php echo esc_html( $value->request ); ?></td>
                    <td><?php echo esc_html( $value->response ); ?></td>
                    <td><?php echo esc_html( $value->err_message ); ?></td>
                    <td><?php echo esc_html( $date ); ?></td>
                </tr>
            <?php
                $pCount++;
            }

            ?>
        </tbody>

    </table>
</div>

<script>
    jQuery(document).ready(function($) {
        var table = $('#logstable').DataTable({
            scrollX: true,
            scrollY: '75vh',
            scrollCollapse: true,

            columnDefs: [{
                target: 0,
                visible: false,
                searchable: false,
            }],
        });
    });
</script>
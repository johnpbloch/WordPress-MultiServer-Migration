<?php
$urlParts = parse_url( home_url() );
$serverName = empty( $urlParts['host'] ) ? home_url() : $urlParts['host'];
?>{
"serverName" : "<?php echo esc_attr( $serverName ); ?>",
"baseURL" : "<?php echo esc_url( home_url() ); ?>",
"publicSignature" : "<?php echo WP_MSM_Options::instance()->publicSignature; ?>",
"acceptsConnections" : <?php echo WP_MSM_Options::instance()->acceptsConnections ? 'true' : 'false' ?>
}
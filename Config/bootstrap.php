<?php
$config = array(
    'Rest' => array(
        'ENV_DEV' => ENV_DEV,
        'ENV_STAGING' => ENV_STAGING,
        'ENV_LIVE' => ENV_LIVE,
        'requireSignature' => false, # Setting this to true requires a key and a signature to sign all api requests
    )
);

Configure::write($config);
<?php
# Connect the api calls to the dispatch-function
Router::connect('/rest/:version/:noun/*',
    array('plugin' => 'Rest', 'controller' => 'Rest', 'action' => 'dispatch')
);

# Map all rest resources and return json or xml
Router::mapResources('Rest.Rest');
Router::parseExtensions('json', 'xml');
<?php
App::uses('RestAppController', 'Rest.Controller');
App::import('Utility', 'Xml');
class ApiController extends RestAppController {
    public $name = 'Api';

    public $uses = array(
        'Rest.ApiApplication',
    );

    public $components = array(
        'Rest.Error', // Standardized error component
        'Rest.Tools',
    );

    /**
     * @var array with api information
     */
    public $apiInformation = array(
        'name' => '-enter name here-',
        'version' => '0.1', // Format: major.minor
        'description' => '-enter description here-',
        'copyright' => '-enter copyright notice here-',
        'server' => '',
        'serverTime' => '',
        'availableOutput' => array('json', 'xml'),
        'objects' => array(
            'info' => array('get'),
            'application' => array('get'),
            // Define the objects and their methods here <object>_<method:get/put/update/delete>
        ),
    );


    /**
     * Return basic api information, so we know what version we are looking at
     * @return array api information
     */
    public function info_get() {
        $result['return'] = $this->apiInformation;
        $result['return']['server'] = $_SERVER['HTTP_HOST'];
        $result['return']['serverTime'] = time();
        return $result;
    }

    /**
     * Return basic application information
     * @param array $call
     * @return array|null application information
     */
    public function applications_get($call = array()) {
        # Set defaults
        $result = null;
        $params = array('conditions' => array('id' => -1)); // don't show any results by default
        $ignoreParamFields = array('id', 'secret'); // ignore these fields when building the query

        # Check if we are looking for a specific ID
        if (isset($call['id'])) {
            $params['conditions']['id'] = intval($call['id']);
        } else {
            # Check the available fields for this model
            $availableFields = array_keys($this->ApiApplication->getColumnTypes());

            # Create the query if parameters are set
            if ($this->Tools->hasParameters($call['parameters'])) {
                $response = $this->Tools->createQueryParams($call['parameters'], $availableFields, $ignoreParamFields);
                if (!empty($response))
                    $params = $response;
            }
        }

        # Run the query
        $response = $this->ApiApplication->find('all', $params);

        # Handle the result
        if($response) {
            foreach ($response as $application) {
                unset($application['ApiApplication']['secret']);
                $result['return'][] = $application['ApiApplication'];
            }
        } else {
            $result['status'] = $this->Error->throwError(4004, 'no application found');
        }

        # And return it...
        return $result;
    }


}
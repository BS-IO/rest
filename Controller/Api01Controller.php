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
    public function application_get($call = array()) {
        $result = null;

        if (isset($call['id'])) {
            $response = $this->ApiApplication->findById($call['id']);

            if($response)
                unset($response['ApiApplication']['secret']);

            $result['return'] = $response['ApiApplication'];
        }

        return $result;
    }


}
<?php
App::uses('RestAppController', 'Rest.Controller');
App::import('Utility', 'Xml');
class RestController extends RestAppController {

    public $uses = array('Rest.ApiApplication');

    public $components = array('Rest.Error');

    public $running_timers = array();

    /**
     * Start a microtimer
     * @param $k timer name
     */
    function start($k) {
        $time = microtime();
        $time = explode(' ', $time);
        $time = $time[1] + $time[0];
        $this->running_timers[$k] = $time;
    }

    /**
     * Stop a microtimer
     * @param $k timer name
     * @return microtime
     */
    function stop($k) {
        $time = microtime();
        $time = explode(" ", $time);
        $time = $time[1] + $time[0];
        $endtime = $time;
        return ($endtime - $this->running_timers[$k]);
    }


    public function index() {
    }

    /**
     * REST api dispatcher
     */
    public function dispatch() {
        $header = $this->getHeaderInformation($_SERVER);

        # Load the appropriate version of the api
        $api['version'] = $this->params['version'];

        # Detect method: get/post/put/delete
        $api['method'] = strtolower($_SERVER['REQUEST_METHOD']);

        # Override the method when it is explicitly set
        if (isset($this->params->query['method'])) {
            $api['method'] = strtolower($this->params->query['method']);
            unset($this->params->query['method']);
        }

        # Detect the extension: json or xml
        $api['extension'] = $this->params['ext'];
        if ($api['extension'] != 'xml' && $api['extension'] != 'json')
            $result['status'] = $this->Error->throwError(1002, 'only xml and json are allowed');

        # Assign the appropriate layouts
        switch ($api['extension']) {
            case 'xml':
                $this->layout = 'Rest.xml';
                break;
            default:
                $this->layout = 'Rest.json';
        }

        # Define the noun
        $api['noun'] = $this->params['noun'];

        # Check if we have a passed argument we should use
        if (isset($this->params['pass'][0]))
            $api['id'] = $this->params['pass'][0];

        # Define possible parameters
        $api['parameters'] = $this->params->query;

        # If the header has signature and key, override the api['parameters']-value
        if (isset($header['HTTP_KEY']))
            $api['parameters']['key'] = $header['HTTP_KEY'];

        if (isset($header['HTTP_SIGNATURE']))
            $api['parameters']['signature'] = $header['HTTP_SIGNATURE'];

        # Check if we need to suppress the response codes
        if (isset($api['parameters']['suppress_response_code'])) {
            unset($api['parameters']['suppress_response_code']);
            $api['suppress_response_code'] = true;
        }

        # Check if we are debugging: ?debug should be set (or debug should be defined in header)
        if (isset($api['parameters']['debug']) || isset($header['HTTP_DEBUG'])) {
            unset($api['parameters']['debug']);
            $api['debug'] = true;

            # Set the call information
            $result['call'] = $api;

            # Start the timer
            $this->start('apiTimer');
        }

        # Load the controller
        $controllerVersion = str_replace('.', '', $api['version']);
        $filename = APP.'Plugin'.DS.$this->plugin.DS.'Controller'.DS.'api'.$controllerVersion.'Controller.php';

        # If the controller does not exist, return a 'version not found' error
        if (file_exists($filename)) {
            App::import('Controller', 'Rest.Api'.$controllerVersion);
            $controller = new ApiController;
            $controller->constructClasses();

            # return a method does not exist if the noun doesn't exist
            $availableMethods = get_class_methods($controller);
            $method = Inflector::underscore($api['noun']).'_'.$api['method'];
            if (!in_array($method, $availableMethods)) {
                $result['status'] = $this->Error->throwError(1003, $api['method'] .' not available for '.$api['noun']);
            } else {
                # Check the key and token
                $apiAccess = true;
                if (Configure::read('Rest.requireSignature')) {
                    if (!isset($api['parameters']['key']) || !isset($api['parameters']['signature']))  {
                        # Throw an unauthorized error
                        $result['status'] = $this->Error->throwError(4001, 'missing required key or signature');
                        $apiAccess = false;
                    } else {
                        $token = $this->getRequestHash($api['parameters']['key']);
                        if (!$token || $token != $api['parameters']['signature']) {
                            # Only show debug info on token if we are on staging or development server (NEVER on live)
                            $debugTokenInfo = '';
                            if ($_SERVER['SERVER_NAME'] == Configure::read('Rest.ENV_DEV') || $_SERVER['SERVER_NAME'] == Configure::read('Rest.ENV_STAGING'))
                                $debugTokenInfo = ' (should be: '.$token.')';

                            # Throw an unauthorized error
                            $result['status'] = $this->Error->throwError(4001, 'invalid signature'.$debugTokenInfo);
                            $apiAccess = false;
                        }
                    }
                }

                # We have access to the api
                if ($apiAccess) {
                    # Set the default header to 200/OK
                    $result['status'] = $this->Error->throwError();

                    # Call the function with the parameters
                    if (isset($this->request->data) && !empty($this->request->data)) {
                        $apiResult = $controller->$method($this->request->data, $api);
                    } else {
                        $apiResult = $controller->$method($api);
                    }

                    # if we don't have any results, return an empty array
                    if(empty($apiResult['return']))
                        $apiResult['return'] = array();

                    # filter the results if we only want specific field (only 1 level)
                    if (isset($api['parameters']['fields'])) {
                        $fieldset = array_map('trim', explode(",", $api['parameters']['fields']));
                        foreach ($apiResult['return'] as $key => $value) {
                            if (!in_array($key, $fieldset))
                                unset($apiResult['return'][$key]);
                        }
                    }

                    $result['return'] = $apiResult['return'];

                    if (!empty($apiResult['status']))
                        $result['status'] = $apiResult['status'];
                }
            }
        } else {
            $result['status'] = $this->Error->throwError(1001);
        }

        # Set the header based on the status code, except when we are suppressing header codes..
        if (isset($api['suppress_response_code']) || isset($header['HTTP_SUPPRESS_RESPONSE_CODE'])) {
            $this->response->statusCode(200);
        } else {
            $this->response->statusCode($result['status']['http']);
        }

        # Stop the timer if we are debugging
        if (isset($api['debug']))
            $result['status']['duration'] = $this->stop('apiTimer');

        # Return the result
        $this->set('result', $this->renderApiResult($result, $api['extension']));
    }

    /**
     * Transform content into json or xml
     *
     * @param array $content source
     * @param string $ext json or xml
     * @return string containing xml or json
     */
    private function renderApiResult($content = array(), $ext = 'json') {
        $result = '';

        if ($ext == 'json') {
            $result = json_encode($content);
        } elseif ($ext == 'xml') {
            $xmlArray = array('response' => $content);
            $xmlObject = Xml::fromArray($xmlArray, array('format' => 'tags'));
            $result = $xmlObject->asXML();
        }

        return $result;
    }


    /**
     * Return the hash coming from an application
     * @param string $key
     * @return bool|string
     */
    private function getRequestHash($key = '') {
        # Get the secret from the application
        $response = $this->ApiApplication->findById($key);
        if (!$response)
            return false;

        $secret = $response['ApiApplication']['secret'];

        # return the hash from the data
        return hash_hmac("sha256", urlencode($_SERVER['REDIRECT_SCRIPT_URI']), $secret);
    }


    /**
     * Get all header information out of an array (like $_SERVER)
     * @param array $header
     * @return array with header details
     */
    private function getHeaderInformation($header = array()) {
        $result = array();
        foreach ($header as $key => $value) {
            $found = strpos($key, 'HTTP_');
            if ($found !== false) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

}
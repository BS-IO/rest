<?php
class ErrorComponent extends Component {
    public $httpCodes = array(
        200 => 'OK',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out'
    );

    public $errorCodes = array(
        0    => array('http' => 200, 'reason' => 'OK'),
        1001 => array('http' => 500, 'reason' => 'Version does not exist'),
        1002 => array('http' => 500, 'reason' => 'Unknown output file'),
        1003 => array('http' => 500, 'reason' => 'Unrecognised method'),
        4001 => array('http' => 401, 'reason' => 'Unauthorized'),
    );

    /**
     * Throw an error and set the http-header accordingly
     *
     * @param int $errorCode
     * @param string $remark
     * @return array
     */
    public function throwError($errorCode = 0, $remark = '') {
        # Get the proper error code
        $error = $this->errorCodes[$errorCode];
        $error['verbose'] = $this->httpCodes[$error['http']];
        if (!empty($remark))
            $error['reason'] = $error['reason'].' ('.$remark.')';

        # Return the header code
        return $error;
    }



}

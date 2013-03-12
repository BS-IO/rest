<?php
class ToolsComponent extends Component {

    /**
     * Create a neat array with parameters for a cake query
     *
     * @param $parameters array with parameters from the requested URL
     * @param array $availableFields with fields that are available for this model
     * @param array $ignoreFields with fields that are not allowed to be queried
     * @param bool $wildcardSearchAllowed switch to set wildcard-search on or off
     * @return array with parameters for the query
     */
    public function createQueryParams($parameters, $availableFields = array(), $ignoreFields = array(), $wildcardSearchAllowed = false) {
        $params = array();

        # Determine the operator
        $operator = 'AND';
        if (isset($parameters['operator']))
            $operator = $parameters['operator'];

        foreach ($parameters as $key => $value) {
            # If the key is valid, process the value
            if(in_array($key, $availableFields) && !in_array($key, $ignoreFields)) {

                # Check for IN clause
                if ($value[0] == '(' && substr($value, -1) == ')') {
                    $value = str_replace('(','', $value);
                    $value = str_replace(')','',$value);
                    $params['conditions'][$operator][$key] = explode(',',$value);

                # Check for BETWEEN clause
                } elseif ($value[0] == '[' && substr($value, -1) == ']') {
                    $value = str_replace('[','', $value);
                    $value = str_replace(']','',$value);
                    $params['conditions'][$operator][$key.' BETWEEN ? AND ?'] = explode(',',$value);

                # Check for >=, <=, >, <
                } elseif(strpos(substr($value,0,2),'>=') !== false) {
                    $params['conditions'][$operator][$key.' >='] = substr($value,2);
                } elseif (strpos(substr($value,0,2),'<=') !== false) {
                    $params['conditions'][$operator][$key.' <='] = substr($value,2);
                } elseif (strpos(substr($value,0,1),'>') !== false) {
                    $params['conditions'][$operator][$key.' >'] = substr($value,1);
                } elseif (strpos(substr($value,0,1),'<') !== false) {
                    $params['conditions'][$operator][$key.' <'] = substr($value,1);

                # Check for wildcards
                } elseif (($value[0] == '*' || substr($value, -1) == '*') && $wildcardSearchAllowed) {
                    $value = str_replace('*','%', $value);
                    $params['conditions'][$operator][$key.' LIKE'] = $value;

                # Process this as an ordinary field
                } else {
                    $params['conditions'][$operator][$key] = $value;
                }
            }
        }

        return $params;
    }

    /**
     * Check if an array has actual parameters
     *
     * @param array $parameters
     * @return bool
     */
    public function hasParameters($parameters = array()) {
        unset($parameters['fields']);

        if(!empty($parameters))
            return true;

        return false;
    }

}

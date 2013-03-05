<?php
App::uses('RestAppModel', 'Rest.Model');
/**
 * Api Application model
 */
class ApiApplication extends RestAppModel {

    /**
     * Validation rules
     *
     * @var array
     */
    public $validate = array(
        'name' => array(
            'notempty' => array(
                'rule' => array('notempty'),
                'message' => 'Please enter a valid application name',
                'allowEmpty' => false,
                'required' => true,
                'last' => true,
            ),
        ),
    );

    /**
     * The beforeSave sets a random key and secret upon creation
     * @return bool
     */
    function beforeSave() {
        if (!$this->id && !isset($this->data[$this->alias][$this->primaryKey])) {
            # create random id on create based on a random number, the application name & the time
            $key = md5(rand(0,99999).$this->data[$this->alias]['name'].time());
            $this->data[$this->alias][$this->primaryKey] = $key;

            # Also, create a random secret
            $secret = hash_hmac("sha256", rand(0,99999).time(), Configure::read('Security.salt'));
            $this->data[$this->alias]['secret'] = $secret;
        }
        return true;
    }

}

<?php

namespace Novosga\Http;

use Symfony\Component\HttpFoundation\JsonResponse as BaseResponse;

/**
 * JsonResponse.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class JsonResponse extends BaseResponse
{
    
    private $body;

    public function __construct($data = '', $success = true)
    {
        $this->body = [
            'success'  => $success,
            'invalid'  => false,
            'inactive' => false,
            'time'     => time() * 1000
        ];
        
        if ($success) {
            $this->body['data'] = $data;
        } else {
            $this->body['message']  = $data;
        }
        
        parent::__construct($this->body);
    }
    
    public function __get($name)
    {
        return $this->body[$name];
    }
    
    public function __set($name, $value)
    {
        $this->body[$name] = $value;
        $this->setData($this->body);
    }
}

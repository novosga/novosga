<?php

namespace Novosga\Http;

/**
 * JsonResponse.
 *
 * @author Rogerio Lino <rogeriolino@gmail.com>
 */
class JsonResponse
{
    public $success;
    public $message;
    public $data = array();
    public $invalid = false;
    public $inactive = false;

    public function __construct($success = false, $message = '')
    {
        $this->success = $success;
        $this->message = $message;
    }

    /**
     * Retorna o response no formato JSON evitando overhead de campos nulos
     * ou vazios.
     *
     * @return string
     */
    public function toJson()
    {
        $arr = array(
            'success' => ($this->success == true),
            'data' => $this->data,
            'time' => time() * 1000,
        );
        if (!empty($this->message)) {
            $arr['message'] = $this->message;
        }
        if ($this->inactive) {
            $arr['inactive'] = true;
        }
        if ($this->invalid) {
            $arr['invalid'] = true;
        }

        return json_encode($arr);
    }
}

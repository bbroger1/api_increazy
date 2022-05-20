<?php

namespace App\Traits;

trait TransformDataTrait
{

    public function TransformDataCep($data)
    {
        $data = explode(",", $data);
        $data = str_replace("-", "", $data);
        $data = str_replace(".", "", $data);

        $cep = [];

        foreach ($data as $value) {
            if (preg_match('/^[0-9]{5,5}([- ]?[0-9]{3,3})?$/', $value)) {
                array_push($cep, $value);
            } else {
                array_push($cep, "error");
            }
        };

        return $cep;
    }
}

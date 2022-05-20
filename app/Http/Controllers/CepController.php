<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\TransformDataTrait;

class CepController extends Controller
{
    use TransformDataTrait;

    public function search(Request $request)
    {
        try {
            //recebe os CEP a serem consultados pelos parâmetros da rota
            $request = $request->route('cep');

            //limpa e valida os CEP conforme o formato aceito no ViaCep
            if (!$data = $this->transformDataCep($request)) {
                return response()->json('error', 'Invalid data');
            }

            /*faz a consulta na API do ViaCep, caso o formato do CEP não tenha sido validado
            * retorna mensagem de erro para o usuário
            */
            $results = [];
            foreach ($data as $cep) {
                if ($cep !== "error") {
                    $result = json_decode(file_get_contents("https://viacep.com.br/ws/$cep/json/"), true);
                    if (!isset($result['erro'])) {
                        $result = $this->arrayKey($result);
                        array_push($results, $result);
                    } else {
                        array_push($results, ["error" => "Not found"]);
                    }
                } else {
                    array_push($results, ["error" => "Incorrect format"]);
                }
            }

            //retorna o resultado da consulta na ordem solicitada
            return response()
                ->json(array_reverse($results));
        } catch (\Throwable $e) {
            throw_if(app()->environment('local'), $e);
            return response()
                ->json(["message" => "Something unexpected has occurred, please try again"]);
        }
    }

    public function arrayKey($result)
    {
        $new_index = ['label' => $result['logradouro'] . ', ' . $result['localidade']];
        $searchIndex = array_search('cep', array_keys($result));

        $result = array_merge(array_slice($result, 0, $searchIndex + 1), $new_index, array_slice($result, $searchIndex + 1));

        return $result;
    }
}

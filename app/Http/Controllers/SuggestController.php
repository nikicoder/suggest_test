<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Exception;
use App\RequestLogger;

class SuggestController extends Controller
{

    public function loadSuggest($input)
    {   
        $response = [];

        $url = env('GEOSERV_SUGG_URL');
        $url .= 'input=' . $input;
        $url .= '&types=geocode&language=ru&key=' . env('SEOSERV_SUGG_KEY');

        $client = new Client();

        try {
            $response = $client->request('GET', $url);
        } catch(GuzzleException $e) {
            // детальный разбор исключений выходит за рамки тестового задания
            throw new Exception('Server request error');
        }

        $data = json_decode($response->getBody()->getContents(), TRUE);
        if(isset($data['predictions']) && count($data['predictions']) > 0) {
            // обрабатываем запрос в удобоваримый формат фронтэнду
            $response = $this->formatOutput($data['predictions']);
            // логгируем ответ от сервера
            //$this->req_logger->logSuggRequest($input, $data['predictions']);
            $rl = new RequestLogger;
            $rl->request = urldecode($input);
            $rl->response = json_encode($data['predictions']);
            $rl->save();
        }
        
        return response()->json($response);
    }

    private function formatOutput($input)
    {
        $return = [];

        if(!is_array($input) || count($input) < 1) {
            return $return;
        } 

        foreach($input as $in) {
            $return[] = [
                'id'    => $in['id'],
                'name'  => $in['description']
            ];
        }

        return $return;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UseCases\Card\Register;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\DefaultResponse;
use App\Integrations\Banking\Card\Find;

/* PONTO DE ATENÇÃO
    Dentro da função "show" há uma chamada direta na camada de integração,
    o que quebra o princípio de separação de responsabilidades, cria acoplamento e
    dificulta os testes unitários.
    
    O correto seria que esta chamada do Find fosse realizada na camada de UseCases.
*/

class CardController extends Controller
{
    /**
     * Exibe dados de um cartão
     *
     * POST api/users/{id}/card
     *
     * @return JsonResponse
     */
    public function show(string $userId): JsonResponse
    {
        $response = (new Find($userId))->handle();

        return $this->response(
            new DefaultResponse($response['data'])
        );
    }

    /**
     * Ativa um cartão
     *
     * POST api/users/{id}/card
     *
     * @return JsonResponse
     */
    public function register(string $userId, Request $request): JsonResponse
    {
        $response = (new Register($userId, $request->pin, $request->card_id))->handle();

        return $this->response(
            new DefaultResponse($response['data'])
        );
    }
}

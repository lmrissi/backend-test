<?php

namespace Tests\Feature\Account;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Http;
use Tests\Providers\Banking\AccountProvider;
use Symfony\Component\HttpFoundation\Response;

/* SUGESTÃO DE MELHORIAS
    O teste não cobre cenários de erro, apenas o caso de sucesso.
    Possíveis cenários de erro para adicionar:
        - Falha na autenticação
        - Erro na resposta do banking
        - O usuário não foi localizado
*/

class ShowTest extends TestCase
{
    /**
     * Teste de busca de conta
     *
     * @return void
     */
    public function testShow()
    {
        $user    = User::factory()->user()->create();
        $account = Account::factory()->registered()->create(
            [
                'user_id' => $user->id,
            ]
        );
        $token = $user->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $bankingResponse = app(AccountProvider::class)->balance();

        $urlAuth      = config('auth.banking_base_url') . 'auth/vexpenses/token';
        $urlRetrieve  = config('auth.banking_base_url') . "accounts/$account->external_id";

        Http::fake(
            [
                $urlAuth      => Http::response(
                    [],
                    Response::HTTP_OK
                ),
                $urlRetrieve  => Http::response(
                    $bankingResponse,
                    Response::HTTP_OK,
                ),
                '*'           => Http::response(
                    [],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ),
            ]
        );

        $response = $this->get("/api/users/$user->id/account", $headers);

        Http::assertSentInOrder(
            [
                $urlAuth,
                $urlRetrieve,
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'GET',
                'code'    => 200,
                'data'    => [
                    'balance' => $bankingResponse['balance'],
                ],
            ],
            true
        );
    }
}

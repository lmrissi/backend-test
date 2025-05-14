<?php

namespace Tests\Feature\Account;

use Tests\TestCase;
use App\Models\User;
use App\Models\Account;
use Illuminate\Support\Facades\Http;
use Tests\Providers\Banking\AccountProvider;
use Symfony\Component\HttpFoundation\Response;

/* SUGESTÃO DE MELHORIAS
    O token está sendo criado para todos os testes, seria interessante centralizar isso
    em um método auxiliar e ao final dos testes destruir esse token.

    *Essa sugestão é válida para todos os testes

    O teste não cobre cenários de erro, apenas o caso de sucesso.
    Possíveis cenários de erro para adicionar:
        - Falha na autenticação
        - Erro na resposta do banking
        - Conta já estava ativa
*/

class ActiveTest extends TestCase
{
    /**
     * Teste de desbloqueio de conta
     *
     * @return void
     */
    public function testActive()
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

        $bankingResponse = app(AccountProvider::class)->active();

        $urlAuth   = config('auth.banking_base_url') . 'auth/vexpenses/token';
        $urlActive = config('auth.banking_base_url') . "accounts/$account->external_id/status/active";

        Http::fake(
            [
                $urlAuth      => Http::response(
                    [],
                    Response::HTTP_OK
                ),
                $urlActive  => Http::response(
                    $bankingResponse,
                    Response::HTTP_OK,
                ),
                '*'           => Http::response(
                    [],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ),
            ]
        );

        $response = $this->put("/api/users/$user->id/account/active", [], $headers);

        Http::assertSentInOrder(
            [
                $urlAuth,
                $urlActive,
            ]
        );

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PUT',
                'code'    => 200,
                'data'    => null,
            ],
            true
        );
    }
}

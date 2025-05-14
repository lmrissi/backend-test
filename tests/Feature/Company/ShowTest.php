<?php

namespace Tests\Feature\Company;

use Tests\TestCase;
use App\Models\User;

/* SUGESTÃO DE MELHORIAS
    O teste não cobre cenários de erro, apenas o caso de sucesso.
    Possíveis cenários de erro para adicionar:
        - Falha na autenticação
        - Falha ao não encontrar companhia
*/

class ShowTest extends TestCase
{
    /**
     * Teste de busca de dados de empresa
     *
     * @return void
     */
    public function testShow()
    {
        $user    = User::factory()->user()->create();
        $company = $user->company;
        $token   = $user->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $response = $this->get('/api/company', $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'GET',
                'code'    => 200,
                'data'    => [
                    'id'   => $company->id,
                    'name' => $company->name,
                ],
            ],
            true
        );
    }
}

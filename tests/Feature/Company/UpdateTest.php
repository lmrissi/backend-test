<?php

namespace Tests\Feature\Company;

use Tests\TestCase;
use App\Models\User;

/* SUGESTÃO DE MELHORIAS
    O teste não cobre cenários de erro, apenas o caso de sucesso.
    Possíveis cenários de erro para adicionar:
        - Falha na autenticação
        - Falha no retorno do update
*/

class UpdateTest extends TestCase
{
    /**
     * Teste de modificação de empresa quando não autorizado
     *
     * @return void
     */
    public function testUpdateWhenUnauthorized()
    {
        $user  = User::factory()->user()->create();
        $token = $user->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [];

        $response = $this->patchJson('/api/company', $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => false,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => null,
                'errors'  => [
                    [
                        'code' => 146001003,
                    ]
                ]
            ],
            true
        );
        $response->assertJsonStructure(
            [
                'errors',
            ]
        );
    }

    /**
     * Teste de modificação de nome
     *
     * @return void
     */
    public function testUpdateName()
    {
        $user  = User::factory()->manager()->create();
        $token = $user->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [
            'name' => $this->faker->name,
        ];

        $response = $this->patchJson('/api/company', $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'   => $user->company_id,
                    'name' => $body['name'],
                ],
            ],
            true
        );

        $this->assertDatabaseHas(
            'companies',
            [
                'id'   => $user->company_id,
                'name' => $body['name'],
            ]
        );
    }
}

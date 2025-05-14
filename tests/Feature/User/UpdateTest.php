<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;

/* SUGESTÃO DE MELHORIAS
    O teste não cobre cenários de erro, apenas o caso de sucesso.
    Possíveis cenários de erro para adicionar:
        - Falha ao criar o usuário por erro no método validateUser
*/


class UpdateTest extends TestCase
{
    /**
     * Teste de modificação de usuário quando não autorizado
     *
     * @return void
     */
    public function testUpdateWhenUnauthorized()
    {
        $user1 = User::factory()->user()->create();
        $user2 = User::factory()->create(
            [
                'company_id' => $user1->company_id,
            ]
        );
        $token = $user1->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [];

        $response = $this->patchJson("/api/users/$user2->id", $body, $headers);

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
     * Teste de modificação de dados do próprio usuário logado
     *
     * @return void
     */
    public function testUpdateWhenSearchingAsLoggedUser()
    {
        $user = User::factory()->user()->create();
        $token = $user->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [];

        $response = $this->patchJson("/api/users/$user->id", $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'type'  => $user->type,
                ],
            ],
            true
        );
    }

    /**
     * Teste de modificação de dados quando usuário logado é gestor
     *
     * @return void
     */
    public function testUpdateWhenSearchingAsManager()
    {
        $user1 = User::factory()->manager()->create();
        $user2 = User::factory()->create(
            [
                'company_id' => $user1->company_id,
            ]
        );
        $token = $user1->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [];

        $response = $this->patchJson("/api/users/$user2->id", $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'    => $user2->id,
                    'name'  => $user2->name,
                    'email' => $user2->email,
                    'type'  => $user2->type,
                ],
            ],
            true
        );
    }

    /**
     * Teste de modificação de nome
     *
     * @return void
     */
    public function testUpdateName()
    {
        $user1 = User::factory()->manager()->create();
        $user2 = User::factory()->create(
            [
                'company_id' => $user1->company_id,
            ]
        );
        $token = $user1->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [
            'name' => $this->faker->name,
        ];

        $response = $this->patchJson("/api/users/$user2->id", $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'    => $user2->id,
                    'name'  => $body['name'],
                    'email' => $user2->email,
                    'type'  => $user2->type,
                ],
            ],
            true
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id'    => $user2->id,
                'name'  => $body['name'],
                'email' => $user2->email,
                'type'  => $user2->type,
            ]
        );
    }

    /**
     * Teste de modificação de email
     *
     * @return void
     */
    public function testUpdateEmail()
    {
        $user1 = User::factory()->manager()->create();
        $user2 = User::factory()->create(
            [
                'company_id' => $user1->company_id,
            ]
        );
        $token = $user1->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [
            'email' => $this->faker->email,
        ];

        $response = $this->patchJson("/api/users/$user2->id", $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'    => $user2->id,
                    'name'  => $user2->name,
                    'email' => $body['email'],
                    'type'  => $user2->type,
                ],
            ],
            true
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id'    => $user2->id,
                'name'  => $user2->name,
                'email' => $body['email'],
                'type'  => $user2->type,
            ]
        );
    }

    /**
     * Teste de modificação de tipo
     *
     * @return void
     */
    public function testUpdateType()
    {
        $user1 = User::factory()->manager()->create();
        $user2 = User::factory()->create(
            [
                'company_id' => $user1->company_id,
            ]
        );
        $token = $user1->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [
            'type' => $this->faker->randomElement(['USER', 'VIRTUAL', 'MANAGER']),
        ];

        $response = $this->patchJson("/api/users/$user2->id", $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'    => $user2->id,
                    'name'  => $user2->name,
                    'email' => $user2->email,
                    'type'  => $body['type'],
                ],
            ],
            true
        );

        $this->assertDatabaseHas(
            'users',
            [
                'id'    => $user2->id,
                'name'  => $user2->name,
                'email' => $user2->email,
                'type'  => $body['type'],
            ]
        );
    }

    /**
     * Teste de modificação de senha
     *
     * @return void
     */
    public function testUpdatePassword()
    {
        $user1 = User::factory()->manager()->create();
        $user2 = User::factory()->create(
            [
                'company_id' => $user1->company_id,
            ]
        );
        $token = $user1->createToken(config('auth.token_name'))->plainTextToken;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        $body = [
            'password' => $this->faker->word,
        ];

        $response = $this->patchJson("/api/users/$user2->id", $body, $headers);

        $response->assertStatus(200);
        $response->assertJson(
            [
                'success' => true,
                'method'  => 'PATCH',
                'code'    => 200,
                'data'    => [
                    'id'    => $user2->id,
                    'name'  => $user2->name,
                    'email' => $user2->email,
                    'type'  => $user2->type,
                ],
            ],
            true
        );

        $oldPassword = $user2->password;
        $newPassword = $user2->refresh()->password;

        $this->assertNotEquals($oldPassword, $newPassword);
    }
}

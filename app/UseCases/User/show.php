<?php

namespace App\UseCases\User;

use Throwable;
use App\UseCases\BaseUseCase;
use App\Repositories\User\Find;

/* PONTO DE ATENÇÃO
    Utilizar nomes mais descritivos para as variáveis "a" e "b"
 */

/* SUGESTÃO DE MELHORIA
    Alterar o nome do arquivo para "Show", para manter o padrão com letra maiúscula.
 */

class show extends BaseUseCase
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $a;

    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $b;

    /**
     * Usuário
     *
     * @var array|null
     */
    protected ?array $c;

    public function __construct(string $a, string $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    /**
     * Encontra o usuário
     *
     * @return void
     */
    protected function find(): void
    {
        $this->c = (new Find($this->a, $this->b))->handle();
    }

    /**
     * Retorna usuário, se encontrado
     */
    public function handle(): ?array
    {
        try {
            $this->find();
        } catch (Throwable $th) {
            $this->defaultErrorHandling(
                $th,
                [
                    'a' => $this->a,
                    'b' => $this->b,
                ]
            );
        }

        return $this->c;
    }
}

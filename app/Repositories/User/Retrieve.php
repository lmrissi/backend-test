<?php

namespace App\Repositories\User;

use App\Models\User;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;

/* PONTO DE ATENÇÃO
    Não utilizar interpolação na query com SQL puro, isso expõe o código a SQL Injection.
    Se possível utilizar uma ORM, para evitar isso.
*/

/* SUGESTÃO DE MELHORIA
    Separar os filtros realizadas no handle em funções auxiliares.

    Armazenar o valor INACTIVE em um enum
*/

class Retrieve extends BaseRepository
{
    /**
     * Id da empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Name
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * Email
     *
     * @var string|null
     */
    protected ?string $email;

    /**
     * Status
     *
     * @var string|null
     */
    protected ?string $status;

    /**
     * Setar a model do usuário
     *
     * @return void
     */
    public function setModel(): void
    {
        $this->model = User::class;
    }

    public function __construct(string $companyId, ?string $name, ?string $email, ?string $status)
    {
        $this->companyId = $companyId;
        $this->name      = $name;
        $this->email     = $email;
        $this->status    = $status;

        parent::__construct();
    }

    /**
     * Left join com accounts
     *
     * @return void
     */
    protected function leftJoinAccount(): void
    {
        $this->builder->leftJoin(
            'accounts',
            'accounts.user_id',
            '=',
            'users.id'
        );
    }

    /**
     * Lista de usuários (Paginado)
     *
     * @return LengthAwarePaginator
     */
    public function handle(): LengthAwarePaginator
    {
        $this->leftJoinAccount();

        if ($this->name) {
            $this->builder->whereRaw("name LIKE '%" . $this->name . "%'");
        }

        if ($this->email) {
            $this->builder->whereRaw("email LIKE '%" . $this->email . "%'");
        }

        if ($this->status) {
            if ($this->status === 'INACTIVE') {
                $this->builder->whereRaw('accounts.id IS NULL');
            } else {
                $this->builder->whereRaw('accounts.status = "' . $this->status . '"');
            }
        }

        $this->builder->where('company_id', $this->companyId)
            ->orderBy('name');

        return $this->paginate(['users.*']);
    }
}

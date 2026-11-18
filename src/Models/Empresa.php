<?php

class Empresa
{
    private int $id;
    private string $nome;
    private string $setor;
    private string $porteEmpresa;
    private string $email;
    private string $site;
    private int $telefone;
    private string $senha;
    private string $cidadeSede;
    private int $cnpj;

    public function __construct(
        int $id,
        string $nome,
        string $setor,
        string $porteEmpresa,
        string $email,
        string $site,
        int $telefone,
        string $senha,
        string $cidadeSede,
        int $cnpj
    ) {
        $this->id = $id;
        $this->nome = $nome;
        $this->setor = $setor;
        $this->porteEmpresa = $porteEmpresa;
        $this->email = $email;
        $this->site = $site;
        $this->telefone = $telefone;
        $this->senha = $senha;
        $this->cidadeSede = $cidadeSede;
        $this->cnpj = $cnpj;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getNome(): string
    {
        return $this->nome;
    }

    public function setNome(string $nome): void
    {
        $this->nome = $nome;
    }

    public function getSetor(): string
    {
        return $this->setor;
    }

    public function setSetor(string $setor): void
    {
        $this->setor = $setor;
    }

    public function getPorteEmpresa(): string
    {
        return $this->porteEmpresa;
    }

    public function setPorteEmpresa(string $porteEmpresa): void
    {
        $this->porteEmpresa = $porteEmpresa;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function setSite(string $site): void
    {
        $this->site = $site;
    }

    public function getTelefone(): int
    {
        return $this->telefone;
    }

    public function setTelefone(int $telefone): void
    {
        $this->telefone = $telefone;
    }

    public function getSenha(): string
    {
        return $this->senha;
    }

    public function setSenha(string $senha): void
    {
        $this->senha = $senha;
    }

    public function getCidadeSede(): string
    {
        return $this->cidadeSede;
    }

    public function setCidadeSede(string $cidadeSede): void
    {
        $this->cidadeSede = $cidadeSede;
    }

    public function getCnpj(): int
    {
        return $this->cnpj;
    }

    public function setCnpj(int $cnpj): void
    {
        $this->cnpj = $cnpj;
    }
}